<?php

class ChargeValidator
{
  public static function chargeName($chargeName)
  {
    $chargeName = trim($chargeName);

    if (!$chargeName || strlen($chargeName) > 100) {
      throw new InvalidArgumentException(
        'Charge name is required and must not exceed 100 characters.'
      );
    }
  }

  public static function amount(float $amount)
  {
    if ($amount <= 0) {
      throw new InvalidArgumentException(
        'Charge amount must be greater than zero.'
      );
    }
  }

  public static function chargeBasis($chargeBasis)
  {
    if (!in_array(
      $chargeBasis,
      ['fixed_monthly', 'per_unit'],
      true
    )) {
      throw new InvalidArgumentException(
        'Invalid charge basis.'
      );
    }
  }

  public static function unit($chargeBasis, $unit)
  {
    if ($chargeBasis === 'per_unit') {
      if (!$unit || strlen($unit) > 30) {
        throw new InvalidArgumentException(
          'Unit is required for a per-unit charge and must not exceed 30 characters.'
        );
      }
      return true;
    }

    /* For fixed monthly charges, unit should not be supplied. */
    if ($unit !== null && trim($unit) !== '') {
      throw new InvalidArgumentException(
        'Unit should only be provided for per-unit charges.'
      );
    }
    return true;
  }

  public static function validateDescription($description)
  {
    if (
      $description !== null &&
      mb_strlen($description) > 2000
    ) {
      throw new InvalidArgumentException(
        'Description cannot exceed 2000 characters.'
      );
    }
  }

  public static function validate($amount, $chargeBasis, $unit, $description)
  {
    self::amount($amount);
    self::chargeBasis($chargeBasis);
    self::unit($chargeBasis, $unit);
    self::validateDescription($description);
  }
}
