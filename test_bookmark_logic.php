<?php
// Test Script for Bookmark Logic (Simulated DB Interaction)
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

// Mock User ID (assuming user 1 exists, if not create/use available)
// We will look for a user
$stmt = $pdo->query("SELECT user_id FROM qc_users LIMIT 1");
$user = $stmt->fetch();
$userId = $user ? $user['user_id'] : 1;

// Mock Question ID
$stmt = $pdo->query("SELECT id FROM qc_questions LIMIT 1");
$question = $stmt->fetch();
$questionId = $question ? $question['id'] : 1;

if (!$user || !$question) {
    echo "Warning: No user or question found to test with. Setup DB first.\n";
    exit;
}

echo "Testing Bookmarks for User ID: $userId, Question ID: $questionId\n";

// 1. Clear existing bookmark
$pdo->prepare("DELETE FROM qc_bookmarks WHERE user_id = ? AND question_id = ?")->execute([$userId, $questionId]);
echo "[1] Cleared existing bookmarks.\n";

// 2. Add Bookmark
echo "[2] Adding bookmark...\n";
// Call the logic directly (simulating what ajax_bookmark.php does)
$stmtIns = $pdo->prepare("INSERT INTO qc_bookmarks (user_id, question_id) VALUES (?, ?)");
$stmtIns->execute([$userId, $questionId]);

// Verify
$stmtCheck = $pdo->prepare("SELECT id FROM qc_bookmarks WHERE user_id = ? AND question_id = ?");
$stmtCheck->execute([$userId, $questionId]);
$bm = $stmtCheck->fetch();
if ($bm) {
    echo "SUCCESS: Bookmark added. ID: " . $bm['id'] . "\n";
} else {
    echo "FAILURE: Bookmark NOT added.\n";
}

// 3. Remove Bookmark
echo "[3] Removing bookmark...\n";
$stmtDel = $pdo->prepare("DELETE FROM qc_bookmarks WHERE id = ?");
$stmtDel->execute([$bm['id']]);

// Verify
$stmtCheck->execute([$userId, $questionId]);
if (!$stmtCheck->fetch()) {
    echo "SUCCESS: Bookmark removed.\n";
} else {
    echo "FAILURE: Bookmark NOT removed.\n";
}

// 4. Test Fetching (AJAX simulation)
echo "[4] Testing Fetch Logic...\n";
// Re-add
$stmtIns->execute([$userId, $questionId]);

$offset = 0;
$limit = 5;
$search = '';

$sql = "
    SELECT b.id as bookmark_id, q.title
    FROM qc_bookmarks b
    JOIN qc_questions q ON b.question_id = q.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmtFetch = $pdo->prepare($sql);
$stmtFetch->execute([$userId]);
$results = $stmtFetch->fetchAll();

if (count($results) > 0) {
    echo "SUCCESS: Fetched " . count($results) . " bookmarks.\n";
    echo "First title: " . $results[0]['title'] . "\n";
} else {
    echo "FAILURE: No bookmarks fetched.\n";
}

// Cleanup
$pdo->prepare("DELETE FROM qc_bookmarks WHERE user_id = ? AND question_id = ?")->execute([$userId, $questionId]);
echo "[5] Cleanup complete.\n";

?>
