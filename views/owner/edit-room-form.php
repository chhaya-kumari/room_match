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
    action="">

    <h2>Edit Rooms</h2>
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


    <input type="hidden" name="csrf_token"
      value="<?= htmlspecialchars(Token::getToken()) ?>">

    <input type="hidden" name="room_id"
      value="<?= htmlspecialchars($room['room_id']) ?>">

    <!-- Room Type -->
    <label for="room_type">Room Type</label>

    <select id="room_type" name="room_type" required>

      <option value="">-- Select Room Type --</option>
      <option value="single" <?= ($room['room_type'] ?? '') === 'single' ? 'selected' : '' ?>>Single</option>
      <option value="double" <?= ($room['room_type'] ?? '') === 'double' ? 'selected' : '' ?>>Double</option>
      <option value="triple" <?= ($room['room_type'] ?? '') === 'triple' ? 'selected' : '' ?>>Triple</option>
      <option value="multi_sharing" <?= ($room['room_type'] ?? '') === 'multi_sharing' ? 'selected' : '' ?>>Multi Sharing</option>

    </select>


    <!-- Capacity -->
    <label for="capacity"> Capacity </label>
    <input
      type="number"
      id="capacity"
      name="capacity"
      min="1"
      required
      value="<?= htmlspecialchars(
                $room['capacity'] ?? ''
              ) ?>">


    <!-- Occupied Beds -->
    <label for="occupied_beds">Occupied Beds</label>
    <input
      type="number"
      id="occupied_beds"
      name="occupied_beds"
      min="0"
      value="<?= htmlspecialchars(
                $room['occupied_beds'] ?? ''
              ) ?>"
      required>

    <!-- Rent -->
    <label for="rent">Monthly Rent</label>

    <input
      type="number"
      id="rent"
      name="rent"
      min="0.01"
      step="0.01"
      required
      value="<?= htmlspecialchars(
                $room['rent'] ?? ''
              ) ?>">

    <!-- Security Deposit -->
    <label for="security_deposit">Security Deposit</label>

    <input
      type="number"
      id="security_deposit"
      name="security_deposit"
      min="0"
      step="0.01"
      value="<?= htmlspecialchars($room['security_deposit'] ?? '') ?>">

    <!-- Bathroom -->
    <label for="bathroom_type">Bathroom</label>

    <select id="bathroom_type" name="bathroom_type" required>
      <option value="" disabled <?= empty($room['bathroom_type']) ? 'selected' : '' ?>>-- Select Bathroom Type --</option>

      <option value="shared" <?= ($room['bathroom_type'] ?? '') === 'shared' ? 'selected' : '' ?>>Shared</option>

      <option value="attached" <?= ($room['bathroom_type'] ?? '') === 'attached' ? 'selected' : '' ?>>Attached</option>

      <option value="none" <?= ($room['bathroom_type'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
    </select>


    <!-- Description -->
    <label for="description">Room Description</label>
    <textarea
      id="description"
      name="description"
      maxlength="2000">
      <?= htmlspecialchars($room['description'] ?? '') ?>
    </textarea>

    <!-- Window Ventilation -->
    <label for="window">Window Ventilation Available </label>
    <input
      type="checkbox"
      id="window"
      name="window_ventilation"
      value="1"
      <?= (!empty($room['window_ventilation'])) ? 'checked' : '' ?>>

    <button type="submit">Update Room</button>
  </form>

  <a href="../../public/owner/rooms.php?pg_id=<?= htmlspecialchars($room['pg_id']) ?>">
    Cancel
  </a>

</body>

</html>