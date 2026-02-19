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
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM qc_answers WHERE user_id = ?");
    $stmtCount->execute([$userId]);
    $totalRecords = $stmtCount->fetchColumn();
    $totalPages = ($limit > 0) ? ceil($totalRecords / $limit) : 0;

    // Fetch
    $stmt = $pdo->prepare("
        SELECT a.id, CONVERT(a.content USING utf8mb4) as content, a.created_at, a.vote_score, a.is_accepted,
               CONVERT(q.title USING utf8mb4) as question_title, q.slug as question_slug, q.id as question_id
        FROM qc_answers a
        JOIN qc_questions q ON a.question_id = q.id
        WHERE a.user_id = ?
        ORDER BY a.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $answers = $stmt->fetchAll();

    $formatted = [];
    foreach ($answers as $a) {
        $rawContent = strip_tags($a['content']);
        $displayContent = mb_substr($rawContent, 0, 150) . (mb_strlen($rawContent) > 150 ? '...' : '');

        $formatted[] = [
            'id' => $a['id'],
            'content' => htmlspecialchars($displayContent),
            'question_title' => htmlspecialchars($a['question_title']),
            'question_url' => generateUrl('question', $a['question_id'], $a['question_slug']),
            'answer_url' => generateUrl('question', $a['question_id'], $a['question_slug']) . "#answer-card-" . $a['id'],
            'created_at' => time_elapsed_string($a['created_at']),
            'vote_score' => $a['vote_score'],
            'is_accepted' => (bool)$a['is_accepted']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $formatted,
        'total_pages' => $totalPages,
        'current_page' => ($limit > 0) ? floor($offset / $limit) + 1 : 1
    ]);

} catch (PDOException $e) {
    error_log("Answers Fetch Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba.']);
}
