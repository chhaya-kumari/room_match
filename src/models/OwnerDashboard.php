<?php

class OwnerDashboard
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  /**
   * Summary numbers across all PGs owned by this owner.
   */
  public function getSummaryByOwnerId(int $ownerId): array
  {
    $sql = "
            SELECT
                COUNT(*) AS total_pgs,

                COALESCE(SUM(room_totals.total_rooms), 0) AS total_rooms,
                COALESCE(SUM(room_totals.occupied_beds), 0) AS occupied_beds,
                COALESCE(SUM(room_totals.available_beds), 0) AS available_beds,

                SUM(CASE WHEN p.listing_status = 'draft' THEN 1 ELSE 0 END)
                    AS draft_pgs,

                SUM(CASE WHEN p.listing_status = 'pending' THEN 1 ELSE 0 END)
                    AS pending_pgs,

                SUM(CASE WHEN p.listing_status = 'active' THEN 1 ELSE 0 END)
                    AS active_pgs

            FROM pgs p

            LEFT JOIN (
                SELECT
                    pg_id,
                    COUNT(*) AS total_rooms,
                    SUM(occupied_beds) AS occupied_beds,
                    SUM(GREATEST(capacity - occupied_beds, 0)) AS available_beds
                FROM rooms
                GROUP BY pg_id
            ) room_totals ON room_totals.pg_id = p.pg_id

            WHERE p.owner_id = :owner_id
        ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':owner_id' => $ownerId]);

    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
      'total_pgs' => (int)($summary['total_pgs'] ?? 0),
      'total_rooms' => (int)($summary['total_rooms'] ?? 0),
      'occupied_beds' => (int)($summary['occupied_beds'] ?? 0),
      'available_beds' => (int)($summary['available_beds'] ?? 0),
      'draft_pgs' => (int)($summary['draft_pgs'] ?? 0),
      'pending_pgs' => (int)($summary['pending_pgs'] ?? 0),
      'active_pgs' => (int)($summary['active_pgs'] ?? 0),
    ];
  }

  /**
   * One row per PG, with its location and room/beds totals.
   */
  public function getPgCardsByOwnerId(int $ownerId): array
  {
    $sql = "
            SELECT
                p.pg_id,
                p.pg_name,
                p.address,
                p.listing_status,
                p.verification_status,
                l.state,
                l.city,
                l.area,

                COALESCE(room_totals.total_rooms, 0) AS total_rooms,
                COALESCE(room_totals.occupied_beds, 0) AS occupied_beds,
                COALESCE(room_totals.available_beds, 0) AS available_beds

            FROM pgs p

            LEFT JOIN locations l
                ON l.location_id = p.location_id

            LEFT JOIN (
                SELECT
                    pg_id,
                    COUNT(*) AS total_rooms,
                    SUM(occupied_beds) AS occupied_beds,
                    SUM(GREATEST(capacity - occupied_beds, 0)) AS available_beds
                FROM rooms
                GROUP BY pg_id
            ) room_totals ON room_totals.pg_id = p.pg_id

            WHERE p.owner_id = :owner_id
            ORDER BY p.created_at DESC, p.pg_id DESC
        ";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([':owner_id' => $ownerId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
