<?php

final class InputValidator
{
  // allows only letters, spaces, and hyphens in the full name. It also checks that the length of the full name is between 2 and 50 characters.
  public static function fullname($fullname)
  {
    $length = mb_strlen($fullname);
    if ($length < 2 || $length > 50) {
      return false;
    }
    return preg_match("/^[a-zA-Z\s\-]+$/u", $fullname);
  }

  //email format validation
  public static function email($email)
  {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
  }

  // due to different formats of phone number, contact number validation is permissive
  public static function contact($contact)
  {
    return is_string($contact)
      && preg_match('/^[0-9+\-\s()]{7,20}$/', $contact);
  }

  public static function gender($gender)
  {
    return $gender === '' || in_array($gender, ['male', 'female', 'other'], true);
  }

  // mb_strlen($value) to count the characters. The mb_ (multibyte) prefix is crucial because it accurately counts special characters, emojis, and non-English alphabets (like ä, ñ, or 中), whereas standard strlen() might miscount them.
  public static function password($password)
  {
    return is_string($password) && mb_strlen($password) >= 8;
  }

  /** Checks if the password and confirm password fields match exactly */
  public static function passwordsMatch($password, $confirmPassword)
  {
    return $password === $confirmPassword;
  }


  public static function role($role)
  {
    return in_array($role, ['user', 'owner'], true);  // its third parameter set to true (strict type checking) to prevent type coercion vulnerabilities
  }

  public static function cleanString($value)
  {
    return trim($value);
  }
}
