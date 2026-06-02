<?php
/**
 * api/services.php
 * GET    /api/services.php               – List all services (grouped by category)
 * GET    /api/services.php?id=N          – Single service
 * GET    /api/services.php?category_id=N – Services in a category
 * POST   /api/services.php               – Create service (admin)
 * PUT    /api/services.php               – Update service (admin)
 * DELETE /api/services.php?id=N          – Delete service (admin)
 *
 * GET    /api/services.php?type=categories  – List all categories
 */

require_once __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];
$pdo    = getDB();
$type   = $_GET['type'] ?? '';

// ── GET ──────────────────────────────────────────────────────
if ($method === 'GET') {

    // Categories list
    if ($type === 'categories') {
        $rows = $pdo->query(
            "SELECT * FROM service_categories ORDER BY sort_order, name"
        )->fetchAll();
        success(['categories' => $rows]);
    }

    // Single service
    if (!empty($_GET['id'])) {
        $stmt = $pdo->prepare(
            "SELECT s.*, sc.name AS category_name
             FROM services s
             JOIN service_categories sc ON sc.id = s.category_id
             WHERE s.id = ? LIMIT 1"
        );
        $stmt->execute([(int) $_GET['id']]);
        $row = $stmt->fetch();
        if (!$row) error('Service not found.', 404);
        success(['service' => $row]);
    }

    // All services grouped by category
    $catStmt = $pdo->query(
        "SELECT * FROM service_categories ORDER BY sort_order, name"
    );
    $categories = $catStmt->fetchAll();

    $svcStmt = $pdo->query(
        "SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order, name"
    );
    $services = $svcStmt->fetchAll();

    // Group
    $grouped = [];
    foreach ($categories as $cat) {
        $grouped[] = [
            'id'        => $cat['id'],
            'name'      => $cat['name'],
            'icon'      => $cat['icon_class'],
            'services'  => array_values(array_filter(
                $services, fn($s) => $s['category_id'] == $cat['id']
            )),
        ];
    }

    success(['service_groups' => $grouped]);
}

// ── POST — Create service ────────────────────────────────────
if ($method === 'POST') {
    requireAdminAuth();
    $body = bodyJson();

    $name       = sanitize($body['name']        ?? '');
    $category   = (int)($body['category_id']    ?? 0);
    $desc       = sanitize($body['description'] ?? '');
    $sort       = (int)($body['sort_order']      ?? 0);

    if (!$name)     error('Service name is required.');
    if (!$category) error('Category ID is required.');

    $pdo->prepare(
        "INSERT INTO services (category_id, name, description, sort_order)
         VALUES (:cat, :name, :desc, :sort)"
    )->execute([':cat' => $category, ':name' => $name, ':desc' => $desc, ':sort' => $sort]);

    success(['id' => $pdo->lastInsertId()], 'Service created successfully.');
}

// ── PUT — Update service ─────────────────────────────────────
if ($method === 'PUT') {
    requireAdminAuth();
    $body = bodyJson();
    $id   = (int)($body['id'] ?? 0);
    if (!$id) error('Service ID is required.');

    $fields = [];
    $params = [':id' => $id];

    foreach (['name', 'description'] as $f) {
        if (isset($body[$f])) {
            $fields[]    = "{$f} = :{$f}";
            $params[":{$f}"] = sanitize($body[$f]);
        }
    }
    foreach (['category_id', 'sort_order', 'is_active'] as $f) {
        if (isset($body[$f])) {
            $fields[]    = "{$f} = :{$f}";
            $params[":{$f}"] = (int) $body[$f];
        }
    }

    if (empty($fields)) error('Nothing to update.');

    $pdo->prepare("UPDATE services SET " . implode(', ', $fields) . " WHERE id = :id")
        ->execute($params);

    success([], 'Service updated.');
}

// ── DELETE ────────────────────────────────────────────────────
if ($method === 'DELETE') {
    requireAdminAuth();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) error('Service ID required.');

    $pdo->prepare("UPDATE services SET is_active = 0 WHERE id = ?")->execute([$id]);
    success([], 'Service deactivated.');
}

error('Method not allowed.', 405);
