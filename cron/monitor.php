<?php
/**
 * AkoNet Web Monitor - Cron Monitoring Script
 * 
 * Usage: php monitor.php
 * Cron:  * /5 * * * * php /path/to/cron/monitor.php
 * 
 * This script pings all active providers and logs results.
 */

// Prevent direct web access when run standalone
if (php_sapi_name() !== 'cli' && !defined('MANUAL_REFRESH')) {
    // Allow inclusion from api/refresh.php
    if (!isset($GLOBALS['_REFRESH_CALL'])) {
        define('MANUAL_REFRESH', true);
    }
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

/**
 * Ping a host and get results
 * Compatible with both Linux and Windows
 */
function pingHost($host, $count = null, $timeout = null) {
    $count = $count ?? PING_COUNT;
    $timeout = $timeout ?? PING_TIMEOUT;
    
    $result = [
        'status' => 'down',
        'ping' => 0,
        'packet_loss' => 100
    ];

    // Determine OS
    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    
    if ($isWindows) {
        $cmd = "ping -n {$count} -w " . ($timeout * 1000) . " " . escapeshellarg($host) . " 2>&1";
    } else {
        $cmd = "ping -c {$count} -W {$timeout} " . escapeshellarg($host) . " 2>&1";
    }

    $output = shell_exec($cmd);
    
    if ($output === null) {
        return $result;
    }

    // Parse packet loss
    if (preg_match('/(\d+)%\s*(packet\s*)?loss/i', $output, $matches)) {
        $result['packet_loss'] = (float)$matches[1];
    }

    // Parse average ping
    if ($isWindows) {
        // Windows: Average = 12ms
        if (preg_match('/Average\s*=\s*(\d+)ms/i', $output, $matches)) {
            $result['ping'] = (float)$matches[1];
        }
    } else {
        // Linux: rtt min/avg/max/mdev = 11.234/12.456/13.789/0.678 ms
        if (preg_match('/\/(\d+\.?\d*)\//', $output, $matches)) {
            $result['ping'] = (float)$matches[1];
        }
    }

    // Determine status
    if ($result['packet_loss'] < 100) {
        $result['status'] = 'up';
    }

    return $result;
}

/**
 * Run monitoring check for all active providers
 */
function runMonitoringCheck() {
    $db = getDB();
    
    // Get all active providers
    $stmt = $db->query("SELECT id, name, host, status as old_status FROM providers WHERE is_active = 1");
    $providers = $stmt->fetchAll();

    foreach ($providers as $provider) {
        $pingResult = pingHost($provider['host']);
        
        // Insert monitoring log
        $logStmt = $db->prepare("INSERT INTO monitoring_logs (provider_id, status, ping, packet_loss, checked_at) VALUES (?, ?, ?, ?, NOW())");
        $logStmt->execute([
            $provider['id'],
            $pingResult['status'],
            $pingResult['ping'],
            $pingResult['packet_loss']
        ]);

        // Update provider current status
        $updateStmt = $db->prepare("UPDATE providers SET status = ?, ping = ?, packet_loss = ?, updated_at = NOW() WHERE id = ?");
        $updateStmt->execute([
            $pingResult['status'],
            $pingResult['ping'],
            $pingResult['packet_loss'],
            $provider['id']
        ]);

        // Handle downtime tracking
        handleDowntime($db, $provider['id'], $provider['old_status'], $pingResult['status']);
        
        if (php_sapi_name() === 'cli') {
            echo "[" . date('H:i:s') . "] {$provider['name']} ({$provider['host']}): {$pingResult['status']} | Ping: {$pingResult['ping']}ms | Loss: {$pingResult['packet_loss']}%\n";
        }
    }
}

/**
 * Track downtime transitions
 */
function handleDowntime($db, $providerId, $oldStatus, $newStatus) {
    if ($oldStatus !== 'down' && $newStatus === 'down') {
        // Transition to DOWN: create new downtime entry
        $stmt = $db->prepare("INSERT INTO downtime_logs (provider_id, started_at) VALUES (?, NOW())");
        $stmt->execute([$providerId]);
    } elseif ($oldStatus === 'down' && $newStatus === 'up') {
        // Transition to UP: close the open downtime entry
        $stmt = $db->prepare("SELECT id, started_at FROM downtime_logs WHERE provider_id = ? AND ended_at IS NULL ORDER BY started_at DESC LIMIT 1");
        $stmt->execute([$providerId]);
        $openDowntime = $stmt->fetch();
        
        if ($openDowntime) {
            $duration = (int)((time() - strtotime($openDowntime['started_at'])) / 60);
            $updateStmt = $db->prepare("UPDATE downtime_logs SET ended_at = NOW(), duration_minutes = ? WHERE id = ?");
            $updateStmt->execute([$duration, $openDowntime['id']]);
        }
    }
}

// Run if called directly (CLI or via refresh endpoint)
runMonitoringCheck();
