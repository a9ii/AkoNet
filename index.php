<?php
/**
 * AkoNet Web Monitor - Main Dashboard
 */
define('PAGE_TITLE', 'Network Status');
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// Get data
$search = sanitizeSearch($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$result = getProviders($search, $page);
$summary = getSummary();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container-fluid px-4 fade-in">
    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="summary-card card-total">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="summary-label mb-1">Total Providers</div>
                        <div class="summary-value" id="totalCount"><?= (int)$summary['total'] ?></div>
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
                        <div class="summary-value text-success" id="upCount"><?= (int)$summary['up_count'] ?></div>
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
                        <div class="summary-value text-danger" id="downCount"><?= (int)$summary['down_count'] ?></div>
                    </div>
                    <div class="summary-icon icon-down">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Providers Table Card -->
    <div class="content-card">
        <div class="content-card-header">
            <h2>
                <i class="bi bi-broadcast" style="color: var(--accent-secondary);"></i>
                Network Status
            </h2>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="search-box">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" id="searchInput" class="form-control" 
                           placeholder="Search providers..." value="<?= e($search) ?>">
                </div>
                <button class="btn btn-refresh" id="refreshBtn" onclick="manualRefresh()">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>
        <div class="content-card-body">
            <div id="tableContainer">
                <?php if (empty($result['providers'])): ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p>No providers found.</p>
                    </div>
                <?php else: ?>
                    <table class="monitor-table">
                        <thead>
                            <tr>
                                <th style="width: 35%;">Provider</th>
                                <th style="width: 20%;">Status</th>
                                <th style="width: 20%;">Ping</th>
                                <th style="width: 20%;">Packet Loss</th>
                                <th style="width: 5%;" class="text-center">Details</th>
                            </tr>
                        </thead>
                        <tbody id="providersBody">
                            <?php foreach ($result['providers'] as $provider): ?>
                            <tr class="<?= $provider['status'] === 'down' ? 'row-down' : '' ?>" 
                                onclick="window.location='provider.php?id=<?= (int)$provider['id'] ?>'" 
                                style="cursor:pointer;">
                                <td>
                                    <a href="provider.php?id=<?= (int)$provider['id'] ?>" class="provider-name">
                                        <?= providerAvatarHTML($provider) ?>
                                        <div>
                                            <div><?= e($provider['name']) ?></div>
                                            <small class="text-muted"><?= e($provider['host'] ?? '') ?></small>
                                        </div>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($provider['status'] === 'down'): ?>
                                        <span class="pulse-danger"><?= statusBadge($provider['status']) ?></span>
                                    <?php else: ?>
                                        <?= statusBadge($provider['status']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= formatPing($provider['ping']) ?></td>
                                <td><?= formatPacketLoss($provider['packet_loss']) ?></td>
                                <td class="text-center">
                                    <a href="provider.php?id=<?= (int)$provider['id'] ?>" 
                                       class="btn btn-sm btn-outline-primary border-0" title="View details">
                                        <i class="bi bi-arrow-right-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <?php if ($result['total_pages'] > 1): ?>
            <div class="p-3">
                <?= paginationHTML($result['page'], $result['total_pages'], $search) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
