<?php
/**
 * AkoNet Web Monitor - Provider Details
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$provider = getProvider($id);
if (!$provider) {
    header('Location: index.php');
    exit;
}

define('PAGE_TITLE', $provider['name'] . ' - Details');
require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4 fade-in">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
            <li class="breadcrumb-item">
                <a href="index.php" class="text-decoration-none" style="color: var(--accent-secondary);">
                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($provider['name']) ?></li>
        </ol>
    </nav>

    <!-- Provider Header -->
    <div class="provider-header">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <?= providerAvatarHTML($provider, 56, '1.2rem') ?>
                <div>
                    <h1 class="provider-name-lg mb-1"><?= e($provider['name']) ?></h1>
                    <span class="text-muted"><?= e($provider['host'] ?? '') ?></span>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php if ($provider['status'] === 'down'): ?>
                    <span class="pulse-danger"><?= statusBadge($provider['status']) ?></span>
                <?php else: ?>
                    <?= statusBadge($provider['status']) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <div class="stat-pill">
                <i class="bi bi-activity" style="color: var(--accent-secondary);"></i>
                <span>Ping: <strong><?= $provider['ping'] !== null ? number_format($provider['ping'], 2) . ' ms' : '—' ?></strong></span>
            </div>
            <div class="stat-pill">
                <i class="bi bi-exclamation-diamond" style="color: var(--warning);"></i>
                <span>Packet Loss: <strong><?= $provider['packet_loss'] !== null ? number_format($provider['packet_loss'], 2) . '%' : '—' ?></strong></span>
            </div>
            <div class="stat-pill">
                <i class="bi bi-clock-history" style="color: var(--text-muted);"></i>
                <?php
                    // Use MAX(checked_at) from monitoring_logs for accurate last-check time
                    $updatedTime = !empty($provider['latest_checked_at'])
                        ? $provider['latest_checked_at']
                        : ($provider['updated_at'] ?? null);
                ?>
                <span>Updated: <strong><?= $updatedTime ? timeAgo($updatedTime) : '—' ?></strong></span>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4">
        <!-- Packet Loss 24h -->
        <div class="col-12">
            <div class="chart-card">
                <h3>
                    <i class="bi bi-graph-up"></i>
                    Packet Loss — Last 24 Hours
                </h3>
                <div id="chartPacketLoss" style="min-height: 300px;"></div>
            </div>
        </div>

        <!-- Downtime 24h -->
        <div class="col-12 col-lg-6">
            <div class="chart-card">
                <h3>
                    <i class="bi bi-calendar-x"></i>
                    Downtime — Last 24 Hours
                </h3>
                <div id="chartDowntime24h" style="min-height: 280px;"></div>
            </div>
        </div>

        <!-- Downtime Daily -->
        <div class="col-12 col-lg-6">
            <div class="chart-card">
                <h3>
                    <i class="bi bi-bar-chart-line"></i>
                    Downtime — Daily Summary
                </h3>
                <div id="chartDowntimeDaily" style="min-height: 280px;"></div>
            </div>
        </div>
    </div>
</div>

<script>
    const PROVIDER_ID = <?= (int)$provider['id'] ?>;
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
