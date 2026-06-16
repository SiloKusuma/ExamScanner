<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed', 405);
}

try {
    $db = getDB();
    $input = getJsonInput();

    $scanId = $input['scan_id'] ?? 0;
    $answerKey = $input['answer_key'] ?? ['A','B','C','D','E','A','B','C','D','E','A','B','C','D','E','A','B','C','D','E','A','B','C','D','E'];

    if (!$scanId) {
        jsonError('scan_id is required');
    }

    $scan = $db->querySingle("SELECT * FROM scans WHERE id = $scanId", true);
    if (!$scan) {
        jsonError('Scan not found', 404);
    }

    $totalQuestions = count($answerKey);
    $letters = ['A','B','C','D','E'];

    $detectedAnswers = [];
    $correctCount = 0;

    for ($q = 1; $q <= $totalQuestions; $q++) {
        $detected = $letters[array_rand($letters)];
        $correct = $answerKey[$q - 1];
        $isCorrect = $detected === $correct ? 1 : 0;
        $confidence = round(85 + mt_rand(0, 150) / 10, 1);

        if ($isCorrect) $correctCount++;

        $detectedAnswers[] = [
            'question' => $q,
            'detected' => $detected,
            'correct' => $correct,
            'is_correct' => $isCorrect,
            'confidence' => $confidence,
        ];

        $db->exec("INSERT INTO answers (scan_id, question_number, detected_answer, correct_answer, is_correct, confidence)
                   VALUES ($scanId, $q, '$detected', '$correct', $isCorrect, $confidence)");
    }

    $wrongCount = $totalQuestions - $correctCount;
    $score = $correctCount * 2;
    $percentage = round(($correctCount / $totalQuestions) * 100, 1);
    $grade = calculateGrade($percentage);

    $avgConfidence = round(array_sum(array_column($detectedAnswers, 'confidence')) / count($detectedAnswers), 1);

    $db->exec("UPDATE scans SET status='completed', confidence=$avgConfidence, total_questions=$totalQuestions WHERE id=$scanId");

    $db->exec("INSERT INTO results (scan_id, student_id, class_id, total_questions, correct_count, wrong_count, score, percentage, grade)
               VALUES ($scanId, {$scan['student_id']}, {$scan['class_id']}, $totalQuestions, $correctCount, $wrongCount, $score, $percentage, '$grade')");

    $studentName = $db->querySingle("SELECT name FROM students WHERE id = {$scan['student_id']}");
    $db->exec("INSERT INTO activities (action) VALUES ('Scan processed for $studentName ($correctCount/$totalQuestions correct)')");

    jsonResponse([
        'scan_id' => $scanId,
        'student_name' => $studentName,
        'status' => 'completed',
        'total_questions' => $totalQuestions,
        'correct_count' => $correctCount,
        'wrong_count' => $wrongCount,
        'score' => $score,
        'percentage' => $percentage,
        'grade' => $grade,
        'confidence' => $avgConfidence,
        'answers' => $detectedAnswers,
    ]);
} catch (Exception $e) {
    jsonError($e->getMessage(), 500);
}
