<?php
/**
 * api/dashboard.php
 * GET /api/dashboard.php              – Admin dashboard stats (admin)
 * GET /api/dashboard.php?type=settings– Site settings (admin)
 * PUT /api/dashboard.php?type=settings– Update site settings (admin)
 *
 * Public data endpoints:
 * GET /api/dashboard.php?type=accreditations
 * GET /api/dashboard.php?type=directors
 * GET /api/dashboard.php?type=testimonials
 * GET /api/dashboard.php?type=gallery
 * GET /api/dashboard.php?type=strengths
 * GET /api/dashboard.php?type=projects
 * GET /api/dashboard.php?type=sectors
 */

require_once __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();
$type   = $_GET['type'] ?? '';

// ── PUBLIC DATA ENDPOINTS ─────────────────────────────────────
$publicTypes = [
    'accreditations' => "SELECT * FROM accreditations WHERE is_active = 1 ORDER BY sort_order",
    'directors'      => "SELECT * FROM directors WHERE is_active = 1 ORDER BY sort_order",
    'testimonials'   => "SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order",
    'gallery'        => "SELECT * FROM gallery_images WHERE is_active = 1 ORDER BY sort_order",
    'strengths'      => "SELECT * FROM strengths WHERE is_active = 1 ORDER BY sort_order",
    'projects'       => "SELECT * FROM projects WHERE is_active = 1 ORDER BY sort_order",
    'sectors'        => "SELECT * FROM client_sectors WHERE is_active = 1 ORDER BY sort_order",
    'infrastructure' => "SELECT * FROM infrastructure_items WHERE is_active = 1 ORDER BY sort_order",
];

if (isset($publicTypes[$type]) && $method === 'GET') {
    $rows = $pdo->query($publicTypes[$type])->fetchAll();
    success([$type => $rows]);
}

// ── SITE SETTINGS ─────────────────────────────────────────────
if ($type === 'settings') {
    if ($method === 'GET') {
        $rows = $pdo->query("SELECT setting_key, setting_value, description FROM site_settings")
                    ->fetchAll();
        $map  = array_column($rows, 'setting_value', 'setting_key');
        success(['settings' => $map]);
    }

    if ($method === 'PUT') {
        requireAdminAuth();
        $body = bodyJson();
        if (!is_array($body)) error('Expected JSON object of key=>value pairs.');

        $stmt = $pdo->prepare(
            "INSERT INTO site_settings (setting_key, setting_value)
             VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = :v2"
        );
        foreach ($body as $key => $value) {
            $stmt->execute([':k' => $key, ':v' => $value, ':v2' => $value]);
        }
        success([], 'Settings saved.');
    }
}

// ── ADMIN DASHBOARD STATS ─────────────────────────────────────
if ($method === 'GET' && !$type) {
    requireAdminAuth();

    $stats = [];

    // Inquiry counts
    $stmt = $pdo->query(
        "SELECT status, COUNT(*) AS cnt FROM contact_inquiries GROUP BY status"
    );
    $inquiryStats = [];
    foreach ($stmt->fetchAll() as $row) {
        $inquiryStats[$row['status']] = (int) $row['cnt'];
    }
    $stats['inquiries'] = [
        'total'       => array_sum($inquiryStats),
        'new'         => $inquiryStats['new']         ?? 0,
        'in_progress' => $inquiryStats['in_progress'] ?? 0,
        'resolved'    => $inquiryStats['resolved']    ?? 0,
        'spam'        => $inquiryStats['spam']        ?? 0,
    ];

    // Today's inquiries
    $stats['inquiries_today'] = (int) $pdo->query(
        "SELECT COUNT(*) FROM contact_inquiries WHERE DATE(submitted_at) = CURDATE()"
    )->fetchColumn();

    // Active jobs
    $stats['active_jobs'] = (int) $pdo->query(
        "SELECT COUNT(*) FROM job_openings WHERE is_active = 1
         AND (expires_on IS NULL OR expires_on >= CURDATE())"
    )->fetchColumn();

    // Active services
    $stats['active_services'] = (int) $pdo->query(
        "SELECT COUNT(*) FROM services WHERE is_active = 1"
    )->fetchColumn();

    // Last 7 days inquiry trend
    $trend = $pdo->query(
        "SELECT DATE(submitted_at) AS day, COUNT(*) AS cnt
         FROM contact_inquiries
         WHERE submitted_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
         GROUP BY DATE(submitted_at)
         ORDER BY day"
    )->fetchAll();
    $stats['inquiry_trend_7d'] = $trend;

    // Recent 5 inquiries
    $recent = $pdo->query(
        "SELECT id, inquiry_code, full_name, email, service_interest, status, submitted_at
         FROM contact_inquiries
         ORDER BY submitted_at DESC LIMIT 5"
    )->fetchAll();
    $stats['recent_inquiries'] = $recent;

    success(['stats' => $stats]);
}

error('Invalid type or method.', 405);
