<?php
$path = $_SERVER["DOCUMENT_ROOT"];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

SessionManager::start();
AuthMiddleware::requireRole('owner');

$error = null;
$success = null;

// display flash message
$success = SessionManager::getFlash('success');

$pgName = '';
$pgAddress = '';
$locationId = 0;
// handle post req

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {

  if (!Token::verifyToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    error_log('CSRF token validation failed.');
    echo "<h1>403 Forbidden</h1><p>CSRF token validation failed.</p>";
    exit;
  }

  $pgName = trim($_POST['pg_name'] ?? '');
  $pgAddress = trim($_POST['pg_address'] ?? '');
  $locationId = (int)($_POST['location_id'] ?? 0);

  $errors = [];

  if ($pgName === '') {
    $errors[] = 'Please enter PG Name.';
  }

  if ($pgAddress === '') {
    $errors[] = 'Please enter PG Address.';
  }

  if ($locationId <= 0) {
    $errors[] = 'Please select a valid location.';
  }

  $error = !empty($errors) ? implode(' ', $errors) : null;

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

      $geoCodeService = new GeoCodeService($delhiveryConfig['base_url'], $delhiveryConfig['token']);

      $database = new Database();
      $pgService = new PGService($database->conn);

      $ownerId = SessionManager::userId();

      $pgId = $pgService->createPG(
        $ownerId,
        $pgName,
        $pgAddress,
        $locationId,
        $geoCodeService
      );

      SessionManager::setFlash(
        'success',
        "PG created successfully. PG ID: {$pgId}"
      );

      header(
        'Location: /pg_recommendation/public/owner/pg-details.php?pg_id=' . $pgId
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
