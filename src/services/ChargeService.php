<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/src/models/Charge.php';
require_once $path . '/pg_recommendation/src/models/Amenity.php';
require_once $path . '/pg_recommendation/src/services/AuthorizationService.php';

class ChargeService
{
  private PDO $db;
  private Charge $chargeModel;
  private Amenity $amenityModel;

  public function __construct(PDO $db)
  {
    $this->db = $db;
    $this->chargeModel = new Charge($db);
    $this->amenityModel = new Amenity($db);
  }

  private function validatePgAmenityId(int $pgId, ?int $pgAmenityId)
  {
    if ($pgAmenityId === null) {
      return null; // No pgAmenityId provided, so it's valid
    }

    $separateAmenities = $this->amenityModel->findSeparateChargeAmenitiesByPgId($pgId);

    foreach ($separateAmenities as $amenity) {
      if ($amenity['pg_amenity_id'] === $pgAmenityId) {
        return $amenity; // Valid pgAmenityId
      }
    }

    throw new RuntimeException('The selected amenity is not configured as a separately billed amenity for this PG.');
  }

  public function getChargesByPgId(int $pgId, $ownerId)
  {
    $authorizationService = new AuthorizationService($this->db);
    $authorizationService->verifyPGOwnership($pgId, $ownerId);

    return $this->chargeModel->findByPgId($pgId);
  }

  public function getSeparateAmenities(int $pgId, $ownerId)
  {
    $authorizationService = new AuthorizationService($this->db);
    $authorizationService->verifyPGOwnership($pgId, $ownerId);
    return $this->amenityModel->findSeparateChargeAmenitiesByPgId($pgId);
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

  public function createCharge($pgId, $ownerId, $pgAmenityId, $chargeName, $amount, $chargeBasis, $unit, $description)
  {
    // Check if the PG exists and belongs to the owner
    $authorizationService = new AuthorizationService($this->db);
    $authorizationService->verifyPGOwnership($pgId, $ownerId);

    //Amenity based charges validation

    if ($pgAmenityId !== null) {

      $amenity = $this->validatePgAmenityId($pgId, $pgAmenityId);

      // derive charge name from the selected amentiy
      $chargeName = $amenity['amenity_name'];
    } else {
      // Independent charge
      ChargeValidator::chargeName($chargeName);
    }

    ChargeValidator::validate($amount, $chargeBasis, $unit, $description);

    // fixed monthly charges don't require a unit;

    if ($chargeBasis === 'fixed_monthly') {
      $unit = null;
    }

    // Create the charge

    return $this->chargeModel->create($pgId, $pgAmenityId, $chargeName, $amount, $chargeBasis, $unit, $description);
  }

  public function updateCharge($chargeId, $ownerId, $chargeName, $amount, $chargeBasis, $unit, $description)
  {
    // Check if the PG exists and belongs to the owner
    $authorizationService = new AuthorizationService($this->db);
    $charge = $authorizationService->verifyChargeOwnership($chargeId, $ownerId);

    //Amenity based charges : names cannot be updated.
    if ($charge['pg_amenity_id'] !== null) {
      $chargeName = $charge['charge_name'];
    } else {
      ChargeValidator::chargeName($chargeName);
    }

    ChargeValidator::validate($amount, $chargeBasis, $unit, $description);

    // fixed monthly charges don't require a unit;
    if ($chargeBasis === 'fixed_monthly') {
      $unit = null;
    }

    // Update the charge
    return $this->chargeModel->update($chargeId, $chargeName, $amount, $chargeBasis, $unit, $description);
  }

  public function deleteCharge($ownerId, $chargeId)
  {
    // Check if the PG exists and belongs to the owner
    $authorizationService = new AuthorizationService($this->db);
    $authorizationService->verifyChargeOwnership($chargeId, $ownerId);

    // Delete the charge
    return $this->chargeModel->delete($chargeId);
  }
}
