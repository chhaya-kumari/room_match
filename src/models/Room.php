<?php

class Room
{
  private $db;
  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function create(int $pgId, string $roomType, int $capacity, int $occupiedBeds, float $rent, ?float $securityDeposit, string $bathroomType, ?string $description, bool $windowVentilation): int
  {
    $sql = "INSERT INTO rooms (pg_id, room_type, capacity, occupied_beds, rent, security_deposit, bathroom_type, description, window_ventilation) VALUES (:pg_id, :room_type, :capacity, :occupied_beds, :rent, :security_deposit, :bathroom_type, :description, :window_ventilation)";

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

  public function findByPgId(int $pgId): array
  {
    $sql = "SELECT room_id, pg_id, room_type, capacity, occupied_beds, (capacity - occupied_beds) AS available_beds, rent, security_deposit, bathroom_type, description, window_ventilation, created_at, updated_at FROM rooms WHERE pg_id = :pg_id ORDER BY room_id ASC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':pg_id' => $pgId
    ]);

    return $stmt->fetchAll();
  }

  public function findByRoomId(int $roomId)
  {
    $sql = "SELECT room_id, pg_id, room_type, capacity, occupied_beds, (capacity - occupied_beds) AS available_beds, rent, security_deposit, bathroom_type, description, window_ventilation, created_at, updated_at from rooms where room_id = :room_id limit 1";
    $stmt = $this->db->prepare($sql);

    $stmt->execute([
      ':room_id' => $roomId
    ]);

    $room = $stmt->fetch();

    return $room ?: null;
  }

  public function update(int $roomId, int $pgId, string $roomType, int $capacity, int $occupiedBeds, float $rent, ?float $securityDeposit, string $bathroomType, ?string $description, bool $windowVentilation)
  {
    $sql = "UPDATE rooms SET room_type = :room_type, capacity= :capacity, occupied_beds = :occupied_beds, rent = :rent, security_deposit = :security_deposit, bathroom_type = :bathroom_type, description = :description, window_ventilation = :window_ventilation, updated_at = CURRENT_TIMESTAMP WHERE room_id = :room_id AND pg_id = :pg_id";

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
    $sql = "DELETE FROM rooms WHERE room_id = :room_id AND pg_id= :pg_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ":room_id" => $roomId,
      ":pg_id" => $pgId
    ]);
  }
}
