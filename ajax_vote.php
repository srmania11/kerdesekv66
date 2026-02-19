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

$type = $_POST['type'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$direction = $_POST['direction'] ?? ''; // 'up' or 'down'

if (!in_array($type, ['question', 'answer']) || $id <= 0 || !in_array($direction, ['up', 'down'])) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen kérés.']);
    exit;
}

$voteValue = ($direction === 'up') ? 1 : -1;

// 1. Get Target Info (Author, current score, is_ai)
$table = ($type === 'question') ? 'qc_questions' : 'qc_answers';

$stmt = $pdo->prepare("SELECT user_id, vote_score" . ($type === 'answer' ? ", is_ai" : "") . " FROM $table WHERE id = ?");
$stmt->execute([$id]);
$target = $stmt->fetch();

if (!$target) {
    echo json_encode(['success' => false, 'message' => 'Tartalom nem található.']);
    exit;
}

// Check AI
if ($type === 'answer' && !empty($target['is_ai'])) {
    echo json_encode(['success' => false, 'message' => 'AI válaszra nem lehet szavazni.']);
    exit;
}

// Check Own Content
if ($target['user_id'] == $currentUser['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Saját tartalomra nem szavazhatsz.']);
    exit;
}

// 2. Check Existing Vote
$stmt = $pdo->prepare("SELECT id, vote_value FROM qc_votes WHERE user_id = ? AND target_type = ? AND target_id = ?");
$stmt->execute([$currentUser['user_id'], $type, $id]);
$existingVote = $stmt->fetch();

$repChange = 0;

try {
    $pdo->beginTransaction();

    if ($existingVote) {
        // Explicit cast for comparison
        $existingValue = (int)$existingVote['vote_value'];

        if ($existingValue === $voteValue) {
            // Same vote: Toggle Off (Remove Vote)
            $stmt = $pdo->prepare("DELETE FROM qc_votes WHERE id = ?");
            $stmt->execute([$existingVote['id']]);

            // Revert points: if vote was 1, we remove 1. If -1, we remove -1 (add 1).
            $repChange = -1 * $voteValue;
        } else {
            // Different vote: Change Vote (Flip)
            // Flipping from -1 to 1: Remove -1 (Rep +1), Add 1 (Rep +1). Total Rep +2.
            // Flipping from 1 to -1: Remove 1 (Rep -1), Add -1 (Rep -1). Total Rep -2.

            // Update timestamp to ensure it appears as fresh activity
            $stmt = $pdo->prepare("UPDATE qc_votes SET vote_value = :val, created_at = NOW() WHERE id = :id");
            $stmt->bindValue(':val', $voteValue, PDO::PARAM_INT);
            $stmt->bindValue(':id', $existingVote['id'], PDO::PARAM_INT);
            $stmt->execute();

            $repChange = 2 * $voteValue;
        }
    } else {
        // New Vote
        $stmt = $pdo->prepare("INSERT INTO qc_votes (user_id, target_type, target_id, vote_value) VALUES (:uid, :type, :tid, :val)");
        $stmt->bindValue(':uid', $currentUser['user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':type', $type, PDO::PARAM_STR);
        $stmt->bindValue(':tid', $id, PDO::PARAM_INT);
        $stmt->bindValue(':val', $voteValue, PDO::PARAM_INT);
        $stmt->execute();

        $repChange = $voteValue;
    }

    // 3. Update Target Score
    // Calculate fresh sum
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(vote_value), 0) FROM qc_votes WHERE target_type = ? AND target_id = ?");
    $stmt->execute([$type, $id]);
    $newScore = (int)$stmt->fetchColumn();

    // Update table cache
    $stmt = $pdo->prepare("UPDATE $table SET vote_score = ? WHERE id = ?");
    $stmt->execute([$newScore, $id]);

    // 4. Update Author Reputation
    if ($target['user_id']) {
        // Ensure repChange is integer
        $repChange = (int)$repChange;

        $pdo->prepare("UPDATE qc_users SET reputation_points = reputation_points + ? WHERE user_id = ?")
            ->execute([$repChange, $target['user_id']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'new_score' => $newScore]);

} catch (Exception $e) {
    $pdo->rollBack();
    // Use generic error message in production
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba.']);
}
?>
