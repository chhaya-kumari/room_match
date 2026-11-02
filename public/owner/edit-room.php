<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

SessionManager::start();

AuthMiddleware::requireRole('owner');

$ownerId = SessionManager::userId();

$error = null;
$room = null;

$database = new Database();

$pgModel = new PG($database->conn);
$roomModel = new Room($database->conn);

$authorizationService = new AuthorizationService(
  $database->conn
);

$roomService = new RoomService($database->conn);

// Get Request Handle

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET') {

  try {
    // Get Room ID from URL
    $roomId = filter_input(
      INPUT_GET,
      'room_id',
      FILTER_VALIDATE_INT
    );

    if (!$roomId) {
      throw new InvalidArgumentException(
        'Invalid room ID.'
      );
    }

    $room = $authorizationService->verifyRoomOwnership($roomId, $ownerId);
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());
    header('Location: /pg_recommendation/public/owner/dashboard.php');
    exit;
  } catch (InvalidArgumentException $e) {
    $error = $e->getMessage();
  } catch (Throwable $e) {
    error_log('Room edit retrieval failed: ' . $e->getMessage());
    $error = "Unable to load room.";
  }
}

// post request handling
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $formData = [];
  try {
    // csrf 
    if (!Token::verifyToken($_POST['csrf_token'] ?? '')) {
      http_response_code(403);
      error_log('CSRF token validation failed.');
      echo "<h1>403 Forbidden</h1><p>CSRF token validation failed.</p>";
      exit;
    }

    $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);

    if (!$roomId) {
      throw new InvalidArgumentException('Invalid room Id.');
    }

    $room = $authorizationService->verifyRoomOwnership($roomId, $ownerId);
    $pgId = $room['pg_id'];

    // Collect form data into an array so we don't lose it if validation fails

    $formData = [
      'room_id' => $roomId,
      'room_type' => trim($_POST['room_type'] ?? ''),
      'capacity' => (int)($_POST['capacity'] ?? 0),
      'occupied_beds'  => (int)($_POST['occupied_beds'] ?? 0),
      'rent' => (float)($_POST['rent'] ?? 0),
      'security_deposit' => $_POST['security_deposit'] ?? '',
      'bathroom_type' => trim($_POST['bathroom_type'] ?? ''),
      'description' => trim($_POST['description'] ?? ''),
      'window_ventilation' => isset($_POST['window_ventilation']) ? 1 : 0
    ];


    // convert optional values to null for db;
    $securityDeposit = $formData['security_deposit'] === '' ? null : (float) $formData['security_deposit'];

    $description = $formData['description'] === '' ? null : $formData['description'];

    // Update

    $roomService->updateRoom(
      $roomId,
      $ownerId,
      $formData['room_type'],
      $formData['capacity'],
      $formData['occupied_beds'],
      $formData['rent'],
      $securityDeposit,
      $formData['bathroom_type'],
      $description,
      $formData['window_ventilation']
    );

    //success

    SessionManager::setFlash('success', 'Room updated successfully');


    header(
      'Location: /pg_recommendation/public/owner/rooms.php?pg_id='
        . $pgId
    );

    exit;
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (InvalidArgumentException $e) {

    $error = $e->getMessage();

    //If validation fails, preserve what the user typed so they don't have to retype everything
    if ($room !== null && !empty($formData)) {
      $room = array_merge($room, $formData);
    }
  } catch (Throwable $e) {
    error_log('Room update failed:' . $e->getMessage());
    $error = 'Unable to update room. Please try again.';

    if ($room !== null && !empty($formData)) {
      $room = array_merge($room, $formData);
    }
  }
}

if ($room === null) {
  SessionManager::setFlash('error', 'Room not found or unavailable.');
  header('Location: /pg_recommendation/public/owner/dashboard.php');
  exit;
}

require_once $path .  '/pg_recommendation/views/owner/edit-room-form.php';
