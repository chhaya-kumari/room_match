<?php

require_once __DIR__ . '/../services/SessionManager.php';

class Token
{
  private const SESSION_KEY = 'csrf_token';

  public static function generateToken()
  {
    SessionManager::start();
    if (!isset($_SESSION[self::SESSION_KEY])) {
      $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }

    return $_SESSION[self::SESSION_KEY];
  }

  public static function getToken()
  {

    SessionManager::start();

    if (empty($_SESSION[self::SESSION_KEY])) {
      self::generateToken();
    }

    return $_SESSION[self::SESSION_KEY];
  }

  public static function verifyToken($token)
  {

    if (!is_string($token) || $token === '') {
      return false;
    }
    $storedToken = self::getToken();

    return hash_equals($token,  $storedToken);
  }
}
