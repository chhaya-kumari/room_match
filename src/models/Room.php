<?php

class Room
{
  private $db;
  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function create($pgId, $roomType, $capacity, $occupiedBeds, $rent, $securityDeposit, $bathroomType, $description, $windowVentilation)
  {
    $sql = "insert into rooms (pg_id, room_type, capacity, occupied_beds, rent, security_deposit, bathroom_type, description, window_ventilation) values (:pg_id, :room_type, :capacity, :occupied_beds, :rent, :security_deposit, :bathroom_type, :description, :window_ventilation)";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
      ':pg_id' => $pgId,
      ':room_type' => $roomType,
      ':capacity' => $capacity,
      ':occupied_beds' => $occupiedBeds,
      ':rent' => $rent,
      ':security_deposit' => $securityDeposit,
      ':bathroom_type' => $bathroomType,
      ':description' => $description,
      ':window_ventilation' => $windowVentilation ? 1 : 0
    ]);
    return (int) $this->db->lastInsertId();
  }

  public function findByPgId($pgId)
  {
    $sql = "select room_id, pg_id, room_type, capacity, occupied_beds, (capacity - occupied_beds) AS available_beds, rent, security_deposit, bathroom_type, description, window_ventilation, created_at, updated_at from rooms where pg_id = :pg_id order by room_id asc";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':pg_id' => $pgId
    ]);

    return $stmt->fetchAll();
  }

  public function findByRoomId($roomId)
  {
    $sql = "select room_id, pg_id, room_type, capacity, occupied_beds, (capacity - occupied_beds) AS available_beds, rent, security_deposit, bathroom_type, description, window_ventilation, created_at, updated_at from rooms where room_id = :room_id limit 1";
    $stmt = $this->db->prepare($sql);

    $stmt->execute([
      ':room_id' => $roomId
    ]);

    $room = $stmt->fetch();

    return $room ?: null;
  }

  public function update(int $roomId, int $pgId, $roomType, int $capacity, int $occupiedBeds, float $rent, ?float $securityDeposit, $bathroomType, $description, $windowVentilation)
  {
    $sql = "update rooms set room_type = :room_type, capacity= :capacity, occupied_beds = :occupied_beds, rent = :rent, security_deposit = :security_deposit, bathroom_type = :bathroom_type, description = :description, window_ventilation = :window_ventilation updated_at = CURRENT_TIMESTAMP where room_id = :room_id and pg_id = :pg_id";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':room_type' => $roomType,
      ':capacity' => $capacity,
      ':occupied_beds' => $occupiedBeds,
      ':rent' => $rent,
      ':security_deposit' => $securityDeposit,
      ':bathroom_type' => $bathroomType,
      ':description' => $description,
      ':window_ventilation' => $windowVentilation ? 1 : 0,
      ':room_id' => $roomId,
      ':pg_id' => $pgId
    ]);
  }

  public function delete(int $roomId, int $pgId)
  {
    $sql = "delete from rooms where room_id = :room_id and pg_id= :pg_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ":room_id" => $roomId,
      ":pg_id" => $pgId
    ]);
  }
}
