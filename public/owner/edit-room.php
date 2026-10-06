<?php

$path = $_SERVER['DOCUMENT_ROOT'];

require_once $path . '/pg_recommendation/src/middleware/AuthMiddleware.php';
require_once $path . '/pg_recommendation/src/services/SessionManager.php';
require_once $path . '/pg_recommendation/src/services/RoomService.php';
require_once $path . '/pg_recommendation/src/services/AuthorizationService.php';
require_once $path . '/pg_recommendation/src/models/PG.php';
require_once $path . '/pg_recommendation/src/models/Room.php';
require_once $path . '/pg_recommendation/src/validators/RoomValidator.php';
require_once $path . '/pg_recommendation/src/security/Csrf.php';
require_once $path . '/pg_recommendation/config/db.php';
require_once $path . '/pg_recommendation/src/exceptions/AuthorizationException.php';


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
    if (isset($formData)) {
      $room = $formData;
    } else if (!empty($roomId)) {
      try {
        $room = $authorizationService
          ->verifyRoomOwnership(
            $roomId,
            $ownerId
          );
      } catch (Throwable $ignored) {
        $room = null;
      }
    }
  } catch (Throwable $e) {
    error_log('Room update failed:' . $e->getMessage());
    $error = 'Unable to update room. Please try again.';

    if (isset($formData)) {
      $room = $formData;
    }
  }
}

require_once $path .  '/pg_recommendation/views/owner/edit-room-form.php';

// during test error 1 encountered
// No, the reason your previous values are not displaying is due to a variable assignment mismatch in your GET request logic.In your GET block, you call $authorizationService->verifyRoomOwnership($roomId, $ownerId) and assign the result to a variable named plural $rooms instead of singular $room. Because your HTML form relies entirely on $room, it finds nothing, and your form fields load completely blank.Additionally, in your POST block, if validation fails (inside the catch block), you attempt to reload the room data, but if $room was already populated with submitted form data, your catch block overwrites it completely by fetching the old database version again, wiping out what the user just typed.

// error 2
// IDOR (Insecure Direct Object Reference) : owner being able to edit the another room just by changing the room id in url; 