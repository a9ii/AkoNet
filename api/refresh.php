<?php
/**
 * API: Manual Refresh - Trigger monitoring check
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Include the monitoring logic
require_once __DIR__ . '/../cron/monitor.php';

jsonResponse([
    'success' => true,
    'message' => 'Monitoring check completed',
    'timestamp' => date('Y-m-d H:i:s')
]);
