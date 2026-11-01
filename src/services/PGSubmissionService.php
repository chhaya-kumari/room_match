<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

class PGSubmissionService
{
  private PG $pgModel;
  private Room $roomModel;
  private Amenity $amenityModel;
  private Charge $chargeModel;
  private PGImage $imageModel;
  private AuthorizationService $authorizationService;

  public function __construct(PDO $db)
  {
    $this->pgModel = new PG($db);
    $this->roomModel = new Room($db);
    $this->amenityModel = new Amenity($db);
    $this->chargeModel = new Charge($db);
    $this->imageModel = new PGImage($db);
    $this->authorizationService = new AuthorizationService($db);
  }

  public function getReviewSummary(int $pgId, int $ownerId): array
  {
    $this->authorizationService->verifyPGOwnership($pgId, $ownerId);

    $pg = $this->pgModel->findReviewDetailsById($pgId);

    if ($pg === null) {
      throw new InvalidArgumentException(
        'PG not found.'
      );
    }

    $rooms = $this->roomModel->findByPgId($pgId);
    $amenities = $this->amenityModel->findSelectedForReview($pgId);
    $separateChargeAmenities = $this->amenityModel->findSeparateChargeAmenitiesByPgId($pgId);
    $charges = $this->chargeModel->findByPgId($pgId);
    $images = $this->imageModel->findByPgId($pgId);

    $errors = [];

    // required pg info

    if (trim((string)($pg['pg_name'] ?? '')) === '') {
      $errors[] = 'PG name is required.';
    }

    $address = trim((string)($pg['address'] ?? ''));
    $locationId = (int)($pg['location_id'] ?? 0);

    if ($address === '') {
      $errors[] = 'PG address is required.';
    }

    if ($locationId <= 0) {
      $errors[] = 'A valid PG location is required.';
    }

    // at least one available bed

    $hasAvailableBed = false;

    foreach ($rooms as $room) {
      if ((int)($room['available_beds'] ?? 0) > 0) {
        $hasAvailableBed = true;
        break;
      }
    }

    if (!$hasAvailableBed) {
      $errors[] = 'Add at least one room with an available bed.';
    }

    // at least one amenity must be selected.
    if (empty($amenities)) {
      $errors[] = 'Select at least one amenity for this PG.';
    }

    // every seperately billed amenity must have valid saved charge.

    $chargesByAmenity = [];

    foreach ($charges as $charge) {
      if ($charge['pg_amenity_id'] !== null) {
        $pgAmenityId = (int)$charge['pg_amenity_id'];
        $chargesByAmenity[$pgAmenityId][] = $charge;
      }
    }

    foreach ($separateChargeAmenities as $amenity) {
      $pgAmenityId = (int)$amenity['pg_amenity_id'];
      $amenityCharges = $chargesByAmenity[$pgAmenityId] ?? [];
      $hasValidCharge = false;

      foreach ($amenityCharges as $charge) {
        if ($this->isValidSavedCharge($charge)) {
          $hasValidCharge = true;
          break;
        }
      }

      if (!$hasValidCharge) {
        $amenityName = trim((string)($amenity['amenity_name'] ?? ''));

        $errors[] = $amenityName !== ''
          ? "Add a valid charge for the separately billed amenity: {$amenityName}."
          : 'Add a valid charge for each separately billed amenity.';
      }
    }

    // atleast one property image must be uploaded.
    if (empty($images)) {
      $errors[] = 'Upload at least one image of this PG.';
    }

    $hasOptionalDetails =
      trim((string)($pg['description'] ?? '')) !== '' ||
      trim((string)($pg['rules'] ?? '')) !== '' ||
      ($pg['notice_period_days'] ?? null) !== null ||
      ($pg['lock_in_period_months'] ?? null) !== null;

    return [
      'pg' => $pg,
      'rooms' => $rooms,
      'amenities' => $amenities,
      'separate_charge_amenities' => $separateChargeAmenities,
      'charges' => $charges,
      'images' => $images,
      'errors' => $errors,
      'is_ready' => empty($errors),
      'is_draft' => ($pg['listing_status'] ?? '') === 'draft',
      'has_optional_details' => $hasOptionalDetails,
    ];
  }

  /**
   * Reuse ChargeValidator so readiness checks do not maintain a second
   * copy of the charge amount, basis, unit and description rules.
   */
  private function isValidSavedCharge(array $charge)
  {
    try {
      ChargeValidator::chargeName($charge['charge_name'] ?? '');

      $unit = $charge['unit'] ?? null;
      $description = $charge['description'] ?? null;

      ChargeValidator::validate(
        (float)($charge['amount'] ?? 0),
        $charge['charge_basis'] ?? '',
        $unit,
        $description
      );

      return true;
    } catch (InvalidArgumentException $e) {
      // An invalid saved charge does not satisfy submission readiness.
      return false;
    }
  }
  /** 
   * Re-check readiness immediately before changing the listing status.
   * @return array{submitted: bool, errors: array}
   */

  public function submitPG(int $pgId, int $ownerId): array
  {
    $summary = $this->getReviewSummary($pgId, $ownerId);

    if (!$summary['is_draft']) {
      throw new InvalidArgumentException(
        'Only draft PG listings can be submitted for review.'
      );
    }

    if (!$summary['is_ready']) {
      return [
        'submitted' => false,
        'errors' => $summary['errors'],
      ];
    }

    if (!$this->pgModel->submitForReview($pgId, $ownerId)) {
      throw new RuntimeException(
        'The PG could not be submitted. Refresh the page and try again.'
      );
    }

    return [
      'submitted' => true,
      'errors' => [],
    ];
  }
}
