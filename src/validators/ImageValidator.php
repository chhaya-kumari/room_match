<?php

class ImageValidator
{
  private const MAX_FILE_SIZE = 5 * 1024 * 1024;

  private const ALLOWED_MIME_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
  ];

  public static function validateUpload($file)
  {
    if (!isset($file['error']) || !isset($file['tmp_name']) || !isset($file['size'])) {
      throw new InvalidArgumentException(
        'Invalid image upload.'
      );
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
      throw new InvalidArgumentException(
        'Image upload failed.'
      );
    }

    if ($file['size'] <= 0) {
      throw new InvalidArgumentException(
        'Uploaded image is empty.'
      );
    }

    if ($file['size'] > self::MAX_FILE_SIZE) {
      throw new InvalidArgumentException(
        'Image size should not exceed 5 MB.'
      );
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mimeType = $finfo->file(
      $file['tmp_name']
    );

    if (!isset(self::ALLOWED_MIME_TYPES[$mimeType])) {
      throw new InvalidArgumentException(
        'Only jpg and png images are allowed.'
      );
    }

    return self::ALLOWED_MIME_TYPES[$mimeType];
  }

  public static function validateImageType($imageType)
  {

    if ($imageType === null || $imageType === '') {
      return;
    }

    if (strlen($imageType) > 50) {
      throw new InvalidArgumentException(
        'Image type must not exceed 100 characters.'
      );
    }
  }
}
