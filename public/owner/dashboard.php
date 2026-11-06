<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

SessionManager::start();
AuthMiddleware::requireRole('owner');

$ownerId = (int)SessionManager::userId();

$error = SessionManager::getFlash('error');
$success = SessionManager::getFlash('success');

$ownerName = $_SESSION['full_name'] ?? 'PG Owner';

$dashboardData = [
  'summary' => [
    'total_pgs' => 0,
    'total_rooms' => 0,
    'occupied_beds' => 0,
    'available_beds' => 0,
    'draft_pgs' => 0,
    'pending_pgs' => 0,
    'active_pgs' => 0,
  ],
  'pgs' => [],
];

try {
  $database = new Database();
  $dashboardService = new OwnerDashboardService($database->conn);

  $dashboardData = $dashboardService->getDashboardData($ownerId);
} catch (Throwable $e) {
  error_log('Owner dashboard loading failed: ' . $e->getMessage());
  $error = 'Unable to load your dashboard right now. Please try again.';
}

$summary = $dashboardData['summary'];
$pgs = $dashboardData['pgs'];

require_once $path . '/pg_recommendation/views/owner/dashboard-form.php';
