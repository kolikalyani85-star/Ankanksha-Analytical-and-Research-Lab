<?php
/**
 * includes/helpers.php
 * Utility functions used across all API endpoints.
 */

require_once __DIR__ . '/../config/database.php';

// ── CORS & JSON headers ───────────────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');                  // Restrict to your domain in production
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Response helpers ──────────────────────────────────────────
function respond(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function success(array $data = [], string $message = 'OK'): void {
    respond(['success' => true, 'message' => $message] + $data);
}

function error(string $message, int $code = 400): void {
    respond(['success' => false, 'message' => $message], $code);
}

// ── Input helpers ─────────────────────────────────────────────
function bodyJson(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw ?: '{}', true) ?? [];
}

function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

// ── Auth helpers ──────────────────────────────────────────────
function requireAdminAuth(): array {
    $headers = getallheaders();
    $token   = $headers['Authorization'] ?? '';
    $token   = str_replace('Bearer ', '', $token);

    if (empty($token)) {
        error('Unauthorised — missing token.', 401);
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT id, username, role FROM admin_users
         WHERE session_token = ? AND is_active = 1
         LIMIT 1"
    );
    $stmt->execute([$token]);
    $admin = $stmt->fetch();

    if (!$admin) {
        error('Unauthorised — invalid or expired session.', 401);
    }
    return $admin;
}

// ── Pagination helper ─────────────────────────────────────────
function pagination(int $total, int $page, int $perPage): array {
    return [
        'total'        => $total,
        'per_page'     => $perPage,
        'current_page' => $page,
        'last_page'    => (int) ceil($total / $perPage),
        'from'         => (($page - 1) * $perPage) + 1,
        'to'           => min($page * $perPage, $total),
    ];
}
