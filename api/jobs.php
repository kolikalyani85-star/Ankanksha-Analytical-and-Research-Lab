<?php
/**
 * api/jobs.php
 * GET    /api/jobs.php       – List active job openings (public)
 * GET    /api/jobs.php?id=N  – Single job (public)
 * GET    /api/jobs.php?all=1 – All jobs including inactive (admin)
 * POST   /api/jobs.php       – Create job (admin)
 * PUT    /api/jobs.php       – Update job (admin)
 * DELETE /api/jobs.php?id=N  – Delete job (admin)
 *
 * POST   /api/jobs.php?action=apply  – Job application submission (public)
 */

require_once __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();
$action = $_GET['action'] ?? '';

// ── JOB APPLICATION ──────────────────────────────────────────
if ($method === 'POST' && $action === 'apply') {
    $body     = bodyJson();
    $job_id   = (int)($body['job_id']   ?? 0);
    $name     = sanitize($body['name']   ?? '');
    $email    = trim($body['email']      ?? '');
    $phone    = sanitize($body['phone']  ?? '');
    $message  = sanitize($body['message']?? '');

    if (!$job_id)                          error('Job ID is required.');
    if (strlen($name) < 2)                 error('Full name is required.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) error('Valid email is required.');

    // Check job exists & active
    $stmt = $pdo->prepare("SELECT id, title FROM job_openings WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();
    if (!$job) error('Job opening not found or has been closed.', 404);

    $pdo->prepare(
        "INSERT INTO job_applications (job_id, applicant_name, email, phone, cover_message)
         VALUES (:job, :name, :email, :phone, :msg)"
    )->execute([
        ':job'   => $job_id,
        ':name'  => $name,
        ':email' => $email,
        ':phone' => $phone ?: null,
        ':msg'   => $message ?: null,
    ]);

    success([], "Application submitted for \"{$job['title']}\". We'll be in touch!");
}

// ── GET ──────────────────────────────────────────────────────
if ($method === 'GET') {
    // Single job
    if (!empty($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT * FROM job_openings WHERE id = ? LIMIT 1");
        $stmt->execute([(int) $_GET['id']]);
        $row = $stmt->fetch();
        if (!$row) error('Job not found.', 404);
        success(['job' => $row]);
    }

    // Admin: all jobs
    if (!empty($_GET['all'])) {
        requireAdminAuth();
        $rows = $pdo->query("SELECT * FROM job_openings ORDER BY posted_on DESC")->fetchAll();
        success(['jobs' => $rows]);
    }

    // Public: active jobs (not expired)
    $rows = $pdo->query(
        "SELECT id, title, employment_type, experience, qualification, location, description, posted_on
         FROM job_openings
         WHERE is_active = 1
           AND (expires_on IS NULL OR expires_on >= CURDATE())
         ORDER BY posted_on DESC"
    )->fetchAll();

    success(['jobs' => $rows]);
}

// ── POST — Create job ─────────────────────────────────────────
if ($method === 'POST') {
    requireAdminAuth();
    $body = bodyJson();

    $title  = sanitize($body['title']         ?? '');
    $type   = $body['employment_type']          ?? 'Full-Time';
    $exp    = sanitize($body['experience']     ?? '');
    $qual   = sanitize($body['qualification']  ?? '');
    $loc    = sanitize($body['location']       ?? 'Pune');
    $desc   = sanitize($body['description']    ?? '');
    $email  = trim($body['apply_email']        ?? 'akankshalab2007@gmail.com');
    $posted = $body['posted_on']               ?? date('Y-m-d');
    $expire = $body['expires_on']              ?? null;

    if (!$title) error('Job title is required.');

    $pdo->prepare(
        "INSERT INTO job_openings
            (title, employment_type, experience, qualification, location, description, apply_email, posted_on, expires_on)
         VALUES
            (:t, :et, :exp, :q, :loc, :desc, :email, :posted, :expire)"
    )->execute([
        ':t' => $title, ':et' => $type, ':exp' => $exp, ':q' => $qual,
        ':loc' => $loc, ':desc' => $desc, ':email' => $email,
        ':posted' => $posted, ':expire' => $expire,
    ]);

    success(['id' => $pdo->lastInsertId()], 'Job opening created.');
}

// ── PUT — Update job ──────────────────────────────────────────
if ($method === 'PUT') {
    requireAdminAuth();
    $body = bodyJson();
    $id   = (int)($body['id'] ?? 0);
    if (!$id) error('Job ID required.');

    $allowed = ['title', 'employment_type', 'experience', 'qualification',
                'location', 'description', 'apply_email', 'posted_on',
                'expires_on', 'is_active'];

    $fields = [];
    $params = [':id' => $id];

    foreach ($allowed as $f) {
        if (array_key_exists($f, $body)) {
            $fields[]    = "{$f} = :{$f}";
            $params[":{$f}"] = in_array($f, ['is_active']) ? (int)$body[$f] : sanitize((string)$body[$f]);
        }
    }
    if (empty($fields)) error('Nothing to update.');

    $pdo->prepare("UPDATE job_openings SET " . implode(', ', $fields) . " WHERE id = :id")
        ->execute($params);

    success([], 'Job updated successfully.');
}

// ── DELETE ────────────────────────────────────────────────────
if ($method === 'DELETE') {
    requireAdminAuth();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) error('Job ID required.');

    $pdo->prepare("UPDATE job_openings SET is_active = 0 WHERE id = ?")->execute([$id]);
    success([], 'Job deactivated.');
}

error('Method not allowed.', 405);
