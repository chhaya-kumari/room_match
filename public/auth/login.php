<?php
$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/db.php';
require_once $path . '/pg_recommendation/src/models/User.php';
require_once $path . '/pg_recommendation/src/services/AuthService.php';
require_once $path . '/pg_recommendation/src/services/SessionManager.php';
require_once $path . '/pg_recommendation/src/validators/InputValidator.php';

SessionManager::start();

$error = null;

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if(!InputValidator::email($email) || !InputValidator::password($password)) {
    $error = "Invalid email or password.";
  } else {
    $database = new Database();
    $userModel = new User($database->conn);
    $authService = new AuthService($userModel);

    $result = $authService->login($email, $password);

    if(!$result['success']){
      $error = $result['message'];
    } else {
      SessionManager::login($result['user']);
      header("Location: ../session-test.php");
      exit;
    }
  }

}
