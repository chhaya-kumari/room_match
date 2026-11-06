<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

SessionManager::start();
AuthMiddleware::requireRole('owner');

$ownerId = SessionManager::userId();
$pgId = null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  SessionManager::setFlash('error', 'Invalid request method.');
  header('Location: /pg_recommendation/public/owner/dashboard.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {

    if (!Token::verifyToken($_POST['csrf_token'] ?? '')) {
      http_response_code(403);
      error_log('CSRF token validation failed.');
      echo "<h1>403 Forbidden</h1><p>CSRF token validation failed.</p>";
      exit;
    }

    $chargeId = filter_input(
      INPUT_POST,
      'charge_id',
      FILTER_VALIDATE_INT
    );

    if (!$chargeId) {
      throw new InvalidArgumentException('Invalid charge ID.');
    }

    $db = new Database();
    $chargeService = new ChargeService($db->conn);
    $authorizationService = new AuthorizationService($db->conn);

    $charge = $authorizationService->verifyChargeOwnership($chargeId, $ownerId);
    $pgId = $charge['pg_id'];

    $chargeService->deleteCharge($ownerId, $chargeId);

    SessionManager::setFlash('success', 'Charge deleted successfully.');

    header('Location: /pg_recommendation/public/owner/charges.php?pg_id=' . $pgId);

    exit;
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (InvalidArgumentException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    if (!empty($pgId)) {
      header('Location: /pg_recommendation/public/owner/charges.php?pg_id=' . $pgId);
    } else {
      header('Location: /pg_recommendation/public/owner/dashboard.php');
    }
    exit;
  } catch (Throwable $e) {

    error_log('Delete charge failed: ' . $e->getMessage());

    SessionManager::setFlash(
      'error',
      'Unable to delete charge. Please try again.'
    );

    if (!empty($pgId)) {
      header('Location: /pg_recommendation/public/owner/charges.php?pg_id=' . $pgId);
    } else {
      header('Location: /pg_recommendation/public/owner/dashboard.php');
    }

    exit;
  }
}
