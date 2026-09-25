<?php

class AuthorizationService
{
  private PG $pgModel;
  private Room $roomModel;

  public function __construct(PG $pgModel, Room $roomModel)
  {
    $this->pgModel = $pgModel;
    $this->roomModel = $roomModel;
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
      throw new InvalidArgumentException(
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
}
