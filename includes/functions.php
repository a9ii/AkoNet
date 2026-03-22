<?php
/**
 * AkoNet Web Monitor - Utility Functions
 */

/**
 * Sanitize output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Get status badge HTML
 */
function statusBadge($status) {
    if ($status === 'up') {
        return '<span class="badge status-up px-3 py-2"><i class="bi bi-check-circle-fill me-1"></i>UP</span>';
    } elseif ($status === 'down') {
        return '<span class="badge status-down px-3 py-2"><i class="bi bi-exclamation-triangle-fill me-1"></i>DOWN</span>';
    }
    return '<span class="badge status-unknown px-3 py-2"><i class="bi bi-question-circle-fill me-1"></i>UNKNOWN</span>';
}

/**
 * Render provider avatar HTML (logo image or initials fallback)
 */
function providerAvatarHTML($provider, $size = 38, $fontSize = '0.82rem') {
    $style = "width:{$size}px;height:{$size}px;font-size:{$fontSize};";
    if (!empty($provider['logo'])) {
        $assetPrefix = defined('ASSET_PREFIX') ? ASSET_PREFIX : '';
        $src = e($assetPrefix . 'assets/img/logos/' . basename($provider['logo']));
        return '<div class="provider-avatar provider-avatar--image" style="' . $style . '">' .
               '<img src="' . $src . '" alt="' . e($provider['name']) . '" loading="lazy">' .
               '</div>';
    }
    $initials = strtoupper(substr($provider['name'], 0, 2));
    return '<div class="provider-avatar" style="' . $style . '">' . $initials . '</div>';
}

/**
 * Format ping value
 */
function formatPing($ping) {
    if ($ping === null || $ping == 0) {
        return '<span class="text-muted">—</span>';
    }
    $class = $ping < 50 ? 'text-success' : ($ping < 100 ? 'text-warning' : 'text-danger');
    return '<span class="' . $class . '">' . number_format($ping, 2) . ' ms</span>';
}

/**
 * Format packet loss value
 */
function formatPacketLoss($loss) {
    if ($loss === null) {
        return '<span class="text-muted">—</span>';
    }
    $class = $loss == 0 ? 'text-success' : ($loss < 10 ? 'text-warning' : 'text-danger');
    return '<span class="' . $class . '">' . number_format($loss, 2) . '%</span>';
}

/**
 * Time ago format
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Get all providers with optional search and pagination
 */
function getProviders($search = '', $page = 1, $perPage = null) {
    $perPage = $perPage ?? ITEMS_PER_PAGE;
    $db = getDB();
    
    $where = "WHERE is_active = 1";
    $params = [];
    
    if (!empty($search)) {
        $where .= " AND name LIKE ?";
        $params[] = "%{$search}%";
    }
    
    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(*) FROM providers {$where}");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();
    
    // Get paginated results
    $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("SELECT * FROM providers {$where} ORDER BY status DESC, name ASC LIMIT ? OFFSET ?");
    
    $allParams = array_merge($params, [$perPage, $offset]);
    $stmt->execute($allParams);
    $providers = $stmt->fetchAll();
    
    return [
        'providers' => $providers,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total / $perPage),
    ];
}

/**
 * Get provider by ID
 */
function getProvider($id) {
    $db = getDB();
    // Also pull the MAX(checked_at) from monitoring_logs for accurate "last updated" display
    $stmt = $db->prepare(
        "SELECT p.*, 
                MAX(ml.checked_at) AS latest_checked_at
         FROM providers p
         LEFT JOIN monitoring_logs ml ON ml.provider_id = p.id
         WHERE p.id = ?
         GROUP BY p.id"
    );
    $stmt->execute([(int)$id]);
    return $stmt->fetch();
}

/**
 * Get summary counts
 */
function getSummary() {
    $db = getDB();
    $stmt = $db->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'up' THEN 1 ELSE 0 END) as up_count,
        SUM(CASE WHEN status = 'down' THEN 1 ELSE 0 END) as down_count
        FROM providers WHERE is_active = 1");
    return $stmt->fetch();
}

/**
 * Get monitoring logs for a provider (last 24h)
 */
function getMonitoringLogs24h($providerId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT status, ping, packet_loss, checked_at 
        FROM monitoring_logs 
        WHERE provider_id = ? AND checked_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) 
        ORDER BY checked_at ASC");
    $stmt->execute([(int)$providerId]);
    return $stmt->fetchAll();
}

/**
 * Get downtime logs for a provider (last 24h)
 */
function getDowntimeLogs24h($providerId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT started_at, ended_at, duration_minutes 
        FROM downtime_logs 
        WHERE provider_id = ? AND started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) 
        ORDER BY started_at ASC");
    $stmt->execute([(int)$providerId]);
    return $stmt->fetchAll();
}

/**
 * Get daily downtime summary (last 7 days)
 */
function getDailyDowntime($providerId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT DATE(started_at) as day, 
        SUM(COALESCE(duration_minutes, TIMESTAMPDIFF(MINUTE, started_at, NOW()))) as total_minutes
        FROM downtime_logs 
        WHERE provider_id = ? AND started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) 
        GROUP BY DATE(started_at) 
        ORDER BY day ASC");
    $stmt->execute([(int)$providerId]);
    return $stmt->fetchAll();
}

/**
 * Generate pagination HTML
 */
function paginationHTML($currentPage, $totalPages, $search = '') {
    if ($totalPages <= 1) return '';
    
    $searchParam = !empty($search) ? '&search=' . urlencode($search) : '';
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center mb-0">';
    
    // Previous
    $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
    $html .= '<li class="page-item ' . $prevDisabled . '"><a class="page-link" href="?page=' . ($currentPage - 1) . $searchParam . '"><i class="bi bi-chevron-left"></i></a></li>';
    
    // Pages
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="?page=1' . $searchParam . '">1</a></li>';
        if ($start > 2) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $active = $i == $currentPage ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . $searchParam . '">' . $i . '</a></li>';
    }
    
    if ($end < $totalPages) {
        if ($end < $totalPages - 1) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        $html .= '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . $searchParam . '">' . $totalPages . '</a></li>';
    }
    
    // Next
    $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
    $html .= '<li class="page-item ' . $nextDisabled . '"><a class="page-link" href="?page=' . ($currentPage + 1) . $searchParam . '"><i class="bi bi-chevron-right"></i></a></li>';
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Sanitize search input
 */
function sanitizeSearch($input) {
    return trim(preg_replace('/[^a-zA-Z0-9\s\-\._]/', '', $input ?? ''));
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
