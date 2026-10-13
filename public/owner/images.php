<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

SessionManager::start();
AuthMiddleware::requireRole('owner');

$error = null;
$success = SessionManager::getFlash('success');

$ownerId = SessionManager::userId();
$pgId = null;

$images = [];

$database = new Database();
$pgImageService = new PGImageService($database->conn);
$authorizationService = new AuthorizationService($database->conn);

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === "GET") {

  try {
    $pgId = filter_input(INPUT_GET, 'pg_id', FILTER_VALIDATE_INT);

    if (!$pgId) {
      header('Location: /pg_recommendation/public/owner/create-pg.php');
      exit;
    }

    $images = $pgImageService->getImageByPgId($pgId, $ownerId);
  } catch (AuthorizationException $e) {
    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (InvalidArgumentException $e) {
    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (Throwable $e) {

    error_log('Images access failed: ' . $e->getMessage());

    $error = 'Unable to process your request. Please try again.';
  }
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === "POST") {

  try {

    if (!Token::verifyToken($_POST['csrf_token'] ?? '')) {
      http_response_code(403);
      error_log('CSRF token validation failed.');
      echo "<h1>403 Forbidden</h1><p>CSRF token validation failed.</p>";
      exit;
    }

    $pgId = filter_input(INPUT_POST, 'pg_id', FILTER_VALIDATE_INT);

    if (!$pgId) {
      header('Location: /pg_recommendation/public/owner/create-pg.php');
      exit;
    }

    $authorizationService->verifyPGOwnership($pgId, $ownerId);

    // form data

    $imageType = trim($_POST['image_type'] ?? '');
    $file = $_FILES['pg_image'] ?? null;

    if (!is_array($file)) {
      throw new InvalidArgumentException(
        'Please select an image to upload.'
      );
    }

    // nulllify optional values;
    $imageType = $imageType === '' ? null : $imageType;

    $pgImageService->uploadImage($pgId, $ownerId, $file, $imageType);

    SessionManager::setFlash('success', 'Image uploaded successfully.');

    header("Location: /pg_recommendation/public/owner/images.php?pg_id=" . $pgId);
    exit;
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    header("Location: /pg_recommendation/public/owner/dashboard.php");
    exit;
  } catch (InvalidArgumentException $e) {
    $error = $e->getMessage();
  } catch (Throwable $e) {
    error_log('Unable to add image:' . $e->getMessage() . 'in' . $e->getFile() . 'on line' . $e->getLine());
    http_response_code(500);
    $error = "Unable to upload your image. PLease try again.";
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($pgId)) {
  try {
    $images = $pgImageService->getImageByPgId($pgId, $ownerId);
  } catch (AuthorizationException $e) {
    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');
    exit;
  } catch (Throwable $e) {
    error_log('Image loading failed: ' . $e->getMessage());

    $error = 'Unable to load existing images. Please try again.';
  }
}

require_once $path . '/pg_recommendation/views/owner/image-form.php';
