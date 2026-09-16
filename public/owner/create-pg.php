<?php
$path = $_SERVER["DOCUMENT_ROOT"];
require_once $path . '/pg_recommendation/src/middleware/AuthMiddleware.php';
require_once $path . '/pg_recommendation/src/services/SessionManager.php';
require_once $path . '/pg_recommendation/src/services/PGService.php';
require_once $path . '/pg_recommendation/config/db.php';
require_once $path . '/pg_recommendation/src/models/User.php';
require_once $path . '/pg_recommendation/src/security/Csrf.php';

SessionManager::start();
AuthMiddleware::requireRole('owner');

$error = null;
$success = null;

// display flash message
$success = SessionManager::getFlash('success');

// handle post req

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $pgName = trim($_POST['pg_name'] ?? '');
  $pgAddress = trim($_POST['pg_address'] ?? '');
  $locationId = $_POST['location_id'] ?? 0;

  if (empty($pgName)) {
    $error = "Please enter PG Name";
  }
  if (empty($pgAddress)) {
    $error = "Please enter PG Address";
  }
  if ($locationId <= 0) {
    $error = 'Please select a valid location.';
  }

  // create PG

  if ($error === null) {

    try {

      // load service configuration

      $serviceConfig = require $path . '/pg_recommendation/config/services.php';

      $delhiveryConfig = $serviceConfig['delhivery_maps'] ?? null;

      if ($delhiveryConfig === null) {
        throw new RuntimeException(
          'Delhivery Maps configuration is missing.'
        );
      }

      $database = new Database();
      $pgService = new PGService($database->conn, $delhiveryConfig['base_url'], $delhiveryConfig['token']);

      $ownerId = SessionManager::userId();

      $pgId = $pgService->createPG(
        $ownerId,
        $pgName,
        $pgAddress,
        $locationId
      );

      SessionManager::setFlash(
        'success',
        "PG created successfully. PG ID: {$pgId}"
      );


      header(
        'Location: /pg_recommendation/public/owner/create-pg.php'
      );

      exit;
    } catch (InvalidArgumentException $e) {
      // safe validation errors
      $error = $e->getMessage();
    } catch (Throwable $e) {

      // technical error goes to server log
      error_log(
        'Create PG controller Error: ' . $e->getMessage()
      );


      // safe message for user

      $error = 'Unable to create PG. Please try again.';
    }
  }
}
require_once $path . "/pg_recommendation/views/owner/create-pg-form.php";
