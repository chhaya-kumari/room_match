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

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === "POST") {

  try {

    if (!Token::verifyToken($_POST['csrf_token'] ?? '')) {
      http_response_code(403);
      error_log('CSRF token validation failed.');
      echo "<h1>403 Forbidden</h1><p>CSRF token validation failed.</p>";
      exit;
    }

    $imageId = filter_input(INPUT_POST, 'image_id', FILTER_VALIDATE_INT);

    if (!$imageId) {
      throw new InvalidArgumentException(
        'Invalid Image Id.'
      );
    }

    $database = new Database();
    $pgImageService = new PGImageService($database->conn);
    $authorizationService = new AuthorizationService($database->conn);

    $image = $authorizationService->verifyImageOwnership($imageId, $ownerId);
    $pgId = $image['pg_id'];

    $pgImageService->deleteImage($imageId, $ownerId);

    SessionManager::setFlash('success', 'Image deleted successfully.');

    header("Location: /pg_recommendation/public/owner/images.php?pg_id=" . $pgId);
    exit;
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (InvalidArgumentException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    if (!empty($pgId)) {
      header('Location: /pg_recommendation/public/owner/images.php?pg_id=' . $pgId);
    } else {
      header('Location: /pg_recommendation/public/owner/dashboard.php');
    }
    exit;
  } catch (Throwable $e) {

    error_log('Delete image failed: ' . $e->getMessage());

    SessionManager::setFlash(
      'error',
      'Unable to delete image. Please try again.'
    );

    if (!empty($pgId)) {
      header('Location: /pg_recommendation/public/owner/images.php?pg_id=' . $pgId);
    } else {
      header('Location: /pg_recommendation/public/owner/dashboard.php');
    }

    exit;
  }
}
