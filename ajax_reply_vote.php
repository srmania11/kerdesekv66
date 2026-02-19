<?php
// Suppress warnings to prevent JSON corruption
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

// Auth Check
$currentUser = getCurrentUser($pdo);
if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Jelentkezz be!']);
    exit;
}

// Get Inputs
$reply_id = isset($_POST['reply_id']) ? (int)$_POST['reply_id'] : 0;
$type = isset($_POST['type']) ? $_POST['type'] : '';

// Validation
if ($reply_id <= 0 || !in_array($type, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen kérés.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check if reply exists
    $stmt = $pdo->prepare("SELECT id FROM qc_answer_replies WHERE id = ?");
    $stmt->execute([$reply_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'A válasz nem található.']);
        $pdo->rollBack();
        exit;
    }

    // Check Existing Vote
    $stmt = $pdo->prepare("SELECT id, vote_type FROM qc_reply_votes WHERE user_id = ? AND reply_id = ?");
    $stmt->execute([$currentUser['user_id'], $reply_id]);
    $existingVote = $stmt->fetch();

    $likesChange = 0;
    $dislikesChange = 0;
    $newVoteType = null;

    if ($existingVote) {
        if ($existingVote['vote_type'] === $type) {
            // Same vote: Toggle Off (Remove Vote)
            $stmt = $pdo->prepare("DELETE FROM qc_reply_votes WHERE id = ?");
            $stmt->execute([$existingVote['id']]);

            if ($type === 'like') {
                $likesChange = -1;
            } else {
                $dislikesChange = -1;
            }
        } else {
            // Different vote: Change Vote (Flip)
            // Update timestamp to ensure it appears as fresh activity
            $stmt = $pdo->prepare("UPDATE qc_reply_votes SET vote_type = ?, created_at = NOW() WHERE id = ?");
            $stmt->execute([$type, $existingVote['id']]);

            if ($type === 'like') {
                // Was dislike, now like
                $likesChange = 1;
                $dislikesChange = -1;
            } else {
                // Was like, now dislike
                $likesChange = -1;
                $dislikesChange = 1;
            }
            $newVoteType = $type;
        }
    } else {
        // New Vote
        $stmt = $pdo->prepare("INSERT INTO qc_reply_votes (user_id, reply_id, vote_type) VALUES (?, ?, ?)");
        $stmt->execute([$currentUser['user_id'], $reply_id, $type]);

        if ($type === 'like') {
            $likesChange = 1;
        } else {
            $dislikesChange = 1;
        }
        $newVoteType = $type;
    }

    // Update Counts
    // We update explicitly to keep in sync, though counting votes from table is safer,
    // keeping counters in `qc_answer_replies` is faster for read.
    // We use GREATEST(0, ...) to avoid negative numbers if sync is off.

    // Update likes
    if ($likesChange !== 0) {
        $op = $likesChange > 0 ? '+' : '-';
        $val = abs($likesChange);
        $sql = "UPDATE qc_answer_replies SET likes = GREATEST(0, CAST(likes AS SIGNED) $op $val) WHERE id = ?";
        $pdo->prepare($sql)->execute([$reply_id]);
    }

    // Update dislikes
    if ($dislikesChange !== 0) {
        $op = $dislikesChange > 0 ? '+' : '-';
        $val = abs($dislikesChange);
        $sql = "UPDATE qc_answer_replies SET dislikes = GREATEST(0, CAST(dislikes AS SIGNED) $op $val) WHERE id = ?";
        $pdo->prepare($sql)->execute([$reply_id]);
    }

    // Fetch New Counts
    $stmt = $pdo->prepare("SELECT likes, dislikes FROM qc_answer_replies WHERE id = ?");
    $stmt->execute([$reply_id]);
    $result = $stmt->fetch();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'likes' => (int)$result['likes'],
        'dislikes' => (int)$result['dislikes'],
        'user_vote' => $newVoteType
    ]);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error in ajax_reply_vote.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Adatbázis hiba történt.']);
    exit;
}
?>
