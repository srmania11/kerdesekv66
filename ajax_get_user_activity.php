<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

// Get parameters
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Validate limit (max 50)
if ($limit > 50) $limit = 50;

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen felhasználó azonosító.']);
    exit;
}

try {
    // 1. Calculate Total Count for Pagination
    $sqlCount = "
        SELECT COUNT(*) FROM (
            (SELECT id FROM qc_questions WHERE user_id = ?)
            UNION ALL
            (SELECT id FROM qc_answers WHERE user_id = ?)
            UNION ALL
            (SELECT id FROM qc_answer_replies WHERE user_id = ?)
            UNION ALL
            (SELECT id FROM qc_reply_votes WHERE user_id = ?)
            UNION ALL
            (SELECT id FROM qc_votes WHERE user_id = ? AND target_type = 'answer')
        ) as total
    ";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute([$userId, $userId, $userId, $userId, $userId]);
    $totalRecords = $stmtCount->fetchColumn();
    $totalPages = ($limit > 0) ? ceil($totalRecords / $limit) : 0;

    // 2. Recent Activity Query
    // Changes per user request:
    // - Answer Vote: Added.
    // - Reply: content is PARENT ANSWER content.
    // - Reply Vote: content is TARGET REPLY content.
    // - Truncation: LEFT(..., 100) on DB side to be safe + visual handling.
    // - Collation: CONVERT(... USING utf8mb4) everywhere.

    $sql = "
        (SELECT 'question' as type, id, CONVERT(title USING utf8mb4) as content, created_at, slug, id as question_id, NULL as answer_id, NULL as reply_id, vote_score, 0 as accepted, NULL as vote_type
         FROM qc_questions
         WHERE user_id = ?)
        UNION
        (SELECT 'answer' as type, a.id, CONVERT(q.title USING utf8mb4) as content, a.created_at, q.slug, q.id as question_id, a.id as answer_id, NULL as reply_id, a.vote_score, a.is_accepted as accepted, NULL as vote_type
         FROM qc_answers a
         JOIN qc_questions q ON a.question_id = q.id
         WHERE a.user_id = ?)
        UNION
        (SELECT 'reply' as type, r.id, CONVERT(a.content USING utf8mb4) as content, r.created_at, q.slug, q.id as question_id, a.id as answer_id, r.id as reply_id, 0 as vote_score, 0 as accepted, NULL as vote_type
         FROM qc_answer_replies r
         JOIN qc_answers a ON r.answer_id = a.id
         JOIN qc_questions q ON a.question_id = q.id
         WHERE r.user_id = ?)
        UNION
        (SELECT 'reply_vote' as type, v.id,
                CONVERT(r.content USING utf8mb4) as content,
                v.created_at, q.slug, q.id as question_id, a.id as answer_id, r.id as reply_id, 0 as vote_score, 0 as accepted, v.vote_type
         FROM qc_reply_votes v
         JOIN qc_answer_replies r ON v.reply_id = r.id
         JOIN qc_answers a ON r.answer_id = a.id
         JOIN qc_questions q ON a.question_id = q.id
         WHERE v.user_id = ?)
        UNION
        (SELECT 'answer_vote' as type, v.id,
                CONVERT(a.content USING utf8mb4) as content,
                v.created_at, q.slug, q.id as question_id, a.id as answer_id, NULL as reply_id, 0 as vote_score, 0 as accepted,
                CASE WHEN v.vote_value > 0 THEN 'like' ELSE 'dislike' END as vote_type
         FROM qc_votes v
         JOIN qc_answers a ON v.target_id = a.id
         JOIN qc_questions q ON a.question_id = q.id
         WHERE v.user_id = ? AND v.target_type = 'answer')
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $userId, PDO::PARAM_INT);
    $stmt->bindValue(2, $userId, PDO::PARAM_INT);
    $stmt->bindValue(3, $userId, PDO::PARAM_INT);
    $stmt->bindValue(4, $userId, PDO::PARAM_INT);
    $stmt->bindValue(5, $userId, PDO::PARAM_INT);
    $stmt->bindValue(6, $limit, PDO::PARAM_INT);
    $stmt->bindValue(7, $offset, PDO::PARAM_INT);
    $stmt->execute();

    $activities = $stmt->fetchAll();

    // Format data for frontend
    $formattedActivities = [];
    foreach ($activities as $act) {
        // Base URL to question
        $baseUrl = generateUrl('question', $act['question_id'], $act['slug']);

        // Append deep link parameters
        $url = $baseUrl;
        if ($act['type'] === 'answer') {
            $url .= "#answer-card-" . $act['answer_id'];
        } elseif ($act['type'] === 'reply') {
            $url .= "&answer_id=" . $act['answer_id'] . "&highlight_reply=" . $act['reply_id'] . "#answer-card-" . $act['answer_id'];
        } elseif ($act['type'] === 'reply_vote') {
            $url .= "&answer_id=" . $act['answer_id'] . "&highlight_reply=" . $act['reply_id'] . "#answer-card-" . $act['answer_id'];
        } elseif ($act['type'] === 'answer_vote') {
            $url .= "#answer-card-" . $act['answer_id'];
        }

        $timeElapsed = time_elapsed_string($act['created_at']);

        // Process content snippet (Common truncation)
        // Strip tags and truncate
        $rawContent = strip_tags($act['content']);
        $displayContent = mb_substr($rawContent, 0, 80) . (mb_strlen($rawContent) > 80 ? '...' : '');

        $formattedActivities[] = [
            'type' => $act['type'],
            'content' => $displayContent,
            'url' => htmlspecialchars($url),
            'time_elapsed' => $timeElapsed,
            'accepted' => (bool)$act['accepted'],
            'vote_score' => $act['vote_score'],
            'vote_type' => $act['vote_type']
        ];
    }

    echo json_encode([
        'success' => true,
        'activities' => $formattedActivities,
        'has_more' => count($formattedActivities) === $limit,
        'total_pages' => $totalPages,
        'current_page' => floor($offset / $limit) + 1
    ]);

} catch (PDOException $e) {
    error_log("Activity Fetch Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba.']);
}
?>
