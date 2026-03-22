    </main>

    <!-- Footer -->
    <footer class="app-footer">
        <div class="container-fluid px-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center py-3">
                <span class="text-muted small">
                    &copy; <?= date('Y') ?> <?= e(APP_NAME) ?> v<?= APP_VERSION ?> — Network Monitoring Dashboard
                </span>
                <span class="text-muted small mt-2 mt-md-0">
                    <span id="autoRefreshBadge" class="badge bg-success-subtle text-success me-2">
                        <i class="bi bi-arrow-repeat me-1"></i>Auto-refresh: <span id="refreshCountdown"><?= AUTO_REFRESH_SECONDS ?></span>s
                    </span>
                </span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- App JS -->
    <script>
        const APP_CONFIG = {
            refreshInterval: <?= AUTO_REFRESH_SECONDS ?>,
            assetPrefix: '<?= defined('ASSET_PREFIX') ? ASSET_PREFIX : '' ?>'
        };
    </script>
    <script src="<?= defined('ASSET_PREFIX') ? ASSET_PREFIX : '' ?>assets/js/app.js"></script>
</body>
</html>
