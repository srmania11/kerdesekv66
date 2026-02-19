<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS qc_answer_replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        answer_id INT NOT NULL,
        user_id INT DEFAULT NULL,
        content TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (answer_id) REFERENCES qc_answers(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES qc_users(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "Table qc_answer_replies created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
    exit(1);
}
?>
