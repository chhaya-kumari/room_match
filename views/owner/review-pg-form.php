<?php
// The controller supplies $summary, $pgId, $error and $success.

$pg = $summary['pg'] ?? [];
$rooms = $summary['rooms'] ?? [];
$amenities = $summary['amenities'] ?? [];
$charges = $summary['charges'] ?? [];
$images = $summary['images'] ?? [];
$errors = $summary['errors'] ?? [];
$isDraft = (bool)($summary['is_draft'] ?? false);
$isReady = (bool)($summary['is_ready'] ?? false);

$includedAmenities = [];
foreach ($amenities as $amenity) {
  $includedAmenities[] = $amenity['amenity_name'] . ((int)$amenity['included_in_rent'] === 1 ? '(included in rent)' : '(charged sepaerately)');
} // eg. amenity = wifi (included in rent) 

$availableBeds = 0;
foreach ($rooms as $room) {
  $availableBeds += max(0, (int) ($room['available_beds'] ?? 0));
}

$stateCityArea = array_filter([
  $pg['area'] ?? '',
  $pg['city'] ?? '',
  $pg['state'] ?? '',
], static fn($value) => trim($value) !== '');

$listingStatus = $pg['listing_status'] ?? 'draft';

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Review &amp; Publish PG</title>
  <link rel="stylesheet" href="../../public/assets/css/review-pg-style.css">
</head>

<body>
  <main class="review-page">

    <?php if ($isDraft): ?>
      <h1>Review &amp; Publish</h1>
      <p>Review the information saved for your PG before submitting it for verification.</p>
    <?php else: ?>
      <h1>PG Listing Details</h1>
      <p>Here are the details of your submitted PG listing.</p>
    <?php endif; ?>

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

    <?php if (!empty($pg)) : ?>

      <section class="review-section">
        <h2>Listing Status </h2>
        <p><strong><?= htmlspecialchars($listingStatus) ?></strong></p>
        <?php if ($listingStatus === 'pending') : ?>
          <p> Your Pg has been submitted for review </p>
        <?php elseif ($listingStatus === 'active') : ?>
          <p>This Pg has been approved already and is active.</p>
        <?php endif; ?>
      </section>

      <?php if (!empty($errors)) : ?>
        <section class="review-errors">
          <h2 id="readiness-errors-heading"> Complete these items before submitting </h2>
          <ul>
            <?php foreach ($errors as $error) : ?>
              <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
          </ul>
        </section>
      <?php elseif ($isReady && $isDraft) : ?>
        <p class="review-success"> All required checks passed. Your PG is ready to submit.</p>
      <?php endif;  ?>

      <section class="review-section">
        <h2>Basic information</h2>
        <div class="review-row">
          <strong>PG name</strong>
          <span class="review-value"><?= htmlspecialchars($pg['pg_name'] ?? '') ?></span>
        </div>
        <div class="review-row">
          <strong>Address</strong>
          <span class="review-value"><?= htmlspecialchars($pg['address'] ?? '') ?></span>
        </div>
        <div class="review-row">
          <strong>Locality</strong>
          <span class="review-value"><?= htmlspecialchars(implode(', ', $stateCityArea)) ?></span>
        </div>
        <p>Basic name, address and locality editing is not currently available in the uploaded owner controllers.</p>
      </section>

      <section class="review-section">
        <div class="review-row">
          <div>
            <h2>Additional PG details</h2>
            <p>Description, gender preference, rules, notice period and lock-in period.</p>
            <p><?= !empty($summary['has_optional_details']) ? 'Some additional details have been provided.' : 'Additional details have not been provided yet.' ?></p>
          </div>
          <?php if ($isDraft): ?>
            <a class="review-link" href="/pg_recommendation/public/owner/pg-details.php?pg_id=<?= (int)$pgId ?>">Edit details</a>
          <?php endif; ?>
        </div>
        <div class="review-row">
          <strong>Description</strong>
          <span class="review-value"><?= htmlspecialchars($pg['description'] ?? 'Not provided') ?></span>
        </div>
        <div class="review-row">
          <strong>Gender preference</strong>
          <span class="review-value"><?= htmlspecialchars($pg['gender_preference'] ?? 'Not provided') ?></span>
        </div>
        <div class="review-row">
          <strong>Rules</strong>
          <span class="review-value"><?= htmlspecialchars($pg['rules'] ?? 'Not provided') ?></span>
        </div>
        <div class="review-row">
          <strong>Notice period (days)</strong>
          <span class="review-value"><?= htmlspecialchars($pg['notice_period_days'] ?? 'Not provided') ?></span>
        </div>
        <div class="review-row">
          <strong>Lock-in period (months)</strong>
          <span class="review-value"><?= htmlspecialchars($pg['lock_in_period_months'] ?? 'Not provided') ?></span>
        </div>
      </section>

      <section class="review-section">
        <div class="review-row">
          <div>
            <h2>Rooms</h2>
            <p><?= count($rooms) ?> room type(s); <?= $availableBeds ?> available bed(s) in total.</p>
            <?php if (empty($rooms)): ?>
              <p>No rooms have been added.</p>
            <?php else: ?>
              <ul>
                <?php foreach ($rooms as $room): ?>
                  <li>
                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $room['room_type'] ?? 'Room'))) ?>:
                    <?= (int)($room['available_beds'] ?? 0) ?> available bed(s);
                    rent <?= htmlspecialchars(number_format((float)($room['rent'] ?? 0), 2)) ?>/month
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
          <?php if ($isDraft): ?>
            <a class="review-link" href="/pg_recommendation/public/owner/rooms.php?pg_id=<?= (int)$pgId ?>">Edit rooms</a>
          <?php endif; ?>
        </div>
      </section>

      <section class="review-section">
        <div class="review-row">
          <div>
            <h2>Amenities</h2>
            <?php if (empty($includedAmenities)): ?>
              <p>No amenities selected.</p>
            <?php else: ?>
              <ul>
                <?php foreach ($includedAmenities as $amenityText): ?>
                  <li><?= htmlspecialchars($amenityText) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
          <?php if ($isDraft): ?>
            <a class="review-link" href="/pg_recommendation/public/owner/amenities.php?pg_id=<?= (int)$pgId ?>">Edit amenities</a>
          <?php endif; ?>
        </div>
      </section>

      <section class="review-section">
        <div class="review-row">
          <div>
            <h2>Charges</h2>
            <?php if (empty($charges)) : ?>
              <p>No charges added.</p>
            <?php else: ?>
              <ul>
                <?php foreach ($charges as $charge) : ?>
                  <li>
                    <?= htmlspecialchars($charge['charge_name'] ?? 'Charge') ?>
                    <?= htmlspecialchars(number_format((float)($charge['amount'] ?? 0), 2)) ?>
                    <?php if (($charge['charge_basis'] ?? '') === 'per_unit'): ?>
                      per <?= htmlspecialchars($charge['unit'] ?? 'unit') ?>
                    <?php else: ?>
                      per month
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
          <?php if ($isDraft): ?>
            <a class="review-link" href="/pg_recommendation/public/owner/charges.php?pg_id=<?= (int)$pgId ?>">Edit charges</a>
          <?php endif; ?>
        </div>
      </section>

      <section class="review-section">
        <div class="review-row">
          <div>
            <h2>Images</h2>
            <p><?= count($images) ?> image(s) uploaded.</p>
            <?php if (!empty($images)): ?>
              <div class="review-images">
                <?php foreach ($images as $image): ?>
                  <img
                    src="/pg_recommendation/public/<?= htmlspecialchars(ltrim((string)$image['image_path'], '/')) ?>"
                    alt="PG property image"
                    loading="lazy">
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
          <?php if ($isDraft): ?>
            <a class="review-link" href="/pg_recommendation/public/owner/images.php?pg_id=<?= (int)$pgId ?>">Edit images</a>
          <?php endif; ?>
        </div>
      </section>

      <?php if ($isDraft): ?>
        <form method="POST" action="/pg_recommendation/public/owner/review-pg.php" onsubmit="return confirm('Submit this PG for review? You may not be able to edit it while it is under review.');">
          <input type="hidden" name="pg_id" value="<?= (int)$pgId ?>">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Token::getToken()) ?>">
          <button type="submit" <?= !$isReady ? 'disabled' : '' ?>>
            Submit for review
          </button>
        </form>
      <?php endif; ?>

      <p><a href="/pg_recommendation/public/owner/dashboard.php">Back to dashboard</a></p>
    <?php endif; ?>

  </main>
</body>

</html>