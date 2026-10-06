<?php

class AmenityValidator
{
  public static function validateSelection(array $amenities)
  {
    foreach ($amenities as $amenityId => $data) {

      if (!filter_var($amenityId, FILTER_VALIDATE_INT)) {
        throw new InvalidArgumentException(
          'Invalid amenity ID.'
        );
      }

      if (!is_array($data)) {
        throw new InvalidArgumentException(
          'Invalid amenity data.'
        );
      }

      if (
        !isset($data['included_in_rent']) ||
        !in_array(
          $data['included_in_rent'],
          ['0', '1', 0, 1],
          true
        )
      ) {
        throw new InvalidArgumentException(
          'Invalid rent inclusion value.'
        );
      }
    }
  }
}
