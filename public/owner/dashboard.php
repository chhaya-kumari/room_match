<?php
require_once __DIR__ . '/../../src/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../src/security/Csrf.php';

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/db.php';
require_once $path . '/pg_recommendation/src/models/User.php';
AuthMiddleware::requireRole('owner');
$database = new Database();
$userModel = new User($database->conn);
$user_id = SessionManager::userId();
$user = $userModel->findProfile($user_id);
?>


<!DOCTYPE html>
<html>

<head>
  <title>Owner Dashboard</title>
</head>

<body>
  <h1>Welcome to Owner Dashboard</h1>

  <h2>Owner Profile</h2>

  <p>
    <strong>Name:</strong>
    <?= htmlspecialchars($user['full_name']) ?>
  </p>

  <p>
    <strong>Email:</strong>
    <?= htmlspecialchars($user['email']) ?>
  </p>

  <p>
    <strong>Contact:</strong>
    <?= htmlspecialchars($user['contact_number']) ?>
  </p>

  <p>
    <strong>Gender:</strong>
    <?= htmlspecialchars($user['gender'] ?? 'Not provided') ?>
  </p>

  <p>
    <strong>Role:</strong>
    <?= htmlspecialchars($user['role']) ?>
  </p>

  <form method="POST" action="../auth/logout.php">
    <input
      type="hidden"
      name="csrf_token"
      value="<?= htmlspecialchars(Token::getToken()) ?>">

    <button type="submit">Logout</button>
  </form>
</body>

</html>