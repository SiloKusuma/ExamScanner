<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

try {
    $db = getDB();

    $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
    $search = isset($_GET['search']) ? sanitize($db, $_GET['search']) : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $perPage = 8;
    $offset = ($page - 1) * $perPage;

    $where = [];
    $params = [];

    if ($classId) {
        $where[] = "r.class_id = $classId";
    }
    if ($search) {
        $where[] = "s.name LIKE '%$search%' OR s.student_id LIKE '%$search%'";
    }

    $whereClause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $total = $db->querySingle("SELECT COUNT(*) FROM results r
        JOIN students s ON r.student_id = s.id
        $whereClause");

    $result = $db->query("SELECT r.*, s.name as student_name, s.student_id as student_id_code
        FROM results r
        JOIN students s ON r.student_id = s.id
        $whereClause
        ORDER BY r.created_at DESC
        LIMIT $perPage OFFSET $offset");

    $results = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $results[] = $row;
    }

    $classes = [];
    $classResult = $db->query("SELECT id, name, section FROM classes ORDER BY name");
    while ($row = $classResult->fetchArray(SQLITE3_ASSOC)) {
        $classes[] = $row;
    }

    jsonResponse([
        'results' => $results,
        'total' => (int)$total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => max(1, ceil($total / $perPage)),
        'classes' => $classes,
    ]);
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}
