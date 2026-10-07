<?php

class Charge
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  /**
   * Get all charges belonging to a PG.
   */
  public function findByPgId(int $pgId)
  {
    $sql = "SELECT
                c.charge_id,
                c.pg_id,
                c.pg_amenity_id,
                c.charge_name,
                c.amount,
                c.charge_basis,
                c.unit,
                c.description,
                c.created_at,
                c.updated_at,
                a.name AS amenity_name
            FROM charges c
            LEFT JOIN pg_amenities pa
                ON c.pg_amenity_id = pa.id
            LEFT JOIN amenities a
                ON pa.amenity_id = a.amenity_id
            WHERE c.pg_id = :pg_id
            ORDER BY c.charge_id DESC";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
      ':pg_id' => $pgId,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // find one charge by its ID
  public function findById(int $chargeId)
  {
    $sql = "SELECT
                charge_id,
                pg_id,
                pg_amenity_id,
                charge_name,
                amount,
                charge_basis,
                unit,
                description,
                created_at,
                updated_at
            FROM charges
            WHERE charge_id = :charge_id
            LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':charge_id' => $chargeId]);
    $charge = $stmt->fetch(PDO::FETCH_ASSOC);

    return $charge ?: null;
  }

  // insert a new charge into the database
  public function create($pgId, $pgAmenityId, $chargeName, $amount, $chargeBasis, $unit, $description)
  {
    $sql = "INSERT INTO charges (pg_id, pg_amenity_id, charge_name, amount, charge_basis, unit, description)
            VALUES (:pg_id, :pg_amenity_id, :charge_name, :amount, :charge_basis, :unit, :description)";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':pg_id' => $pgId,
      ':pg_amenity_id' => $pgAmenityId,
      ':charge_name' => $chargeName,
      ':amount' => $amount,
      ':charge_basis' => $chargeBasis,
      ':unit' => $unit,
      ':description' => $description,
    ]);

    return $this->db->lastInsertId();
  }

  /* 
    Update editable charge information.
    Notice that pg_amenity_id is NOT updated.
  */

  public function update($chargeId, $chargeName, $amount, $chargeBasis, $unit, $description)
  {
    $sql = "UPDATE charges
            SET charge_name = :charge_name,
                amount = :amount,
                charge_basis = :charge_basis,
                unit = :unit,
                description = :description,
                updated_at = CURRENT_TIMESTAMP
            WHERE charge_id = :charge_id";

    $stmt = $this->db->prepare($sql);
    return $stmt->execute([
      ':charge_id' => $chargeId,
      ':charge_name' => $chargeName,
      ':amount' => $amount,
      ':charge_basis' => $chargeBasis,
      ':unit' => $unit,
      ':description' => $description,
    ]);
  }

  public function delete($chargeId)
  {
    $sql = "DELETE FROM charges WHERE charge_id = :charge_id";
    $stmt = $this->db->prepare($sql);
    return $stmt->execute([':charge_id' => $chargeId]);
  }
}
