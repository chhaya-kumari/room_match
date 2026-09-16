<?php

class PG
{
  private $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function create($ownerId, $locationId, $pgName, $address, $latitude, $longitude)
  {
    $sql = "insert into pgs(owner_id, location_id, pg_name, address, latitude, longitude) values(:owner_id,:location_id, :pg_name, :address, :latitude,:longitude) ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':owner_id' => $ownerId,
      ':location_id' => $locationId,
      ':pg_name' => $pgName,
      ':address' => $address,
      ':latitude' => $latitude,
      ':longitude' => $longitude
    ]);

    return $this->db->lastInsertId();
  }

  public function findById($pgId)
  {
    $sql = "select pg_id, owner_id, location_id, pg_name, address, latitude, longitude, description, gender_preference, rules, notice_period_days, lock_in_period_months, verification_status,  listing_status, created_at, updated_at from pgs where pg_id = :pg_id limit 1";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([":pg_id" => $pgId]);

    $pg = $stmt->fetch();
    return $pg ?: null;
  }
}
