<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/src/models/Location.php';
require_once $path . '/pg_recommendation/config/db.php';
//Ajax endpoint for js communication.

header('Content-Type: application/json');

try {
  $database = new Database();
  $locationModel = new Location($database->conn);
  $action = $_GET['action'] ?? '';

  switch ($action) {
    case 'states':
      $states = $locationModel->getStates();

      echo json_encode([
        'success' => true,
        'data' => $states
      ]);

      break;

    case 'cities':
      $state = trim($_GET['state'] ?? '');
      if ($state === '') {
        throw new InvalidArgumentException('State is required');
      }

      $cities = $locationModel->getCitiesByState($state);

      echo json_encode([
        'success' => true,
        'data' => $cities
      ]);

      break;

    case 'areas':
      $state = trim($_GET['state'] ?? '');
      $city = trim($_GET['city'] ?? '');

      if ($state === '' || $city === '') {
        throw new InvalidArgumentException('State and City are required');
      }

      $areas = $locationModel->getAreasByCity($state, $city);

      echo json_encode([
        'success' => true,
        'data' => $areas
      ]);

      break;

    default:
      http_response_code(400);

      echo json_encode([
        'success' => false,
        'message' => 'Invalid action.'
      ]);
      break;
  }
} catch (InvalidArgumentException $e) {
  http_response_code(400);

  echo json_encode([
    'success' => false,
    'message' => $e->getMessage()
  ]);
} catch (\Throwable $th) {
  http_response_code(500);

  echo json_encode([
    'success' => false,
    'message' => 'Unable to load locations.'
  ]);
}
