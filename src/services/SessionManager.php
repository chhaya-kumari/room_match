<?php

require_once "AuthService.php";

class SessionManager
{
  public static function start()
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }

  public static function login($user) {
    self::start();

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
  }

  public static function logout() {
    self::start();

    $_SESSION=[];
    if(ini_get('session.use_cookie')) {
      $params = session_get_cookie_params();

      setcookie(
        session_name(),
        '',
        time() -42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
      );
    }
    session_destroy();   
  }

  public static function isAuthenticated() {
    self::start();
    return isset($_SESSION['user_id']);
  }

  public static function userId() {
    self::start();
    return $_SESSION['user_id'] ?? null;
  }

  public static function role() {
    self::start();
    return $_SESSION['role'] ?? null;
  }
}
