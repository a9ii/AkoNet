<?php
/**
 * API: Provider Details with Chart Data
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    jsonResponse(['success' => false, 'error' => 'Invalid provider ID'], 400);
}

$provider = getProvider($id);
if (!$provider) {
    jsonResponse(['success' => false, 'error' => 'Provider not found'], 404);
}

$monitoringLogs = getMonitoringLogs24h($id);
$downtime24h = getDowntimeLogs24h($id);
$downtimeDaily = getDailyDowntime($id);

jsonResponse([
    'success' => true,
    'provider' => $provider,
    'monitoring_logs' => $monitoringLogs,
    'downtime_24h' => $downtime24h,
    'downtime_daily' => $downtimeDaily
]);
