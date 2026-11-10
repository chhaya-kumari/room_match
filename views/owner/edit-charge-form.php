<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Charges</title>
</head>

<body>
  <div class="edit-form">
    <h2>Edit Charge</h2>

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

    <form
      method="POST"
      class="charges-form"
      action="">

      <input type="hidden" name="csrf_token"
        value="<?= htmlspecialchars(Token::getToken()) ?>">

      <input type="hidden" name="charge_id"
        value="<?= $charge['charge_id'] ?>">

      <?php if ($charge['pg_amenity_id'] !== null): ?>

        <!-- AMENITY-BASED CHARGE -->
        <div class="amenity-charge-name">

          <label class="form-label">Charge Name</label>
          <input
            type="text"
            name="charge_name"
            id="charge_name"
            value="<?= htmlspecialchars($charge['charge_name']) ?>"
            readonly>
          <small class="text-muted">
            This charge is linked to an existing PG amenity.
            The amenity relationship cannot be changed here.
          </small>

        </div>

      <?php else: ?>

        <!-- Independent/ other charges -->

        <label for="charge_name">Charge Name</label>
        <input type="text"
          name="charge_name"
          id="charge_name"
          placeholder="e.g. Maintenance"
          maxlength="100"
          value="<?= htmlspecialchars($charge['charge_name'] ?? '') ?>">

      <?php endif; ?>

      <label for="amount">Amount</label>
      <input type="number"
        name="amount"
        id="amount"
        step="0.01"
        min="0.01"
        required
        value="<?= htmlspecialchars($charge['amount'] ?? '') ?>">

      <label for="charge_basis">Charge Basis</label>
      <select name="charge_basis" id="charge_basis" required>
        <option value="" disabled <?= empty($charge['charge_basis']) ? 'selected' : '' ?>>-- Select Charge Basis --</option>
        <option value="fixed_monthly" <?= ($charge['charge_basis'] ?? '') === 'fixed_monthly' ? 'selected' : '' ?>>Fixed Monthly</option>
        <option value="per_unit" <?= ($charge['charge_basis'] ?? '') === 'per_unit' ? 'selected' : '' ?>>Per Unit</option>
      </select>

      <label for="unit">Unit (if applicable)</label>
      <input type="text"
        name="unit"
        id="unit"
        placeholder="e.g. kWh"
        maxlength="30"
        value="<?= htmlspecialchars($charge['unit'] ?? '') ?>">

      <label for="description">Description</label>
      <textarea name="description" id="description" placeholder="optional description"><?= htmlspecialchars($charge['description'] ?? '') ?></textarea>

      <button type="submit">Update Charge</button>
      <a href="../../public/owner/charges.php?pg_id=<?= htmlspecialchars($charge['pg_id']) ?>">Cancel</a>
    </form>
  </div>
</body>

</html>