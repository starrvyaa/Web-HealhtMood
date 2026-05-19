<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!$pdo) {
    echo json_encode(['error' => 'Database belum tersambung']);
    exit;
}

if (!is_logged_in()) {
    echo json_encode([
        'mood' => [
            ['date' => date('Y-m-d', strtotime('-6 days')), 'value' => 3.5],
            ['date' => date('Y-m-d', strtotime('-5 days')), 'value' => 4.5],
            ['date' => date('Y-m-d', strtotime('-4 days')), 'value' => 3.1],
            ['date' => date('Y-m-d', strtotime('-3 days')), 'value' => 3.7],
            ['date' => date('Y-m-d', strtotime('-2 days')), 'value' => 3.0],
            ['date' => date('Y-m-d', strtotime('-1 days')), 'value' => 4.0],
            ['date' => date('Y-m-d'), 'value' => 4.8],
        ],
        'sleep' => [
            ['date' => date('Y-m-d', strtotime('-6 days')), 'value' => 6.5],
            ['date' => date('Y-m-d', strtotime('-5 days')), 'value' => 8.0],
            ['date' => date('Y-m-d', strtotime('-4 days')), 'value' => 2.6],
            ['date' => date('Y-m-d', strtotime('-3 days')), 'value' => 4.0],
            ['date' => date('Y-m-d', strtotime('-2 days')), 'value' => 5.0],
            ['date' => date('Y-m-d', strtotime('-1 days')), 'value' => 0],
            ['date' => date('Y-m-d'), 'value' => 0],
        ],
        'updated_at' => date('H:i:s'),
    ]);
    exit;
}

$userId = $_SESSION['user_id'];

$moodStmt = $pdo->prepare(
    'SELECT mood_date AS date, ROUND(AVG(mood_score), 2) AS value
     FROM moods
     WHERE user_id = ? AND mood_date >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
     GROUP BY mood_date
     ORDER BY mood_date'
);
$moodStmt->execute([$userId]);

$sleepStmt = $pdo->prepare(
    'SELECT sleep_date AS date, ROUND(AVG(hours), 2) AS value
     FROM sleeps
     WHERE user_id = ? AND sleep_date >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
     GROUP BY sleep_date
     ORDER BY sleep_date'
);
$sleepStmt->execute([$userId]);

echo json_encode([
    'mood' => $moodStmt->fetchAll(),
    'sleep' => $sleepStmt->fetchAll(),
    'updated_at' => date('H:i:s'),
]);
