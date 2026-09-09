<?php
require_once __DIR__ . '/../services/SessionManager.php';

class AuthMiddleware
{
  public static function requireLogin()
  {
    if (!SessionManager::isAuthenticated()) {
      header('Location: ../../public/auth/login.php');
      exit;
    }
  }

  public static function requireRole($requireRole)
  {
    self::requireLogin();

    if (SessionManager::role() !== $requireRole) {
      http_response_code(403);
      exit('Forbidden');
    }
  }
}
