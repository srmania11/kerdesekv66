<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

$currentUser = getCurrentUser($pdo);
if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Jelentkezz be!']);
    exit;
}

$answer_id = (int)($_POST['answer_id'] ?? 0);

if ($answer_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen válasz ID.']);
    exit;
}

// 1. Fetch Answer and Question Info
$stmt = $pdo->prepare("
    SELECT a.id, a.question_id, a.is_accepted, q.user_id as question_owner_id
    FROM qc_answers a
    JOIN qc_questions q ON a.question_id = q.id
    WHERE a.id = ?
");
$stmt->execute([$answer_id]);
$data = $stmt->fetch();

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'A válasz nem található.']);
    exit;
}

// 2. Verify Ownership (Must be Question Owner)
if ($data['question_owner_id'] != $currentUser['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Csak a kérdés feltevője jelölheti meg a megoldást.']);
    exit;
}

// 3. Toggle Logic
try {
    $pdo->beginTransaction();

    if ($data['is_accepted']) {
        // Unaccept
        $pdo->prepare("UPDATE qc_answers SET is_accepted = 0 WHERE id = ?")->execute([$answer_id]);
        // Revert question status to open
        $pdo->prepare("UPDATE qc_questions SET status = 'open' WHERE id = ?")->execute([$data['question_id']]);
        $status = 'removed';
    } else {
        // Accept: First clear others, then set this
        $pdo->prepare("UPDATE qc_answers SET is_accepted = 0 WHERE question_id = ?")->execute([$data['question_id']]);
        $pdo->prepare("UPDATE qc_answers SET is_accepted = 1 WHERE id = ?")->execute([$answer_id]);
        // Set question status to solved
        $pdo->prepare("UPDATE qc_questions SET status = 'solved' WHERE id = ?")->execute([$data['question_id']]);
        $status = 'accepted';
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'status' => $status]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba.']);
}
?>
