<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/src/services/SessionManager.php';
require_once $path . '/pg_recommendation/src/security/Csrf.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {

  // The state-changing POST requests are protected by CSRF validation.
  $csrfToken = $_POST['csrf_token'] ?? '';

  if (!Token::verifyToken($csrfToken)) {
    http_response_code(403);
    exit("Invalid CSRF token");
  }

  SessionManager::logout();

  header("Location: login.php");
  exit;
}
