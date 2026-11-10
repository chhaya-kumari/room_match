<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

SessionManager::start();
AuthMiddleware::requireRole('owner');

$ownerId = SessionManager::userId();

$db = new Database();
$chargeService = new ChargeService($db->conn);
$authorizationService = new AuthorizationService($db->conn);

$success = null;
$error = null;
$charge = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  try {

    $chargeId = filter_input(
      INPUT_GET,
      'charge_id',
      FILTER_VALIDATE_INT
    );

    if (!$chargeId) {
      throw new InvalidArgumentException('Invalid charge ID.');
    }

    $charge = $authorizationService->verifyChargeOwnership($chargeId, $ownerId);
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (InvalidArgumentException $e) {
    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (Throwable $e) {

    error_log('Edit charge retrieval failed: ' . $e->getMessage());

    $error = 'Unable to process your request. Please try again.';
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (!Token::verifyToken($_POST['csrf_token'] ?? '')) {
      http_response_code(403);
      error_log('CSRF token validation failed.');
      echo "<h1>403 Forbidden</h1><p>CSRF token validation failed.</p>";
      exit;
    }

    $chargeId = filter_input(INPUT_POST, 'charge_id', FILTER_VALIDATE_INT);

    if (!$chargeId) {
      throw new InvalidArgumentException('Invalid charge ID.');
    }

    $charge = $authorizationService->verifyChargeOwnership($chargeId, $ownerId);

    // Normalize form data.

    $chargeName = trim($_POST['charge_name'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $formData = [
      'charge_name' => $chargeName,
      'amount' => (float)($_POST['amount'] ?? 0),
      'unit' => $unit === '' ? null : $unit,
      'description' => $description === '' ? null : $description,
      'charge_basis' => $_POST['charge_basis'] ?? '',
    ];

    $chargeService->updateCharge($chargeId, $ownerId, $formData['charge_name'], $formData['amount'], $formData['charge_basis'], $formData['unit'], $formData['description']);

    SessionManager::setFlash('success', 'Charge updated successfully.');

    $pgId = $charge['pg_id'];

    header('Location: /pg_recommendation/public/owner/charges.php?pg_id=' . $pgId);

    exit;
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (InvalidArgumentException $e) {
    $error = $e->getMessage();
  } catch (Throwable $e) {

    error_log('Edit charge failed: ' . $e->getMessage());

    $error = 'Unable to edit charge. Please try again.';
  }
}

require_once $path . '/pg_recommendation/views/owner/edit-charge-form.php';
