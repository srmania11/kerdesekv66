<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

echo "Starting storage setup...\n";

// 1. Add storage_used column if not exists
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM qc_users LIKE 'storage_used'");
    if ($stmt->rowCount() == 0) {
        echo "Adding 'storage_used' column to qc_users table...\n";
        $pdo->exec("ALTER TABLE qc_users ADD COLUMN storage_used BIGINT DEFAULT 0");
        echo "Column added successfully.\n";
    } else {
        echo "'storage_used' column already exists.\n";
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}

// 2. Calculate current usage for all users
echo "Recalculating storage usage for all users...\n";

// Reset all to 0 first
$pdo->exec("UPDATE qc_users SET storage_used = 0");

$stmt = $pdo->query("SELECT user_id FROM qc_users");
$users = $stmt->fetchAll(PDO::FETCH_COLUMN);

$totalUpdated = 0;

foreach ($users as $userId) {
    $totalSize = 0;

    // A. Profile Images (Avatar & Cover)
    $chunk = floor($userId / 200);

    // Avatar (Pattern: uploads/avatars/CHUNK/USERID_*.webp)
    $avatarDir = __DIR__ . "/uploads/avatars/$chunk/";
    if (is_dir($avatarDir)) {
        $files = glob($avatarDir . "{$userId}_*.webp"); // Only custom uploads, default avatars usually don't match this strict pattern or are shared
        if ($files) {
            foreach ($files as $f) {
                if (is_file($f)) {
                    $totalSize += filesize($f);
                }
            }
        }
        // Also check simplified naming if used (though ajax_profile_upload uses random suffix)
        // Check exact ID.webp just in case old system used it
        if (file_exists($avatarDir . "{$userId}.webp")) {
             $totalSize += filesize($avatarDir . "{$userId}.webp");
        }
    }

    // Cover (Pattern: uploads/covers/CHUNK/USERID.webp)
    $coverPath = __DIR__ . "/uploads/covers/$chunk/{$userId}.webp";
    if (file_exists($coverPath)) {
        $totalSize += filesize($coverPath);
    }

    // B. Question Images
    $stmtQ = $pdo->prepare("
        SELECT qi.image_path
        FROM qc_question_images qi
        JOIN qc_questions q ON qi.question_id = q.id
        WHERE q.user_id = ?
    ");
    $stmtQ->execute([$userId]);
    $qImages = $stmtQ->fetchAll(PDO::FETCH_COLUMN);

    foreach ($qImages as $path) {
        $fullPath = __DIR__ . '/' . $path;
        if (file_exists($fullPath)) {
            $totalSize += filesize($fullPath);
        }
    }

    // C. Answer Images
    $stmtA = $pdo->prepare("
        SELECT ai.image_path
        FROM qc_answer_images ai
        JOIN qc_answers a ON ai.answer_id = a.id
        WHERE a.user_id = ?
    ");
    $stmtA->execute([$userId]);
    $aImages = $stmtA->fetchAll(PDO::FETCH_COLUMN);

    foreach ($aImages as $path) {
        $fullPath = __DIR__ . '/' . $path;
        if (file_exists($fullPath)) {
            $totalSize += filesize($fullPath);
        }
    }

    // Update User
    if ($totalSize > 0) {
        $update = $pdo->prepare("UPDATE qc_users SET storage_used = ? WHERE user_id = ?");
        $update->execute([$totalSize, $userId]);
        $totalUpdated++;
    }
}

echo "Storage setup complete. Updated usage for $totalUpdated users.\n";
?>
