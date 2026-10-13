<?php

class PGImage
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  /**
   * Get all images belonging to a PG.
   */
  public function findByPgId(int $pgId)
  {
    $sql = "SELECT
                    image_id,
                    pg_id,
                    image_path,
                    image_type,
                    verification_status,
                    created_at,
                    updated_at
                FROM pg_images
                WHERE pg_id = :pg_id
                ORDER BY image_id DESC";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
      ':pg_id' => $pgId,
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Find one image by ID.
   */
  public function findById(int $imageId)
  {
    $sql = "SELECT
                    image_id,
                    pg_id,
                    image_path,
                    image_type,
                    verification_status,
                    created_at,
                    updated_at
                FROM pg_images
                WHERE image_id = :image_id
                LIMIT 1";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
      ':image_id' => $imageId,
    ]);

    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    return $image ?: null;
  }

  /**
   * Create a new PG image record.
   */
  public function create(int $pgId, $imagePath, $imageType)
  {
    $sql = "INSERT INTO pg_images (pg_id, image_path, image_type) VALUES (:pg_id, :image_path, :image_type)";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
      ':pg_id' => $pgId,
      ':image_path' => $imagePath,
      ':image_type' => $imageType,
    ]);

    return $this->db->lastInsertId();
  }

  /**
   * Delete an image record.
   */
  public function delete(int $imageId)
  {
    $sql = "DELETE FROM pg_images WHERE image_id = :image_id";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
      ':image_id' => $imageId,
    ]);
  }
}
