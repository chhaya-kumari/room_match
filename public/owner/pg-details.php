<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

$serviceConfig = require_once $path . '/pg_recommendation/config/services.php';

SessionManager::start();
AuthMiddleware::requireRole('owner');

$ownerId = SessionManager::userId();

$error = null;
$success = null;

// display flash message
$success = SessionManager::getFlash('success');

$database = new Database();
$delhiveryConfig = $serviceConfig['delhivery_maps'] ?? null;
$pgService = new PGService($database->conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

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

    $authorizationService = new AuthorizationService($database->conn);
    $authorizationService->verifyPGOwnership($pgId, $ownerId);

    // Get PG through service
    $pg = $pgService->getPG($pgId, $ownerId);
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (InvalidArgumentException $e) {
    // safe validation errors
    $error = $e->getMessage();
  } catch (Throwable $e) {

    error_log(
      'PG details retrieval failed: ' . $e->getMessage()
    );

    $error = 'PG details retrieval failed.';
  }
}


if ($_SERVER['REQUEST_METHOD'] && $_SERVER['REQUEST_METHOD'] === 'POST') {

  $pgId = filter_input(INPUT_POST, 'pg_id', FILTER_VALIDATE_INT);

  try {

    if (!$pgId) {
      throw new InvalidArgumentException('Invalid PG ID.');
    }

    if (!Token::verifyToken($_POST['csrf_token'] ?? '')) {
      http_response_code(403);
      error_log('CSRF token validation failed.');
      echo "<h1>403 Forbidden</h1><p>CSRF token validation failed.</p>";
      exit;
    }

    // submitted details 

    $description = trim($_POST['description'] ?? '');
    $gender_preference = trim($_POST['gender_preference'] ?? '');
    $rules = trim($_POST['rules'] ?? '');
    $notice_period_days = trim($_POST['notice_period_days'] ?? '');
    $lock_in_period_months = trim($_POST['lock_in_period_months'] ?? '');

    // convert empty optional values to null

    $description = $description === '' ? null :  $description;

    $rules = $rules === '' ? null : $rules;

    $notice_period_days = $notice_period_days === '' ? null : (int)$notice_period_days;

    $lock_in_period_months = $lock_in_period_months === '' ? null : (int)$lock_in_period_months;

    //update pg details

    $pgService->updatePGDetails($pgId, $ownerId, $description, $gender_preference, $rules, $notice_period_days, $lock_in_period_months);

    // set flash message

    SessionManager::setFlash(
      'success',
      "PG details updated successfully."
    );

    //redirect to next phase 

    header('Location: /pg_recommendation/public/owner/rooms.php?pg_id=' . $pgId);

    exit;
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (InvalidArgumentException $e) {
    // Expected validation error: preserve the useful message.
    $error = $e->getMessage();

    if (!empty($pgId)) {
      try {
        $pg = $authorizationService->verifyPGOwnership($pgId, $ownerId);
      } catch (Throwable $exception) {
        $pg = null;
      }
    }
    exit;
  } catch (Throwable $e) {
    error_log('PG details update failed: ' . $e->getMessage());

    // Show a safe message, not internal exception details.
    $error = 'Unable to update PG. Please try again.';

    if (!empty($pgId)) {
      try {
        $pg = $authorizationService->verifyPGOwnership($pgId, $ownerId);
      } catch (Throwable $exception) {
        $pg = null;
      }
    }
  }
}

require_once $path . '/pg_recommendation/views/owner/pg-details-form.php';
