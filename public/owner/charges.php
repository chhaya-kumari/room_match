<?php

$path = $_SERVER['DOCUMENT_ROOT'];

require_once $path . '/pg_recommendation/config/fileautoloader.php';

SessionManager::start();
AuthMiddleware::requireRole('owner');

$ownerId = SessionManager::userId();

$db = new Database();
$chargeService = new ChargeService($db->conn);
$authorizationService = new AuthorizationService($db->conn);

$success = SessionManager::getFlash('success'); // <-- Change this line to check flash storage
$error = SessionManager::getFlash('error') ?: null;     // <-- Optional: catch errors sent via flash too

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  try {
    $pgId = filter_input(
      INPUT_GET,
      'pg_id',
      FILTER_VALIDATE_INT
    );

    if (!$pgId) {
      header('Location: /pg_recommendation/public/owner/create-pg.php');
      exit;
    }

    $authorizationService->verifyPGOwnership($pgId, $ownerId);
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (InvalidArgumentException $e) {
    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (Throwable $e) {

    error_log('Charge creation failed: ' . $e->getMessage());

    $error = 'Unable to process your request. Please try again.';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $pgId = filter_input(INPUT_POST, 'pg_id', FILTER_VALIDATE_INT);

  try {
    if (!$pgId) {
      throw new InvalidArgumentException('Invalid PG ID.');
    }

    // csrf validation

    if (!Token::verifyToken($_POST['csrf_token'] ?? '')) {
      http_response_code(403);
      error_log('CSRF token validation failed.');
      echo "<h1>403 Forbidden</h1><p>CSRF token validation failed.</p>";
      exit;
    }

    // check if the PG exists and belongs to the owner

    $authorizationService->verifyPGOwnership($pgId, $ownerId);

    // form submitted values

    $pgAmenityId = $_POST['pg_amenity_id'] ?? null;
    $chargeName = trim($_POST['charge_name'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $chargeBasis = $_POST['charge_basis'] ?? '';
    $unit = trim($_POST['unit'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // convert optional values to null

    $pgAmenityId = $pgAmenityId === '' ? null : (int)$pgAmenityId;

    $description = $description === '' ? null : $description;

    $unit = $unit === '' ? null : $unit;

    $chargeId = $chargeService->createCharge(
      $pgId,
      $ownerId,
      $pgAmenityId,
      $chargeName,
      $amount,
      $chargeBasis,
      $unit,
      $description
    );

    SessionManager::setFlash('success', 'Charges added successfully.');

    header('Location: /pg_recommendation/public/owner/charges.php?pg_id=' . $pgId);

    exit;
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (InvalidArgumentException $e) {
    $error = $e->getMessage();
  } catch (Throwable $e) {

    error_log('Charge creation failed: ' . $e->getMessage());

    $error = 'Unable to create charge. Please try again.';
  }
}

// load page data
if (!empty($pgId)) {
  try {
    $charges =
      $chargeService->getChargesByPgId($pgId, $ownerId);

    $separateAmenities =
      $chargeService->getAvailableSeparateAmenities($pgId, $ownerId);
  } catch (AuthorizationException $e) {

    SessionManager::setFlash(
      'error',
      $e->getMessage()
    );

    header(
      'Location: /pg_recommendation/public/owner/dashboard.php'
    );

    exit;
  } catch (InvalidArgumentException $e) {

    SessionManager::setFlash(
      'error',
      $e->getMessage()
    );

    header(
      'Location: /pg_recommendation/public/owner/dashboard.php'
    );

    exit;
  } catch (Throwable $e) {

    error_log(
      'Charge page data loading failed: '
        . $e->getMessage()
    );

    $error =
      'Unable to load charges. Please try again.';
  }
}
require_once $path . '/pg_recommendation/views/owner/charge-form.php';
