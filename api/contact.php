<?php
/**
 * api/contact.php
 * POST   /api/contact.php        – Submit a contact inquiry
 * GET    /api/contact.php        – List all inquiries (admin only)
 * GET    /api/contact.php?id=N   – Get single inquiry (admin only)
 * PUT    /api/contact.php        – Update inquiry status (admin only)
 * DELETE /api/contact.php?id=N   – Soft-delete / mark spam (admin only)
 */

require_once __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── POST — Public: Submit inquiry ────────────────────────────
if ($method === 'POST') {
    // Honeypot bot check
    $post = $_POST ?: bodyJson();

    if (!empty($post['website'])) {
        error('Bot detected.', 400);
    }

    $full_name = sanitize($post['full_name'] ?? '');
    $email     = trim($post['email'] ?? '');
    $service   = sanitize($post['service']   ?? '');
    $message   = sanitize($post['message']   ?? '');
    $phone     = sanitize($post['phone']     ?? '');
    $ip        = $_SERVER['REMOTE_ADDR'] ?? null;

    // Validation
    if (strlen($full_name) < 2)       error('Please enter your full name (min 2 characters).');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) error('Please enter a valid email address.');
    if (strlen($message) < 10)        error('Message is too short (min 10 characters).');

    $pdo = getDB();

    // Generate unique inquiry code
    $stmt  = $pdo->query("SELECT COUNT(*) FROM contact_inquiries WHERE DATE(submitted_at) = CURDATE()");
    $count = (int) $stmt->fetchColumn() + 1;
    $code  = sprintf('INQ-%s-%04d', date('Ymd'), $count);

    $sql = "INSERT INTO contact_inquiries
                (inquiry_code, full_name, email, phone, service_interest, message, ip_address)
            VALUES
                (:code, :name, :email, :phone, :service, :message, :ip)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':code'    => $code,
        ':name'    => $full_name,
        ':email'   => $email,
        ':phone'   => $phone ?: null,
        ':service' => $service ?: null,
        ':message' => $message,
        ':ip'      => $ip,
    ]);

    success(
        ['inquiry_id' => $code],
        "Thank you, {$full_name}! We'll get back to you within 1–2 business days."
    );
}

// ── GET — Admin: List / single inquiry ───────────────────────
if ($method === 'GET') {
    requireAdminAuth();
    $pdo = getDB();

    if (!empty($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM contact_inquiries WHERE id = ? LIMIT 1");
        $stmt->execute([(int) $_GET['id']]);
        $row = $stmt->fetch();
        if (!$row) error('Inquiry not found.', 404);
        success(['inquiry' => $row]);
    }

    // List with filters & pagination
    $status  = $_GET['status']  ?? null;
    $search  = $_GET['search']  ?? null;
    $page    = max(1, (int)($_GET['page'] ?? 1));
    $perPage = min(100, max(5, (int)($_GET['per_page'] ?? 20)));
    $offset  = ($page - 1) * $perPage;

    $where  = ['1=1'];
    $params = [];

    if ($status) {
        $where[]  = 'status = :status';
        $params[':status'] = $status;
    }
    if ($search) {
        $where[]  = '(full_name LIKE :s OR email LIKE :s2 OR inquiry_code LIKE :s3)';
        $like = "%{$search}%";
        $params[':s'] = $params[':s2'] = $params[':s3'] = $like;
    }

    $whereSQL = implode(' AND ', $where);

    $total = (int) $pdo->prepare("SELECT COUNT(*) FROM contact_inquiries WHERE {$whereSQL}")
                        ->execute($params) ? $pdo->query("SELECT FOUND_ROWS()")->fetchColumn() : 0;

    // Re-run count properly
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM contact_inquiries WHERE {$whereSQL}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $dataStmt = $pdo->prepare(
        "SELECT * FROM contact_inquiries WHERE {$whereSQL}
         ORDER BY submitted_at DESC LIMIT {$perPage} OFFSET {$offset}"
    );
    $dataStmt->execute($params);

    success([
        'inquiries'  => $dataStmt->fetchAll(),
        'pagination' => pagination($total, $page, $perPage),
    ]);
}

// ── PUT — Admin: Update inquiry status ───────────────────────
if ($method === 'PUT') {
    requireAdminAuth();
    $body   = bodyJson();
    $id     = (int)($body['id'] ?? 0);
    $status = $body['status']      ?? null;
    $notes  = sanitize($body['admin_notes'] ?? '');

    if (!$id) error('Missing inquiry ID.');
    $allowed = ['new', 'in_progress', 'resolved', 'spam'];
    if ($status && !in_array($status, $allowed)) error('Invalid status value.');

    $pdo    = getDB();
    $fields = [];
    $params = [':id' => $id];

    if ($status) {
        $fields[] = 'status = :status';
        $params[':status'] = $status;
        if ($status === 'resolved') {
            $fields[] = 'resolved_at = NOW()';
        }
    }
    if ($notes !== '') {
        $fields[] = 'admin_notes = :notes';
        $params[':notes'] = $notes;
    }

    if (empty($fields)) error('Nothing to update.');

    $pdo->prepare("UPDATE contact_inquiries SET " . implode(', ', $fields) . " WHERE id = :id")
        ->execute($params);

    success([], 'Inquiry updated successfully.');
}

// ── DELETE — Admin: Mark as spam ─────────────────────────────
if ($method === 'DELETE') {
    requireAdminAuth();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) error('Missing inquiry ID.');

    getDB()->prepare("UPDATE contact_inquiries SET status = 'spam' WHERE id = ?")
           ->execute([$id]);

    success([], 'Inquiry marked as spam.');
}

error('Method not allowed.', 405);
