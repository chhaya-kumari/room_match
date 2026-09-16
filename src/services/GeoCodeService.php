<?php

class GeoCodeService
{
  private $baseUrl;
  private $token;

  public function __construct($baseUrl, $token)
  {
    $this->baseUrl = rtrim($baseUrl, '/');
    $this->token = trim($token);

    if ($this->token === '') {
      throw new RuntimeException('Delhivery Maps token is not configured.');
    }
  }

  public function geocode($pgAddress)
  {
    $address = trim($pgAddress);
    if ($address === '') {
      throw new InvalidArgumentException(
        'Address is required for geocode'
      );
    }

    $url = $this->baseUrl . '/geocode';
    $payload = ['address' =>  $pgAddress];

    $ch = curl_init($url);

    if ($ch === false) {
      throw new RuntimeException(
        'Unable to initialize geocoding request.'
      );
    }

    curl_setopt_array($ch, [
      CURLOPT_POST => true,

      CURLOPT_RETURNTRANSFER => true,

      CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $this->token,
        'Content-Type: application/json',
        'Accept: application/json'
      ],

      CURLOPT_POSTFIELDS => json_encode($payload),

      CURLOPT_TIMEOUT => 15,

      CURLOPT_CONNECTTIMEOUT => 5
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
      $error = curl_error($ch);
      curl_close($ch);

      throw new RuntimeException(
        'unable to connect to Delhivery Map'
      );
    }

    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $data = json_decode($response, true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
      throw new RuntimeException(
        'Delhivery Maps returned invalid JSON.'
      );
    }

    if ($httpStatus < 200 || $httpStatus >= 300) {

      $message = $data['message']
        ?? $data['error']
        ?? 'Delhivery Maps request failed.';

      throw new RuntimeException(
        $message . ' HTTP Status: ' . $httpStatus
      );
    }

    if (
      !isset($data['lat']) ||
      !isset($data['lng'])
    ) {
      throw new RuntimeException(
        'Delhivery Maps response did not contain coordinates.'
      );
    }

    $latitude = (float) $data['lat'];
    $longitude = (float) $data['lng'];

    if (
      $latitude < -90 ||
      $latitude > 90 ||
      $longitude < -180 ||
      $longitude > 180
    ) {
      throw new RuntimeException(
        'Delhivery Maps returned invalid coordinates.'
      );
    }

    return [
      'latitude' => $latitude,
      'longitude' => $longitude
    ];
  }
}
