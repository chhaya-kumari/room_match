<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

SessionManager::start();
AuthMiddleware::requireRole('owner');

$ownerId = (int)SessionManager::userId();
$error = SessionManager::getFlash('error');
$success = SessionManager::getFlash('success');

$database = new Database();
$pgSubmissionService = new PGSubmissionService($database->conn);
$summary = null;

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
  $pgId = filter_input(INPUT_GET, 'pg_id', FILTER_VALIDATE_INT);

  if (!$pgId || $pgId <= 0) {
    SessionManager::setFlash('error', 'Please select a valid PG to review.');
    header('Location: /pg_recommendation/public/owner/create-pg.php');
    exit;
  }

  try {
    $summary = $pgSubmissionService->getReviewSummary((int)$pgId, $ownerId);
  } catch (AuthorizationException $e) {
    SessionManager::setFlash('error', $e->getMessage());
    header('Location: /pg_recommendation/public/owner/dashboard.php');
    exit;
  } catch (InvalidArgumentException $e) {
    SessionManager::setFlash('error', $e->getMessage());
    header('Location: /pg_recommendation/public/owner/dashboard.php');
    exit;
  } catch (Throwable $e) {
    error_log('Review page loading failed: ' . $e->getMessage());
    $error = 'Unable to load the PG review page. Please try again.';
  }
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  // csrf validation
  try {
    if (!Token::verifyToken($_POST['csrf_token'] ?? '')) {
      http_response_code(403);
      error_log('CSRF token validation failed.');
      echo "<h1>403 Forbidden</h1><p>CSRF token validation failed.</p>";
      exit;
    }

    $pgId = filter_input(INPUT_POST, 'pg_id', FILTER_VALIDATE_INT);

    if (!$pgId) {
      throw new InvalidArgumentException('Invalid PG ID.');
    }

    // check if the PG exists and belongs to the owner
    $result = $pgSubmissionService->submitPG((int)$pgId, $ownerId);

    if (!$result['submitted']) {
      SessionManager::setFlash(
        'error',
        'This PG is not ready for submission. Review the checklist below.'
      );
    } else {
      SessionManager::setFlash(
        'success',
        'Your PG has been submitted for review.'
      );
    }
    header(
      'Location: /pg_recommendation/public/owner/review-pg.php?pg_id='
        . (int)$pgId
    );
    exit;
  } catch (AuthorizationException $e) {
    SessionManager::setFlash('error', $e->getMessage());
    header('Location: /pg_recommendation/public/owner/dashboard.php');
    exit;
  } catch (InvalidArgumentException $e) {
    SessionManager::setFlash('error', $e->getMessage());

    if ($pgId && $pgId > 0) {
      header(
        'Location: /pg_recommendation/public/owner/review-pg.php?pg_id='
          . (int)$pgId
      );
    } else {
      header('Location: /pg_recommendation/public/owner/dashboard.php');
    }
    exit;
  } catch (Throwable $e) {
    error_log(
      'PG submission failed: ' . $e->getMessage()
    );

    SessionManager::setFlash(
      'error',
      'Unable to submit this PG right now. Please try again.'
    );

    if ($pgId && $pgId > 0) {
      header(
        'Location: /pg_recommendation/public/owner/review-pg.php?pg_id='
          . (int)$pgId
      );
    } else {
      header('Location: /pg_recommendation/public/owner/dashboard.php');
    }
    exit;
  }
}

require_once $path . '/pg_recommendation/views/owner/review-pg-form.php';
