<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/db.php';
require_once $path . '/pg_recommendation/src/models/User.php';
require_once $path . '/pg_recommendation/src/services/AuthService.php';
require_once $path . '/pg_recommendation/src/services/SessionManager.php';
require_once $path . '/pg_recommendation/src/validators/InputValidator.php';


SessionManager::start();

$error = null;
$success = null;
$old = [];

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = trim($_POST['fullname'] ?? '');

  $email = trim($_POST['email'] ?? '');
  $contact = trim($_POST['contact'] ?? '');
  $gender = $_POST['gender'] ?? '';

  $password = $_POST['password'] ?? '';
  $confirmPassword = $_POST['confirm_password'] ?? '';
  $role = $_POST['role'] ?? '';

  // Preserve non-sensitive form values after validation errors.
  $old = compact('fullname', 'email', 'contact', 'gender', 'role');

  if (empty($fullname) || empty($email) || empty($gender) || empty($contact) || empty($password) || empty($confirmPassword) || empty($role)) {
    $error = "All fields are required.";
  } elseif (!InputValidator::fullname($fullname)) {
    $error = "Invalid full name. It should only contain letters, spaces, and hyphens, and be between 2 and 50 characters long.";
  } else if (!InputValidator::email($email)) {
    $error = "Please enter a valid email address.";
  } else if (!InputValidator::contact($contact)) {
    $error = "Please enter a valid contact number.";
  } else if (!InputValidator::gender($gender)) {
    $error = "Please select a valid gender option.";
  } else if (!InputValidator::password($password)) {
    $error = "Password must be at least 8 characters long.";
  } else if (!InputValidator::passwordsMatch($password, $confirmPassword)) {
    $error = "Passwords do not match.";
  } else if (!InputValidator::role($role)) {
    $error = "Please select a valid role.";
  }

  if ($error === null) {
    try {
      $database = new Database();
      $userModel = new User($database->conn);
      $authService = new AuthService($userModel);
      $result = $authService->register($fullname, $email, $password, $role, $contact, $gender);

      if (!$result['success']) {
        $error = $result['message'];
      } else {
        // $success = "Account created successfully. You can now Log in.";
        $old = [];
        header("Location: login.php?success=1");
        exit;
      }
    } catch (Exception $e) {
      error_log($e->getMessage()); // Log the error for debugging purposes
      $error = "An error occurred. Please try again later. ";
    }
  }
}
// Render the view AFTER processing
require_once $path . '/pg_recommendation/views/auth/register-form.php';
