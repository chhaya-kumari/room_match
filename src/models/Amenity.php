<?php

class Amenity
{
  private $db;
  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function findAll()
  {
    $sql = "SELECT amenity_id, name FROM amenities ORDER BY name ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function findByPgId(int $pgId): array
  {
    $sql = "SELECT pa.amenity_id, pa.included_in_rent FROM pg_amenities pa WHERE pa.pg_id= :pg_id";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':pg_id' => $pgId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function findExistingIds(array $amenityIds)
  {
    if (empty($amenityIds)) {
      return [];
    }
    $placeholders = implode(
      ',',
      array_fill(0, count($amenityIds), '?')
    );

    $sql = "SELECT amenity_id FROM amenities WHERE amenity_id IN ($placeholders)";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($amenityIds);

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  public function replaceForPg(int $pgId, array $amenities)
  {
    try {
      $this->db->beginTransaction();

      $deleteSql = "DELETE FROM pg_amenities WHERE pg_id=:pg_id";
      $deleteStmt = $this->db->prepare($deleteSql);
      $deleteStmt->execute([
        ':pg_id' => $pgId
      ]);

      if (!empty($amenities)) {
        $insertSql = "INSERT INTO pg_amenities(pg_id, amenity_id, included_in_rent) VALUES(:pg_id, :amenity_id, :included_in_rent)";
        $insertStmt = $this->db->prepare($insertSql);
        foreach ($amenities as $amenityId => $data) {
          $insertStmt->execute([
            ':pg_id' => $pgId,
            ':amenity_id' => $amenityId,
            ':included_in_rent' => $data['included_in_rent']
          ]);
        }
      }

      $this->db->commit();
    } catch (Throwable $e) {
      if ($this->db->inTransaction()) {
        $this->db->rollBack();
      }

      throw $e;
    }
  }

  public function findSeparateChargeAmenitiesByPgId(int $pgId)
  {
    $sql = "SELECT
        pa.id AS pg_amenity_id,
        pa.pg_id,
        pa.amenity_id,
        a.name AS amenity_name,
        pa.included_in_rent
      FROM pg_amenities pa
        INNER JOIN amenities a
            ON pa.amenity_id = a.amenity_id
        WHERE pa.pg_id = :pg_id
          AND pa.included_in_rent = 0
        ORDER BY a.name ASC";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([':pg_id' => $pgId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function findSelectedForReview(int $pgId)
  {
    $sql = "SELECT
        pa.id AS pg_amenity_id,
        pa.pg_id,
        pa.amenity_id,
        a.name AS amenity_name,
        pa.included_in_rent
      FROM pg_amenities pa
        INNER JOIN amenities a
            ON pa.amenity_id = a.amenity_id
        WHERE pa.pg_id = :pg_id
        ORDER BY a.name ASC";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([':pg_id' => $pgId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

// replaceForPg = 
// BEGIN
//     DELETE existing PG amenities
//     INSERT current selected amenities
// COMMIT
