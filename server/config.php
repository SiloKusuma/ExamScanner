<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function getDB() {
    $dbPath = __DIR__ . '/db/database.sqlite';
    $dbDir = dirname($dbPath);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }
    $db = new SQLite3($dbPath);
    $db->enableExceptions(true);
    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');
    return $db;
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function jsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}

function sanitize($db, $str) {
    return $db->escapeString(trim($str));
}

function generateStudentId() {
    return 'S' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
}

function calculateGrade($percentage) {
    if ($percentage >= 90) return 'A';
    if ($percentage >= 85) return 'A-';
    if ($percentage >= 80) return 'B+';
    if ($percentage >= 75) return 'B';
    if ($percentage >= 70) return 'B-';
    if ($percentage >= 65) return 'C+';
    if ($percentage >= 60) return 'C';
    if ($percentage >= 55) return 'C-';
    if ($percentage >= 50) return 'D';
    return 'F';
}

function timeAgo($datetime) {
    $now = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->getTimestamp() - $then->getTimestamp();

    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hour' . (floor($diff / 3600) > 1 ? 's' : '') . ' ago';
    if ($diff < 604800) return floor($diff / 86400) . ' day' . (floor($diff / 86400) > 1 ? 's' : '') . ' ago';
    return $then->format('M j');
}
