<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Charge form</title>
</head>

<body>

  <div class="container">

    <h2>PG Charges</h2>

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

      <h3>Add Charge</h3>

      <form
        method="POST"
        class="charges-form"
        action="../../public/owner/charges.php?pg_id=<?= htmlspecialchars($pgId) ?>">

        <input type="hidden" name="csrf_token"
          value="<?= htmlspecialchars(Token::getToken()) ?>">

        <input type="hidden" name="pg_id"
          value="<?= htmlspecialchars($pgId) ?>">

        <!--Existing separately billed amenity-->

        <label for="pg_amenity_id">
          Charge for Existing Amenity
        </label>

        <select
          name="pg_amenity_id"
          id="pg_amenity_id">

          <option value="">
            -- Other / New Charge --
          </option>

          <?php foreach ($separateAmenities as $amenity): ?>
            <option value="<?= (int)$amenity['pg_amenity_id'] ?>">
              <?= htmlspecialchars($amenity['amenity_name']) ?>
            </option>
          <?php endforeach; ?>

        </select>

        <!-- Independent/ other charges -->

        <label for="charge_name">Charge Name</label>
        <input type="text" name="charge_name" id="charge_name" placeholder="e.g. Maintenance" maxlength="100" required>

        <label for="amount">Amount</label>
        <input type="number" name="amount" id="amount" step="0.01" min="0.01" required>

        <label for="charge_basis">Charge Basis</label>
        <select name="charge_basis" id="charge_basis" required>
          <option value="">-- Select Charge Basis --</option>
          <option value="fixed_monthly">Fixed Monthly</option>
          <option value="per_unit">Per Unit</option>
        </select>

        <label for="unit">Unit (if applicable)</label>
        <input type="text" name="unit" id="unit" placeholder="e.g. kWh" maxlength="30">

        <label for="description">Description</label>
        <textarea name="description" id="description" placeholder="optional description"></textarea>

        <button type="submit">Add Charge</button>
      </form>
    </section>

    <!-- Existing Charges -->
    <section>
      <h3>Existing Charges</h3>
      <?php if (!empty($charges)): ?>
        <?php foreach ($charges as $charge): ?>
          <div class="charge-card">

            <h4><?= htmlspecialchars($charge['charge_name']) ?></h4>

            <?php if (!empty($charge['amenity_name'])): ?>
              <p>Amenity: <?= htmlspecialchars($charge['amenity_name']) ?></p>
            <?php else : ?>
              <p>Independent Charge</p>
            <?php endif; ?>

            <p>Amount: ₹<?= htmlspecialchars($charge['amount']) ?></p>

            <p>Basis: <?= htmlspecialchars($charge['charge_basis']) ?>
              <?php if (!empty($charge['unit'])): ?>
                / <?= htmlspecialchars($charge['unit']) ?>
              <?php endif; ?>
            </p>

            <?php if (!empty($charge['description'])): ?>
              <p>Description: <?= nl2br(htmlspecialchars($charge['description'])) ?></p>
            <?php endif; ?>

            <a href="/pg_recommendation/public/owner/edit-charge.php?charge_id=<?= htmlspecialchars($charge['charge_id']) ?>">
              Edit
            </a>

            <form
              method="POST"
              action="../../public/owner/delete-charge.php"
              onsubmit="return confirm('Are you sure you want to delete this charge?');">

              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Token::getToken()) ?>">
              <input type="hidden" name="charge_id" value="<?= htmlspecialchars($charge['charge_id']) ?>">

              <button type="submit">Delete</button>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No charges have been added for this PG.</p>
      <?php endif; ?>
    </section>

    <!-- Continue -->

    <section>
      <a href="images.php?pg_id=<?= (int)$pgId ?>" class="button">
        Continue
      </a>
    </section>

  </div>

  <script>
    // charge name should be disabled if an existing amenity is selected
    const pgAmenitySelect = document.getElementById('pg_amenity_id');
    const chargeNameInput = document.getElementById('charge_name');

    function toggleChargeNameInput() {
      if (pgAmenitySelect.value !== '') {
        chargeNameInput.disabled = true;
      } else {
        chargeNameInput.disabled = false;
      }
    }

    pgAmenitySelect.addEventListener('change', toggleChargeNameInput);
    toggleChargeNameInput(); // Call it once on page load to set the initial state
  </script>
</body>

</html>