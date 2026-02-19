<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

echo "Setting up Bookmark Table...\n";

try {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'qc_bookmarks'");
    if ($stmt->rowCount() > 0) {
        echo "Table 'qc_bookmarks' already exists.\n";
    } else {
        // Create table
        $sql = "CREATE TABLE qc_bookmarks (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            question_id INT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_question (user_id, question_id),
            INDEX idx_user_id (user_id),
            INDEX idx_question_id (question_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo->exec($sql);
        echo "Table 'qc_bookmarks' created successfully.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
