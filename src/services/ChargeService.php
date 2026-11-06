<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

class ChargeService
{
  private Charge $chargeModel;
  private Amenity $amenityModel;
  private AuthorizationService $authorizationService;

  public function __construct(PDO $db)
  {

    $this->chargeModel = new Charge($db);
    $this->amenityModel = new Amenity($db);
    $this->authorizationService = new AuthorizationService($db);
  }

  private function validatePgAmenityId(int $pgId, ?int $pgAmenityId)
  {
    if ($pgAmenityId === null) {
      return null; // No pgAmenityId provided, so it's valid
    }

    $separateAmenities = $this->amenityModel->findSeparateChargeAmenitiesByPgId($pgId);

    foreach ($separateAmenities as $amenity) {
      if ((int) $amenity['pg_amenity_id'] === $pgAmenityId) {
        return $amenity; // Valid pgAmenityId
      }
    }

    throw new InvalidArgumentException('The selected amenity is not configured as a separately billed amenity for this PG.');
  }

  public function getChargesByPgId(int $pgId, int $ownerId)
  {

    $this->authorizationService->verifyPGOwnership($pgId, $ownerId);

    return $this->chargeModel->findByPgId($pgId);
  }

  public function getSeparateAmenities(int $pgId, int $ownerId)
  {
    $this->authorizationService->verifyPGOwnership($pgId, $ownerId);
    return $this->amenityModel->findSeparateChargeAmenitiesByPgId($pgId);
  }

  public function getAvailableSeparateAmenities(
    int $pgId,
    int $ownerId
  ): array {
    $this->authorizationService->verifyPGOwnership($pgId, $ownerId);

    $separateAmenities =
      $this->amenityModel->findSeparateChargeAmenitiesByPgId($pgId);

    $usedAmenityIds =
      $this->chargeModel->findUsedPgAmenityIdsByPgId($pgId);

    return array_values(array_filter(
      $separateAmenities,
      fn($amenity) =>
      !in_array(
        (int) $amenity['pg_amenity_id'],
        $usedAmenityIds,
        true
      )
    ));
  }
  // public function getChargeById(int $chargeId, int $pgId, $ownerId)
  // {
  //   $authorizationService = new AuthorizationService($this->db);
  //   $authorizationService->verifyPGOwnership($pgId, $ownerId);

  //   $charge = $this->chargeModel->findById($chargeId);

  //   if (!$charge) {
  //     throw new InvalidArgumentException('Charge not found.');
  //   }

  //   if ((int)$charge['pg_id'] !== $pgId) {
  //     throw new AuthorizationException(
  //       'This charge does not belong to the selected PG.'
  //     );
  //   }

  //   return $charge;
  // }

  public function createCharge(int $pgId, int $ownerId, ?int $pgAmenityId, string $chargeName, float $amount, string $chargeBasis, ?string  $unit, ?string $description)
  {
    // Check if the PG exists and belongs to the owner

    $this->authorizationService->verifyPGCanBeEdited($pgId, $ownerId);

    //Amenity based charges validation

    if ($pgAmenityId !== null) {

      $amenity = $this->validatePgAmenityId($pgId, $pgAmenityId);

      // derive charge name from the selected amentiy
      $chargeName = $amenity['amenity_name'];
    } else {
      // Independent charge
      ChargeValidator::chargeName($chargeName);
    }

    // fixed monthly charges don't require a unit;
    if ($chargeBasis === 'fixed_monthly') {
      $unit = null;
    }

    ChargeValidator::validate($amount, $chargeBasis, $unit, $description);

    // Create the charge
    return $this->chargeModel->create($pgId, $pgAmenityId, $chargeName, $amount, $chargeBasis, $unit, $description);
  }

  public function updateCharge(int $chargeId, int $ownerId, string $chargeName, float  $amount, string $chargeBasis, ?string $unit, ?string $description)
  {
    // Check if the PG exists and belongs to the owner
    $charge = $this->authorizationService->verifyChargeOwnership($chargeId, $ownerId);
    $this->authorizationService->verifyPGCanBeEdited($charge['pg_id'], $ownerId);

    //Amenity based charges : names cannot be updated.
    if ($charge['pg_amenity_id'] !== null) {
      $chargeName = $charge['charge_name'];
    } else {
      ChargeValidator::chargeName($chargeName);
    }

    // fixed monthly charges don't require a unit;   
    if ($chargeBasis === 'fixed_monthly') {
      $unit = null;
    }

    ChargeValidator::validate($amount, $chargeBasis, $unit, $description);

    // Update the charge
    return $this->chargeModel->update($chargeId, $chargeName, $amount, $chargeBasis, $unit, $description);
  }

  public function deleteCharge(int $ownerId, int $chargeId)
  {
    // Check if the PG exists and belongs to the owner
    $charge = $this->authorizationService->verifyChargeOwnership($chargeId, $ownerId);
    $this->authorizationService->verifyPGCanBeEdited($charge['pg_id'], $ownerId);

    // Delete the charge
    return $this->chargeModel->delete($chargeId);
  }
}
