<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
if ($limit > 50) $limit = 50;

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen felhasználó azonosító.']);
    exit;
}

try {
    // Count
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM qc_questions WHERE user_id = ?");
    $stmtCount->execute([$userId]);
    $totalRecords = $stmtCount->fetchColumn();
    $totalPages = ($limit > 0) ? ceil($totalRecords / $limit) : 0;

    // Fetch
    $stmt = $pdo->prepare("
        SELECT id, CONVERT(title USING utf8mb4) as title, slug, created_at, view_count, vote_score,
        (SELECT COUNT(*) FROM qc_answers WHERE question_id = qc_questions.id) as answer_count
        FROM qc_questions
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $questions = $stmt->fetchAll();

    $formatted = [];
    foreach ($questions as $q) {
        $formatted[] = [
            'id' => $q['id'],
            'title' => htmlspecialchars($q['title']),
            'url' => generateUrl('question', $q['id'], $q['slug']),
            'created_at' => time_elapsed_string($q['created_at']),
            'view_count' => $q['view_count'],
            'vote_score' => $q['vote_score'],
            'answer_count' => $q['answer_count']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $formatted,
        'total_pages' => $totalPages,
        'current_page' => ($limit > 0) ? floor($offset / $limit) + 1 : 1
    ]);

} catch (PDOException $e) {
    error_log("Questions Fetch Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba.']);
}
