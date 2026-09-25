<!DOCTYPE html>
<html>

<head>
  <title>Create PG</title>
  <link rel="stylesheet" href="../../css/style.css">
</head>

<body>

  <form method="POST" action="../../public/owner/create-pg.php" class="login-form form">
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
    <input type="text" id="pg_name" name="pg_name" placeholder="Enter PG Name" required>

    <label for="pg_address">Complete PG Address</label>
    <textarea id="pg_address" name="pg_address" required></textarea>

    <label for="state">State:</label>
    <select id="state" name="state" required>
      <option>-- Select State --</option>
    </select>

    <label for="city">City:</label>
    <select id="city" name="city" required disabled>
      <option>-- Select City --</option>
    </select>

    <label for="area">Area:</label>
    <select id="area" name="location_id" required disabled>
      <option>-- Select Area --</option>
    </select>

    <button type="submit">Continue</button>
  </form>

  <script src="/pg_recommendation/public/js/create-pg.js"></script>
</body>

</html>