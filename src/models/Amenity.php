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
    $sql = "select amenity_id, name from amenities order by name asc";
    $stmt = $this->db->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public function findByPgId(int $pgId)
  {
    $sql = "select pa.amenity_id, pa.included_in_rent from pg_amenities pa where pa.pg_id= :pg_id";
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

    $sql = "select amenity_id from amenities where amenity_id in ($placeholders)";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($amenityIds);

    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  public function replaceForPg(int $pgId, $amenities)
  {
    try {
      $this->db->beginTransaction();

      $deleteSql = "Delete from pg_amenities where pg_id=:pg_id";
      $deleteStmt = $this->db->prepare($deleteSql);
      $deleteStmt->execute([
        ':pg_id' => $pgId
      ]);

      if (!empty($amenities)) {
        $insertSql = "insert into pg_amenities(pg_id, amenity_id, included_in_rent) values(:pg_id, :amenity_id, :included_in_rent)";
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
}

// replaceForPg = 
// BEGIN
//     DELETE existing PG amenities
//     INSERT current selected amenities
// COMMIT
