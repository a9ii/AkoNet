<?php
/**
 * AkoNet Web Monitor - Admin Dashboard
 */
define('PAGE_TITLE', 'Admin Dashboard');
define('ASSET_PREFIX', '../');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
checkSessionTimeout();

$summary = getSummary();
$db = getDB();

// Recent monitoring logs
$recentLogs = $db->query("SELECT ml.*, p.name as provider_name 
    FROM monitoring_logs ml 
    JOIN providers p ON p.id = ml.provider_id 
    ORDER BY ml.checked_at DESC LIMIT 10")->fetchAll();

// Active downtime
$activeDowntime = $db->query("SELECT dl.*, p.name as provider_name 
    FROM downtime_logs dl 
    JOIN providers p ON p.id = dl.provider_id 
    WHERE dl.ended_at IS NULL 
    ORDER BY dl.started_at ASC")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4 fade-in">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Admin Dashboard</h1>
            <p class="text-muted mb-0 small">Welcome, <?= e($_SESSION['admin_username'] ?? 'Admin') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="providers.php" class="btn btn-primary btn-sm">
                <i class="bi bi-hdd-network me-1"></i>Manage Providers
            </a>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="summary-card card-total">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="summary-label mb-1">Total Providers</div>
                        <div class="summary-value"><?= (int)$summary['total'] ?></div>
                    </div>
                    <div class="summary-icon icon-total">
                        <i class="bi bi-hdd-network"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="summary-card card-up">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="summary-label mb-1">Providers UP</div>
                        <div class="summary-value text-success"><?= (int)$summary['up_count'] ?></div>
                    </div>
                    <div class="summary-icon icon-up">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="summary-card card-down">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="summary-label mb-1">Providers DOWN</div>
                        <div class="summary-value text-danger"><?= (int)$summary['down_count'] ?></div>
                    </div>
                    <div class="summary-icon icon-down">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Active Downtime -->
        <div class="col-12 col-lg-6">
            <div class="content-card">
                <div class="content-card-header">
                    <h2><i class="bi bi-exclamation-diamond text-danger me-2"></i>Active Downtime</h2>
                </div>
                <div class="content-card-body">
                    <?php if (empty($activeDowntime)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-emoji-smile"></i>
                        <p>All providers are operational!</p>
                    </div>
                    <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th>Started</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeDowntime as $dt): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($dt['provider_name']) ?></td>
                                <td class="small"><?= e($dt['started_at']) ?></td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger">
                                        <?= (int)((time() - strtotime($dt['started_at'])) / 60) ?> min
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="col-12 col-lg-6">
            <div class="content-card">
                <div class="content-card-header">
                    <h2><i class="bi bi-clock-history me-2" style="color:var(--accent-secondary);"></i>Recent Checks</h2>
                </div>
                <div class="content-card-body">
                    <?php if (empty($recentLogs)): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-inbox"></i>
                        <p>No monitoring logs yet.</p>
                    </div>
                    <?php else: ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Provider</th>
                                <th>Status</th>
                                <th>Ping</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td class="fw-semibold small"><?= e($log['provider_name']) ?></td>
                                <td><?= statusBadge($log['status']) ?></td>
                                <td class="small"><?= $log['ping'] ? number_format($log['ping'], 1) . ' ms' : '—' ?></td>
                                <td class="small text-muted"><?= timeAgo($log['checked_at']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
