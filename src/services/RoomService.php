<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/src/models/PG.php';
require_once $path . '/pg_recommendation/src/models/Room.php';
require_once $path . '/pg_recommendation/src/validators/RoomValidator.php';
require_once $path . '/pg_recommendation/src/services/AuthorizationService.php';

class RoomService
{
  private Room $roomModel;
  private PG $pgModel;
  private AuthorizationService $authorizationService;

  public function __construct(PDO $db)
  {
    $this->pgModel = new PG($db);
    $this->roomModel = new Room($db);
    $this->authorizationService = new AuthorizationService($this->pgModel, $this->roomModel);
  }

  public function createRoom($pgId, $ownerId, $roomType, $capacity, $occupiedBeds, $rent, $securityDeposit, $bathroomType, $description, $windowVentilation)
  {

    // verify pgId, ownerId, ownership
    $pg = $this->authorizationService->verifyPGOwnership($pgId, $ownerId);

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

  public function getRoomsByPgId($pgId, $ownerId)
  {
    $pg = $this->authorizationService->verifyPGOwnership($pgId, $ownerId);

    return $this->roomModel->findByPgId($pgId);
  }

  public function getRoom($roomId, $ownerId)
  {
    $room = $this->authorizationService->verifyRoomOwnership($roomId, $ownerId);

    return $room;
  }

  public function updateRoom(int $roomId, int $ownerId,  $roomType, int $capacity, int $occupiedBeds, float $rent, ?float $securityDeposit, $bathroomType, $description, $windowVentilation)
  {
    $room = $this->authorizationService->verifyRoomOwnership($roomId, $ownerId);

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

  public function deleteRoom($roomId, $ownerId)
  {
    $room = $this->authorizationService->verifyRoomOwnership($roomId, $ownerId);

    return $this->roomModel->delete(
      $roomId,
      (int) $room['pg_id']
    );
  }
}
