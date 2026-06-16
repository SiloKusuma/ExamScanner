<?php
require_once __DIR__ . '/config.php';

try {
    $db = getDB();

    $db->exec("
        CREATE TABLE IF NOT EXISTS classes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            section TEXT DEFAULT '',
            student_count INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            class_id INTEGER,
            name TEXT NOT NULL,
            student_id TEXT UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (class_id) REFERENCES classes(id)
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS scans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            class_id INTEGER,
            student_id INTEGER,
            image_path TEXT,
            total_questions INTEGER DEFAULT 25,
            status TEXT DEFAULT 'pending',
            confidence REAL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (class_id) REFERENCES classes(id),
            FOREIGN KEY (student_id) REFERENCES students(id)
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS answers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            scan_id INTEGER,
            question_number INTEGER,
            detected_answer TEXT,
            correct_answer TEXT,
            is_correct INTEGER DEFAULT 0,
            confidence REAL DEFAULT 0,
            FOREIGN KEY (scan_id) REFERENCES scans(id)
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS results (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            scan_id INTEGER,
            student_id INTEGER,
            class_id INTEGER,
            total_questions INTEGER DEFAULT 0,
            correct_count INTEGER DEFAULT 0,
            wrong_count INTEGER DEFAULT 0,
            score INTEGER DEFAULT 0,
            percentage REAL DEFAULT 0,
            grade TEXT DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (scan_id) REFERENCES scans(id),
            FOREIGN KEY (student_id) REFERENCES students(id),
            FOREIGN KEY (class_id) REFERENCES classes(id)
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS activities (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            action TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $count = $db->querySingle("SELECT COUNT(*) FROM classes");

    if ($count == 0) {
        $db->exec("INSERT INTO classes (name, section, student_count) VALUES ('Physics 101', 'A', 32)");
        $db->exec("INSERT INTO classes (name, section, student_count) VALUES ('Biology 201', 'B', 28)");
        $db->exec("INSERT INTO classes (name, section, student_count) VALUES ('Math 301', 'C', 24)");

        $students = [
            ['Alex Johnson', 1], ['Maria Garcia', 1], ['James Chen', 1],
            ['Sarah Kim', 1], ['David Park', 1], ['Emily Zhang', 1],
            ['Michael Brown', 2], ['Lisa Anderson', 2], ['Robert Taylor', 2],
            ['Jessica Wilson', 2], ['Daniel Lee', 3], ['Sophie Martin', 3],
        ];
        foreach ($students as $s) {
            $sid = generateStudentId();
            $db->exec("INSERT INTO students (class_id, name, student_id) VALUES ({$s[1]}, '{$s[0]}', '$sid')");
        }

        $key = ['A','B','C','D','E','A','B','C','D','E','A','B','C','D','E','A','B','C','D','E','A','B','C','D','E'];
        $letters = ['A','B','C','D','E'];

        for ($i = 1; $i <= 8; $i++) {
            $sid = $i;
            $correct = 0;
            $answers = [];
            for ($q = 1; $q <= 25; $q++) {
                $detected = $letters[array_rand($letters)];
                $isCorrect = $detected === $key[$q-1] ? 1 : 0;
                if ($isCorrect) $correct++;
                $answers[] = "($i, $q, '$detected', '{$key[$q-1]}', $isCorrect, " . round(85 + mt_rand(0, 150) / 10, 1) . ")";
            }

            $wrong = 25 - $correct;
            $percentage = round(($correct / 25) * 100, 1);
            $grade = calculateGrade($percentage);
            $score = $correct * 2;

            $db->exec("INSERT INTO scans (class_id, student_id, total_questions, status, confidence, created_at)
                       VALUES (1, $sid, 25, 'completed', " . round(90 + mt_rand(0, 99) / 10, 1) . ", datetime('now', '-" . mt_rand(1, 168) . " hours'))");
            $scanId = $db->lastInsertRowID();

            $db->exec("INSERT INTO answers (scan_id, question_number, detected_answer, correct_answer, is_correct, confidence)
                       VALUES " . implode(',', $answers));

            $db->exec("INSERT INTO results (scan_id, student_id, class_id, total_questions, correct_count, wrong_count, score, percentage, grade)
                       VALUES ($scanId, $sid, 1, 25, $correct, $wrong, $score, $percentage, '$grade')");
        }

        $activities = [
            "Physics 101 Midterm scanned",
            "Results exported for Class 3B",
            "New class \"Biology Honors\" created",
            "Answer key updated for Quiz 4",
            "Batch scan completed (35 papers)",
            "Sarah Kim joined your classroom",
            "Physics 101 answer key updated",
            "Monthly report generated",
        ];
        foreach ($activities as $i => $action) {
            $db->exec("INSERT INTO activities (action, created_at) VALUES ('$action', datetime('now', '-" . ($i * 2) . " hours'))");
        }
    }

    jsonResponse(['message' => 'Database initialized successfully', 'tables_exist' => true]);
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}
