# PG Image Module

## 1. Purpose
Allows PG owners to upload, view, and delete property images. Images help users assess the property's exterior, rooms, bathrooms, etc.

## 2. Database
- **Table:** `pg_images`
- **Important fields:** `image_id`, `pg_id`, `image_path`, `image_type`, `verification_status`, `created_at`, `updated_at`.
- `image_path` stores the relative path to the image file; the actual file is stored on the server.
- `verification_status` is retrieved by the model and would be updates by the admin.

## 3. Workflow

**Upload**
1. Verify PG ownership.
2. Validate the uploaded file's size and MIME type, and validate its optional image category.
3. Create a PG-specific upload directory if needed.
4. Generate a random filename and save the file.
5. Insert its path and metadata into `pg_images`.
6. If the database insert fails, delete the newly uploaded file.

**Delete**
1. Find the image and verify ownership through its associated PG.
2. Delete the database record.
3. Remove the physical file from server storage.
4. Log a warning if physical-file cleanup fails after the database record has been deleted.

## 4. MVC Responsibilities and Files

### Controller — Request handling

- `public/owner/images.php` - Loads PG images and handles upload requests, validation errors, and redirects. -
- `public/owner/delete-image.php` - Validates deletion requests, checks authorization, and redirects after deletion. -

Controllers handle sessions, CSRF checks, request data, flash messages, and redirects.

### View — User interface

- `views/owner/image-form.php` - Displays the image upload form and existing images. -

The view must use `enctype="multipart/form-data"` for file uploads.

### Model — Database operations

- `src/models/PGImage.php` - Fetches image records by PG or image ID, creates records, and deletes records using PDO. -

### Service — Business logic

- `src/services/PGImageService.php` - Coordinates ownership checks, file validation, physical file storage, database operations, and cleanup. -
- `src/validators/ImageValidator.php` - Validates uploaded files and allowed image categories. -
- `src/services/AuthorizationService.php` - Verifies PG ownership and image ownership for deletion. -

1. uploadImage Function

This method handles receiving an uploaded file, validating it, saving it safely to the server, and logging it in the database.
• Security & Authorization: It immediately calls authorizationService->verifyPGOwnership() to verify that the logged-in user ($ownerId) actually owns the PG property ($pgId) they are trying to upload an image to. If they don't, this throws an error and stops execution.
• Validation:
• ImageValidator::validateUpload($file) checks if the file is a valid image (e.g., checks file size and MIME type) and returns its file extension.
• ImageValidator::validateImageType($imageType) ensures the type (like 'cover', 'room', or 'bathroom') is allowed.
• Directory Setup: It defines a storage path unique to the PG (/uploads/pg-images/{pgId}). If that folder doesn't exist yet, it automatically creates it using mkdir() with safe permissions (0755).
• Secure File Naming: To prevent users from overwriting existing files or uploading malicious filenames, it generates a cryptographically secure random 32-character string (bin2hex(random_bytes(16))) as the new filename.
• Moving the File: It moves the file out of the server's temporary storage to its final destination using move_uploaded_file().
• Database Record & Rollback:
• It attempts to insert the relative path of the image into the database using $this->imageModel->create().
• The Safety Net (catch block): If the database insert fails for some reason (e.g., database timeout), it triggers a cleanup. It deletes (unlink()) the newly saved physical file from the server so you don't end up with "orphaned" storage clutter, then bubbles up the error.

2. deleteImage Function

This method removes an image from both the database and the server's physical storage.
• Lookup & Validation: It looks up the image metadata using $imageId. If the image doesn't exist in the database, it immediately halts and throws an InvalidArgumentException.
• Authorization: Even for a deletion, it cross-references the $image['pg_id'] linked to that file against the $ownerId to guarantee that owners can only delete images belonging to their own properties.
• Database Removal First: It attempts to delete the row from the database using $this->imageModel->delete(). If the query fails, it throws a RuntimeException and stops execution before touching the physical file.
• File System Cleanup: Once the database confirms the deletion, it resolves the file's absolute path on the server ($fullPath). If the physical file exists, it permanently deletes it using unlink().

## 5. Key Decisions
- **File restrictions:** Maximum size is 5 MB; only JPEG and PNG MIME types are accepted.
- **Image categories:**  The category is optional.
- **Safe filenames:** Random filenames are generated instead of trusting the original uploaded filename.
- **Separate storage:** Files live under `public/uploads/pg-images/{pgId}/`; the database stores their relative paths.
- **Failure cleanup:** A saved file is removed if its database insert fails, preventing an orphaned file.
- **Deletion order:** The database record is deleted before the physical file. If file cleanup fails, a warning is logged.

## 6. Tested

---------
- Upload form uses `multipart/form-data`  
- File input accepts JPG/JPEG/PNG 
- File size and MIME validation 
- Database insert failure cleanup 
- Ownership and deletion flow ; 

## 7. Problems 

* Issue 1 : An invalid image type displayed the validation error but existing images were not visible.

**Problem:** - when the image type is wrong I'm getting error message but all the existing images are also not visible now, that should not happen if imge type is wrong

**Cause:** - In controller, the images are loaded before the form is submitted

**Solution:** load the images after the POST processing as well, before rendering the page.

**Learning:** - Always load existing images before rendering the page.

