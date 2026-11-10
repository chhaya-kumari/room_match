<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PG Details</title>
  <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>

<body>
  <div class="container">
    <h2>Add PG Details</h2>
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
      <form method="POST" class="pg-details-form form" action="../../public/owner/pg-details.php">
        <!-- auth_form class for just testing and basic css -->
        <input
          type="hidden"
          name="csrf_token"
          value="<?= htmlspecialchars(Token::getToken()) ?>">

        <input type="hidden" name="pg_id" value="<?= htmlspecialchars($pg['pg_id']) ?>">

        <label for="description">PG Description</label>
        <textarea
          id="description"
          name="description"
          maxlength="2000"
          placeholder="Describe your PG, facilities, surroundings, etc."><?= htmlspecialchars($pg['description'] ?? '') ?>
        </textarea>

        <label for="gender_preference">Gender Preference</label>
        <select id="gender_preference" name="gender_preference" required>
          <option value="co-ed" <?= (($pg['gender_preference'] ?? 'co-ed') === 'co-ed') ? 'selected' : '' ?>>Co-ed</option>
          <option value="male" <?= (($pg['gender_preference'] ?? '') === 'male') ? 'selected' : '' ?>>Male</option>
          <option value="female" <?= (($pg['gender_preference'] ?? '') === 'female') ? 'selected' : '' ?>>Female</option>
        </select>

        <label for="rules">PG Rules</label>
        <textarea
          id="rules"
          name="rules"
          maxlength="3000"
          placeholder="Enter PG rules..."><?= htmlspecialchars($pg['rules'] ?? '') ?>
        </textarea>

        <label for="notice_period_days">Notice Period Days</label>
        <select name="notice_period_days" id="notice_period_days">
          <option value="">-- Select --</option>
          <option value="7" <?= (isset($pg['notice_period_days']) && (int)$pg['notice_period_days'] === 7) ? 'selected' : '' ?>>7 days</option>
          <option value="15" <?= (isset($pg['notice_period_days']) && (int)$pg['notice_period_days'] === 15) ? 'selected' : '' ?>>15 days</option>
          <option value="30" <?= (isset($pg['notice_period_days']) && (int)$pg['notice_period_days'] === 30) ? 'selected' : '' ?>>30 days</option>
          <option value="60" <?= (isset($pg['notice_period_days']) && (int)$pg['notice_period_days'] === 60) ? 'selected' : '' ?>>60 days</option>
        </select>

        <label for="lock_in_period_months">Lock In Period Month</label>
        <select name="lock_in_period_months" id="lock_in_period_months">
          <option value="">-- Select --</option>
          <option value="1" <?= (isset($pg['lock_in_period_months']) && (int)$pg['lock_in_period_months'] === 1) ? 'selected' : '' ?>>1 month</option>
          <option value="3" <?= (isset($pg['lock_in_period_months']) && (int)$pg['lock_in_period_months'] === 3) ? 'selected' : '' ?>>3 months</option>
          <option value="6" <?= (isset($pg['lock_in_period_months']) && (int)$pg['lock_in_period_months'] === 6) ? 'selected' : '' ?>>6 months</option>
          <option value="12" <?= (isset($pg['lock_in_period_months']) && (int)$pg['lock_in_period_months'] === 12) ? 'selected' : '' ?>>12 months</option>
        </select>

        <button type='submit'> Add Details </button>
      </form>
    </section>
    <a href="/pg_recommendation/public/owner/rooms.php?pg_id=<?= (int) $pgId ?>">
      Continue
    </a>
  </div>
</body>

</html>