<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PG Image</title>
</head>

<body>

  <div class="container">
    <h2>PG Images</h2>

    <?php if (!empty($error)): ?>
      <div class="error-message">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="success-message alert alert-success">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <section>

      <h3>Add Image</h3>

      <form
        method="POST"
        class="charges-form"
        enctype="multipart/form-data"
        action="../../public/owner/images.php?pg_id=<?= htmlspecialchars($pgId) ?>"
        required>

        <input type="hidden" name="csrf_token"
          value="<?= htmlspecialchars(Token::getToken()) ?>">

        <input type="hidden" name="pg_id"
          value="<?= htmlspecialchars($pgId) ?>">

        <label for="pg_image">Select Image</label>
        <input type="file" name="pg_image" id="pg_image" accept=".jpg,.jpeg,.png" />

        <label for="image_type"> Image Type</label>
        <input type="text" id="image_type" name="image_type" />

        <button type="submit">Upload Image</button>
      </form>

      <a href="/pg_recommendation/public/owner/charges.php?pg_id=<?= (int) $pgId ?>">
        Back
      </a>

      <a href="/pg_recommendation/public/owner/review-pg.php?pg_id=<?= (int) $pgId ?>">
        Continue
      </a>
    </section>

    <!-- existing images -->
    <section>
      <h3> Existing Images </h3>
      <?php if (!empty($images)) : ?>
        <?php foreach ($images as $image) : ?>
          <div class="image-card">
            <img width="300" height="400" src="/pg_recommendation/public/<?= htmlspecialchars($image['image_path']); ?>" alt="<?= htmlspecialchars($image['image_type'] ?? 'PG Image', ENT_QUOTES, 'UTF-8') ?>" />

            <p>
              <?= htmlspecialchars($image['image_type'] ?? 'Uncategorized', ENT_QUOTES, 'UTF-8') ?>
            </p>

            <form method="POST"
              action="../../public/owner/delete-image.php"
              onsubmit="return confirm('Are you sure you want to delete this image?');">

              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Token::getToken()) ?>" />
              <input type="hidden" name="image_id" value="<?= (int)$image['image_id'] ?>" />
              <input type="hidden" name="pg_id" value="<?= (int)$pgId ?>" />

              <button type="submit">Delete</button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php else : ?>
        <p>No images have been added for this PG.</p>
      <?php endif; ?>

    </section>
  </div>
</body>

</html>