<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

try {
    $db = getDB();

    $format = $_GET['format'] ?? 'csv';

    $classId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : null;
    $where = $classId ? "WHERE r.class_id = $classId" : '';

    $result = $db->query("SELECT s.student_id, s.name, c.name as class, r.total_questions, r.correct_count, r.wrong_count, r.score, r.percentage, r.grade
        FROM results r
        JOIN students s ON r.student_id = s.id
        JOIN classes c ON r.class_id = c.id
        $where
        ORDER BY s.name");

    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }

    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=exam_results_' . date('Ymd') . '.csv');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Student ID', 'Name', 'Class', 'Total Questions', 'Correct', 'Wrong', 'Score', 'Percentage', 'Grade']);
        foreach ($rows as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }

    if ($format === 'json') {
        jsonResponse(['data' => $rows, 'count' => count($rows)]);
    }

    jsonError('Unsupported format. Use csv or json.', 400);
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}
