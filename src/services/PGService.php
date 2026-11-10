<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

class PGService
{
  private PG $pgModel;
  private Location $locationModel;
  private AuthorizationService $authorizationService;

  public function __construct(PDO $db)
  {
    $this->pgModel = new PG($db);
    $this->locationModel = new Location($db);
    $this->authorizationService = new AuthorizationService($db);
  }

  public function createPG(int $ownerId, string $pgName, string $pgAddress, int $locationId, GeoCodeService $geoCodeService)
  {
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
    // validate pg name and address
    PGValidator::validateCreate($pgName, $pgAddress);

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
  }

  public function getPG(int $pgId, int $ownerId)
  {
    if ($pgId <= 0) {
      throw new InvalidArgumentException(
        'Invalid PG ID.'
      );
    }

    $this->authorizationService->verifyPGOwnership($pgId, $ownerId);

    return $this->pgModel->findById($pgId);
  }

  public function updatePGDetails(int $pgId, int $ownerId, ?string $description, string $genderPreference, ?string $rules, ?int $noticePeriodDays, ?int $lockInPeriodMonths): bool
  {
    // Validation
    $this->authorizationService->verifyPGCanBeEdited($pgId, $ownerId);
    PGValidator::validateDetails($description, $genderPreference, $rules, $noticePeriodDays, $lockInPeriodMonths);

    return $this->pgModel->updateDetails(
      $pgId,
      $ownerId,
      $description,
      $genderPreference,
      $rules,
      $noticePeriodDays,
      $lockInPeriodMonths
    );
  }
}
