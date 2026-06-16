<?php
require_once __DIR__ . '/config.php';

try {
    $db = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $result = $db->query("SELECT c.*, (SELECT COUNT(*) FROM students WHERE class_id = c.id) as student_count FROM classes c ORDER BY c.created_at DESC");
        $classes = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $classes[] = $row;
        }
        jsonResponse(['classes' => $classes]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = getJsonInput();
        $name = $input['name'] ?? '';
        $section = $input['section'] ?? '';

        if (empty($name)) {
            jsonError('Class name is required');
        }

        $stmt = $db->prepare("INSERT INTO classes (name, section) VALUES (:name, :section)");
        $stmt->bindValue(':name', sanitize($db, $name), SQLITE3_TEXT);
        $stmt->bindValue(':section', sanitize($db, $section), SQLITE3_TEXT);
        $stmt->execute();

        $id = $db->lastInsertRowID();

        $db->exec("INSERT INTO activities (action) VALUES ('New class \"$name\" created')");

        jsonResponse(['id' => $id, 'name' => $name, 'section' => $section, 'student_count' => 0], 201);
    }

    jsonError('Method not allowed', 405);
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}
