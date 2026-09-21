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

  public function __construct(PDO $db)
  {
    $this->db = $db;
    $this->pgModel = new PG($db);
    $this->locationModel = new Location($db);
  }

  public function createPG($ownerId, $pgName, $pgAddress, $locationId, GeoCodeService $geoCodeService)
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
      $coordinates = $geoCodeService->geocode($pgAddress);

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

  public function updatePGDetails($pgId, $ownerId, $description, $gender_preference, $rules, $notice_period_days, $lock_in_period_months)
  {
    // Validation

    if ($pgId <= 0) {
      throw new InvalidArgumentException('Invalid Pg');
    }

    if ($ownerId <= 0) {
      throw new InvalidArgumentException('Invalid owner');
    }

    $pg = $this->getPG($pgId);

    if ($pg === null || (int)$pg['owner_id'] !== (int)$ownerId) {
      throw new InvalidArgumentException(
        'PG not found or you are not authorized to edit it'
      );
    }

    $allowedGenderPreference = ['male', 'female', 'co-ed'];

    if (!in_array($gender_preference, $allowedGenderPreference, true)) {
      throw new InvalidArgumentException(
        'Invalid gender preference.'
      );
    }

    if ($description !== null && strlen($description) > 2000) {
      throw new InvalidArgumentException(
        'Description cannot exceed 2000 characters.'
      );
    }

    if ($rules !== null && strlen($rules) > 3000) {
      throw new InvalidArgumentException(
        'Rules cannot exceed 3000 characters.'
      );
    }

    if (
      $notice_period_days !== null &&
      $notice_period_days < 0
    ) {
      throw new InvalidArgumentException(
        'Invalid notice period.'
      );
    }

    if (
      $lock_in_period_months !== null &&
      $lock_in_period_months < 0
    ) {
      throw new InvalidArgumentException(
        'Invalid lock-in period.'
      );
    }

    return $this->pgModel->updateDetails(
      $pgId,
      $ownerId,
      $description,
      $gender_preference,
      $rules,
      $notice_period_days,
      $lock_in_period_months
    );
  }
}
