<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

class RoomService
{
  private Room $roomModel;
  private AuthorizationService $authorizationService;

  public function __construct(PDO $db)
  {
    $this->roomModel = new Room($db);
    $this->authorizationService = new AuthorizationService($db);
  }

  public function createRoom(int $pgId, int $ownerId, string $roomType, int $capacity, int $occupiedBeds, float $rent, ?float $securityDeposit, string $bathroomType, ?string $description, bool $windowVentilation)
  {

    // verify pgId, ownerId, ownership
    $this->authorizationService->verifyPGCanBeEdited($pgId, $ownerId);

    // validate room values
    $roomValidator = new RoomValidator();
    $roomValidator::validate($roomType, $capacity, $occupiedBeds, $rent, $securityDeposit, $bathroomType, $description);

    //create room
    return $this->roomModel->create(
      $pgId,
      $roomType,
      $capacity,
      $occupiedBeds,
      $rent,
      $securityDeposit,
      $bathroomType,
      $description,
      $windowVentilation
    );
  }

  public function getRoomsByPgId(int $pgId, int $ownerId)
  {
    $this->authorizationService->verifyPGOwnership($pgId, $ownerId);
    return $this->roomModel->findByPgId($pgId);
  }

  public function getRoom(int $roomId, int $ownerId)
  {
    $room = $this->authorizationService->verifyRoomOwnership($roomId, $ownerId);

    return $room;
  }

  public function updateRoom(int $roomId, int $ownerId, string  $roomType, int $capacity, int $occupiedBeds, float $rent, ?float $securityDeposit, string $bathroomType, ?string $description, bool $windowVentilation)
  {
    $room = $this->authorizationService->verifyRoomOwnership($roomId, $ownerId);
    $this->authorizationService->verifyPGCanBeEdited(
      (int)$room['pg_id'],
      $ownerId
    );
    // validate room input
    $roomValidator = new RoomValidator();
    $roomValidator::validate($roomType, $capacity, $occupiedBeds, $rent, $securityDeposit, $bathroomType, $description);

    return $this->roomModel->update(
      $roomId,
      (int) $room['pg_id'],
      $roomType,
      $capacity,
      $occupiedBeds,
      $rent,
      $securityDeposit,
      $bathroomType,
      $description,
      $windowVentilation
    );
  }

  public function deleteRoom(int $roomId, int $ownerId)
  {
    $room = $this->authorizationService->verifyRoomOwnership($roomId, $ownerId);

    $this->authorizationService->verifyPGCanBeEdited(
      (int)$room['pg_id'],
      $ownerId
    );

    return $this->roomModel->delete(
      $roomId,
      (int) $room['pg_id']
    );
  }
}
