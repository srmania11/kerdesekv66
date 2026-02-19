<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

try {
    // 1. Create qc_polls table if not exists
    $sql = "
    CREATE TABLE IF NOT EXISTS qc_polls (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question_id INT NOT NULL,
        question_text VARCHAR(255) NOT NULL,
        is_closed TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (question_id) REFERENCES qc_questions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);
    echo "Table qc_polls checked/created.\n";

    // 2. Check if 'is_closed' column exists (for updates)
    $stmt = $pdo->query("SHOW COLUMNS FROM qc_polls LIKE 'is_closed'");
    $col = $stmt->fetch();
    if (!$col) {
        $pdo->exec("ALTER TABLE qc_polls ADD COLUMN is_closed TINYINT(1) DEFAULT 0 AFTER question_text");
        echo "Column 'is_closed' added to qc_polls.\n";
    }

    // 3. Create qc_poll_options table
    $sql = "
    CREATE TABLE IF NOT EXISTS qc_poll_options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        poll_id INT NOT NULL,
        option_text VARCHAR(255) NOT NULL,
        vote_count INT DEFAULT 0,
        FOREIGN KEY (poll_id) REFERENCES qc_polls(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);
    echo "Table qc_poll_options checked/created.\n";

    // 4. Create qc_poll_votes table
    $sql = "
    CREATE TABLE IF NOT EXISTS qc_poll_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        poll_id INT NOT NULL,
        option_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_poll_vote (poll_id, user_id),
        FOREIGN KEY (poll_id) REFERENCES qc_polls(id) ON DELETE CASCADE,
        FOREIGN KEY (option_id) REFERENCES qc_poll_options(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES qc_users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);
    echo "Table qc_poll_votes checked/created.\n";

} catch (PDOException $e) {
    // Handle SQLite fallback if needed (SHOW COLUMNS doesn't work in SQLite the same way)
    if (strpos($e->getMessage(), 'syntax error') !== false || strpos($e->getMessage(), 'no such table') !== false) {
        // SQLite specific check
        try {
            $stmt = $pdo->query("PRAGMA table_info(qc_polls)");
            $cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 1);
            if (!in_array('is_closed', $cols)) {
                $pdo->exec("ALTER TABLE qc_polls ADD COLUMN is_closed INTEGER DEFAULT 0");
                echo "Column 'is_closed' added to qc_polls (SQLite).\n";
            }
        } catch (Exception $ex) {
            echo "Error checking/updating schema: " . $ex->getMessage() . "\n";
        }
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
?>
