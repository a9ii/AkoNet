<?php
/**
 * AkoNet Web Monitor - Configuration
 */

// ============================================
// Database Configuration
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'akonet_monitor');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// Application Settings
// ============================================
define('APP_NAME', 'AkoNet Monitor');
define('APP_VERSION', '1.0.0');
define('APP_URL', '');  // Leave empty for relative paths, or set full URL
define('TIMEZONE', 'Asia/Baghdad');

// ============================================
// Monitoring Settings
// ============================================
define('AUTO_REFRESH_SECONDS', 30);
define('PING_COUNT', 4);
define('PING_TIMEOUT', 5);
define('ITEMS_PER_PAGE', 20);

// ============================================
// Security
// ============================================
define('CSRF_TOKEN_NAME', 'akonet_csrf');
define('SESSION_LIFETIME', 3600); // 1 hour

// ============================================
// Timezone
// ============================================
date_default_timezone_set(TIMEZONE);

// ============================================
// Error Reporting (set to 0 in production)
// ============================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
