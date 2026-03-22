<?php
/**
 * AkoNet Web Monitor - Authentication Helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    if (empty($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Get CSRF hidden input
 */
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . htmlspecialchars($token) . '">';
}

/**
 * Check if admin is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require admin login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Login admin
 */
function loginAdmin($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['login_time'] = time();
        return true;
    }
    return false;
}

/**
 * Logout admin
 */
function logoutAdmin() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > SESSION_LIFETIME)) {
        logoutAdmin();
        header('Location: login.php?timeout=1');
        exit;
    }
}
