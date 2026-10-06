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

  public function updateDetails($pgId, $ownerId, $description, $gender_preference, $rules, $notice_period_days, $lock_in_period_months)
  {
    $sql = "update pgs set description = :desc, gender_preference = :gender_preference, 
    rules = :rules,notice_period_days = :notice_period_days, lock_in_period_months = :lock_in_period_months updated_at = CURRENT_TIMESTAMP where pg_id = :pg_id and owner_id = :owner_id";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ":desc" => $description,
      ":gender_preference" => $gender_preference,
      ":rules" => $rules,
      ":notice_period_days" => $notice_period_days,
      ":lock_in_period_months" => $lock_in_period_months,
      ":pg_id" => $pgId,
      ":owner_id" => $ownerId
    ]);

    return $stmt->rowCount() > 0;
  }
}
