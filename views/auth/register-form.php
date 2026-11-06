<!DOCTYPE html>
<html>

<head>
  <title>Register Form</title>
  <link rel="stylesheet" href="../../css/style.css">
</head>

<body>

  <form class="register-form auth-form" action="../../public/auth/register.php" method="POST">
    <input
      type="hidden"
      name="csrf_token"
      value="<?= htmlspecialchars(Token::getToken()) ?>">

    <h2>Register Form</h2>
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

    <label for="fullname">Full Name:</label>
    <input type="text" id="fullname" name="fullname" placeholder="Full Name" value="<?= htmlspecialchars($old['fullname'] ?? '') ?>" required>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" placeholder="Email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>

    <label for="contact">Contact Number:</label>
    <input type="tel" id="contact" name="contact" placeholder="Contact Number" value="<?= htmlspecialchars($old['contact'] ?? '') ?>" required>

    <div id="gender_box">
      <label for="gender-box">Gender:</label>

      <input type="radio" id="gender_male" name="gender" value="male" <?= isset($old['gender']) && $old['gender'] === 'male' ? 'checked' : '' ?> required>
      <label for="gender_male">Male</label>

      <input type="radio" id="gender_female" name="gender" value="female" <?= isset($old['gender']) && $old['gender'] === 'female' ? 'checked' : '' ?> required>
      <label for="gender_female">Female</label>

      <input type="radio" id="gender_other" name="gender" value="other" <?= isset($old['gender']) && $old['gender'] === 'other' ? 'checked' : '' ?> required>
      <label for="gender_other">Other</label>
    </div>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" placeholder="Password" required>

    <label for="confirm_password">Confirm Password:</label>
    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>

    <label for="role">Role:</label>
    <select id="role" name="role" required>
      <option value="">Select Role</option>
      <option value="owner" <?= isset($old['role']) && $old['role'] === 'owner' ? 'selected' : '' ?>>Owner</option>
      <option value="user" <?= isset($old['role']) && $old['role'] === 'user' ? 'selected' : '' ?>>User</option>
    </select>

    <button type="submit">Register</button>

  </form>

</body>

</html>