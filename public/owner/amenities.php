<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/src/services/AuthorizationService.php';
require_once $path . '/pg_recommendation/src/validators/AmenityValidator.php';
require_once $path . '/pg_recommendation/src/middleware/AuthMiddleware.php';
require_once $path . '/pg_recommendation/src/services/SessionManager.php';
require_once $path . '/pg_recommendation/src/services/AmenityService.php';
require_once $path . '/pg_recommendation/src/security/Csrf.php';
require_once $path . '/pg_recommendation/config/db.php';
require_once $path . '/pg_recommendation/src/models/Amenity.php';
require_once $path . '/pg_recommendation/src/exceptions/AuthorizationException.php';

SessionManager::start();
AuthMiddleware::requireRole('owner');

$error = null;
$success = SessionManager::getFlash('success');

$ownerId = SessionManager::userId();

$database = new Database();
$amenityService = new AmenityService($database->conn);
$authorizationService = new AuthorizationService($database->conn);

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
  try {
    // Get PG ID from URL
    $pgId = filter_input(
      INPUT_GET,
      'pg_id',
      FILTER_VALIDATE_INT
    );

    if (!$pgId) {
      header('Location: /pg_recommendation/public/owner/create-pg.php');
      exit;
    }

    $authorizationService->verifyPGOwnership($pgId,  $ownerId);

    $amenities = $amenityService->getAllAmenities();
    $selectedRows = $amenityService->getPgAmenities($pgId, $ownerId);
    $selectedAmenities = [];

    foreach ($selectedRows as $row) {
      $selectedAmenities[$row['amenity_id']] = (int)$row['included_in_rent'];
    }
  } catch (Throwable $e) {
    error_log(
      'Amenities retrieval failed: ' . $e->getMessage()
    );

    $error = $e->getMessage();
  }
}

// post
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {

  $pgId = filter_input(
    INPUT_POST,
    'pg_id',
    FILTER_VALIDATE_INT
  );

  try {

    if (!$pgId) {
      throw new InvalidArgumentException(
        'Invalid PG ID.'
      );
    }

    if (!Token::verifyToken($_POST['csrf_token'] ?? '')) {
      http_response_code(403);
      error_log('CSRF token validation failed.');
      echo "<h1>403 Forbidden</h1><p>CSRF token validation failed.</p>";
      exit;
    }

    $submittedAmenities = $_POST['amenities'] ?? [];

    if (!is_array($submittedAmenities)) {
      throw new InvalidArgumentException(
        'Invalid amenities data.'
      );
    }

    $amenities = [];

    foreach ($submittedAmenities as $amenityId => $data) {
      if (!is_array($data) || !isset($data['selected'])) {
        continue;
      }

      if ($data['selected'] !== '1') {
        continue;
      }

      $amenities[$amenityId] = [
        'included_in_rent' => $data['included_in_rent'] ?? '1'
      ];
    }

    $amenityService->updatePgAmenities($pgId, $ownerId, $amenities);
    SessionManager::setFlash('success', 'Amenities updated successfully.');

    header(
      'Location: charges.php?pg_id=' . $pgId
    );

    exit;
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (InvalidArgumentException $e) {
    $error = $e->getMessage();
  } catch (Throwable $e) {
    error_log(
      'Amenities update failed: ' . $e->getMessage()
    );

    $error = 'Unable to update amenities. Please try again.';
  }
}

require_once $path . '/pg_recommendation/views/owner/amenity-form.php';
