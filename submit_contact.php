<?php
/**
 * submit_contact.php
 * Public-facing contact form handler (legacy direct-POST endpoint).
 * Kept for backward compatibility with index.html form.
 * For new integrations use api/contact.php instead.
 *
 * Akanksha Analytical & Research Lab
 */

header('Content-Type: application/json; charset=utf-8');

// ── DB CONFIG ────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'akanksha_analytical');
define('DB_USER',    'root');      // ← Change to your DB user
define('DB_PASS',    '');          // ← Change to your DB password
define('DB_CHARSET', 'utf8mb4');

// ── HONEYPOT ─────────────────────────────────
if (!empty($_POST['website'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Bot detected.']);
    exit;
}

// ── INPUT ────────────────────────────────────
$full_name = trim($_POST['full_name']  ?? '');
$email     = trim($_POST['email']      ?? '');
$service   = trim($_POST['service']    ?? '');
$message   = trim($_POST['message']    ?? '');
$phone     = trim($_POST['phone']      ?? '');
$ip        = $_SERVER['REMOTE_ADDR']   ?? null;

// ── VALIDATION ───────────────────────────────
if (strlen($full_name) < 2) {
    echo json_encode(['success' => false, 'message' => 'Please enter your full name.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}
if (strlen($message) < 10) {
    echo json_encode(['success' => false, 'message' => 'Message is too short (min 10 characters).']);
    exit;
}

// ── DB CONNECTION ─────────────────────────────
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed. Please email us directly.']);
    exit;
}

// ── GENERATE INQUIRY CODE ────────────────────
$stmt  = $pdo->query("SELECT COUNT(*) FROM contact_inquiries WHERE DATE(submitted_at) = CURDATE()");
$count = (int) $stmt->fetchColumn() + 1;
$code  = sprintf('INQ-%s-%04d', date('Ymd'), $count);

// ── INSERT ────────────────────────────────────
try {
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

    echo json_encode([
        'success'    => true,
        'inquiry_id' => $code,
        'message'    => "Thank you, {$full_name}! We'll get back to you within 1–2 business days.",
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save your inquiry. Please try again.']);
}
