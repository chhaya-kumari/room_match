<?php
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>
  <main class="main-content">
    <header class="topbar">
      <div>
        <p class="eyebrow">OWNER WORKSPACE</p>
        <h1>Dashboard</h1>
      </div>

      <a class="button button-primary"
        href="/pg_recommendation/public/owner/create-pg.php">
        + Add New PG
      </a>
    </header>

    <?php if (!empty($error)): ?>
      <p role="alert">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
      </p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <p role="status">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
      </p>
    <?php endif; ?>

    <section class="welcome-section">
      <div>
        <p>YOUR OVERVIEW</p>
        <h2>
          Welcome back,
          <?= htmlspecialchars($ownerName) ?>!
        </h2>
        <p class="muted">
          Here's an overview of your PG properties.
        </p>
      </div>
      <div class="welcome-decoration">⌂</div>
    </section>

    <section>
      <h2>Overview</h2>

      <p>Total PGs: <?= $summary['total_pgs'] ?></p>
      <p>Total rooms: <?= $summary['total_rooms'] ?></p>
      <p>Occupied beds: <?= $summary['occupied_beds'] ?></p>
      <p>Available beds: <?= $summary['available_beds'] ?></p>

      <p>Draft listings: <?= $summary['draft_pgs'] ?></p>
      <p>Pending review: <?= $summary['pending_pgs'] ?></p>
      <p>Active listings: <?= $summary['active_pgs'] ?></p>
    </section>

    <section>
      <h2>Your PGs</h2>

      <?php if (empty($pgs)): ?>
        <p>You have not added a PG yet.</p>
        <a href="/pg_recommendation/public/owner/create-pg.php">
          Add your first PG
        </a>
      <?php else: ?>
        <?php foreach ($pgs as $pg): ?>
          <article>
            <h3>
              <?= htmlspecialchars($pg['pg_name'], ENT_QUOTES, 'UTF-8') ?>
            </h3>

            <p>
              <?= htmlspecialchars($pg['area'] ?? '', ENT_QUOTES, 'UTF-8') ?>,
              <?= htmlspecialchars($pg['city'] ?? '', ENT_QUOTES, 'UTF-8') ?>,
              <?= htmlspecialchars($pg['state'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p>
              Status:
              <?= htmlspecialchars($pg['listing_status'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p>Rooms: <?= (int)$pg['total_rooms'] ?></p>
            <p>Occupied beds: <?= (int)$pg['occupied_beds'] ?></p>
            <p>Available beds: <?= (int)$pg['available_beds'] ?></p>

            <a href="/pg_recommendation/public/owner/review-pg.php?pg_id=<?= (int)$pg['pg_id'] ?>">
              View PG details
            </a>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </main>
</body>

</html>


<?php require __DIR__ . '/layouts/footer.php'; ?>