<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

class PGImageService
{
  private PGImage $imageModel;
  private AuthorizationService $authorizationService;

  public function __construct(PDO $db)
  {
    $this->imageModel = new PGImage($db);
    $this->authorizationService = new AuthorizationService($db);
  }

  public function getImageByPgId(int $pgId, int $ownerId)
  {
    $this->authorizationService->verifyPGOwnership($pgId, $ownerId);
    return $this->imageModel->findByPgId($pgId);
  }

  public function uploadImage(int $pgId, int $ownerId, $file, $imageType)
  {
    $this->authorizationService->verifyPGCanBeEdited($pgId, $ownerId);

    $extension = ImageValidator::validateUpload($file);
    ImageValidator::validateImageType($imageType);

    $directory = $_SERVER['DOCUMENT_ROOT'] . '/pg_recommendation/public/uploads/pg-images/' . $pgId;

    if (!is_dir($directory)) {
      if (!mkdir($directory, 0755, true)) { //  0755 =  secure default | owner: read, write, execute ;;;  group & others : read, write;
        throw new RuntimeException(
          'Unable to create image directory.'
        );
      }
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = $directory . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
      throw new RuntimeException(
        'Unable to save uploaded image.'
      );
    }

    $relativePath = 'uploads/pg-images/' . $pgId . '/' . $filename;

    try {
      return $this->imageModel->create($pgId, $relativePath, $imageType);
    } catch (Throwable $e) {
      if (file_exists($destination)) {
        unlink($destination);
      }

      throw $e;
    }
  }

  public function deleteImage(int $imageId, int $ownerId)
  {
    $image = $this->imageModel->findById($imageId);
    if ($image == null) {
      throw new InvalidArgumentException(
        'Image not found'
      );
    }

    $this->authorizationService->verifyPGCanBeEdited($image['pg_id'], $ownerId);

    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/pg_recommendation/public/' . $image['image_path'];

    $deleted = $this->imageModel->delete($imageId);

    if (!$deleted) {
      throw new RuntimeException(
        'Unable to delete image.'
      );
    }

    if (file_exists($fullPath)) {
      if (!unlink($fullPath)) {
        error_log("CRITICAL CLEANUP WARNING: Database record for Image ID {$imageId} was removed, but the physical file at '{$fullPath}' could not be deleted due to a system or permission error.");
      }
    }

    return true;
  }
}
