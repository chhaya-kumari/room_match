<!DOCTYPE html>
<html>

<head>
  <title>Create PG</title>
  <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>

<body>

  <form method="POST" action="../../public/owner/create-pg.php" class="login-form form" id="create-pg-form">
    <input
      type="hidden"
      name="csrf_token"
      value="<?= htmlspecialchars(Token::getToken()) ?>">

    <h2>Create Your PG</h2>
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

    <label for="pg_name">PG Name</label>
    <input type="text" id="pg_name" name="pg_name" placeholder="Enter PG Name" value="<?= htmlspecialchars($pgName, ENT_QUOTES, 'UTF-8') ?>" required>

    <label for="pg_address">Complete PG Address</label>
    <textarea id="pg_address" name="pg_address" required><?= htmlspecialchars($pgAddress, ENT_QUOTES, 'UTF-8') ?></textarea>

    <label for="state">State:</label>
    <select id="state" name="state" required>
      <option value="">-- Select State --</option>
    </select>

    <label for="city">City:</label>
    <select id="city" name="city" required disabled>
      <option value="">-- Select City --</option>
    </select>

    <label for="area">Area:</label>
    <select id="area" name="location_id" required disabled>
      <option value="">-- Select Area --</option>
    </select>

    <button type="submit" id="create-pg-submit">Create PG</button>
  </form>

  <script src="/pg_recommendation/public/assets/js/create-pg.js"></script>
</body>

</html>