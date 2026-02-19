<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';

try {
    // Check if columns exist
    $stmt = $pdo->query("DESCRIBE qc_answer_replies");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('likes', $columns)) {
        $pdo->exec("ALTER TABLE qc_answer_replies ADD COLUMN likes INT DEFAULT 0");
        echo "Column 'likes' added.\n";
    } else {
        echo "Column 'likes' already exists.\n";
    }

    if (!in_array('dislikes', $columns)) {
        $pdo->exec("ALTER TABLE qc_answer_replies ADD COLUMN dislikes INT DEFAULT 0");
        echo "Column 'dislikes' added.\n";
    } else {
        echo "Column 'dislikes' already exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
