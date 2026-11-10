<?php

class PGValidator
{

  public static function validateCreate(
    string $pgName,
    string $pgAddress
  ): void {
    $pgName = trim($pgName);
    $pgAddress = trim($pgAddress);

    if ($pgName === '') {
      throw new InvalidArgumentException(
        'PG name is required.'
      );
    }

    if (mb_strlen($pgName) > 150) {
      throw new InvalidArgumentException(
        'PG name is too long.'
      );
    }

    if ($pgAddress === '') {
      throw new InvalidArgumentException(
        'PG address is required.'
      );
    }
  }

  public static function validateDetails(
    ?string $description,
    string $genderPreference,
    ?string $rules,
    ?int $noticePeriodDays,
    ?int $lockInPeriodMonths
  ): void {
    $allowedGenderPreferences = [
      'male',
      'female',
      'co-ed'
    ];

    if (!in_array(
      $genderPreference,
      $allowedGenderPreferences,
      true
    )) {
      throw new InvalidArgumentException(
        'Invalid gender preference.'
      );
    }

    if (
      $description !== null &&
      mb_strlen($description) > 2000
    ) {
      throw new InvalidArgumentException(
        'Description cannot exceed 2000 characters.'
      );
    }

    if ($rules !== null && mb_strlen($rules) > 3000) {
      throw new InvalidArgumentException(
        'Rules cannot exceed 3000 characters.'
      );
    }

    if (
      $noticePeriodDays !== null &&
      $noticePeriodDays < 0
    ) {
      throw new InvalidArgumentException(
        'Invalid notice period.'
      );
    }

    if (
      $lockInPeriodMonths !== null &&
      $lockInPeriodMonths < 0
    ) {
      throw new InvalidArgumentException(
        'Invalid lock-in period.'
      );
    }
  }
}
