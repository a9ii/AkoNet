<?php
/**
 * AkoNet Web Monitor - Admin Logout
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';

logoutAdmin();
header('Location: login.php');
exit;
