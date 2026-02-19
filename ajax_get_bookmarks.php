<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

$currentUser = getCurrentUser($pdo);
if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Nincs bejelentkezve']);
    exit;
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : $currentUser['user_id'];
// Restrict to viewing own bookmarks unless admin
if ($user_id !== $currentUser['user_id'] && empty($currentUser['is_admin'])) {
    $user_id = $currentUser['user_id'];
}

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $sql = "
        SELECT b.id as bookmark_id, b.created_at as bookmarked_at,
               q.id as question_id, q.title, q.slug, q.created_at as question_created_at,
               u.username, q.user_id as question_user_id
        FROM qc_bookmarks b
        JOIN qc_questions q ON b.question_id = q.id
        LEFT JOIN qc_users u ON q.user_id = u.user_id
        WHERE b.user_id = :user_id
    ";

    $params = [':user_id' => $user_id];

    if ($search !== '') {
        $sql .= " AND (q.title LIKE :search)";
        // No params binding here for simplicity with variable query, but better to use binds
    }

    $sql .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    if ($search !== '') {
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $bookmarks = $stmt->fetchAll();

    // Count Total
    $countSql = "
        SELECT COUNT(*)
        FROM qc_bookmarks b
        JOIN qc_questions q ON b.question_id = q.id
        WHERE b.user_id = :user_id
    ";
    if ($search !== '') {
        $countSql .= " AND (q.title LIKE :search)";
    }
    $stmtCount = $pdo->prepare($countSql);
    $stmtCount->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    if ($search !== '') {
        $stmtCount->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }
    $stmtCount->execute();
    $total = $stmtCount->fetchColumn();

    // Process URLs and formatting
    foreach ($bookmarks as &$bm) {
        $bm['url'] = generateUrl('question', $bm['question_id'], $bm['slug']);
        $bm['time_elapsed'] = time_elapsed_string($bm['bookmarked_at']);
    }

    echo json_encode([
        'success' => true,
        'bookmarks' => $bookmarks,
        'total_count' => $total,
        'offset' => $offset,
        'limit' => $limit
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
