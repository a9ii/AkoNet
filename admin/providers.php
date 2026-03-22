<?php
/**
 * AkoNet Web Monitor - Manage Providers
 */
define('PAGE_TITLE', 'Manage Providers');
define('ASSET_PREFIX', '../');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
checkSessionTimeout();

$db = getDB();

// Handle delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Invalid security token.';
    } else {
        $deleteId = (int)($_POST['provider_id'] ?? 0);
        if ($deleteId > 0) {
            // Clean up logo file before deleting record
            $providerRow = $db->prepare("SELECT logo FROM providers WHERE id = ?");
            $providerRow->execute([$deleteId]);
            $providerRow = $providerRow->fetch();
            if (!empty($providerRow['logo'])) {
                $logoPath = dirname(__DIR__) . '/assets/img/logos/' . basename($providerRow['logo']);
                if (is_file($logoPath)) {
                    @unlink($logoPath);
                }
            }
            $stmt = $db->prepare("DELETE FROM providers WHERE id = ?");
            $stmt->execute([$deleteId]);
            header('Location: providers.php?deleted=1');
            exit;
        }
    }
}

$search = sanitizeSearch($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

// Get all providers (including inactive)
$where = "WHERE 1=1";
$params = [];
if (!empty($search)) {
    $where .= " AND name LIKE ?";
    $params[] = "%{$search}%";
}

$countStmt = $db->prepare("SELECT COUNT(*) FROM providers {$where}");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$perPage = ITEMS_PER_PAGE;
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

$stmt = $db->prepare("SELECT * FROM providers {$where} ORDER BY name ASC LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$perPage, $offset]));
$providers = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4 fade-in">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Manage Providers</h1>
            <p class="text-muted mb-0 small"><?= $total ?> provider(s) registered</p>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Dashboard
            </a>
            <a href="provider_form.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg me-1"></i>Add Provider
            </a>
        </div>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-glass border-success py-2 px-3 mb-3 small">
        <i class="bi bi-check-circle text-success me-1"></i>Provider deleted successfully.
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['saved'])): ?>
    <div class="alert alert-glass border-success py-2 px-3 mb-3 small">
        <i class="bi bi-check-circle text-success me-1"></i>Provider saved successfully.
    </div>
    <?php endif; ?>

    <div class="content-card">
        <div class="content-card-header">
            <h2><i class="bi bi-hdd-network" style="color:var(--accent-secondary);"></i> Providers</h2>
            <div class="search-box">
                <i class="bi bi-search search-icon"></i>
                <input type="text" class="form-control" placeholder="Search..." 
                       value="<?= e($search) ?>"
                       onkeyup="if(event.key==='Enter')window.location='providers.php?search='+this.value">
            </div>
        </div>
        <div class="content-card-body">
            <?php if (empty($providers)): ?>
            <div class="empty-state py-5">
                <i class="bi bi-inbox"></i>
                <p>No providers found.</p>
                <a href="provider_form.php" class="btn btn-primary btn-sm mt-2">
                    <i class="bi bi-plus-lg me-1"></i>Add First Provider
                </a>
            </div>
            <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Provider</th>
                        <th>Host</th>
                        <th>Status</th>
                        <th>Ping</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($providers as $p): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?= providerAvatarHTML($p, 32, '0.7rem') ?>
                                <span class="fw-semibold"><?= e($p['name']) ?></span>
                            </div>
                        </td>
                        <td class="small text-muted"><?= e($p['host']) ?></td>
                        <td><?= statusBadge($p['status']) ?></td>
                        <td class="small"><?= $p['ping'] ? number_format($p['ping'], 1) . ' ms' : '—' ?></td>
                        <td>
                            <?php if ($p['is_active']): ?>
                                <span class="badge bg-success-subtle text-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="provider_form.php?id=<?= (int)$p['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary border-0" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" class="d-inline" 
                                      onsubmit="return confirm('Are you sure you want to delete <?= e(addslashes($p['name'])) ?>?');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="provider_id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="p-3">
            <?= paginationHTML($page, $totalPages, $search) ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
