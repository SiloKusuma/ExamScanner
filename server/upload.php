<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

try {
    $db = getDB();

    $classId = $_POST['class_id'] ?? 1;
    $studentName = $_POST['student_name'] ?? 'Unknown';

    $studentResult = $db->query("SELECT id FROM students WHERE class_id = $classId LIMIT 1");
    $student = $studentResult->fetchArray(SQLITE3_ASSOC);
    $studentId = $student ? $student['id'] : null;

    if (!$studentId) {
        $sid = generateStudentId();
        $db->exec("INSERT INTO students (class_id, name, student_id) VALUES ($classId, '" . sanitize($db, $studentName) . "', '$sid')");
        $studentId = $db->lastInsertRowID();
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $imagePath = '';

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($ext, $allowed)) {
            jsonError('Invalid file type. Allowed: ' . implode(', ', $allowed));
        }
        $filename = 'scan_' . time() . '_' . uniqid() . '.' . $ext;
        $dest = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
            $imagePath = 'uploads/' . $filename;
        }
    }

    if (empty($imagePath)) {
        jsonError('No file uploaded or upload failed');
    }

    $stmt = $db->prepare("INSERT INTO scans (class_id, student_id, image_path, status) VALUES (:class_id, :student_id, :image_path, 'pending')");
    $stmt->bindValue(':class_id', $classId, SQLITE3_INTEGER);
    $stmt->bindValue(':student_id', $studentId, SQLITE3_INTEGER);
    $stmt->bindValue(':image_path', $imagePath, SQLITE3_TEXT);
    $stmt->execute();
    $scanId = $db->lastInsertRowID();

    $db->exec("INSERT INTO activities (action) VALUES ('New scan uploaded for student #$studentId')");

    jsonResponse([
        'scan_id' => $scanId,
        'image_path' => $imagePath,
        'status' => 'uploaded',
        'message' => 'File uploaded successfully',
        'student_id' => $studentId,
    ], 201);
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}
