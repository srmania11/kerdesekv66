<?php
// setup_profile_db.php
// Setup script for updating qc_users and related tables.

require_once 'db_connect.php';

echo "Starting database setup...\n";

try {
    $pdo->beginTransaction();

    // 1. Add columns to qc_users if they don't exist
    $columns = [
        'email' => "VARCHAR(255) UNIQUE DEFAULT NULL",
        'full_name' => "VARCHAR(255) DEFAULT NULL",
        'password_hash' => "VARCHAR(255) DEFAULT NULL",
        'avatar' => "VARCHAR(255) DEFAULT NULL",
        'cover_image' => "VARCHAR(255) DEFAULT NULL",
        'bio' => "TEXT DEFAULT NULL",
        'website' => "VARCHAR(255) DEFAULT NULL",
        'social_links' => "TEXT DEFAULT NULL",
        'location' => "VARCHAR(100) DEFAULT NULL",
        'last_login' => "DATETIME DEFAULT NULL",
        'last_login_ip' => "VARCHAR(45) DEFAULT NULL",
        'status' => "ENUM('active', 'suspended', 'banned', 'deleted') DEFAULT 'active'",
        'deleted_at' => "DATETIME DEFAULT NULL",
        'role' => "ENUM('user', 'moderator', 'admin') DEFAULT 'user'",
        'questions_count' => "INT DEFAULT 0",
        'answers_count' => "INT DEFAULT 0",
        'accepted_answers_count' => "INT DEFAULT 0",
        'comments_count' => "INT DEFAULT 0",
        'reactions_count' => "INT DEFAULT 0",
        'rank_title' => "VARCHAR(100) DEFAULT 'Newbie'"
    ];

    foreach ($columns as $col => $def) {
        // Check if column exists
        $stmt = $pdo->prepare("SHOW COLUMNS FROM qc_users LIKE ?");
        $stmt->execute([$col]);
        if ($stmt->rowCount() == 0) {
            echo "Adding column '$col' to qc_users...\n";
            $pdo->exec("ALTER TABLE qc_users ADD COLUMN `$col` $def");
        } else {
            echo "Column '$col' already exists in qc_users.\n";
        }
    }

    // Update role based on is_admin if is_admin is present
    // First check if is_admin exists (it should based on now.sql)
    $stmt = $pdo->query("SHOW COLUMNS FROM qc_users LIKE 'is_admin'");
    if ($stmt->rowCount() > 0) {
        echo "Updating roles based on is_admin...\n";
        $pdo->exec("UPDATE qc_users SET role = 'admin' WHERE is_admin = 1 AND role = 'user'");
    }

    // 2. Create qc_login_history table
    echo "Creating qc_login_history table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS qc_login_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        event ENUM('login', 'login_failed', 'logout', 'password_change') NOT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (ip_address),
        FOREIGN KEY (user_id) REFERENCES qc_users(user_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 3. Create qc_badges table
    echo "Creating qc_badges table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS qc_badges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        icon VARCHAR(100) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 4. Create qc_user_badges table
    echo "Creating qc_user_badges table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS qc_user_badges (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        badge_id INT NOT NULL,
        awarded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_badge (user_id, badge_id),
        FOREIGN KEY (user_id) REFERENCES qc_users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (badge_id) REFERENCES qc_badges(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // 5. Insert default badges
    echo "Inserting default badges...\n";
    $badges = [
        ['First Question', 'Asked your first question', 'fa-question-circle'],
        ['First Answer', 'Provided your first answer', 'fa-comment-dots'],
        ['Problem Solver', 'Had an answer accepted', 'fa-check-circle'],
        ['Expert', 'Reached 1000 reputation points', 'fa-star']
    ];
    // Prepare statement for insertion - handling potential duplicates by ignoring errors or checking first?
    // Using INSERT IGNORE is safer if table has unique constraint on name, but it doesn't.
    // So let's check first.
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM qc_badges WHERE name = ?");
    $insert_stmt = $pdo->prepare("INSERT INTO qc_badges (name, description, icon) VALUES (?, ?, ?)");

    foreach ($badges as $badge) {
        $check_stmt->execute([$badge[0]]);
        if ($check_stmt->fetchColumn() == 0) {
             $insert_stmt->execute($badge);
        }
    }

    // 6. Calculate stats for existing users
    echo "Recalculating user stats...\n";
    // Get all user IDs
    $stmt = $pdo->query("SELECT user_id FROM qc_users");
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($users as $uid) {
        // Questions
        $q_stmt = $pdo->prepare("SELECT COUNT(*) FROM qc_questions WHERE user_id = ?");
        $q_stmt->execute([$uid]);
        $q_count = $q_stmt->fetchColumn();

        // Answers
        $a_stmt = $pdo->prepare("SELECT COUNT(*) FROM qc_answers WHERE user_id = ?");
        $a_stmt->execute([$uid]);
        $a_count = $a_stmt->fetchColumn();

        // Accepted Answers
        $aa_stmt = $pdo->prepare("SELECT COUNT(*) FROM qc_answers WHERE user_id = ? AND is_accepted = 1");
        $aa_stmt->execute([$uid]);
        $aa_count = $aa_stmt->fetchColumn();

        // Comments (replies) - assuming qc_answer_replies are comments
        $c_stmt = $pdo->prepare("SELECT COUNT(*) FROM qc_answer_replies WHERE user_id = ?");
        $c_stmt->execute([$uid]);
        $c_count = $c_stmt->fetchColumn();

        // Reactions (received votes on questions and answers)
        // Sum vote_score from questions
        $q_score_stmt = $pdo->prepare("SELECT SUM(vote_score) FROM qc_questions WHERE user_id = ?");
        $q_score_stmt->execute([$uid]);
        $q_score = $q_score_stmt->fetchColumn() ?: 0;

        // Sum vote_score from answers
        $a_score_stmt = $pdo->prepare("SELECT SUM(vote_score) FROM qc_answers WHERE user_id = ?");
        $a_score_stmt->execute([$uid]);
        $a_score = $a_score_stmt->fetchColumn() ?: 0;

        // Sum likes from replies (qc_answer_replies has 'likes' column)
        $r_score_stmt = $pdo->prepare("SELECT SUM(likes) FROM qc_answer_replies WHERE user_id = ?");
        $r_score_stmt->execute([$uid]);
        $r_score = $r_score_stmt->fetchColumn() ?: 0;

        $total_reactions = $q_score + $a_score + $r_score;

        // Update user
        $update_stmt = $pdo->prepare("UPDATE qc_users SET
            questions_count = ?,
            answers_count = ?,
            accepted_answers_count = ?,
            comments_count = ?,
            reactions_count = ?
            WHERE user_id = ?");
        $update_stmt->execute([$q_count, $a_count, $aa_count, $c_count, $total_reactions, $uid]);
    }

    $pdo->commit();
    echo "Database setup completed successfully.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
