<?php
/**
 * AkoNet Web Monitor - Admin Login
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$timeout = isset($_GET['timeout']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } elseif (loginAdmin($username, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= e(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="admin-login-wrapper">
        <div class="login-card">
            <div class="text-center">
                <div class="brand-icon-lg">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <h1 class="h4 fw-bold mb-1">Admin Login</h1>
                <p class="text-muted small mb-4"><?= e(APP_NAME) ?> Control Panel</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-glass py-2 px-3 mb-3 small">
                <i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?>
            </div>
            <?php endif; ?>

            <?php if ($timeout): ?>
            <div class="alert alert-warning alert-glass py-2 px-3 mb-3 small">
                <i class="bi bi-clock me-1"></i>Your session has expired. Please login again.
            </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:rgba(255,255,255,0.04);border-color:var(--border-color);color:var(--text-muted);">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?= e($_POST['username'] ?? '') ?>" required autofocus
                               placeholder="Enter username">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text" style="background:rgba(255,255,255,0.04);border-color:var(--border-color);color:var(--text-muted);">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" 
                               required placeholder="Enter password">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
                </button>
            </form>
            <div class="text-center mt-4">
                <a href="../index.php" class="text-muted small text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
