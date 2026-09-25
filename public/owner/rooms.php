<?php

$path = $_SERVER['DOCUMENT_ROOT'];

require_once $path . '/pg_recommendation/src/middleware/AuthMiddleware.php';
require_once $path . '/pg_recommendation/src/services/SessionManager.php';
require_once $path . '/pg_recommendation/src/services/RoomService.php';
require_once $path . '/pg_recommendation/src/security/Csrf.php';
require_once $path . '/pg_recommendation/config/db.php';

SessionManager::start();
AuthMiddleware::requireRole('owner');

$ownerId = SessionManager::userId();

$error = null;
$success = SessionManager::getFlash('success');

$rooms = [];

$database = new Database();
$roomService = new RoomService($database->conn);

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

    $rooms = $roomService->getRoomsByPgId($pgId, $ownerId);
  } catch (Throwable $e) {
    error_log(
      'Room retrieval failed: ' . $e->getMessage()
    );

    $error = $e->getMessage();
  }
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {

  try {
    if (!Token::verifyToken($_POST['csrf_token'] ?? '')) {
      throw new InvalidArgumentException('Invalid Csrf token.');
    };

    $pgId = filter_input(INPUT_POST, 'pg_id', FILTER_VALIDATE_INT);

    if (!$pgId) {
      throw new InvalidArgumentException('Invalid PG ID.');
    }

    // Form Data

    $roomType = trim($_POST['room_type'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);
    $occupiedBeds = (int) ($_POST['occupied_beds'] ?? 0);
    $rent = (float)($_POST['rent'] ?? 0);
    $securityDeposit = $_POST['security_deposit'] ?? '';
    $bathroomType = trim($_POST['bathroom_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $windowVentilation = isset($_POST['window_ventilation']);

    // convert optional values to null

    $securityDeposit =
      $securityDeposit === '' ? null : (float) $securityDeposit;

    $description =
      $description === '' ? null : $description;

    $roomService->createRoom(
      $pgId,
      $ownerId,
      $roomType,
      $capacity,
      $occupiedBeds,
      $rent,
      $securityDeposit,
      $bathroomType,
      $description,
      $windowVentilation
    );

    SessionManager::setFlash(
      'success',
      'Room added successfully.'
    );

    header(
      'Location: /pg_recommendation/public/owner/rooms.php?pg_id='
        . $pgId
    );
    exit;
  } catch (InvalidArgumentException $e) {
    // safe validation errors
    $error = $e->getMessage();
  } catch (Throwable $e) {
    error_log(
      'Room creatio failed: ' . $e->getMessage()
    );

    $error = $e->getMessage();

    $error = 'Unable to add room. Please try again.';
  }

  // Load rooms

  if ($pgId !== null && empty($rooms)) {
    try {
      $rooms = $roomService->getRoomsByPgId($pgId, $ownerId);
    } catch (Throwable $e) {
      $rooms = [];
    }
  }
}

require_once $path . "/pg_recommendation/views/owner/room-form.php";
