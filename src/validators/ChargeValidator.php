<?php

class ChargeValidator
{
  public static function chargeName(string $chargeName)
  {
    $chargeName = trim($chargeName);

    if (!$chargeName || mb_strlen($chargeName) > 100) {
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

  public static function chargeBasis(string $chargeBasis)
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

  public static function unit(string $chargeBasis, ?string $unit)
  {
    if ($chargeBasis === 'per_unit') {
      $unit = $unit !== null  ? trim($unit) : null;

      if ($unit === null || $unit === '' || mb_strlen($unit) > 30) {
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

  public static function validateDescription(?string $description)
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

  public static function validate(float $amount, string $chargeBasis, ?string $unit, ?string $description)
  {
    self::amount($amount);
    self::chargeBasis($chargeBasis);
    self::unit($chargeBasis, $unit);
    self::validateDescription($description);
  }
}
