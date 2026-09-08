<!DOCTYPE html>
<html>

<head>
  <title>Login Page</title>
  <link rel="stylesheet" href="../../css/style.css">
</head>

<body>

  <form method="POST" action="../../public/auth/login.php" class="login-form auth-form">
    <h2>Login</h2>

    <?php if (!empty($error)): ?>
      <div class="error-message">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="success-message">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <input type="email" id="txtemail" name="email" placeholder="Email" required>
    <input type="password" id="txtpswrd" name="password" placeholder="Password" required>
    <button id="btn-login">Login</button>
  </form>
</body>

</html>