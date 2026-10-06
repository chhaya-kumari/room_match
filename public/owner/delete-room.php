<?php

$path = $_SERVER['DOCUMENT_ROOT'];

require_once $path . '/pg_recommendation/src/middleware/AuthMiddleware.php';
require_once $path . '/pg_recommendation/src/services/SessionManager.php';
require_once $path . '/pg_recommendation/src/services/RoomService.php';
require_once $path . '/pg_recommendation/src/services/AuthorizationService.php';
require_once $path . '/pg_recommendation/src/security/Csrf.php';
require_once $path . '/pg_recommendation/config/db.php';
require_once $path . '/pg_recommendation/src/exceptions/AuthorizationException.php';

SessionManager::start();

AuthMiddleware::requireRole('owner');

$ownerId = SessionManager::userId();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

  header(
    'Location: /pg_recommendation/public/owner/dashboard.php'
  );

  exit;
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (!Token::verifyToken($_POST['csrf_token'] ?? '')) {
      http_response_code(403);
      error_log('CSRF token validation failed.');
      echo "<h1>403 Forbidden</h1><p>CSRF token validation failed.</p>";
      exit;
    }

    $roomId = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);

    if (!$roomId) {
      throw new InvalidArgumentException(
        'Invalid room id.'
      );
    }

    $database = new Database();
    $authorizarionService = new AuthorizationService($database->conn);
    $roomService = new RoomService($database->conn);

    // getting pg Id before deletion

    $room = $authorizarionService->verifyRoomOwnership($roomId, $ownerId);
    $pgId = $room['pg_id'];
    // delete
    $roomService->deleteRoom($roomId, $ownerId);

    // success

    SessionManager::setFlash('success', 'Room deleted successfully.');

    // redirect

    header('Location : /pg_recommendation/public/owner/rooms.php?pg_id=' . $pgId);

    exit;
  } catch (AuthorizationException $e) {

    SessionManager::setFlash('error', $e->getMessage());

    header('Location: /pg_recommendation/public/owner/dashboard.php');

    exit;
  } catch (InvalidArgumentException $e) {

    SessionManager::setFlash(
      'error',
      $e->getMessage()
    );

    header(
      'Location: /pg_recommendation/public/owner/dashboard.php'
    );

    exit;
  } catch (Throwable $e) {
    error_log(
      'Room deletion failed: ' .
        $e->getMessage()
    );

    SessionManager::setFlash(
      'error',
      'Unable to delete room. Please try again.'
    );

    header(
      'Location: /pg_recommendation/public/owner/dashboard.php'
    );

    exit;
  }
}
