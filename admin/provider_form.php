<?php
/**
 * AkoNet Web Monitor - Add/Edit Provider Form
 * Includes secure logo image upload
 */
define('ASSET_PREFIX', '../');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
checkSessionTimeout();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$isEdit = $id > 0;
$provider = null;
$errors = [];

if ($isEdit) {
    $provider = getProvider($id);
    if (!$provider) {
        header('Location: providers.php');
        exit;
    }
}

define('PAGE_TITLE', $isEdit ? 'Edit Provider' : 'Add Provider');

// -----------------------------------------------
// Secure logo upload helper
// -----------------------------------------------
function handleLogoUpload(&$errors) {
    if (empty($_FILES['logo']['name'])) {
        return null; // no file uploaded
    }

    $file      = $_FILES['logo'];
    $maxBytes  = 2 * 1024 * 1024; // 2 MB limit
    $allowed   = ['image/png', 'image/jpeg', 'image/jpg'];
    $allowedExt = ['png', 'jpg', 'jpeg'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Logo upload failed (error code: ' . (int)$file['error'] . ').';
        return null;
    }

    // File size check
    if ($file['size'] > $maxBytes) {
        $errors[] = 'Logo file too large. Maximum size is 2 MB.';
        return null;
    }

    // Extension check (case-insensitive)
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        $errors[] = 'Invalid logo file type. Allowed: PNG, JPG, JPEG.';
        return null;
    }

    // MIME type validation using finfo (real mime, not just extension)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        $errors[] = 'Invalid logo file content. Only real PNG/JPG images are accepted.';
        return null;
    }

    // Build destination directory (prevent path traversal via basename)
    $logoDir = dirname(__DIR__) . '/assets/img/logos/';
    if (!is_dir($logoDir)) {
        mkdir($logoDir, 0755, true);
    }

    // Generate a unique, safe filename
    $filename = 'provider_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $logoDir . $filename)) {
        $errors[] = 'Failed to save logo. Please try again.';
        return null;
    }

    return $filename;
}

// -----------------------------------------------
// Handle form submission
// -----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $host     = trim($_POST['host'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validation
        if (empty($name))             $errors[] = 'Provider name is required.';
        if (strlen($name) > 100)      $errors[] = 'Provider name too long (max 100 chars).';
        if (empty($host))             $errors[] = 'Host/IP address is required.';
        if (strlen($host) > 255)      $errors[] = 'Host too long (max 255 chars).';

        // Validate host format (IP or hostname)
        if (!empty($host) && !filter_var($host, FILTER_VALIDATE_IP) && !preg_match('/^[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}$/', $host)) {
            $errors[] = 'Please enter a valid IP address or hostname.';
        }

        // Check duplicate name
        if (empty($errors)) {
            $checkStmt = $db->prepare("SELECT id FROM providers WHERE name = ? AND id != ?");
            $checkStmt->execute([$name, $isEdit ? $id : 0]);
            if ($checkStmt->fetch()) {
                $errors[] = 'A provider with this name already exists.';
            }
        }

        // Handle logo upload (only if no prior errors so we don't waste a file write)
        $newLogo = null;
        if (empty($errors)) {
            $newLogo = handleLogoUpload($errors);
        }

        if (empty($errors)) {
            // If a new logo was uploaded and we're editing, delete the old one
            if ($newLogo && $isEdit && !empty($provider['logo'])) {
                $oldPath = dirname(__DIR__) . '/assets/img/logos/' . basename($provider['logo']);
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            // Build the logo value to save
            $logoToSave = $newLogo ?? ($isEdit ? ($provider['logo'] ?? null) : null);

            // Handle "remove logo" checkbox
            if (isset($_POST['remove_logo']) && $_POST['remove_logo'] === '1') {
                if ($isEdit && !empty($provider['logo'])) {
                    $oldPath = dirname(__DIR__) . '/assets/img/logos/' . basename($provider['logo']);
                    if (is_file($oldPath)) {
                        @unlink($oldPath);
                    }
                }
                $logoToSave = null;
            }

            if ($isEdit) {
                $stmt = $db->prepare("UPDATE providers SET name = ?, host = ?, is_active = ?, logo = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $host, $isActive, $logoToSave, $id]);
            } else {
                $stmt = $db->prepare("INSERT INTO providers (name, host, is_active, status, logo) VALUES (?, ?, ?, 'unknown', ?)");
                $stmt->execute([$name, $host, $isActive, $logoToSave]);
            }
            header('Location: providers.php?saved=1');
            exit;
        }

        // Preserve form data on validation error
        $provider = [
            'name'      => $name,
            'host'      => $host,
            'is_active' => $isActive,
            'logo'      => $isEdit ? ($provider['logo'] ?? null) : null,
        ];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid px-4 fade-in">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h1 class="h3 fw-bold mb-1"><?= $isEdit ? 'Edit' : 'Add' ?> Provider</h1>
                    <p class="text-muted mb-0 small">
                        <?= $isEdit ? 'Update provider information' : 'Register a new provider to monitor' ?>
                    </p>
                </div>
                <a href="providers.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-glass border-danger py-2 px-3 mb-3 small">
                <i class="bi bi-exclamation-circle text-danger me-1"></i>
                <?= implode('<br>', array_map('e', $errors)) ?>
            </div>
            <?php endif; ?>

            <div class="content-card">
                <div class="content-card-body p-4">
                    <form method="POST" enctype="multipart/form-data">
                        <?= csrfField() ?>

                        <!-- Provider Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-building me-1"></i>Provider Name
                            </label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?= e($provider['name'] ?? '') ?>"
                                   required maxlength="100"
                                   placeholder="e.g., Cloudflare DNS, Google Cloud">
                        </div>

                        <!-- Host / IP -->
                        <div class="mb-3">
                            <label for="host" class="form-label">
                                <i class="bi bi-globe me-1"></i>Host / IP Address
                            </label>
                            <input type="text" class="form-control" id="host" name="host"
                                   value="<?= e($provider['host'] ?? '') ?>"
                                   required maxlength="255"
                                   placeholder="e.g., 1.1.1.1 or dns.google.com">
                            <div class="form-text">Enter the IP address or hostname to ping for monitoring.</div>
                        </div>

                        <!-- Provider Logo Upload -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-image me-1"></i>Provider Logo
                                <span class="text-muted fw-normal">(optional)</span>
                            </label>

                            <!-- Current logo preview -->
                            <?php if ($isEdit && !empty($provider['logo'])): ?>
                            <div class="logo-preview-box mb-2" id="logoPreviewBox">
                                <div class="logo-preview-avatar" id="logoPreviewAvatar">
                                    <img src="../assets/img/logos/<?= e(basename($provider['logo'])) ?>"
                                         alt="Current logo" id="logoPreviewImg">
                                </div>
                                <div>
                                    <div class="small fw-semibold mb-1">Current logo</div>
                                    <div class="form-check mb-0">
                                        <input type="checkbox" class="form-check-input" id="remove_logo" name="remove_logo" value="1">
                                        <label class="form-check-label small text-muted" for="remove_logo">Remove current logo</label>
                                    </div>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="logo-preview-box mb-2" id="logoPreviewBox" style="display:none;">
                                <div class="logo-preview-avatar" id="logoPreviewAvatar">
                                    <img src="" alt="Preview" id="logoPreviewImg" style="display:none;">
                                    <span id="logoPreviewInitials"><?= strtoupper(substr($provider['name'] ?? 'PR', 0, 2)) ?></span>
                                </div>
                                <div class="small text-muted">Preview</div>
                            </div>
                            <?php endif; ?>

                            <input type="file" class="form-control" id="logo" name="logo"
                                   accept="image/png,image/jpeg,image/jpg">
                            <div class="form-text">
                                <i class="bi bi-shield-check me-1"></i>PNG or JPG only &middot; Max 2 MB &middot;
                                Falls back to initials if not set.
                            </div>
                        </div>

                        <!-- Active Toggle -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                       <?= ($provider['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-medium" for="is_active">Active monitoring</label>
                            </div>
                            <div class="form-text">Inactive providers won't be pinged by the cron job.</div>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>
                                <?= $isEdit ? 'Update Provider' : 'Add Provider' ?>
                            </button>
                            <a href="providers.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Live logo preview on file select
document.getElementById('logo')?.addEventListener('change', function () {
    const file = this.files[0];
    const box   = document.getElementById('logoPreviewBox');
    const img   = document.getElementById('logoPreviewImg');
    const init  = document.getElementById('logoPreviewInitials');

    if (file && file.type.match('image.*')) {
        const reader = new FileReader();
        reader.onload = function (e) {
            if (box) box.style.display = 'flex';
            if (img) { img.src = e.target.result; img.style.display = 'block'; }
            if (init) init.style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
});

// Update initials in preview when name changes
document.getElementById('name')?.addEventListener('input', function () {
    const init = document.getElementById('logoPreviewInitials');
    if (init) init.textContent = this.value.substring(0, 2).toUpperCase() || 'PR';
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
