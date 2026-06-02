<?php
/**
 * api/auth.php
 * POST /api/auth.php?action=login   – Admin login
 * POST /api/auth.php?action=logout  – Admin logout
 * GET  /api/auth.php?action=me      – Get current admin info
 */

require_once __DIR__ . '/../includes/helpers.php';

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// ── LOGIN ────────────────────────────────────────────────────
if ($action === 'login' && $method === 'POST') {
    $body     = bodyJson();
    $username = trim($body['username'] ?? '');
    $password = $body['password'] ?? '';

    if (!$username || !$password) error('Username and password are required.');

    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT id, username, email, password_hash, role
         FROM admin_users
         WHERE (username = ? OR email = ?) AND is_active = 1
         LIMIT 1"
    );
    $stmt->execute([$username, $username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        error('Invalid username or password.', 401);
    }

    // Generate session token
    $token = bin2hex(random_bytes(32));

    $pdo->prepare(
        "UPDATE admin_users SET session_token = ?, last_login_at = NOW() WHERE id = ?"
    )->execute([$token, $admin['id']]);

    unset($admin['password_hash']);

    success([
        'token' => $token,
        'admin' => $admin,
    ], 'Login successful.');
}

// ── LOGOUT ───────────────────────────────────────────────────
if ($action === 'logout' && $method === 'POST') {
    $admin = requireAdminAuth();
    getDB()->prepare("UPDATE admin_users SET session_token = NULL WHERE id = ?")
           ->execute([$admin['id']]);
    success([], 'Logged out successfully.');
}

// ── ME ───────────────────────────────────────────────────────
if ($action === 'me' && $method === 'GET') {
    $admin = requireAdminAuth();
    success(['admin' => $admin]);
}

error('Invalid action or method.', 405);
