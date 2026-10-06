<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PG Amenities Selection</title>

</head>

<body>
  <h1>PG Amenities</h1>
  <p>
    Select the amenities available at your PG
    and specify whether each one is included in rent.
  </p>
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

  <form method="POST" action="" class="amenity-form form">
    <input
      type="hidden"
      name="csrf_token"
      value="<?= htmlspecialchars(Token::getToken()) ?>">

    <input
      type="hidden"
      name="pg_id"
      value="<?= htmlspecialchars($pgId) ?>">

    <?php if (empty($amenities)): ?>
      <p>No amenities are available</p>
    <?php else: ?>
      <?php foreach ($amenities as $amenity): ?>

        <?php
        $amenityId = (int) $amenity['amenity_id'];

        $selected =
          isset($selectedAmenities[$amenityId]);

        $includedInRent =
          $selected
          ? $selectedAmenities[$amenityId]
          : 1;
        ?>

        <div class="amenity-item">
          <label>
            <input
              type="checkbox"
              class="amenity-checkbox"
              name="amenities[<?= $amenityId ?>][selected]"
              value="1"
              <?= $selected ? 'checked' : '' ?>>

            <?= htmlspecialchars($amenity['name']) ?>
          </label>

          <select
            name="amenities[<?= $amenityId ?>][included_in_rent]"
            class="rent-option"
            <?= !$selected ? 'disabled' : '' ?>>
            <option
              value="1"
              <?= $includedInRent ? 'selected' : '' ?>>
              Included in rent
            </option>

            <option
              value="0"
              <?= !$includedInRent ? 'selected' : '' ?>>
              Charged separately
            </option>
          </select>

        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <button type="submit">Save Amenities</button>

  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const checkboxes = document.querySelectorAll('.amenity-checkbox');

      checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
          const amenityItem = this.closest('.amenity-item');
          const rentOption = amenityItem.querySelector('.rent-option');
          rentOption.disabled = !this.checked;
        });
      });
    });
  </script>
</body>

</html>

<!-- POST 
amenities => [

    1 => [
        'selected' => '1',
        'included_in_rent' => '1'
    ],

    2 => [
        'selected' => '1',
        'included_in_rent' => '0'
    ]

] -->