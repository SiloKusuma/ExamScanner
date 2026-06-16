<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

try {
    $db = getDB();

    $result = $db->query("SELECT action, created_at FROM activities ORDER BY created_at DESC LIMIT 10");
    $activities = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $activities[] = [
            'action' => $row['action'],
            'time' => timeAgo($row['created_at']),
        ];
    }

    jsonResponse(['activities' => $activities]);
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}
