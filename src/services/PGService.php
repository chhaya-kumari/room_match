<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/src/models/PG.php';
require_once $path . '/pg_recommendation/src/services/GeoCodeService.php';
require_once $path . '/pg_recommendation/src/models/Location.php';

class PGService
{
  private $db;
  private PG $pgModel;
  private Location $locationModel;
  private GeoCodeService $geoCodeService;

  public function __construct(PDO $db, $geoCodeBaseUrl, $geoCodeToken)
  {
    $this->db = $db;
    $this->pgModel = new PG($db);
    $this->locationModel = new Location($db);
    $this->geoCodeService = new GeoCodeService($geoCodeBaseUrl, $geoCodeToken);
  }

  public function createPG($ownerId, $pgName, $pgAddress, $locationId)
  {
    try {
      if ($ownerId <= 0) {
        throw new InvalidArgumentException(
          'Invalid Owner.'
        );
      }

      // validate location 

      if ($locationId <= 0) {
        throw new InvalidArgumentException(
          'Invalid location.'
        );
      }

      $location = $this->locationModel->findByLocationId($locationId);

      if ($location === null) {
        throw new InvalidArgumentException(
          'Selected location does not exist.'
        );
      }
      // validate pg name

      if ($pgName === '') {
        throw new InvalidArgumentException(
          'PG name is required.'
        );
      }

      if (strlen($pgName) > 150) {
        throw new InvalidArgumentException(
          'PG name is too long.'
        );
      }
      // validate pgAdress

      if ($pgAddress === '') {
        throw new InvalidArgumentException(
          'PG address is required.'
        );
      }

      // GeoCode PG Address
      $coordinates = $this->geoCodeService->geocode($pgAddress);

      $latitude = $coordinates['latitude'];
      $longitude = $coordinates['longitude'];

      // //create Pg
      return $this->pgModel->create(
        $ownerId,
        $locationId,
        $pgName,
        $pgAddress,
        $latitude,
        $longitude
      );
    } catch (Throwable $e) {
      error_log(
        'PG creation failed : ' . $e->getMessage()
      );
      throw $e;
    }
  }


  public function getPG($pgId)
  {
    if ($pgId <= 0) {
      throw new InvalidArgumentException(
        'Invalid PG ID.'
      );
    }
    return $this->pgModel->findById($pgId);
  }
}
