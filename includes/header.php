<?php
/**
 * AkoNet Web Monitor - Header Template
 */
if (!defined('PAGE_TITLE')) {
    define('PAGE_TITLE', APP_NAME);
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AkoNet Web Monitor - Real-time ISP and network status monitoring dashboard">
    <title><?= e(PAGE_TITLE) ?> | <?= e(APP_NAME) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2/dist/apexcharts.min.js"></script>
    <!-- Custom CSS -->
    <link href="<?= defined('ASSET_PREFIX') ? ASSET_PREFIX : '' ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top glass-nav">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= defined('ASSET_PREFIX') ? ASSET_PREFIX : '' ?>index.php">
                <div class="brand-icon">
                    <i class="bi bi-reception-4"></i>
                </div>
                <span class="fw-bold"><?= e(APP_NAME) ?></span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= defined('ASSET_PREFIX') ? ASSET_PREFIX : '' ?>index.php">
                            <i class="bi bi-speedometer2 me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= defined('ASSET_PREFIX') ? ASSET_PREFIX : '' ?>admin/index.php">
                            <i class="bi bi-shield-lock me-1"></i> Admin
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <span class="nav-text text-muted small" id="lastUpdated">
                            <i class="bi bi-clock me-1"></i>Updated: <span id="lastUpdateTime">just now</span>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
