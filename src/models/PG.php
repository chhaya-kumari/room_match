<?php

class PG
{
  private $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function create(int $ownerId, int $locationId, string $pgName, string $address, float $latitude, float $longitude): int
  {
    $sql = "INSERT INTO pgs(owner_id, location_id, pg_name, address, latitude, longitude) VALUES(:owner_id,:location_id, :pg_name, :address, :latitude,:longitude)";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':owner_id' => $ownerId,
      ':location_id' => $locationId,
      ':pg_name' => $pgName,
      ':address' => $address,
      ':latitude' => $latitude,
      ':longitude' => $longitude
    ]);

    return (int) $this->db->lastInsertId();
  }

  public function findById(int $pgId): ?array
  {
    $sql = "SELECT pg_id, owner_id, location_id, pg_name, address, latitude, longitude, description, gender_preference, rules, notice_period_days, lock_in_period_months, verification_status,  listing_status, created_at, updated_at FROM pgs WHERE pg_id = :pg_id LIMIT 1";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([":pg_id" => $pgId]);

    $pg = $stmt->fetch();
    return $pg ?: null;
  }

  public function updateDetails(int $pgId, int $ownerId, ?string $description, string $genderPreference, ?string $rules, ?int $noticePeriodDays, ?int $lockInPeriodMonths): bool
  {
    $sql = "UPDATE pgs
        SET
            description = :description,
            gender_preference = :gender_preference,
            rules = :rules,
            notice_period_days = :notice_period_days,
            lock_in_period_months = :lock_in_period_months
        WHERE pg_id = :pg_id
        AND owner_id = :owner_id";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ":description" => $description,
      ":gender_preference" => $genderPreference,
      ":rules" => $rules,
      ":notice_period_days" => $noticePeriodDays,
      ":lock_in_period_months" => $lockInPeriodMonths,
      ":pg_id" => $pgId,
      ":owner_id" => $ownerId
    ]);

    return true;
  }

  public function findReviewDetailsById(int $pgId): ?array
  {
    $sql = "SELECT 
            p.pg_id,
            p.owner_id,
            p.location_id,
            p.pg_name,
            p.address,
            p.latitude,
            p.longitude,
            p.description,
            p.gender_preference,
            p.rules,
            p.notice_period_days,
            p.lock_in_period_months,
            p.verification_status,
            p.listing_status,
            l.state,
            l.city,
            l.area
        FROM pgs p
        INNER JOIN locations l
            ON p.location_id = l.location_id
        WHERE p.pg_id = :pg_id
        LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':pg_id' => $pgId]);

    $pg = $stmt->fetch(PDO::FETCH_ASSOC);

    return $pg ?: null;
  }

  public function submitForReview(int $pgId, int $ownerId): bool
  {
    $sql = "UPDATE pgs
        SET
            listing_status = 'pending',
            verification_status = 'pending',
            updated_at = CURRENT_TIMESTAMP
        WHERE pg_id = :pg_id
          AND owner_id = :owner_id
          AND listing_status = 'draft'
    ";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
      ':pg_id' => $pgId,
      ':owner_id' => $ownerId
    ]);

    return $stmt->rowCount() === 1;
  }
}
