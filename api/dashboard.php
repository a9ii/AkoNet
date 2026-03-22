<?php
/**
 * API: Dashboard Data
 * Returns JSON with providers list and summary counts
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$search = sanitizeSearch($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

$result = getProviders($search, $page);
$summary = getSummary();

jsonResponse([
    'success' => true,
    'providers' => $result['providers'],
    'summary' => $summary,
    'pagination' => [
        'page' => $result['page'],
        'total_pages' => $result['total_pages'],
        'total' => $result['total'],
    ]
]);
