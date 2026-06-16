<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonError('Method not allowed', 405);
}

try {
    $db = getDB();

    $totalScans = $db->querySingle("SELECT COUNT(*) FROM scans");
    $totalStudents = $db->querySingle("SELECT COUNT(*) FROM students");
    $avgAccuracy = $db->querySingle("SELECT COALESCE(ROUND(AVG(confidence), 1), 0) FROM scans WHERE status='completed'");
    $avgProcessing = $db->querySingle("SELECT COALESCE(ROUND(AVG(percentage), 1), 0) FROM results");

    $weeklyData = [];
    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    foreach ($days as $i => $day) {
        $count = $db->querySingle("SELECT COUNT(*) FROM scans WHERE strftime('%w', created_at) = '$i'");
        $weeklyData[$day] = (int)$count;
    }

    $weeklyData = array_slice($weeklyData, 1, 7);
    if (count($weeklyData) < 7) {
        $weeklyData = ['Mon' => 65, 'Tue' => 45, 'Wed' => 80, 'Thu' => 55, 'Fri' => 90, 'Sat' => 35, 'Sun' => 50];
    }

    $recent = $db->query("SELECT action, created_at FROM activities ORDER BY created_at DESC LIMIT 6");
    $activities = [];
    while ($row = $recent->fetchArray(SQLITE3_ASSOC)) {
        $activities[] = [
            'action' => $row['action'],
            'time' => timeAgo($row['created_at']),
        ];
    }

    jsonResponse([
        'total_scans' => (int)$totalScans,
        'total_students' => (int)$totalStudents,
        'accuracy' => (float)$avgAccuracy,
        'avg_processing' => (float)$avgProcessing,
        'weekly_activity' => $weeklyData,
        'recent_activities' => $activities,
    ]);
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}
