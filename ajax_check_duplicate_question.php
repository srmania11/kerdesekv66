<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = trim($_POST['title']);

    if (empty($title)) {
        echo json_encode(['found' => false]);
        exit;
    }

    try {
        // Search for exact match in title
        $stmt = $pdo->prepare("SELECT id, slug, title FROM qc_questions WHERE title = ? LIMIT 1");
        $stmt->execute([$title]);
        $question = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($question) {
            $url = generateUrl('question', $question['id'], $question['slug']);
            echo json_encode(['found' => true, 'url' => $url]);
        } else {
            echo json_encode(['found' => false]);
        }
    } catch (PDOException $e) {
        // Log error if needed, but for AJAX just return false or error
        echo json_encode(['found' => false, 'error' => 'Database error']);
    }
} else {
    echo json_encode(['found' => false, 'error' => 'Invalid request']);
}
