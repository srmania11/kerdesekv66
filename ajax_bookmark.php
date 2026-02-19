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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$question_id = isset($_POST['question_id']) ? (int)$_POST['question_id'] : 0;
if ($question_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

try {
    // Check if already bookmarked
    $stmt = $pdo->prepare("SELECT id FROM qc_bookmarks WHERE user_id = ? AND question_id = ?");
    $stmt->execute([$currentUser['user_id'], $question_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Delete
        $stmtDel = $pdo->prepare("DELETE FROM qc_bookmarks WHERE id = ?");
        $stmtDel->execute([$existing['id']]);
        echo json_encode(['success' => true, 'status' => 'removed']);
    } else {
        // Insert
        $stmtIns = $pdo->prepare("INSERT INTO qc_bookmarks (user_id, question_id) VALUES (?, ?)");
        $stmtIns->execute([$currentUser['user_id'], $question_id]);
        echo json_encode(['success' => true, 'status' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
