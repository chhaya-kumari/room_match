<?php

$path = $_SERVER['DOCUMENT_ROOT'];
require_once $path . '/pg_recommendation/config/fileautoloader.php';

class OwnerDashboardService
{
  private OwnerDashboard $dashboardModel;

  public function __construct(PDO $db)
  {
    $this->dashboardModel = new OwnerDashboard($db);
  }

  public function getDashboardData(int $ownerId): array
  {
    if ($ownerId <= 0) {
      throw new InvalidArgumentException('Invalid owner.');
    }

    return [
      'summary' => $this->dashboardModel->getSummaryByOwnerId($ownerId),
      'pgs' => $this->dashboardModel->getPgCardsByOwnerId($ownerId),
    ];
  }
}
