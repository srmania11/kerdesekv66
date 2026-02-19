<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS qc_reply_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        reply_id INT NOT NULL,
        vote_type ENUM('like', 'dislike') NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_reply_vote (user_id, reply_id),
        FOREIGN KEY (user_id) REFERENCES qc_users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (reply_id) REFERENCES qc_answer_replies(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "Table qc_reply_votes created successfully.\n";

} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
    exit(1);
}
?>
