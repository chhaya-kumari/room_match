<?php
$path = $_SERVER["DOCUMENT_ROOT"];
require_once $path . '/pg_recommendation/src/middleware/AuthMiddleware.php';
require_once $path . '/pg_recommendation/src/services/SessionManager.php';
require_once $path . '/pg_recommendation/src/services/PGService.php';
require_once $path . '/pg_recommendation/config/db.php';
require_once $path . '/pg_recommendation/src/security/Csrf.php';
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

    // Get PG through service
    $pg = $pgService->getPG($pgId);

    // Check whether PG exists
    if ($pg === null) {
      throw new InvalidArgumentException(
        'PG not found.'
      );
    }

    // Check ownership
    if ((int)$pg['owner_id'] !== (int)$ownerId) {
      throw new InvalidArgumentException(
        'You are not authorized to edit this PG.'
      );
    }
  } catch (Throwable $e) {

    error_log(
      'PG details retrieval failed: ' . $e->getMessage()
    );

    $error = $e->getMessage();
  }
}


if ($_SERVER['REQUEST_METHOD'] && $_SERVER['REQUEST_METHOD'] === 'POST') {

  try {
    Token::verifyToken($_POST['csrf_token'] ?? '');

    $pgId = filter_input(INPUT_POST, 'pg_id', FILTER_VALIDATE_INT);

    if (!$pgId) {
      throw new InvalidArgumentException('Invalid PG ID.');
    }

    // submitted details 

    $description = trim($_POST['description'] ?? '');
    $gender_preference = trim($_POST['gender_preference'] ?? '');
    $rules = trim($_POST['rules'] ?? '');
    $notice_period_days = trim($_POST['notice_period_days'] ?? '');
    $lock_in_period_months = trim($_POST['lock_in_period_months'] ?? '');

    // convert empty optional values to null

    $description = $description === '' ? null : $description;

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
  } catch (InvalidArgumentException $e) {
    // safe validation errors
    $error = $e->getMessage();
  } catch (Throwable $e) {
    error_log(
      'PG details update failed: ' . $e->getMessage()
    );

    $error = $e->getMessage();
    /*
      Reload PG so that the form can still be displayed
      when validation fails.
    */
    if (isset($pgId) && $pgId) {

      try {
        $pg = $pgService->getPG($pgId);
      } catch (Throwable $exception) {
        $pg = null;
      }
    }

    $error = 'Unable to update PG. Please try again.';
  }
}

require_once $path . '/pg_recommendation/views/owner/pg-details-form.php';
