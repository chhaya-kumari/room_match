<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/src/models/PG.php';
require_once $path . '/pg_recommendation/src/models/Room.php';
require_once $path . '/pg_recommendation/src/exceptions/AuthorizationException.php';
require_once $path . '/pg_recommendation/src/models/Charge.php';
require_once $path . '/pg_recommendation/src/models/PGImage.php';


class AuthorizationService
{
  private PG $pgModel;
  private Room $roomModel;
  private Charge $chargeModel;
  private PGImage $pgImageModel;
  public function __construct(PDO $db)
  {
    $this->pgModel = new PG($db);
    $this->roomModel = new Room($db);
    $this->chargeModel = new Charge($db);
    $this->pgImageModel = new PGImage($db);
  }

  public function verifyPGOwnership(int $pgId, int $ownerId)
  {
    if ($pgId <= 0) {
      throw new InvalidArgumentException(
        'Invalid PG.'
      );
    }

    if ($ownerId <= 0) {
      throw new InvalidArgumentException(
        'Invalid owner.'
      );
    }

    // ownership check :  checking pg belongs to owner
    $pg = $this->pgModel->findById($pgId);

    if ($pg === null) {
      throw new InvalidArgumentException(
        'PG not found.'
      );
    }

    if ((int)$pg['owner_id'] !== $ownerId) {
      throw new AuthorizationException(
        'You are not authorized to manage this PG.'
      );
    }

    return $pg;
  }

  public function verifyRoomOwnership(
    int $roomId,
    int $ownerId
  ) {

    $room = $this->roomModel->findByRoomId($roomId);

    if ($room === null) {
      throw new InvalidArgumentException(
        'Room not found.'
      );
    }

    $this->verifyPGOwnership(
      (int)$room['pg_id'],
      $ownerId
    );

    return $room;
  }

  public function verifyChargeOwnership(
    int $chargeId,
    int $ownerId
  ) {
    $charge = $this->chargeModel->findById($chargeId);

    if ($charge === null) {
      throw new InvalidArgumentException(
        'Charge not found.'
      );
    }

    $this->verifyPGOwnership((int)$charge['pg_id'], $ownerId);

    return $charge;
  }

  public function verifyImageOwnership(
    int $imageId,
    int $ownerId
  ) {
    $image = $this->pgImageModel->findById($imageId);

    if ($image === null) {
      throw new InvalidArgumentException(
        'Image not found.'
      );
    }

    $this->verifyPGOwnership((int)$image['pg_id'], $ownerId);

    return $image;
  }
}
