<?php

class InputValidator
{
  public static function email($email)
  {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
  }

  public static function password($password)
  {
    return is_string($password) && strlen($password)>=8;
  }

  public static function role($role)
  {
    return in_array($role, ['user', 'owner'], true);
  }

  public static function cleanString($value)
  {
    return trim($value);
  }
}
