<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

$currentUser = getCurrentUser($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Be kell jelentkezned a válaszadáshoz.']);
    exit;
}

$answer_id = isset($_POST['answer_id']) ? (int)$_POST['answer_id'] : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if ($answer_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen válasz azonosító.']);
    exit;
}

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'A válasz nem lehet üres.']);
    exit;
}

$content = clean_html($content);

try {
    $stmt = $pdo->prepare("INSERT INTO qc_answer_replies (answer_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$answer_id, $currentUser['user_id'], $content]);

    echo json_encode(['success' => true, 'message' => 'Válasz sikeresen elküldve.']);
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Hiba történt a válasz mentésekor.']);
}
?>
