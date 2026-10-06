<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/src/models/PG.php';
require_once $path . '/pg_recommendation/src/models/Amenity.php';
require_once $path . '/pg_recommendation/src/validators/AmenityValidator.php';
require_once $path . '/pg_recommendation/src/services/AuthorizationService.php';

class AmenityService
{
  private Amenity $amenityModel;
  private AuthorizationService $authorizationService;
  public function __construct(PDO $db)
  {
    $this->amenityModel = new Amenity($db);
    $this->authorizationService = new AuthorizationService($db);
  }

  public function getAllAmenities()
  {
    return $this->amenityModel->findAll();
  }

  public function getPgAmenities(int $pgId, int $ownerId)
  {
    $this->authorizationService->verifyPGOwnership($pgId, $ownerId);
    return $this->amenityModel->findByPgId($pgId);
  }

  public function updatePgAmenities($pgId, $ownerId, $amenities)
  {
    // verify PG ownership
    $this->authorizationService->verifyPGOwnership($pgId, $ownerId);

    // validate submited values
    AmenityValidator::validateSelection($amenities);

    // extract submitted amenity ids
    $amenityIds = array_map('intval', array_keys($amenities));

    // verify amenities actually exists
    $existingIds = array_map('intval', $this->amenityModel->findExistingIds($amenityIds));

    // find invalid amenity id

    $invalidIds = array_diff($amenityIds,  $existingIds);

    if (!empty($invalidIds)) {
      throw new InvalidArgumentException(
        'One or more selected amenitites are invalid.'
      );
    }

    // replace pg amenities
    $this->amenityModel->replaceForPg($pgId, $amenities);
  }
}
