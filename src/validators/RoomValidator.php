<?php

class RoomValidator
{
  private const ROOM_TYPES = ['single', 'double', 'triple', 'multi_sharing'];

  private const BATHROOM_TYPES = ['attached', 'shared', 'none'];

  public static function validateRoomType($roomType)
  {
    if (!in_array($roomType, self::ROOM_TYPES, true)) {
      throw new InvalidArgumentException(
        'Invalid room type.'
      );
    }
  }

  public static function validateCapacity(int $capacity)
  {
    if ($capacity <= 0) {
      throw new InvalidArgumentException(
        'Capacity must be greater than 0.'
      );
    }
  }

  public static function validateOccupiedBeds(int $occupiedBeds, int $capacity)
  {
    if ($occupiedBeds < 0) {
      throw new InvalidArgumentException(
        'Occupied beds cannot be negative.'
      );
    }

    if ($occupiedBeds > $capacity) {
      throw new InvalidArgumentException(
        'Occupied beds cannot exceed capacity.'
      );
    }
  }

  public static function validateRent(float $rent): void
  {
    if ($rent <= 0) {
      throw new InvalidArgumentException(
        'Rent must be greater than 0.'
      );
    }
  }

  public static function validateSecurityDeposit(?float $securityDeposit)
  {
    if ($securityDeposit !== null && $securityDeposit < 0) {
      throw new InvalidArgumentException(
        'Security deposit cannot be negative.'
      );
    }
  }

  public static function validateBathroomType($bathroomType)
  {
    if (!in_array(
      $bathroomType,
      self::BATHROOM_TYPES,
      true
    )) {
      throw new InvalidArgumentException(
        'Invalid bathroom type.'
      );
    }
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

  public static function validate($roomType, int $capacity, int $occupiedBeds, float $rent, ?float $securityDeposit, $bathroomType, $description)
  {
    self::validateRoomType($roomType);
    self::validateSecurityDeposit($securityDeposit);
    self::validateRent($rent);
    self::validateBathroomType($bathroomType);
    self::validateCapacity($capacity);
    self::validateOccupiedBeds($occupiedBeds, $capacity);
    self::validateDescription($description);
  }
}
