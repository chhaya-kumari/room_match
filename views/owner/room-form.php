<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>PG Rooms</title>

  <link rel="stylesheet" href="../../css/style.css">
</head>

<body>

  <form
    method="POST"
    class="rooms-form form"
    action="../../public/owner/rooms.php?pg_id=<?= htmlspecialchars($pgId) ?>">

    <h2>PG Rooms</h2>
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

    <h3>Add Room</h3>
    <input type="hidden" name="csrf_token"
      value="<?= htmlspecialchars(Token::getToken()) ?>">

    <input type="hidden" name="pg_id"
      value="<?= htmlspecialchars($pgId) ?>">

    <!-- Room Type -->
    <label for="room_type">Room Type</label>

    <select id="room_type" name="room_type" required>

      <option value="">-- Select Room Type --</option>
      <option value="single">Single</option>
      <option value="double">Double</option>
      <option value="triple">Triple</option>
      <option value="multi_sharing">Multi Sharing</option>

    </select>


    <!-- Capacity -->
    <label for="capacity"> Capacity </label>
    <input
      type="number"
      id="capacity"
      name="capacity"
      min="1"
      required>


    <!-- Occupied Beds -->
    <label for="occupied_beds">Occupied Beds</label>
    <input
      type="number"
      id="occupied_beds"
      name="occupied_beds"
      min="0"
      value="0"
      required>

    <!-- Rent -->
    <label for="rent">Monthly Rent</label>

    <input
      type="number"
      id="rent"
      name="rent"
      min="0.01"
      step="0.01"
      required>

    <!-- Security Deposit -->
    <label for="security_deposit">Security Deposit</label>

    <input
      type="number"
      id="security_deposit"
      name="security_deposit"
      min="0"
      step="0.01">

    <!-- Bathroom -->
    <label for="bathroom_type">Bathroom</label>

    <select id="bathroom_type" name="bathroom_type" required>
      <option value="shared">Shared</option>

      <option value="attached">Attached</option>

      <option value="none">None</option>
    </select>


    <!-- Description -->
    <label for="description">Room Description</label>
    <textarea
      id="description"
      name="description"
      maxlength="2000"></textarea>

    <!-- Window Ventilation -->
    <label>
      <input
        type="checkbox"
        name="window_ventilation"
        value="1"
        checked>
      Window / Ventilation Available
    </label>

    <button type="submit">Add Room</button>
  </form>

  <hr>
  <div class='rooms'>
    <h3>Existing Rooms</h3>

    <?php if (empty($rooms)): ?>

      <p>No rooms added yet.</p>

    <?php else: ?>

      <?php foreach ($rooms as $room): ?>

        <div class="room-card">

          <h4>
            <?= htmlspecialchars(
              ucwords(
                str_replace(
                  '_',
                  ' ',
                  $room['room_type']
                )
              )
            ) ?>
          </h4>

          <p>
            Capacity:
            <?= htmlspecialchars($room['capacity']) ?>
          </p>

          <p>
            Occupied:
            <?= htmlspecialchars($room['occupied_beds']) ?>
          </p>

          <p>
            Available Beds:
            <?= htmlspecialchars(
              $room['available_beds']
            ) ?>
          </p>

          <p>
            Rent:
            ₹<?= htmlspecialchars($room['rent']) ?>
          </p>

          <p>
            Security Deposit:
            <?= $room['security_deposit'] !== null
              ? '₹' . htmlspecialchars($room['security_deposit'])
              : 'Not specified'
            ?>
          </p>

          <p>
            Bathroom:
            <?= htmlspecialchars($room['bathroom_type']) ?>
          </p>

          <p>
            Window Ventilation:
            <?= htmlspecialchars($room['window_ventilation']) ?>
          </p>

          <a href="/pg_recommendation/public/owner/edit-room.php?room_id=<?= htmlspecialchars($room['room_id']) ?>">
            Edit
          </a>

          <form method="POST" class="form" action="delete-room.php" onsubmit="return confirm('Are you sure you want to delete this room?');">
            <input type="hidden" name="room_id" value="<?= htmlspecialchars($roomId) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(Token::getToken()) ?>">

            <button type="submit">
              Delete
            </button>
          </form>

        </div>
  </div>
<?php endforeach; ?>

<?php endif; ?>


<button type="button">
  Continue
</button>

</body>

</html>