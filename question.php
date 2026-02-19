<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser($pdo);

// 1. Validate ID
$question_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($question_id <= 0) {
    header("Location: index.php");
    exit;
}

// 2. Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Submit Answer
    if (isset($_POST['submit_answer']) && $currentUser) {
        $content = clean_html($_POST['content'] ?? '');
        if (!empty($content)) {
            $stmt = $pdo->prepare("INSERT INTO qc_answers (question_id, user_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$question_id, $currentUser['user_id'], $content]);

            $answer_id = $pdo->lastInsertId();

            // Handle Answer Image
            if (!empty($_POST['answer_image'])) {
                $path = $_POST['answer_image'];

                // Validate path security and ownership via session
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }

                $is_owned = false;
                if (isset($_SESSION['uploaded_files']) && in_array($path, $_SESSION['uploaded_files'])) {
                    $is_owned = true;
                }

                if ($is_owned && strpos($path, 'uploads/images/') === 0 && strpos($path, '..') === false) {
                    $stmtImg = $pdo->prepare("INSERT INTO qc_answer_images (answer_id, image_path) VALUES (?, ?)");
                    $stmtImg->execute([$answer_id, $path]);

                    // Optional: Remove from session to prevent reuse (if desired, though reuse might be harmless)
                    // $key = array_search($path, $_SESSION['uploaded_files']);
                    // unset($_SESSION['uploaded_files'][$key]);
                }
            }

            header("Location: question.php?id=" . $question_id);
            exit;
        }
    }

    // Delete Question
    if (isset($_POST['delete_question']) && $currentUser) {
        // Verify ownership
        $stmt = $pdo->prepare("SELECT user_id, category_id FROM qc_questions WHERE id = ?");
        $stmt->execute([$question_id]);
        $q = $stmt->fetch();

        if ($q && ($q['user_id'] == $currentUser['user_id'] || !empty($currentUser['is_admin']))) {
            // 1. Process Answers Points Deduction
            $stmt = $pdo->prepare("SELECT id, user_id FROM qc_answers WHERE question_id = ?");
            $stmt->execute([$question_id]);
            $relatedAnswers = $stmt->fetchAll();

            foreach ($relatedAnswers as $ans) {
                // Calculate positive votes
                $stmtScore = $pdo->prepare("SELECT COUNT(*) FROM qc_votes WHERE target_type = 'answer' AND target_id = ? AND vote_value = 1");
                $stmtScore->execute([$ans['id']]);
                $positiveVotes = (int)$stmtScore->fetchColumn();

                if ($positiveVotes > 0 && $ans['user_id']) {
                    $stmtUpdate = $pdo->prepare("UPDATE qc_users SET reputation_points = reputation_points - ? WHERE user_id = ?");
                    $stmtUpdate->execute([$positiveVotes, $ans['user_id']]);
                }

                // Cleanup Votes for Answer
                $stmtDelVotes = $pdo->prepare("DELETE FROM qc_votes WHERE target_type = 'answer' AND target_id = ?");
                $stmtDelVotes->execute([$ans['id']]);
            }

            // 2. Process Question Points Deduction (Optional but logical)
             // Calculate positive votes for question
            $stmtScoreQ = $pdo->prepare("SELECT COUNT(*) FROM qc_votes WHERE target_type = 'question' AND target_id = ? AND vote_value = 1");
            $stmtScoreQ->execute([$question_id]);
            $positiveVotesQ = (int)$stmtScoreQ->fetchColumn();

            if ($positiveVotesQ > 0 && $q['user_id']) {
                $stmtUpdateQ = $pdo->prepare("UPDATE qc_users SET reputation_points = reputation_points - ? WHERE user_id = ?");
                $stmtUpdateQ->execute([$positiveVotesQ, $q['user_id']]);
            }

            // Cleanup Votes for Question
            $stmtDelVotesQ = $pdo->prepare("DELETE FROM qc_votes WHERE target_type = 'question' AND target_id = ?");
            $stmtDelVotesQ->execute([$question_id]);

            // Calculate storage to free before deleting
            $stmtImgs = $pdo->prepare("SELECT image_path FROM qc_question_images WHERE question_id = ?");
            $stmtImgs->execute([$question_id]);
            $imgs = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
            $totalSize = 0;
            foreach ($imgs as $path) {
                if (file_exists(__DIR__ . '/' . $path)) {
                    $totalSize += filesize(__DIR__ . '/' . $path);
                    unlink(__DIR__ . '/' . $path); // Clean up file
                }
            }
            if ($totalSize > 0) {
                update_storage_usage($currentUser['user_id'], -$totalSize, $pdo);
            }

            // 3. Delete Question
            $stmt = $pdo->prepare("DELETE FROM qc_questions WHERE id = ?");
            $stmt->execute([$question_id]);
            // Redirect to category
            header("Location: kategoria.php?id=" . $q['category_id']);
            exit;
        }
    }

    // Update Question
    if (isset($_POST['update_question']) && $currentUser) {
        $title = trim($_POST['title'] ?? '');
        $content = clean_html($_POST['content'] ?? '');

        if ($title && $content) {
             // Verify ownership
            $stmt = $pdo->prepare("SELECT user_id FROM qc_questions WHERE id = ?");
            $stmt->execute([$question_id]);
            $q = $stmt->fetch();

            if ($q && ($q['user_id'] == $currentUser['user_id'] || !empty($currentUser['is_admin']))) {
                $stmt = $pdo->prepare("UPDATE qc_questions SET title = ?, content = ? WHERE id = ?");
                $stmt->execute([$title, $content, $question_id]);

                // --- Image Management ---

                // 1. Deletion
                if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                    foreach ($_POST['delete_images'] as $del_id) {
                        $del_id = (int)$del_id;
                        // Verify ownership of the image via question_id
                        $stmtCheck = $pdo->prepare("SELECT image_path FROM qc_question_images WHERE id = ? AND question_id = ?");
                        $stmtCheck->execute([$del_id, $question_id]);
                        $img = $stmtCheck->fetch();

                        if ($img) {
                            // Remove file
                            if (file_exists(__DIR__ . '/' . $img['image_path'])) {
                                $size = filesize(__DIR__ . '/' . $img['image_path']);
                                if (unlink(__DIR__ . '/' . $img['image_path'])) {
                                    update_storage_usage($currentUser['user_id'], -$size, $pdo);
                                }
                            }
                            // Remove DB entry
                            $pdo->prepare("DELETE FROM qc_question_images WHERE id = ?")->execute([$del_id]);
                        }
                    }
                }

                // 2. Addition
                if (isset($_POST['uploaded_images']) && is_array($_POST['uploaded_images'])) {
                    // Count existing
                    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM qc_question_images WHERE question_id = ?");
                    $stmtCount->execute([$question_id]);
                    $current_count = $stmtCount->fetchColumn();

                    foreach ($_POST['uploaded_images'] as $path) {
                        if ($current_count >= 3) break;

                        // Basic validation
                        if (strpos($path, 'uploads/images/') === 0) {
                            $stmtImg = $pdo->prepare("INSERT INTO qc_question_images (question_id, image_path) VALUES (?, ?)");
                            $stmtImg->execute([$question_id, $path]);
                            $current_count++;
                        }
                    }
                }

                // --- Poll Management ---
                // Check if poll exists
                $stmtPoll = $pdo->prepare("SELECT id FROM qc_polls WHERE question_id = ?");
                $stmtPoll->execute([$question_id]);
                $existingPoll = $stmtPoll->fetch();

                if (isset($_POST['delete_poll']) && $_POST['delete_poll'] == 1) {
                    // Delete Poll
                    if ($existingPoll) {
                        $pdo->prepare("DELETE FROM qc_polls WHERE id = ?")->execute([$existingPoll['id']]);
                    }
                } elseif (isset($_POST['poll_question'])) {
                    $poll_question = trim($_POST['poll_question']);
                    $poll_options_new = $_POST['poll_options'] ?? [];
                    $poll_options_existing = $_POST['poll_options_existing'] ?? [];

                    // Filter empty new options
                    $poll_options_new = array_filter($poll_options_new, function($val) { return trim($val) !== ''; });

                    if ($poll_question && (count($poll_options_new) + count($poll_options_existing) >= 2)) {
                        if ($existingPoll) {
                            // Update existing poll
                            $stmtUpd = $pdo->prepare("UPDATE qc_polls SET question_text = ? WHERE id = ?");
                            $stmtUpd->execute([$poll_question, $existingPoll['id']]);
                            $poll_id = $existingPoll['id'];
                        } else {
                            // Create new poll
                            $stmtIns = $pdo->prepare("INSERT INTO qc_polls (question_id, question_text) VALUES (?, ?)");
                            $stmtIns->execute([$question_id, $poll_question]);
                            $poll_id = $pdo->lastInsertId();
                        }

                        // Handle Existing Options (Update/Delete)
                        // If an existing option ID is NOT in $_POST['poll_options_existing'], it means it was removed in UI
                        // However, simpler logic: Loop through existing DB options, if not in POST, delete. If in POST, update text.
                        $stmtGetOpts = $pdo->prepare("SELECT id FROM qc_poll_options WHERE poll_id = ?");
                        $stmtGetOpts->execute([$poll_id]);
                        $dbOpts = $stmtGetOpts->fetchAll(PDO::FETCH_COLUMN);

                        $keptIds = array_keys($poll_options_existing);

                        foreach ($dbOpts as $dbId) {
                            if (!in_array($dbId, $keptIds)) {
                                $pdo->prepare("DELETE FROM qc_poll_options WHERE id = ?")->execute([$dbId]);
                            }
                        }

                        foreach ($poll_options_existing as $optId => $optText) {
                            if (trim($optText) !== '') {
                                $pdo->prepare("UPDATE qc_poll_options SET option_text = ? WHERE id = ?")->execute([trim($optText), $optId]);
                            }
                        }

                        // Handle New Options
                        $stmtOptIns = $pdo->prepare("INSERT INTO qc_poll_options (poll_id, option_text) VALUES (?, ?)");
                        foreach ($poll_options_new as $optText) {
                            $stmtOptIns->execute([$poll_id, trim($optText)]);
                        }
                    }
                }

                header("Location: question.php?id=" . $question_id);
                exit;
            }
        }
    }

    // Close Question
    if (isset($_POST['close_question']) && $currentUser) {
        // Verify ownership
        $stmt = $pdo->prepare("SELECT user_id FROM qc_questions WHERE id = ?");
        $stmt->execute([$question_id]);
        $q = $stmt->fetch();

        if ($q && ($q['user_id'] == $currentUser['user_id'] || !empty($currentUser['is_admin']))) {
            $stmt = $pdo->prepare("UPDATE qc_questions SET status = 'closed' WHERE id = ?");
            $stmt->execute([$question_id]);
            header("Location: question.php?id=" . $question_id);
            exit;
        }
    }

    // Toggle Poll Status (Close/Open)
    if (isset($_POST['toggle_poll_status']) && $currentUser) {
        $poll_id = (int)$_POST['poll_id'];

        // Verify ownership via question
        $stmt = $pdo->prepare("SELECT q.user_id, p.id FROM qc_questions q JOIN qc_polls p ON q.id = p.question_id WHERE p.id = ? AND q.id = ?");
        $stmt->execute([$poll_id, $question_id]);
        $check = $stmt->fetch();

        if ($check && ($check['user_id'] == $currentUser['user_id'] || !empty($currentUser['is_admin']))) {
            // Toggle
            $stmt = $pdo->prepare("UPDATE qc_polls SET is_closed = NOT is_closed WHERE id = ?");
            $stmt->execute([$poll_id]);
            header("Location: question.php?id=" . $question_id);
            exit;
        }
    }

    // Delete Answer
    if (isset($_POST['delete_answer']) && $currentUser) {
        $answer_id = (int)($_POST['answer_id'] ?? 0);

        // Verify ownership and check question status
        $stmt = $pdo->prepare("
            SELECT a.user_id, q.status
            FROM qc_answers a
            JOIN qc_questions q ON a.question_id = q.id
            WHERE a.id = ?
        ");
        $stmt->execute([$answer_id]);
        $ans = $stmt->fetch();

        if ($ans && ($ans['user_id'] == $currentUser['user_id'] || !empty($currentUser['is_admin'])) && $ans['status'] !== 'solved' && $ans['status'] !== 'closed') {
            // Points Deduction
            $stmtScore = $pdo->prepare("SELECT COUNT(*) FROM qc_votes WHERE target_type = 'answer' AND target_id = ? AND vote_value = 1");
            $stmtScore->execute([$answer_id]);
            $positiveVotes = (int)$stmtScore->fetchColumn();

            if ($positiveVotes > 0 && $ans['user_id']) {
                $stmtUpdate = $pdo->prepare("UPDATE qc_users SET reputation_points = reputation_points - ? WHERE user_id = ?");
                $stmtUpdate->execute([$positiveVotes, $ans['user_id']]);
            }

            // Cleanup Votes
            $stmtDelVotes = $pdo->prepare("DELETE FROM qc_votes WHERE target_type = 'answer' AND target_id = ?");
            $stmtDelVotes->execute([$answer_id]);

            // Calculate storage to free before deleting
            $stmtImgs = $pdo->prepare("SELECT image_path FROM qc_answer_images WHERE answer_id = ?");
            $stmtImgs->execute([$answer_id]);
            $imgs = $stmtImgs->fetchAll(PDO::FETCH_COLUMN);
            $totalSize = 0;
            foreach ($imgs as $path) {
                if (file_exists(__DIR__ . '/' . $path)) {
                    $totalSize += filesize(__DIR__ . '/' . $path);
                    unlink(__DIR__ . '/' . $path); // Clean up file
                }
            }
            if ($totalSize > 0) {
                update_storage_usage($ans['user_id'], -$totalSize, $pdo);
            }

            // Delete Answer
            $stmt = $pdo->prepare("DELETE FROM qc_answers WHERE id = ?");
            $stmt->execute([$answer_id]);

            header("Location: question.php?id=" . $question_id);
            exit;
        }
    }

    // Update Answer
    if (isset($_POST['update_answer']) && $currentUser) {
        $answer_id = (int)($_POST['answer_id'] ?? 0);
        $content = clean_html($_POST['content'] ?? '');

        if ($content && $answer_id > 0) {
            // Verify ownership and check question status
            $stmt = $pdo->prepare("
                SELECT a.user_id, q.status, a.created_at
                FROM qc_answers a
                JOIN qc_questions q ON a.question_id = q.id
                WHERE a.id = ?
            ");
            $stmt->execute([$answer_id]);
            $ans = $stmt->fetch();

            if ($ans && ($ans['user_id'] == $currentUser['user_id'] || !empty($currentUser['is_admin'])) && $ans['status'] !== 'solved' && $ans['status'] !== 'closed') {
                // History Logic: Audit Log Style

                // 1. Fetch current content (Old)
                $stmtOld = $pdo->prepare("SELECT content FROM qc_answers WHERE id = ?");
                $stmtOld->execute([$answer_id]);
                $oldContent = $stmtOld->fetchColumn();

                if ($oldContent !== false) {
                    // 2. Check if history exists
                    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM qc_answer_edits WHERE answer_id = ?");
                    $stmtCheck->execute([$answer_id]);
                    $hasHistory = $stmtCheck->fetchColumn() > 0;

                    // 3. If first edit, backfill original
                    if (!$hasHistory) {
                        $stmtBackfill = $pdo->prepare("INSERT INTO qc_answer_edits (answer_id, user_id, content, edited_at) VALUES (?, ?, ?, ?)");
                        // Attribute to original author, use original creation time
                        $stmtBackfill->execute([$answer_id, $ans['user_id'], $oldContent, $ans['created_at']]);
                    }

                    // 4. Insert NEW version (The edit)
                    $stmtNew = $pdo->prepare("INSERT INTO qc_answer_edits (answer_id, user_id, content, edited_at) VALUES (?, ?, ?, NOW())");
                    $stmtNew->execute([$answer_id, $currentUser['user_id'], $content]);
                }

                $stmt = $pdo->prepare("UPDATE qc_answers SET content = ? WHERE id = ?");
                $stmt->execute([$content, $answer_id]);

                $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
                header("Location: question.php?id=" . $question_id . "&page=" . $page . "#answer-card-" . $answer_id);
                exit;
            }
        }
    }
}

// 3. Fetch Data
// Question
$stmt = $pdo->prepare("
    SELECT q.*, u.username, u.reputation_points, u.custom_title, c.name as category_name, c.slug as category_slug, c.id as category_id
    FROM qc_questions q
    LEFT JOIN qc_users u ON q.user_id = u.user_id
    LEFT JOIN qc_categories c ON q.category_id = c.id
    WHERE q.id = ?
");
$stmt->execute([$question_id]);
$question = $stmt->fetch();

if (!$question) {
    die("A kérdés nem található.");
}

// Fetch Bookmark Status
$isBookmarked = false;
if ($currentUser) {
    $stmtBm = $pdo->prepare("SELECT id FROM qc_bookmarks WHERE user_id = ? AND question_id = ?");
    $stmtBm->execute([$currentUser['user_id'], $question_id]);
    if ($stmtBm->fetch()) {
        $isBookmarked = true;
    }
}

// Fetch Attached Images
$stmt = $pdo->prepare("SELECT * FROM qc_question_images WHERE question_id = ? ORDER BY id ASC");
$stmt->execute([$question_id]);
$question_images = $stmt->fetchAll();

// Fetch Poll Data
$poll = null;
$poll_options = [];
$user_vote = null;
$total_votes = 0;

$stmtPoll = $pdo->prepare("SELECT * FROM qc_polls WHERE question_id = ?");
$stmtPoll->execute([$question_id]);
$poll = $stmtPoll->fetch();

if ($poll) {
    $stmtOpt = $pdo->prepare("SELECT * FROM qc_poll_options WHERE poll_id = ? ORDER BY id ASC");
    $stmtOpt->execute([$poll['id']]);
    $poll_options = $stmtOpt->fetchAll();

    foreach ($poll_options as $opt) {
        $total_votes += $opt['vote_count'];
    }

    if ($currentUser) {
        $stmtVote = $pdo->prepare("SELECT option_id FROM qc_poll_votes WHERE poll_id = ? AND user_id = ?");
        $stmtVote->execute([$poll['id'], $currentUser['user_id']]);
        $vote = $stmtVote->fetch();
        if ($vote) {
            $user_vote = $vote['option_id'];
        }
    }
}

// Sorting Logic
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'best';
$orderBy = "a.is_accepted DESC, a.vote_score DESC, a.created_at ASC"; // Default 'best'

if ($sort === 'newest') {
    $orderBy = "a.is_accepted DESC, a.created_at DESC";
}

// Pagination Logic
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit_options = [5, 10, 15];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limit_options) ? (int)$_GET['limit'] : 5;
$offset = ($page - 1) * $limit;

// Get Total Answers Count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM qc_answers WHERE question_id = ?");
$countStmt->execute([$question_id]);
$total_answers = $countStmt->fetchColumn();
$total_pages = ceil($total_answers / $limit);

// Answers
$stmt = $pdo->prepare("
    SELECT a.*, u.username, u.custom_title, u.reputation_points, u.is_admin,
    (SELECT COUNT(*) FROM qc_answer_replies WHERE answer_id = a.id) as reply_count
    FROM qc_answers a
    LEFT JOIN qc_users u ON a.user_id = u.user_id
    WHERE a.question_id = :question_id
    ORDER BY $orderBy
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':question_id', $question_id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$answers = $stmt->fetchAll();

// Fetch Edit Counts
if (!empty($answers)) {
    $ansIds = array_column($answers, 'id');
    $placeholders = implode(',', array_fill(0, count($ansIds), '?'));

    $stmtEdits = $pdo->prepare("
        SELECT answer_id, COUNT(*) as edit_count
        FROM qc_answer_edits
        WHERE answer_id IN ($placeholders)
        GROUP BY answer_id
    ");
    $stmtEdits->execute($ansIds);
    $editCounts = $stmtEdits->fetchAll(PDO::FETCH_KEY_PAIR);

    foreach ($answers as &$ans) {
        $ans['edit_count'] = $editCounts[$ans['id']] ?? 0;
    }
    unset($ans); // break reference
}

// Fetch Answer Images
if (!empty($answers)) {
    $ansIds = array_column($answers, 'id');
    $placeholders = implode(',', array_fill(0, count($ansIds), '?'));

    $stmtImgs = $pdo->prepare("SELECT answer_id, image_path, original_name FROM qc_answer_images WHERE answer_id IN ($placeholders)");
    $stmtImgs->execute($ansIds);
    $answerImages = $stmtImgs->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

    foreach ($answers as &$ans) {
        if (isset($answerImages[$ans['id']])) {
            $ans['attached_image'] = $answerImages[$ans['id']][0];
        }
    }
    unset($ans);
}

// Increment View Count
$pdo->prepare("UPDATE qc_questions SET view_count = view_count + 1 WHERE id = ?")->execute([$question_id]);

// Edit Mode Check
$editMode = false;
if (isset($_GET['edit']) && $_GET['edit'] == 1 && $currentUser && ($currentUser['user_id'] == $question['user_id'] || !empty($currentUser['is_admin']))) {
    $editMode = true;
}

// Prepare SEO Data
$seoData = [
    'title' => $question['title'] . ' | SilverPC Fórum',
    'description' => mb_substr(strip_tags($question['content']), 0, 160),
    'og_type' => 'article'
];

// Fetch Latest Questions for Sidebar
$stmtLatest = $pdo->prepare("SELECT q.id, q.title, q.created_at, q.slug, u.username FROM qc_questions q LEFT JOIN qc_users u ON q.user_id = u.user_id WHERE q.id != ? ORDER BY q.created_at DESC LIMIT 5");
$stmtLatest->execute([$question_id]);
$latest_questions = $stmtLatest->fetchAll();

if (isset($_GET['ajax_answers'])) {
    include __DIR__ . '/answer_list_partial.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <?php renderSeoHead($seoData); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.ico" sizes="any">
	<link rel="icon" href="/icon.svg" type="image/svg+xml">
	<link rel="apple-touch-icon" href="/apple-touch-icon.png">
	<meta name="theme-color" content="#008080">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,700;1,700&family=Inter:wght@400;600;800&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
	<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/layout.css">
	<link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/article.css">
	<link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/widget.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-css.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-javascript.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup-templating.min.js"></script>
	<script src="https://unpkg.com/lucide@latest"></script>
	<script src="https://jsc.adskeeper.com/site/1013449.js" async></script>
	<script src="https://jsc.mgid.com/site/1013450.js" async></script>
</head>
<body class="min-h-screen relative">
<?php
// header menü beillesztése
require_once __DIR__ . '/../inc/header_menu.php'; ?>
<!-- CONTENT PLACEHOLDER -->
<main class="header-content-wrapper mt-0 pb-0">
<main id="main" class="site-main">

    <!-- SEO: Fő konténer mint NewsArticle -->
    <div class="cikk-fo-container" itemscope itemtype="http://schema.org/NewsArticle">
        <meta itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="<?php echo htmlspecialchars("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>"/>

        <div class="cikk-fo-layout">

            <!-- BAL OSZLOP -->
            <div class="cikk-fo-col-left" style="background: linear-gradient(135deg, rgba(11, 25, 30, 0.9), rgba(10, 15, 20, 0.9));">

 <style>
        /* Latest Questions Widget */
        .latest-question-item {
            display: block;
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            transition: background 0.2s;
            text-decoration: none;
        }
        .latest-question-item:last-child {
            border-bottom: none;
        }
        .latest-question-item:hover {
            background: rgba(255,255,255,0.05);
        }
        .lq-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #e2e8f0;
            margin-bottom: 6px;
            line-height: 1.4;
            transition: color 0.2s;
        }
        .latest-question-item:hover .lq-title {
            color: #38bdf8;
        }
        .lq-meta {
            font-size: 0.75rem;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .lq-meta i { margin-right: 4px; }

        /* Fő Wrapper */
        .q_v_wrapper {
            width: 100%;
            margin: 0 auto;
            background: #f1f5f9; /* Kért szürke háttér */
            padding: 20px;
            box-sizing: border-box;
        }

        /* Navigációs morzsa (Breadcrumb) */
        .q_v_breadcrumb {
            font-size: 0.9rem;
            color: #64748b;
            margin-bottom: 20px;
            padding-left: 5px;
        }
        .q_v_breadcrumb a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
        }
        .q_v_breadcrumb span { margin: 0 5px; opacity: 0.5; }

        /* --- 1. A KÉRDÉS KÁRTYA --- */
        .q_v_question_card {
            background: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            border-top: 4px solid #4f46e5;
            border-radius: 0;
        }

        /* Kérdés Fejléc */
        .q_v_q_header {
            padding: 25px 30px;
            border-bottom: 1px solid #e2e8f0;
        }

        .q_v_q_meta_top {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
            font-size: 0.85rem;
            flex-wrap: wrap; /* Mobilon tördelődjön */
        }

        /* Státusz Badgek */
        .q_v_badge {
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        .q_v_badge_solved { background: #ecfdf5; color: #10b981; border: 1px solid #a7f3d0; }
        .q_v_badge_waiting { background: #fff7ed; color: #f97316; border: 1px solid #fed7aa; }
        .q_v_badge_closed { background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }

        .q_v_q_date { color: #94a3b8; }

        .q_v_q_title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
            line-height: 1.3;
        }

        /* Kérdés Test (Body) Layout */
        .q_v_q_body_grid {
            display: flex;
            padding: 30px;
            gap: 30px;
        }

        /* Bal oszlop: Szöveges tartalom */
        .q_v_q_content {
            flex: 1;
            min-width: 0;
        }

        .q_v_text {
            font-size: 1.05rem;
            line-height: 1.7;
            color: #334155;
            position: relative;
        }

        .q_v_text img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin: 15px 0;
            border: 1px solid #e2e8f0;
            display: block;
        }

        /* Gallery */
        .q_v_images_gallery {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Force 3 columns horizontal */
            gap: 10px;
            margin-top: 25px;
            border-top: 1px dashed #e2e8f0;
            padding-top: 20px;
            max-width: 480px; /* Limit total width on desktop */
        }
        .q_v_gallery_item {
            display: block;
            width: 100%; /* Fill grid cell */
            aspect-ratio: 1; /* Square */
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
        }
        .q_v_gallery_item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .q_v_gallery_item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            margin: 0 !important;
            border: none !important;
        }

        .q_v_video_container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 arány */
            height: 0;
            margin: 15px 0;
            background: #000;
            border-radius: 4px;
            overflow: hidden;
            max-width: 100%; /* Fix overflow */
        }
        .q_v_video_container iframe {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%; border: none;
        }

        /* External Link Style */
        .q_v_external_link {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 2px 8px;
            border-radius: 4px;
            color: #4f46e5 !important;
            font-weight: 500;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
            margin: 0 2px;
            /* Fix overflow on mobile */
            max-width: 100%;
            white-space: normal;
            word-break: break-all;
        }
        .q_v_external_link:hover {
            background: #e2e8f0;
            color: #4338ca !important;
        }

        /* Login Placeholder Style */
        .q_v_login_link_placeholder {
            color: #ef4444 !important;
            font-weight: 600;
            text-decoration: none;
            border-bottom: 1px dashed #ef4444;
            padding-bottom: 1px;
            transition: all 0.2s;
            cursor: pointer;
            margin: 0 2px;
        }
        .q_v_login_link_placeholder:hover {
            color: #dc2626 !important;
            border-bottom-style: solid;
        }

        /* Hosszú tartalom kezelése */
        .q_v_text.truncated {
            max-height: 250px;
            overflow: hidden;
            mask-image: linear-gradient(to bottom, black 60%, transparent 100%);
            -webkit-mask-image: linear-gradient(to bottom, black 60%, transparent 100%);
        }

        .q_v_read_more_btn {
            display: none;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            color: #475569;
            padding: 8px 20px;
            width: 100%;
            text-align: center;
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.2s;
        }
        .q_v_read_more_btn:hover { background: #e2e8f0; }

        /* Jobb oszlop: User Info */
        .q_v_user_sidebar {
            width: 200px;
            flex-shrink: 0;
            background: #f8fafc;
            padding: 20px;
            text-align: center;
            border: 1px solid #f1f5f9;
            height: fit-content;
        }

        .q_v_user_avatar {
            width: 80px; height: 80px;
            border-radius: 50%;
            margin: 0 auto 10px auto;
            border: 3px solid #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .q_v_user_name {
            display: block;
            font-weight: 700;
            color: #1e293b;
            font-size: 1.1rem;
            margin-bottom: 5px;
            text-decoration: none;
        }
        .q_v_user_rank {
            font-size: 0.8rem;
            color: #64748b;
            display: block;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .q_v_user_stats {
            display: flex;
            justify-content: space-around;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .q_v_stat_item { font-size: 0.8rem; color: #475569; }
        .q_v_stat_item b { display: block; font-size: 1rem; color: #334155; }

        .q_v_op_badge {
            display: inline-block;
            background: #4f46e5;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 4px;
            margin-top: 5px;
            margin-bottom: 5px; /* Kis térköz mobilon */
        }

        /* Footer (Gombok) */
        .q_v_q_footer {
            padding: 15px 30px;
            background: #fcfcfc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .q_v_btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .q_v_btn_primary { background: #4f46e5; color: white; }
        .q_v_btn_primary:hover { background: #4338ca; }

        .q_v_btn_secondary { background: white; border: 1px solid #cbd5e1; color: #475569; }
        .q_v_btn_secondary:hover { background: #f1f5f9; color: #1e293b; border-color: #94a3b8; }

        .q_v_btn_danger { color: #ef4444; background: transparent; }
        .q_v_btn_danger:hover { background: #fef2f2; }

        /* --- 2. VÁLASZOK SZEKCIÓ --- */
        .q_v_answers_header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 0 5px;
        }
        .q_v_answers_count { font-size: 1.2rem; font-weight: 700; color: #334155; }
        .q_v_sort_select {
            padding: 6px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: white;
            color: #475569;
        }

        /* Egy Válasz Kártya */
        .q_v_answer_card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            display: flex;
        }

        .q_v_answer_solution {
            border: 2px solid #10b981;
            position: relative;
        }

        /* SPECIÁLIS AI VÁLASZ STÍLUS */
        .q_v_answer_ai {
            background: #eff6ff; /* Halvány kék háttér */
            border: 1px solid #bfdbfe;
        }

        .q_v_solution_banner {
            position: absolute;
            top: 0; right: 0;
            background: #10b981;
            color: white;
            padding: 4px 12px;
            font-size: 0.75rem;
            font-weight: 700;
            border-bottom-left-radius: 8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Szavazó rész */
        .q_v_vote_box {
            width: 60px;
            background: rgba(0,0,0,0.02);
            border-right: 1px solid rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            gap: 5px;
            flex-shrink: 0; /* Ne nyomódjon össze */
        }

        .q_v_vote_btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.2s;
        }
        .q_v_vote_btn:hover { color: #4f46e5; }
        .q_v_vote_count { font-weight: 700; font-size: 1.2rem; color: #334155; }
        .q_v_vote_count.text-red-500 { color: #ef4444 !important; }

        /* Válasz Tartalom */
        .q_v_answer_main { flex: 1; padding: 20px; min-width: 0; }

        .q_v_answer_meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding-bottom: 10px;
            flex-wrap: wrap; /* Hogy mobilon ne lógjon ki */
        }

        .q_v_answer_user {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap; /* Ha hosszú a név/titulus */
        }
        .q_v_answer_avatar { width: 32px; height: 32px; border-radius: 50%; }
        .q_v_verified_badge { color: #10b981; font-size: 0.9rem; margin-left: 5px; cursor: help; }

        /* AI badge a név mellett */
        .q_v_ai_badge_icon { color: #3b82f6; margin-left: 5px; }

        /* --- 3. SZERKESZTŐ (VISUAL EDITOR) --- */
        .q_v_editor_section {
            background: white;
            padding: 25px;
            margin-top: 40px;
            border: 1px solid #e2e8f0;
            border-top: 4px solid #334155;
        }

        .q_v_editor_title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 15px;
            display: block;
        }

        .q_v_toolbar {
            display: flex;
            gap: 5px;
            background: #f1f5f9;
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-bottom: none;
            flex-wrap: wrap;
            position: relative;
        }

        .q_v_tool_btn {
            background: white;
            border: 1px solid #cbd5e1;
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            border-radius: 3px;
            color: #475569;
            position: relative; /* Emoji panelhez */
        }
        .q_v_tool_btn:hover { background: #e2e8f0; color: #000; }

        /* EMOJI PICKER */
        .q_v_emoji_picker {
            display: none; /* Alapból rejtve */
            position: absolute;
            top: 40px;
            left: 0;
            background: white;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 10px;
            width: 250px;
            flex-wrap: wrap;
            gap: 5px;
            z-index: 100;
            border-radius: 4px;
        }
        .q_v_emoji_picker.active { display: flex; }
        .q_v_emoji_btn {
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
            transition: transform 0.2s;
        }
        .q_v_emoji_btn:hover { transform: scale(1.2); }

        /* Vizuális szerkesztőfelület (ContentEditable) */
        .q_v_editor_content {
            width: 100%;
            min-height: 150px;
            padding: 15px;
            border: 1px solid #cbd5e1;
            font-family: inherit;
            box-sizing: border-box;
            font-size: 1rem;
            color: #000000 !important;
            background-color: #ffffff !important;
            overflow-y: auto;
            outline: none;
        }
        .q_v_editor_content:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1); }

        /* JAVÍTVA: Lista stílusok és behúzás */
        .q_v_editor_content ul { list-style-type: disc; padding-left: 20px; margin-left: 20px; }
        .q_v_editor_content ol { list-style-type: decimal; padding-left: 20px; margin-left: 20px; }

        .q_v_editor_content img { max-width: 100%; border: 1px solid #eee; margin: 10px 0; }
        .q_v_editor_content blockquote { border-left: 3px solid #ccc; padding-left: 10px; color: #666; margin: 10px 0; font-style: italic;}

        .q_v_editor_footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .q_v_char_count { font-size: 0.85rem; color: #64748b; }
        .q_v_char_count.limit-near { color: #f97316; }
        .q_v_char_count.limit-reached { color: #ef4444; font-weight: bold; }

        /* LAPOZÁS */
        .q_v_pagination {
            display: flex; justify-content: center; gap: 5px; margin-top: 40px;
        }
        .q_v_page_item {
            padding: 8px 14px;
            background: white;
            border: 1px solid #cbd5e1;
            color: #475569;
            text-decoration: none;
            font-weight: 600;
        }
        .q_v_page_item.active { background: #4f46e5; color: white; border-color: #4f46e5; }
        .q_v_page_item:hover:not(.active) { background: #f1f5f9; }

        /* --- VENDÉG NÉZET ALERT STÍLUSOK --- */
        .q_v_guest_alert_box {
            background: #fff7ed;
            border: 1px solid #fdba74;
            border-left: 5px solid #f97316;
            padding: 40px;
            text-align: center;
            margin-top: 40px;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .q_v_guest_icon {
            font-size: 3rem;
            color: #f97316;
            margin-bottom: 15px;
        }
        .q_v_guest_title {
            font-weight: 700;
            color: #9a3412;
            font-size: 1.4rem;
            margin-bottom: 10px;
        }
        .q_v_guest_text {
            color: #c2410c;
            margin-bottom: 25px;
            font-size: 1rem;
            line-height: 1.5;
        }
        .q_v_guest_actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* ANSWER ACTIONS (Edit/Delete) */
        .q_v_ans_actions {
            display: flex;
            gap: 0;
            opacity: 0;
            margin-left: 0;
            max-width: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .q_v_answer_card:hover .q_v_ans_actions {
            opacity: 1;
            max-width: 100px;
            margin-left: 10px;
            gap: 10px;
        }

        /* Fix Text Colors in Edit Forms */
        .q_v_question_card input[type="text"],
        .q_v_question_card textarea,
        .q_v_question_card select,
        .n_q_input {
            color: #1e293b !important;
        }

        .q_v_question_card button {
            color: #334155;
        }

        .q_v_question_card button.q_v_btn_primary {
            color: white !important;
        }

        .q_v_question_card button.q_v_btn_danger {
            color: #ef4444 !important;
        }

        .q_v_ans_btn {
            border: none; background: none; cursor: pointer; color: #94a3b8; font-size: 0.9rem; padding: 0;
        }
        .q_v_ans_btn:hover { color: #4f46e5; }
        .q_v_ans_btn_del:hover { color: #ef4444; }

        /* New Answer classes (replacing inline styles) */
        .q_v_meta_actions { display: flex; align-items: center; gap: 15px; }
        .q_v_ai_disclaimer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #bfdbfe; color: #64748b; font-size: 0.8rem; font-style: italic; }
        .q_v_accept_container { margin-top: 15px; display: flex; justify-content: flex-end; }

        /* RESPONSIVE JAVÍTÁSOK */
        @media (max-width: 768px) {
            .q_v_ans_actions {
                opacity: 1;
                max-width: none;
                margin-left: 10px;
                gap: 10px;
            } /* Always visible on mobile */

            .q_v_wrapper { padding: 0; background: #f1f5f9; }
            .q_v_q_body_grid { flex-direction: column; padding: 20px; }

            /* Mobil Sidebar Javítás */
            .q_v_user_sidebar {
                width: 100%;
                display: flex;
                align-items: flex-start;
                text-align: left;
                padding: 15px;
                background: #f8fafc;
                gap: 15px;
            }
            .q_v_user_avatar { margin: 0; width: 50px; height: 50px; flex-shrink: 0; }

            .q_v_mobile_user_details {
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .q_v_user_name { margin-bottom: 2px; }
            .q_v_user_rank { margin-bottom: 2px; }
            .q_v_user_stats { display: none; }

            .q_v_q_footer { flex-direction: column; }
            .q_v_btn { width: 100%; justify-content: center; }

            /* --- ÚJ GRID LAYOUT MOBILRA (Answer Card) --- */
            .q_v_answer_card {
                display: grid;
                grid-template-columns: 40px 1fr; /* VoteBox width fixed, Content flexible */
                grid-template-rows: auto auto; /* Header, Body */
            }

            .q_v_vote_box {
                grid-column: 1;
                grid-row: 1;
                width: 100%;
                padding-top: 10px;
                height: auto; /* Only take height of row 1 (header) */
                border-right: none; /* Optional: remove border if it looks weird ending abruptly */
            }
            /* Vote btn adjustments for mobile */
            .q_v_vote_btn { font-size: 1.2rem; }
            .q_v_vote_count { font-size: 1rem; }

            .q_v_answer_main {
                display: contents; /* Children become grid items */
            }

            .q_v_answer_meta {
                grid-column: 2;
                grid-row: 1;
                padding: 10px 10px 0 10px;
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
                border-bottom: none; /* Remove border from meta, maybe apply to grid row if needed? */
            }

            .q_v_text {
                grid-column: 1 / -1; /* Full width */
                grid-row: 2;
                padding: 15px 15px 15px 15px;
                border-top: 1px solid rgba(0,0,0,0.05); /* Subtle separator line */
            }

            /* Handle extra elements in answer_main */
            .q_v_ai_disclaimer,
            .q_v_accept_container {
                grid-column: 1 / -1;
                padding-left: 15px;
                padding-right: 15px;
            }

            /* Improved Meta Actions Layout */
            .q_v_meta_actions {
                width: 100%;
                justify-content: space-between;
                margin-top: 8px;
                flex-wrap: wrap;
                gap: 10px;
            }

            /* Adjust date in meta actions if needed */
            .q_v_meta_actions > span {
                font-size: 0.75rem;
            }
        }

        /* Fix for editing mode on mobile/desktop */
        .q_v_answer_card.q_v_answer_editing {
            display: block !important;
        }
        .q_v_editor_wrapper {
            width: 100%;
            padding: 20px;
        }
        .q_v_edit_buttons {
            display: flex;
            gap: 10px;
        }
        @media (max-width: 768px) {
            .q_v_editor_wrapper {
                padding: 15px;
            }
            .q_v_edit_buttons {
                flex-direction: column;
            }
        }

        /* Robbanás effekt (Like/Dislike) */
        @keyframes explode {
            0% { transform: translate(0, 0) scale(1); opacity: 1; }
            100% { transform: translate(var(--tx), var(--ty)) scale(0); opacity: 0; }
        }

        .particle {
            position: absolute;
            pointer-events: none;
            animation: explode 0.8s ease-out forwards;
            z-index: 9999;
        }

        /* --- ANSWER CONTENT FORMATTING --- */
        .q_v_text {
            font-size: 1.05rem;
            line-height: 1.7;
            color: #334155;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        .q_v_text p { margin-bottom: 1.5em; }
        .q_v_text p:last-child { margin-bottom: 0; }

        .q_v_text ul, .q_v_text ol { margin-bottom: 1.5em; padding-left: 1.5em; }
        .q_v_text ul { list-style-type: disc; }
        .q_v_text ol { list-style-type: decimal; }
        .q_v_text li { margin-bottom: 0.5em; }
        .q_v_text li > ul, .q_v_text li > ol { margin-top: 0.5em; margin-bottom: 0.5em; }

        .q_v_text code {
            font-family: 'Menlo', 'Monaco', 'Courier New', monospace;
            font-size: 0.9em;
            color: #e11d48;
            background-color: #f1f5f9;
            padding: 0.2em 0.4em;
            border-radius: 4px;
            word-break: break-word;
        }

        .q_v_text pre {
            background-color: #1e293b;
            color: #e2e8f0;
            padding: 1em;
            border-radius: 6px;
            overflow-x: auto;
            margin-bottom: 1.5em;
            font-family: 'Menlo', 'Monaco', 'Courier New', monospace;
            font-size: 0.9em;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .q_v_text pre code {
            background-color: transparent;
            color: inherit;
            padding: 0;
            border-radius: 0;
            word-break: normal;
        }

        .q_v_text blockquote {
            border-left: 4px solid #cbd5e1;
            padding: 10px 15px;
            margin: 0 0 1.5em 0;
            background: #f8fafc;
            color: #64748b;
            font-style: italic;
            border-radius: 0 4px 4px 0;
        }

        .q_v_text h1, .q_v_text h2, .q_v_text h3, .q_v_text h4, .q_v_text h5, .q_v_text h6 {
            color: #1e293b;
            font-weight: 700;
            margin-top: 1.5em;
            margin-bottom: 0.5em;
            line-height: 1.3;
        }
        .q_v_text h1 { font-size: 1.8em; }
        .q_v_text h2 { font-size: 1.5em; }
        .q_v_text h3 { font-size: 1.25em; }

        .q_v_text img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }

        .q_v_text table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5em;
        }
        .q_v_text th, .q_v_text td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
        }
        .q_v_text th { background-color: #f1f5f9; font-weight: 600; }

        /* --- ÚJ BREADCRUMB + GOMB LAYOUT --- */
        .q_v_breadcrumb_row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 15px;
            /* Ensure it doesn't overflow parent */
            max-width: 100%;
        }

        .q_v_breadcrumb {
            /* Reset existing margin as it is now handled by row */
            margin-bottom: 0;

            /* Truncation & Single Line */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;

            /* Flex behavior: take available space but shrink if needed */
            flex: 1;
            min-width: 0; /* Important for flex child truncation */

            /* Fade effect at the end */
            mask-image: linear-gradient(to right, black 80%, transparent 100%);
            -webkit-mask-image: linear-gradient(to right, black 80%, transparent 100%);
        }

        .q_v_new_question_btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
            transition: all 0.3s ease;
            white-space: nowrap;
            font-size: 0.9rem;
            flex-shrink: 0; /* Button stays visible */
        }

        .q_v_new_question_btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(16, 185, 129, 0.3);
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            color: white;
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            .q_v_view_text {
                display: none;
            }

            /* Ensure breadcrumb row handles small screens well */
            .q_v_breadcrumb_row {
                gap: 10px;
            }

            .q_v_new_question_btn {
                padding: 6px 12px;
                font-size: 0.85rem;
            }
        }
    </style>

<div class="q_v_wrapper">

        <!-- BREADCRUMB & NEW QUESTION BUTTON -->
        <div class="q_v_breadcrumb_row">
            <div class="q_v_breadcrumb">
                <a href="index.php">Főoldal</a> <span>/</span>
                <a href="kategoria.php?id=<?php echo $question['category_id']; ?>"><?php echo htmlspecialchars($question['category_name']); ?></a> <span>/</span>
                <?php echo htmlspecialchars($question['title']); ?>
            </div>
            <a href="kerdes_bevitele.php?from_cat=<?php echo $question['category_id']; ?>" class="q_v_new_question_btn">
                <i class="fa-solid fa-plus"></i> Új kérdés
            </a>
        </div>

        <!-- A KÉRDÉS FŐ KÁRTYÁJA -->
        <div class="q_v_question_card">

            <?php if ($editMode): ?>
                <div style="padding: 30px;">
                    <form method="POST">
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom: 5px;">Cím</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($question['title']); ?>" style="width:100%; padding: 10px; border:1px solid #ccc;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom: 5px;">Tartalom</label>
                            <!-- Using simple textarea for edit for now, to keep it simple -->
                            <div class="q_v_editor_content" id="editQuestionEditor" contenteditable="true" style="border:1px solid #ccc; min-height: 200px; padding: 10px;"><?php echo $question['content']; ?></div>
                            <input type="hidden" name="content" id="editQuestionContent">
                        </div>

                        <!-- Képfeltöltés (Edit) -->
                        <div style="margin-bottom: 20px; border-top:1px solid #e2e8f0; padding-top:20px;">
                            <label style="display:block; font-weight:bold; margin-bottom: 8px; color:#334155;">Képek kezelése (Max 3 db)</label>

                            <?php if (has_storage_space($currentUser)): ?>
                            <div class="n_q_upload_area" id="dropZoneEdit">
                                <div class="n_q_upload_placeholder">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                    <p>Húzd ide az új képeket</p>
                                </div>
                                <input type="file" id="fileInputEdit" multiple accept="image/*" style="display:none;">
                            </div>
                            <?php else: ?>
                            <div class="bg-red-500/10 border border-red-500/30 text-red-500 p-4 rounded-lg text-center font-bold mb-4">
                                <i class="fas fa-exclamation-triangle mr-2"></i> A tárhelyed megtelt! Nem tölthetsz fel több képet.
                            </div>
                            <?php endif; ?>

                            <div class="n_q_preview_container" id="previewContainerEdit">
                                <!-- Existing Images -->
                                <?php foreach ($question_images as $img): ?>
                                    <div class="n_q_preview_item existing-image" data-id="<?php echo $img['id']; ?>">
                                        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" class="n_q_preview_img">
                                        <button type="button" class="n_q_remove_btn" onclick="markForDeletion(this, <?php echo $img['id']; ?>)"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div id="hiddenInputsContainerEdit"></div>
                        </div>

                        <!-- Poll Edit Section -->
                        <div style="margin-bottom: 20px; border-top:1px solid #e2e8f0; padding-top:20px;">
                            <label style="display:block; font-weight:bold; margin-bottom: 10px; color:#334155;">Szavazás kezelése</label>

                            <?php if ($poll): ?>
                                <div style="margin-bottom:15px; background:#f8fafc; padding:15px; border:1px solid #e2e8f0;">
                                    <div style="margin-bottom:10px;">
                                        <label style="font-size:0.9rem;">Kérdés</label>
                                        <input type="text" name="poll_question" value="<?php echo htmlspecialchars($poll['question_text']); ?>" class="n_q_input" style="width:100%; border:1px solid #cbd5e1; padding:8px;">
                                    </div>

                                    <label style="font-size:0.9rem;">Opciók</label>
                                    <div id="editPollOptionsList">
                                        <?php foreach ($poll_options as $opt): ?>
                                            <div class="poll-option-row" style="margin-bottom:5px; display:flex; gap:10px;">
                                                <input type="text" name="poll_options_existing[<?php echo $opt['id']; ?>]" value="<?php echo htmlspecialchars($opt['option_text']); ?>" class="n_q_input" style="width:100%; border:1px solid #cbd5e1; padding:8px;">
                                                <button type="button" onclick="this.parentElement.remove()" style="color:#ef4444; border:none; background:none; cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <button type="button" onclick="addEditPollOption()" style="margin-top:10px; background:white; border:1px solid #cbd5e1; padding:5px 10px; cursor:pointer; color:#334155;"><i class="fa-solid fa-plus"></i> Opció hozzáadása</button>

                                    <div style="margin-top:20px; border-top:1px dashed #cbd5e1; padding-top:10px;">
                                        <label style="color:#ef4444; font-weight:bold; cursor:pointer;">
                                            <input type="checkbox" name="delete_poll" value="1"> Szavazás törlése véglegesen
                                        </label>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="background:#f8fafc; padding:15px; border:1px solid #e2e8f0;">
                                    <p style="margin-bottom:10px; font-size:0.9rem; color:#64748b;">Nincs szavazás csatolva. Itt létrehozhatsz egyet:</p>
                                    <div style="margin-bottom:10px;">
                                        <input type="text" name="poll_question" placeholder="Szavazás kérdése" class="n_q_input" style="width:100%; border:1px solid #cbd5e1; padding:8px;">
                                    </div>
                                    <div id="editPollOptionsList">
                                        <div class="poll-option-row" style="margin-bottom:5px;">
                                            <input type="text" name="poll_options[]" placeholder="1. opció" class="n_q_input" style="width:100%; border:1px solid #cbd5e1; padding:8px;">
                                        </div>
                                        <div class="poll-option-row" style="margin-bottom:5px;">
                                            <input type="text" name="poll_options[]" placeholder="2. opció" class="n_q_input" style="width:100%; border:1px solid #cbd5e1; padding:8px;">
                                        </div>
                                    </div>
                                    <button type="button" onclick="addEditPollOption()" style="margin-top:10px; background:white; border:1px solid #cbd5e1; padding:5px 10px; cursor:pointer; color:#334155;"><i class="fa-solid fa-plus"></i> Opció hozzáadása</button>
                                </div>
                            <?php endif; ?>

                            <script>
                                function addEditPollOption() {
                                    const container = document.getElementById('editPollOptionsList');
                                    const div = document.createElement('div');
                                    div.className = 'poll-option-row';
                                    div.style.marginBottom = '5px';
                                    div.style.display = 'flex';
                                    div.style.gap = '10px';
                                    div.innerHTML = `
                                        <input type="text" name="poll_options[]" placeholder="Új opció" class="n_q_input" style="width:100%; border:1px solid #cbd5e1; padding:8px;">
                                        <button type="button" onclick="this.parentElement.remove()" style="color:#ef4444; border:none; background:none; cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
                                    `;
                                    container.appendChild(div);
                                }
                            </script>
                        </div>

                        <!-- Reuse styles from input page but scope slightly if needed, or assume global CSS is fine -->
                        <style>
                            .n_q_upload_area { border: 2px dashed #cbd5e1; padding: 20px; text-align: center; cursor: pointer; background: #f8fafc; margin-bottom: 10px; }
                            .n_q_upload_area:hover { border-color: #4f46e5; background: #eff6ff; }
                            .n_q_upload_placeholder i { font-size: 2rem; color: #94a3b8; }
                            .n_q_preview_container { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 10px; }
                            .n_q_preview_item { position: relative; border: 1px solid #e2e8f0; border-radius: 4px; overflow: hidden; aspect-ratio: 1; }
                            .n_q_preview_img { width: 100%; height: 100%; object-fit: cover; }
                            .n_q_remove_btn { position: absolute; top: 5px; right: 5px; background: rgba(255,255,255,0.9); border:none; border-radius:50%; width:24px; height:24px; cursor:pointer; color:#ef4444; display:flex; align-items:center; justify-content:center; }
                        </style>

                        <script>
                            (function() {
                                const dropZone = document.getElementById('dropZoneEdit');
                                const fileInput = document.getElementById('fileInputEdit');
                                const previewContainer = document.getElementById('previewContainerEdit');
                                const hiddenInputsContainer = document.getElementById('hiddenInputsContainerEdit');
                                const MAX_IMAGES = 3;

                                // Initial count
                                let currentCount = <?php echo count($question_images); ?>;

                                dropZone.addEventListener('click', () => fileInput.click());

                                fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

                                dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.style.borderColor = '#4f46e5'; });
                                dropZone.addEventListener('dragleave', () => { dropZone.style.borderColor = '#cbd5e1'; });
                                dropZone.addEventListener('drop', (e) => {
                                    e.preventDefault();
                                    dropZone.style.borderColor = '#cbd5e1';
                                    handleFiles(e.dataTransfer.files);
                                });

                                function handleFiles(files) {
                                    if (currentCount >= MAX_IMAGES) {
                                        alert(`Maximum ${MAX_IMAGES} képet tölthetsz fel!`);
                                        return;
                                    }
                                    Array.from(files).forEach(file => {
                                        if (currentCount >= MAX_IMAGES) return;
                                        if (!file.type.startsWith('image/')) return;
                                        uploadFile(file);
                                    });
                                }

                                function uploadFile(file) {
                                    currentCount++;
                                    const item = document.createElement('div');
                                    item.className = 'n_q_preview_item';
                                    item.innerHTML = '<div style="width:100%;height:100%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-spinner fa-spin"></i></div>';
                                    previewContainer.appendChild(item);

                                    const formData = new FormData();
                                    formData.append('file', file);

                                    fetch('ajax_upload_image.php', { method: 'POST', body: formData })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success) {
                                            item.innerHTML = `<img src="${data.path}" class="n_q_preview_img"><button type="button" class="n_q_remove_btn" onclick="removeNewImage(this, '${data.path}')"><i class="fa-solid fa-xmark"></i></button>`;
                                            const input = document.createElement('input');
                                            input.type = 'hidden'; input.name = 'uploaded_images[]'; input.value = data.path;
                                            hiddenInputsContainer.appendChild(input);
                                        } else {
                                            item.remove();
                                            currentCount--;
                                            alert(data.message);
                                        }
                                    });
                                }

                                window.removeNewImage = function(btn, path) {
                                    btn.closest('.n_q_preview_item').remove();
                                    const input = hiddenInputsContainer.querySelector(`input[value="${path}"]`);
                                    if(input) input.remove();
                                    currentCount--;
                                };

                                window.markForDeletion = function(btn, id) {
                                    if(confirm('Biztosan törlöd ezt a képet a mentéskor?')) {
                                        const item = btn.closest('.n_q_preview_item');
                                        item.style.opacity = '0.3';
                                        btn.remove(); // Remove delete btn so they can't double click

                                        const input = document.createElement('input');
                                        input.type = 'hidden';
                                        input.name = 'delete_images[]';
                                        input.value = id;
                                        hiddenInputsContainer.appendChild(input);

                                        currentCount--;
                                    }
                                };
                            })();
                        </script>

                        <button type="submit" name="update_question" class="q_v_btn q_v_btn_primary" onclick="document.getElementById('editQuestionContent').value = document.getElementById('editQuestionEditor').innerHTML;">Mentés</button>
                        <a href="question.php?id=<?php echo $question_id; ?>" class="q_v_btn q_v_btn_secondary">Mégse</a>
                    </form>
                </div>
            <?php else: ?>

            <div class="q_v_q_header">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="q_v_q_meta_top">
                            <?php if ($question['status'] === 'solved'): ?>
                                <span class="q_v_badge q_v_badge_solved"><i class="fa-solid fa-check"></i> Megoldva</span>
                            <?php elseif ($question['status'] === 'closed'): ?>
                                <span class="q_v_badge q_v_badge_closed"><i class="fa-solid fa-lock"></i> Lezárva</span>
                            <?php else: ?>
                                <span class="q_v_badge q_v_badge_waiting"><i class="fa-regular fa-clock"></i> Nyitott</span>
                            <?php endif; ?>
                            <span class="q_v_q_date"><i class="fa-regular fa-clock"></i> <?php echo time_elapsed_string($question['created_at']); ?></span>
                            <span class="q_v_q_date"><i class="fa-regular fa-eye"></i> <?php echo number_format($question['view_count']); ?> <span class="q_v_view_text">megtekintés</span></span>
                        </div>
                        <h1 class="q_v_q_title"><?php echo htmlspecialchars($question['title']); ?></h1>
                    </div>
                    <?php if ($currentUser): ?>
                    <div class="flex-shrink-0 ml-auto pl-2">
                        <?php
                            $bmIconClass = $isBookmarked ? 'fa-solid fa-bookmark text-orange-400' : 'fa-regular fa-bookmark text-slate-400';
                        ?>
                        <button onclick="toggleBookmark(event, <?php echo $question_id; ?>, this)" class="w-10 h-10 rounded-full bg-white border border-slate-200 hover:bg-slate-50 flex items-center justify-center transition-colors shadow-sm" title="<?php echo $isBookmarked ? 'Könyvjelző törlése' : 'Mentés könyvjelzőnek'; ?>">
                            <i class="<?php echo $bmIconClass; ?> text-xl"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="q_v_q_body_grid">

                <div class="q_v_q_content">
                    <div class="q_v_text" id="mainQuestionText">
                        <?php echo process_content_for_display($question['content'], $currentUser); ?>

                        <?php if (!empty($question_images)): ?>
                            <div class="q_v_images_gallery">
                                <?php foreach ($question_images as $img): ?>
                                    <a href="<?php echo htmlspecialchars($img['image_path']); ?>" target="_blank" class="q_v_gallery_item" title="Kép megnyitása">
                                        <img src="<?php echo htmlspecialchars($img['image_path']); ?>" alt="Csatolt kép">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($poll): ?>
                        <div class="q_v_poll_card" id="poll-card-<?php echo $poll['id']; ?>">
                            <h3 class="q_v_poll_title"><?php echo htmlspecialchars($poll['question_text']); ?></h3>
                            <div class="q_v_poll_options">
                                <?php foreach ($poll_options as $opt): ?>
                                    <?php
                                    $percent = ($total_votes > 0) ? round(($opt['vote_count'] / $total_votes) * 100, 1) : 0;
                                    $is_voted = ($user_vote == $opt['id']);
                                    $show_result = ($user_vote || $poll['is_closed']);
                                    ?>
                                    <div class="q_v_poll_option <?php echo $show_result ? 'show-result' : ''; ?> <?php echo $is_voted ? 'voted' : ''; ?> <?php echo $poll['is_closed'] ? 'disabled' : ''; ?>" data-id="<?php echo $opt['id']; ?>" onclick="votePoll(<?php echo $poll['id']; ?>, <?php echo $opt['id']; ?>)">
                                        <div class="q_v_poll_bar" style="width: <?php echo $show_result ? $percent : 0; ?>%"></div>
                                        <div class="q_v_poll_content">
                                            <span class="q_v_poll_text"><?php echo htmlspecialchars($opt['option_text']); ?></span>
                                            <?php if ($show_result): ?>
                                                <span class="q_v_poll_percent"><?php echo $percent; ?>% (<?php echo $opt['vote_count']; ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="q_v_poll_footer">
                                <div>
                                    <?php if ($poll['is_closed']): ?>
                                        <span class="q_v_poll_status_closed"><i class="fa-solid fa-lock"></i> Lezárva</span>
                                    <?php else: ?>
                                        <?php if ($currentUser && ($currentUser['user_id'] == $question['user_id'] || !empty($currentUser['is_admin']))): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
                                                <input type="hidden" name="toggle_poll_status" value="1">
                                                <button type="submit" style="background:none; border:none; color:#64748b; cursor:pointer; font-size:0.8rem; text-decoration:underline;">Szavazás lezárása</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($poll['is_closed'] && $currentUser && ($currentUser['user_id'] == $question['user_id'] || !empty($currentUser['is_admin']))): ?>
                                        <form method="POST" style="display:inline; margin-left:10px;">
                                            <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
                                            <input type="hidden" name="toggle_poll_status" value="1">
                                            <button type="submit" style="background:none; border:none; color:#4f46e5; cursor:pointer; font-size:0.8rem; text-decoration:underline;">Újranyitás</button>
                                        </form>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <span id="poll-total-votes"><?php echo $total_votes; ?></span> szavazat
                                    <?php if (!$currentUser && !$user_vote && !$poll['is_closed']): ?>
                                        <span style="font-size:0.8rem; color:#ef4444; display:block;">(Jelentkezz be a szavazáshoz)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <style>
                            .q_v_poll_card {
                                background: #fff;
                                border: 1px solid #e2e8f0;
                                border-radius: 8px;
                                padding: 20px;
                                margin-top: 20px;
                                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
                            }
                            .q_v_poll_title {
                                font-size: 1.2rem;
                                font-weight: 700;
                                color: #1e293b;
                                margin-bottom: 15px;
                            }
                            .q_v_poll_options {
                                display: flex;
                                flex-direction: column;
                                gap: 10px;
                            }
                            .q_v_poll_option {
                                position: relative;
                                border: 1px solid #cbd5e1;
                                border-radius: 6px;
                                padding: 10px 15px;
                                cursor: pointer;
                                transition: all 0.2s;
                                overflow: hidden;
                                background: #f8fafc;
                            }
                            .q_v_poll_option:hover:not(.show-result) {
                                background: #e2e8f0;
                                border-color: #94a3b8;
                            }
                            .q_v_poll_option.show-result {
                                cursor: default;
                                border-color: transparent;
                                background: #f1f5f9;
                            }
                            .q_v_poll_option.voted {
                                border: 2px solid #4f46e5;
                            }
                            .q_v_poll_bar {
                                position: absolute;
                                top: 0; left: 0; bottom: 0;
                                background: rgba(79, 70, 229, 0.1);
                                transition: width 0.6s ease-out;
                                z-index: 1;
                            }
                            .q_v_poll_option.voted .q_v_poll_bar {
                                background: rgba(79, 70, 229, 0.2);
                            }
                            .q_v_poll_content {
                                position: relative;
                                z-index: 2;
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                            }
                            .q_v_poll_text {
                                font-weight: 600;
                                color: #334155;
                            }
                            .q_v_poll_percent {
                                font-weight: 700;
                                color: #4f46e5;
                            }
                            .q_v_poll_footer {
                                margin-top: 15px;
                                font-size: 0.85rem;
                                color: #64748b;
                                text-align: right;
                                display: flex;
                                justify-content: space-between;
                                align-items: center;
                            }
                            .q_v_poll_status_closed {
                                color: #ef4444;
                                font-weight: 700;
                                text-transform: uppercase;
                                font-size: 0.75rem;
                                letter-spacing: 0.5px;
                                display: flex;
                                align-items: center;
                                gap: 5px;
                            }
                        </style>

                        <script>
                            function votePoll(pollId, optionId) {
                                // Check if result mode or closed
                                const card = document.getElementById('poll-card-' + pollId);
                                if (card.querySelector('.show-result') || card.querySelector('.q_v_poll_option.disabled')) return;

                                fetch('ajax_vote_poll.php', {
                                    method: 'POST',
                                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                                    body: `poll_id=${pollId}&option_id=${optionId}`
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        // Update UI
                                        const options = card.querySelectorAll('.q_v_poll_option');
                                        const totalEl = document.getElementById('poll-total-votes');

                                        if (totalEl) totalEl.innerText = data.total_votes;

                                        data.stats.forEach(stat => {
                                            const optEl = card.querySelector(`.q_v_poll_option[data-id="${stat.id}"]`);
                                            if (optEl) {
                                                optEl.classList.add('show-result');
                                                if (stat.id == optionId) optEl.classList.add('voted');

                                                optEl.querySelector('.q_v_poll_bar').style.width = stat.percent + '%';

                                                let percentSpan = optEl.querySelector('.q_v_poll_percent');
                                                if (!percentSpan) {
                                                    percentSpan = document.createElement('span');
                                                    percentSpan.className = 'q_v_poll_percent';
                                                    optEl.querySelector('.q_v_poll_content').appendChild(percentSpan);
                                                }
                                                percentSpan.innerText = `${stat.percent}% (${stat.count})`;
                                            }
                                        });
                                    } else {
                                        alert(data.message);
                                    }
                                })
                                .catch(err => console.error(err));
                            }
                        </script>
                    <?php endif; ?>

                    <button class="q_v_read_more_btn" onclick="toggleReadMore()">
                        <i class="fa-solid fa-angles-down"></i> Teljes tartalom megjelenítése
                    </button>
                </div>

                <!-- User Info -->
                <div class="q_v_user_sidebar">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($question['username']); ?>&background=4f46e5&color=fff" alt="Avatar" class="q_v_user_avatar">
                    <div class="q_v_mobile_user_details">
                        <a href="profile.php?id=<?php echo $question['user_id']; ?>" class="q_v_user_name"><?php echo htmlspecialchars($question['username']); ?></a>
                        <span class="q_v_user_rank"><?php echo htmlspecialchars($question['custom_title'] ?? 'Tag'); ?></span>
                        <span class="q_v_op_badge">Kérdező (OP)</span>
                    </div>

                    <div class="q_v_user_stats">
                        <span class="q_v_stat_item"><b><?php echo $question['reputation_points']; ?></b>REP</span>
                    </div>
                </div>

            </div>

            <!-- Footer: Gombok -->
            <div class="q_v_q_footer">
                <?php if ($currentUser && $currentUser['user_id'] != $question['user_id']): ?>
                    <button class="q_v_btn q_v_btn_danger" onclick="window.location.href='tartalom_jelentese.php?type=question&id=<?php echo $question_id; ?>'"><i class="fa-solid fa-flag"></i> Jelentés</button>
                <?php endif; ?>

                <?php if ($currentUser && ($currentUser['user_id'] == $question['user_id'] || !empty($currentUser['is_admin']))): ?>
                    <?php if ($question['status'] === 'open'): ?>
                        <form method="POST" onsubmit="return confirm('Biztosan lezárod a kérdést?');" style="display:inline;">
                            <button type="submit" name="close_question" class="q_v_btn q_v_btn_secondary"><i class="fa-solid fa-lock"></i> Lezárás</button>
                        </form>
                        <a href="question.php?id=<?php echo $question_id; ?>&edit=1" class="q_v_btn q_v_btn_secondary"><i class="fa-solid fa-pen"></i> Szerkesztés</a>
                        <form method="POST" onsubmit="return confirm('Biztosan törölni szeretnéd a kérdést?');" style="display:inline;">
                            <button type="submit" name="delete_question" class="q_v_btn q_v_btn_danger"><i class="fa-solid fa-trash"></i> Törlés</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($currentUser): ?>
                    <button class="q_v_btn q_v_btn_primary" onclick="document.getElementById('replyEditor').scrollIntoView({behavior: 'smooth'})">
                        <i class="fa-solid fa-reply"></i> Válasz írása
                    </button>
                <?php else: ?>
                    <button class="q_v_btn q_v_btn_primary" onclick="document.getElementById('replyEditor').scrollIntoView({behavior: 'smooth'})">
                        <i class="fa-solid fa-reply"></i> Válasz írása
                    </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- VÁLASZOK LISTÁJA -->
        <div id="answers-ajax-wrapper">
            <?php include __DIR__ . "/answer_list_partial.php"; ?>
        </div>

        <?php if ($question['status'] === 'closed'): ?>
        <!-- LEZÁRT KÉRDÉS ÜZENET -->
        <div class="q_v_guest_alert_box" style="border-color: #94a3b8; background: #f8fafc;" id="replyEditor">
            <div class="q_v_guest_icon" style="color: #64748b;"><i class="fa-solid fa-lock"></i></div>
            <div class="q_v_guest_title" style="color: #475569;">A kérdés lezárásra került</div>
            <p class="q_v_guest_text" style="color: #64748b;">
                Ez a téma lezárult. Új válaszok már nem születhetnek, de a meglévő tudás örök!
            </p>
        </div>
        <?php elseif ($currentUser): ?>
        <!-- SZERKESZTŐ (VISUAL EDITOR) -->
        <div class="q_v_editor_section" id="replyEditor">
            <span class="q_v_editor_title">Válasz írása</span>

            <div class="q_v_toolbar">
                <button type="button" class="q_v_tool_btn" title="Félkövér" onclick="formatText('bold')"><i class="fa-solid fa-bold"></i></button>
                <button type="button" class="q_v_tool_btn" title="Dőlt" onclick="formatText('italic')"><i class="fa-solid fa-italic"></i></button>
                <button type="button" class="q_v_tool_btn" title="Aláhúzott" onclick="formatText('underline')"><i class="fa-solid fa-underline"></i></button>
                <div style="width: 1px; background: #cbd5e1; margin: 0 5px;"></div>
                <button type="button" class="q_v_tool_btn" title="Lista" onclick="formatText('insertUnorderedList')"><i class="fa-solid fa-list-ul"></i></button>
                <button type="button" class="q_v_tool_btn" title="Idézet" onclick="formatText('formatBlock', 'blockquote')"><i class="fa-solid fa-quote-right"></i></button>
                <div style="width: 1px; background: #cbd5e1; margin: 0 5px;"></div>
                <button type="button" class="q_v_tool_btn" title="Kép beszúrása" onclick="insertMedia('image')"><i class="fa-regular fa-image"></i></button>
                <button type="button" class="q_v_tool_btn" title="YouTube Videó" onclick="insertMedia('youtube')"><i class="fa-brands fa-youtube"></i></button>
                <button type="button" class="q_v_tool_btn" title="Emoji" onclick="toggleEmojiPicker()"><i class="fa-regular fa-face-smile"></i></button>

                <!-- Emoji Picker Panel -->
                <div class="q_v_emoji_picker" id="emojiPicker">
                    <span class="q_v_emoji_btn" onclick="insertEmoji('😀')">😀</span>
                    <span class="q_v_emoji_btn" onclick="insertEmoji('😂')">😂</span>
                    <span class="q_v_emoji_btn" onclick="insertEmoji('😍')">😍</span>
                    <span class="q_v_emoji_btn" onclick="insertEmoji('👍')">👍</span>
                    <span class="q_v_emoji_btn" onclick="insertEmoji('👎')">👎</span>
                    <span class="q_v_emoji_btn" onclick="insertEmoji('🔥')">🔥</span>
                    <span class="q_v_emoji_btn" onclick="insertEmoji('🎉')">🎉</span>
                    <span class="q_v_emoji_btn" onclick="insertEmoji('😎')">😎</span>
                    <span class="q_v_emoji_btn" onclick="insertEmoji('🤔')">🤔</span>
                    <span class="q_v_emoji_btn" onclick="insertEmoji('😢')">😢</span>
                    <span class="q_v_emoji_btn" onclick="insertEmoji('😡')">😡</span>
                    <span class="q_v_emoji_btn" onclick="insertEmoji('✅')">✅</span>
                </div>
            </div>

            <!-- VIZUÁLIS SZERKESZTŐ (WYSIWYG) -->
            <form method="POST">
                <div class="q_v_editor_content" id="editorContent" contenteditable="true" placeholder="Írd ide a válaszod..."></div>
                <input type="hidden" name="content" id="hiddenAnswerContent">

                <div class="q_v_editor_footer">
                    <!-- Image Upload Section -->
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="file" id="answerFileInput" accept="image/*" style="display:none;">
                        <input type="hidden" name="answer_image" id="hiddenAnswerImage">

                        <?php if (has_storage_space($currentUser)): ?>
                        <button type="button" class="q_v_btn q_v_btn_secondary" id="answerUploadBtn" title="Kép feltöltése">
                            <i class="fa-regular fa-image"></i> <span class="q_v_btn_text_responsive">Kép csatolása</span>
                        </button>
                        <?php else: ?>
                        <span class="text-xs font-bold text-red-500 bg-red-100 px-2 py-1 rounded border border-red-200" title="A tárhelyed megtelt!"><i class="fas fa-exclamation-triangle"></i> Tárhely megtelt!</span>
                        <?php endif; ?>

                        <div id="answerImagePreview" style="display:none; align-items:center; gap:5px; font-size:0.85rem; color:#475569; background:#f1f5f9; padding:4px 8px; border-radius:4px;">
                            <i class="fa-solid fa-check" style="color:#10b981;"></i>
                            <span id="answerImageName" style="max-width:100px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block; vertical-align:middle;"></span>
                            <button type="button" id="removeAnswerImageBtn" style="border:none; background:none; color:#ef4444; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; gap:15px;">
                        <span class="q_v_char_count" id="charCount">
                            <span id="charCountNum">0 / 2500</span> <span class="q_v_char_text">karakter</span>
                        </span>
                        <button type="submit" name="submit_answer" class="q_v_btn q_v_btn_primary" onclick="document.getElementById('hiddenAnswerContent').value = document.getElementById('editorContent').innerHTML;">
                            <span class="q_v_submit_text_desktop">Válasz elküldése</span>
                            <span class="q_v_submit_text_mobile">Válasz</span>
                        </button>
                    </div>
                </div>

                <style>
                    .q_v_submit_text_mobile { display: none; }
                    .q_v_submit_text_desktop { display: inline; }

                    @media (max-width: 600px) {
                        .q_v_submit_text_desktop { display: none; }
                        .q_v_submit_text_mobile { display: inline; }
                        .q_v_btn_text_responsive { display: none; } /* Hide text on mobile for upload btn */
                        .q_v_editor_footer { padding: 10px; }
                        .q_v_char_text { display: none; } /* Hide 'karakter' text on mobile */
                        .q_v_char_count { font-size: 0.75rem; white-space: nowrap; } /* Slightly smaller on mobile, prevent wrapping */
                    }
                </style>
            </form>
        </div>
        <?php else: ?>
        <!-- VENDÉG NÉZET ALERT -->
        <div class="q_v_guest_alert_box" id="replyEditor">
            <div class="q_v_guest_icon"><i class="fa-solid fa-lock"></i></div>
            <div class="q_v_guest_title">Jelentkezz be a válaszadáshoz!</div>
            <p class="q_v_guest_text">Ahhoz, hogy részt vegyél a beszélgetésben, választ írhass vagy értékelhesd a hozzászólásokat, kérjük jelentkezz be vagy regisztrálj. A regisztráció ingyenes és csak egy percet vesz igénybe!</p>
            <div class="q_v_guest_actions">
                <button class="q_v_btn q_v_btn_primary">Bejelentkezés</button>
                <button class="q_v_btn q_v_btn_secondary">Regisztráció</button>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const textBox = document.getElementById('mainQuestionText');
            const btn = document.querySelector('.q_v_read_more_btn');

            if (textBox && textBox.scrollHeight > 250) {
                textBox.classList.add('truncated');
                btn.style.display = 'block';
            }

            window.toggleReadMore = function() {
                if (textBox.classList.contains('truncated')) {
                    textBox.classList.remove('truncated');
                    btn.innerHTML = '<i class="fa-solid fa-angles-up"></i> Kevesebb mutatása';
                } else {
                    textBox.classList.add('truncated');
                    btn.innerHTML = '<i class="fa-solid fa-angles-down"></i> Teljes tartalom megjelenítése';
                    textBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            };
        });

        /* --- ANSWER IMAGE UPLOAD LOGIC --- */
        (function() {
            const uploadBtn = document.getElementById('answerUploadBtn');
            const fileInput = document.getElementById('answerFileInput');
            const previewBox = document.getElementById('answerImagePreview');
            const nameSpan = document.getElementById('answerImageName');
            const removeBtn = document.getElementById('removeAnswerImageBtn');
            const hiddenInput = document.getElementById('hiddenAnswerImage');

            if (uploadBtn && fileInput) {
                uploadBtn.addEventListener('click', () => fileInput.click());

                fileInput.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (!file) return;

                    if (!file.type.startsWith('image/')) {
                        alert('Csak képfájlokat tölthetsz fel!');
                        return;
                    }

                    // Show loading state
                    const originalContent = uploadBtn.innerHTML;
                    uploadBtn.disabled = true;
                    uploadBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Feltöltés...';

                    const formData = new FormData();
                    formData.append('file', file);

                    fetch('ajax_upload_image.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        uploadBtn.disabled = false;
                        uploadBtn.innerHTML = originalContent;

                        if (data.success) {
                            // Success
                            hiddenInput.value = data.path;
                            nameSpan.textContent = data.original_name || 'Csatolt kép';
                            nameSpan.title = data.original_name; // tooltip

                            // Swap visibility
                            uploadBtn.style.display = 'none';
                            previewBox.style.display = 'flex';
                        } else {
                            alert(data.message || 'Hiba történt.');
                        }
                    })
                    .catch(err => {
                        uploadBtn.disabled = false;
                        uploadBtn.innerHTML = originalContent;
                        console.error(err);
                        alert('Hálózati hiba.');
                    });

                    // Reset input so same file can be selected again if removed
                    fileInput.value = '';
                });

                if (removeBtn) {
                    removeBtn.addEventListener('click', () => {
                        hiddenInput.value = '';
                        previewBox.style.display = 'none';
                        uploadBtn.style.display = 'inline-flex';
                    });
                }
            }
        })();

        /* --- VISUAL EDITOR LOGIKA --- */
        const editor = document.getElementById('editorContent');
        if (editor) {
            const countDisplay = document.getElementById('charCount');
            const maxChars = 2500;

            let lastRange = null;

            function saveSelection() {
                const sel = window.getSelection();
                if (sel.getRangeAt && sel.rangeCount) {
                    lastRange = sel.getRangeAt(0);
                }
            }

            editor.addEventListener('keyup', saveSelection);
            editor.addEventListener('mouseup', saveSelection);
            editor.addEventListener('focus', saveSelection);

            editor.addEventListener('input', function() {
                const currentLength = this.innerText.length;
                const numDisplay = document.getElementById('charCountNum');
                if (numDisplay) {
                    numDisplay.textContent = `${currentLength} / ${maxChars}`;
                } else {
                    // Fallback
                    countDisplay.textContent = `${currentLength} / ${maxChars} karakter`;
                }

                if (currentLength >= maxChars) {
                    countDisplay.classList.add('limit-reached');
                } else if (currentLength > maxChars * 0.9) {
                    countDisplay.classList.add('limit-near');
                    countDisplay.classList.remove('limit-reached');
                } else {
                    countDisplay.classList.remove('limit-near', 'limit-reached');
                }
            });
        }

        function formatText(command, value = null) {
            document.execCommand(command, false, value);
            if (editor) editor.focus();
        }

        function insertMedia(type) {
            let url;
            if (type === 'image') {
                url = prompt("Kérlek add meg a kép URL címét:");
                if (url) {
                    const html = `<img src="${url}" alt="beillesztett kép" style="max-width: 100%; height: auto; border-radius: 4px;">`;
                    formatText('insertHTML', html);
                }
            } else if (type === 'youtube') {
                url = prompt("Kérlek add meg a YouTube videó URL címét vagy ID-ját:");
                if (url) {
                    let videoId = url.split('v=')[1];
                    if (!videoId) videoId = url.split('/').pop();
                    const embedUrl = `https://www.youtube.com/embed/${videoId}`;

                    const html = `<div class="q_v_video_container"><iframe src="${embedUrl}" allowfullscreen></iframe></div><br>`;
                    formatText('insertHTML', html);
                }
            }
        }

        function toggleEmojiPicker() {
            const picker = document.getElementById('emojiPicker');
            picker.classList.toggle('active');
        }

        function insertEmoji(emoji) {
            if (editor) editor.focus();
            /* Re-implement simple range restore if needed, or just insert */
            document.execCommand('insertText', false, emoji);
        }

        document.addEventListener('click', function(e) {
            const picker = document.getElementById('emojiPicker');
            const btn = document.querySelector('.q_v_tool_btn[title="Emoji"]');
            if (picker && btn && !picker.contains(e.target) && !btn.contains(e.target)) {
                picker.classList.remove('active');
            }
        });

        function toggleBookmark(event, questionId, btn) {
            event.stopPropagation(); // Ne nyíljon le a kártya (ha lenne ilyen event)

            // Visual toggle immediately (optimistic)
            const icon = btn.querySelector('i');
            // Disable button temporarily
            btn.disabled = true;

            const formData = new FormData();
            formData.append('question_id', questionId);

            fetch('ajax_bookmark.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                if (data.success) {
                    if (data.status === 'added') {
                        icon.className = 'fa-solid fa-bookmark text-orange-400 text-xl';
                        btn.title = 'Könyvjelző törlése';
                        // Animation
                        icon.style.transform = 'scale(1.2)';
                        setTimeout(() => icon.style.transform = 'scale(1)', 200);
                    } else {
                        icon.className = 'fa-regular fa-bookmark text-slate-400 text-xl';
                        btn.title = 'Mentés könyvjelzőnek';
                    }
                } else {
                    alert('Hiba: ' + (data.message || 'Ismeretlen hiba'));
                }
            })
            .catch(err => {
                btn.disabled = false;
                console.error(err);
            });
        }

    </script>

                <!-- SEO: ARTICLE BODY - Itt a tartalom -->
                <div>
                </div>
		</div>
            <aside class="cikk-fo-col-right">
                <style>
                /* Latest Questions Widget Styling */
                .lq-widget {
                    background: #ffffff;
                    border-radius: 12px;
                    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                    overflow: hidden;
                    margin-bottom: 2rem;
                    border: 1px solid #e2e8f0;
                    font-family: 'Inter', sans-serif;
                }

                .lq-widget-header {
                    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
                    padding: 1rem 1.5rem;
                    border-bottom: 1px solid #334155;
                }

                .lq-widget-title {
                    color: #f8fafc;
                    font-size: 1.1rem;
                    font-weight: 700;
                    margin: 0;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    letter-spacing: 0.025em;
                }

                .lq-widget-title i {
                    color: #38bdf8;
                }

                .lq-list {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }

                .lq-item {
                    display: block;
                    padding: 1rem 1.5rem;
                    border-bottom: 1px solid #f1f5f9;
                    transition: all 0.2s ease;
                    text-decoration: none;
                    position: relative;
                }

                .lq-item:last-child {
                    border-bottom: none;
                }

                .lq-item:hover {
                    background-color: #f8fafc;
                    transform: translateX(4px);
                }

                .lq-item:hover .lq-item-title {
                    color: #4f46e5;
                }

                .lq-item-title {
                    font-size: 0.95rem;
                    font-weight: 600;
                    color: #334155;
                    margin-bottom: 0.5rem;
                    line-height: 1.4;
                    transition: color 0.2s ease;
                    display: -webkit-box;
                    -webkit-line-clamp: 2;
                    -webkit-box-orient: vertical;
                    overflow: hidden;
                }

                .lq-meta {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    font-size: 0.75rem;
                    color: #94a3b8;
                }

                .lq-meta-item {
                    display: flex;
                    align-items: center;
                    gap: 0.25rem;
                }

                .lq-user-avatar {
                    width: 20px;
                    height: 20px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 1px solid #e2e8f0;
                }

                /* Responsive adjustments */
                @media (max-width: 768px) {
                    .lq-widget {
                        margin-top: 2rem;
                    }
                }
                </style>

                <div class="lq-widget">
                    <div class="lq-widget-header">
                        <h3 class="lq-widget-title">
                            <i class="fa-solid fa-bolt"></i> Legfrissebb kérdések
                        </h3>
                    </div>
                    <div class="lq-list">
                        <?php if (!empty($latest_questions)): ?>
                            <?php foreach ($latest_questions as $lq): ?>
                                <a href="question.php?id=<?php echo $lq['id']; ?>" class="lq-item">
                                    <div class="lq-item-title"><?php echo htmlspecialchars($lq['title']); ?></div>
                                    <div class="lq-meta">
                                        <div class="lq-meta-item">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($lq['username']); ?>&background=random&size=32" alt="<?php echo htmlspecialchars($lq['username']); ?>" class="lq-user-avatar">
                                            <span><?php echo htmlspecialchars($lq['username']); ?></span>
                                        </div>
                                        <div class="lq-meta-item">
                                            <i class="fa-regular fa-clock"></i>
                                            <span><?php echo time_elapsed_string($lq['created_at']); ?></span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="lq-item" style="text-align:center; color:#94a3b8;">
                                <i class="fa-regular fa-folder-open" style="font-size:2rem; margin-bottom:10px; display:block;"></i>
                                Nincs megjeleníthető kérdés.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </aside>

        </div>
    </div>

    <!-- Alsó elválasztó és ajánló helye -->
    <hr class="elvalaszto_also_">
    <div class="w-full bg-[#1e1b4b] rounded-none shadow-2xl overflow-hidden border border-white/5">
        <!-- Widget area -->
    </div>

</main>

    <!-- Top Up Button -->
    <div id="ugras_top_wrapper" class="ugras_top_button_wrapper">
        <button id="ugras_top_btn" class="ugras_top_btn" title="Vissza az oldal tetejére">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="19" x2="12" y2="5"></line>
                <polyline points="5 12 12 5 19 12"></polyline>
            </svg>
        </button>
    </div>

</main>

<!-- Footer -->
<footer class="footer_alul_wrapper">
    <div class="gradient-border-bottom"></div>
    <div class="header-content-wrapper">
        <div class="footer_alul_grid">
            <div class="footer_alul_column">
                <div class="footer_alul_heading">Rólunk</div>
                <div class="logo-font text-2xl font-bold mb-4 text-white">
                    SILVER<span style="color: var(--win98-teal);">PC</span><span style="color: var(--cyber-teal); font-size: 0.6em;">.HU</span>
                </div>
            </div>
            <div class="footer_alul_column">
                <div class="footer_alul_heading">Információk</div>
                <ul class="footer_alul_links">
                    <li><a href="szabalyzat.php" class="footer_alul_link_item"><i class="fas fa-book"></i> Szabályzat</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer_alul_copyright_section">
        <div class="header-content-wrapper">
             <p class="footer_alul_legal_text" style="text-align: center; margin-top: 10px;">
                <a href="szabalyzat.php" style="color: #94a3b8; text-decoration: none;">
                    <i class="fa-solid fa-scale-balanced" style="margin-right: 5px;"></i> Kérjük, a közösség védelme érdekében <span style="text-decoration: underline;">olvasd el a szabályzatot</span>!
                </a>
            </p>
        </div>
    </div>
</footer>

<script src="https://blog.silverpc.hu/v2/assets/js/share.js"></script>
<script src="https://blog.silverpc.hu/v2/assets/js/ol.js"></script>
<script src="https://blog.silverpc.hu/v2/assets/js/top-up-btn.js"></script>
<script src="https://blog.silverpc.hu/v2/assets/js/copy-btn.js"></script>
<script src="https://blog.silverpc.hu/v2/assets/js/galeria.js"></script>
<script src="https://blog.silverpc.hu/v2/assets/js/search-effect.js"></script>
<script>
    function loadAnswerHistory(id) {
        const main = document.getElementById('answer-main-' + id);
        const hist = document.getElementById('answer-history-' + id);

        if (!main || !hist) return;

        // Hide main, show hist
        main.classList.add('hidden'); // Tailwind hidden
        // Usually q_v_answer_main has display: block (or flex in CSS).
        // Adding 'hidden' class (display: none) should override if it has higher specificity or !important.
        // Tailwind 'hidden' is just display: none.
        // To be safe, let's use style.
        main.style.display = 'none';

        hist.classList.remove('hidden');
        hist.style.display = 'block'; // Or whatever layout it needs

        // Check if loaded
        if (hist.innerHTML.trim() === '') {
            hist.innerHTML = '<div class="text-center p-5 text-gray-500"><i class="fa-solid fa-spinner fa-spin text-2xl"></i><br>Előzmények betöltése...</div>';

            fetch('ajax_get_answer_history.php?answer_id=' + id)
            .then(res => res.text())
            .then(html => {
                hist.innerHTML = html;
            })
            .catch(err => {
                hist.innerHTML = '<div class="text-red-500">Hiba történt a betöltéskor.</div>';
                console.error(err);
            });
        }
    }

    function closeAnswerHistory(id) {
        const main = document.getElementById('answer-main-' + id);
        const hist = document.getElementById('answer-history-' + id);

        if (!main || !hist) return;

        hist.classList.add('hidden');
        hist.style.display = 'none';

        main.classList.remove('hidden');
        main.style.display = ''; // Restore original
    }

    // --- REPLIES LOGIC ---

    function loadAnswerReplies(id, page = 1) {
        const main = document.getElementById('answer-main-' + id);
        const replies = document.getElementById('answer-replies-' + id);
        const voteBox = document.getElementById('vote-box-' + id);

        if (!main || !replies) return Promise.resolve();

        // Hide main and vote box, show replies
        main.classList.add('hidden');
        main.style.display = 'none';

        if (voteBox) {
            voteBox.classList.add('hidden');
            voteBox.style.display = 'none';
        }

        replies.classList.remove('hidden');
        replies.style.display = 'block';

        // Show spinner if empty or paging
        if (replies.innerHTML.trim() === '' || page > 1) {
             replies.innerHTML = '<div class="text-center p-5 text-gray-500"><i class="fa-solid fa-spinner fa-spin text-2xl"></i><br>Válaszok betöltése...</div>';
        }

        return fetch('ajax_get_answer_replies.php?answer_id=' + id + '&page=' + page)
        .then(res => res.text())
        .then(html => {
            replies.innerHTML = html;
        })
        .catch(err => {
            replies.innerHTML = '<div class="text-red-500">Hiba történt a betöltéskor.</div>';
            console.error(err);
        });
    }

    function closeAnswerReplies(id) {
        const main = document.getElementById('answer-main-' + id);
        const replies = document.getElementById('answer-replies-' + id);
        const voteBox = document.getElementById('vote-box-' + id);

        if (!main || !replies) return;

        replies.classList.add('hidden');
        replies.style.display = 'none';

        main.classList.remove('hidden');
        main.style.display = ''; // Restore original

        if (voteBox) {
            voteBox.classList.remove('hidden');
            voteBox.style.display = ''; // Restore
        }
    }

    function changeReplyPage(id, page) {
        loadAnswerReplies(id, page);
    }

    function submitReply(event, id) {
        event.preventDefault();
        const content = document.getElementById('reply-content-' + id).value;

        if (!content.trim()) return;

        const formData = new FormData();
        formData.append('answer_id', id);
        formData.append('content', content);

        fetch('ajax_post_answer_reply.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Reload replies to show new one
                loadAnswerReplies(id, 1);
            } else {
                alert(data.message || 'Hiba történt.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Hálózati hiba.');
        });
    }

    // AJAX VOTING & ACCEPTING LOGIC
    document.addEventListener("DOMContentLoaded", function() {

        // --- VOTING ---
        document.querySelectorAll('.js-vote-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const type = this.dataset.type;
                const id = this.dataset.id;
                const dir = this.dataset.dir;
                const scoreEl = document.getElementById(`vote-score-${type}-${id}`);

                // Simple Optimistic UI (Prevent spam click)
                if(this.disabled) return;
                this.disabled = true;

                fetch('ajax_vote.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `type=${type}&id=${id}&direction=${dir}`
                })
                .then(res => res.json())
                .then(data => {
                    this.disabled = false;
                    if(data.success) {
                        if(scoreEl) {
                            // Update Score
                            const currentScore = parseInt(scoreEl.innerText);
                            const newScore = parseInt(data.new_score);

                            // Effect: Blink color
                            scoreEl.style.transition = 'color 0.3s, transform 0.2s';
                            scoreEl.style.transform = 'scale(1.3)';
                            setTimeout(() => scoreEl.style.transform = 'scale(1)', 200);

                            scoreEl.innerText = newScore;

                            if(newScore < 0) scoreEl.classList.add('text-red-500');
                            else scoreEl.classList.remove('text-red-500');

                            // Reorder if needed
                            const sortSelect = document.querySelector('.q_v_sort_select');
                            if(sortSelect && sortSelect.value === 'best') {
                                setTimeout(reorderAnswers, 600); // Wait for animation
                            }
                        }
                    } else {
                        alert(data.message || 'Hiba történt.');
                    }
                })
                .catch(err => {
                    this.disabled = false;
                    console.error(err);
                });
            });
        });

        // --- SORTING FUNCTION ---
        function reorderAnswers() {
            const container = document.querySelector('.q_v_answers_section');
            if(!container) return;

            const header = container.querySelector('.q_v_answers_header');
            const cards = Array.from(container.querySelectorAll('.q_v_answer_card'));

            cards.sort((a, b) => {
                // Solution first
                const aSol = a.classList.contains('q_v_answer_solution');
                const bSol = b.classList.contains('q_v_answer_solution');
                if (aSol && !bSol) return -1;
                if (!aSol && bSol) return 1;

                // Score Desc
                const aScore = parseInt(a.querySelector('.q_v_vote_count').innerText);
                const bScore = parseInt(b.querySelector('.q_v_vote_count').innerText);
                if (aScore !== bScore) return bScore - aScore;

                // Date Asc (via ID as proxy for creation time)
                const aId = parseInt(a.dataset.id || a.id.replace('answer-card-', ''));
                const bId = parseInt(b.dataset.id || b.id.replace('answer-card-', ''));
                return aId - bId;
            });

            // Re-append with animation
            cards.forEach(card => {
                container.appendChild(card);
                card.style.animation = 'none';
                card.offsetHeight; /* trigger reflow */
                card.style.animation = 'fadeIn 0.5s';
            });
        }

        // --- ACCEPT ANSWER ---
        document.querySelectorAll('.js-accept-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const id = this.dataset.id;

                fetch('ajax_accept_answer.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `answer_id=${id}`
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Hiba történt.');
                    }
                })
                .catch(err => console.error(err));
            });
        });

        function updateSolutionUI(id, status) {
            const container = document.querySelector('.q_v_answers_section');
            const header = container.querySelector('.q_v_answers_header');
            const allCards = document.querySelectorAll('.q_v_answer_card');

            // Reset all
            allCards.forEach(card => {
                card.classList.remove('q_v_answer_solution');
                const banner = card.querySelector('.q_v_solution_banner');
                if(banner) banner.remove();

                const btn = card.querySelector('.js-accept-btn');
                if(btn) {
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Megoldásnak jelölöm';
                    btn.style.borderColor = '';
                    btn.style.color = '';

                    // Reset vote count color
                    const scoreEl = card.querySelector('.q_v_vote_count');
                    if(scoreEl) scoreEl.style.color = '';
                    if(scoreEl && parseInt(scoreEl.innerText) < 0) scoreEl.classList.add('text-red-500');
                }
            });

            if (status === 'accepted') {
                const card = document.getElementById(`answer-card-${id}`);
                if(card) {
                    card.classList.add('q_v_answer_solution');

                    // Banner
                    const banner = document.createElement('div');
                    banner.className = 'q_v_solution_banner';
                    banner.innerHTML = '<i class="fa-solid fa-check"></i> Megoldás';
                    card.prepend(banner);

                    // Button update
                    const btn = card.querySelector('.js-accept-btn');
                    if(btn) {
                        btn.innerHTML = '<i class="fa-solid fa-check"></i> Megoldás visszavonása';
                        btn.style.borderColor = '#10b981';
                        btn.style.color = '#10b981';
                    }

                    // Score color update
                    const scoreEl = card.querySelector('.q_v_vote_count');
                    if(scoreEl) {
                        scoreEl.style.color = '#10b981';
                        scoreEl.classList.remove('text-red-500'); // Green overrides red usually
                    }

                    // Move to top (after header)
                    // Add fade out/in effect
                    card.style.transition = 'opacity 0.3s';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        if(header && header.nextSibling) {
                            container.insertBefore(card, header.nextSibling);
                        } else {
                            container.appendChild(card);
                        }
                        card.style.opacity = '1';
                        card.scrollIntoView({behavior: 'smooth', block: 'center'});
                    }, 300);
                }
            }
        }

        // Add fade animation style dynamically
        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
            .q_v_answer_card { transition: all 0.3s ease; }
        `;
        document.head.appendChild(style);
    });
</script>
<style>
/* Custom Dropdown Styles */
.q_v_dropdown { position: relative; display: inline-block; }
.q_v_dd_btn { padding: 8px 12px; background: white; border: 1px solid #cbd5e1; border-radius: 4px; cursor: pointer; color: #475569; display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 500; transition: all 0.2s; }
.q_v_dd_btn:hover { background: #f1f5f9; border-color: #94a3b8; color: #1e293b; }
.q_v_dd_menu { display: none; position: absolute; top: 100%; right: 0; background: white; border: 1px solid #e2e8f0; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); border-radius: 6px; min-width: 160px; z-index: 50; margin-top: 5px; overflow: hidden; animation: fadeIn 0.2s ease-out; }
.q_v_dd_menu.show { display: block; }
.q_v_dd_menu a { display: block; padding: 10px 16px; text-decoration: none; color: #334155; font-size: 0.9rem; transition: background 0.2s; white-space: nowrap; }
.q_v_dd_menu a:hover { background: #f8fafc; color: #0f172a; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 768px) {
    .q_v_answers_header_modern { flex-direction: row; align-items: center; justify-content: space-between; gap: 10px; padding: 0 10px; }
    .q_v_controls_modern { width: auto; justify-content: flex-end; gap: 5px; }
    .q_v_dropdown { width: auto; }
    .q_v_dd_btn { width: auto; justify-content: center; padding: 8px; }
    .q_v_btn_text { display: none; }
    .q_v_dd_menu { width: auto; left: auto; right: 0; min-width: 150px; }
}
</style>

<script>
    // AJAX Answers Logic
    let currentLimit = <?php echo $limit; ?>;
    let currentSort = '<?php echo $sort; ?>';
    let currentPage = <?php echo $page; ?>;
    const questionId = <?php echo $question_id; ?>;

    function toggleDropdown(id, event) {
        if(event) event.stopPropagation();

        // Close others
        document.querySelectorAll('.q_v_dd_menu').forEach(el => {
            if (el.id !== id) el.style.display = 'none';
        });

        const dd = document.getElementById(id);
        if (dd.style.display === 'block') {
            dd.style.display = 'none';
        } else {
            dd.style.display = 'block';
        }
    }

    // Close dropdowns on click outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.q_v_dropdown')) {
            document.querySelectorAll('.q_v_dd_menu').forEach(el => {
                el.style.display = 'none';
            });
        }
    });

    function changeLimit(newLimit, event) {
        if(event) event.preventDefault();
        if (currentLimit === newLimit) return;
        currentLimit = newLimit;
        currentPage = 1;
        loadAnswers();
        document.querySelectorAll('.q_v_dd_menu').forEach(el => el.style.display = 'none');
    }

    function changeSort(newSort, event) {
        if(event) event.preventDefault();
        if (currentSort === newSort) return;
        currentSort = newSort;
        currentPage = 1;
        loadAnswers();
        document.querySelectorAll('.q_v_dd_menu').forEach(el => el.style.display = 'none');
    }

    function changePage(newPage, event) {
        if(event) event.preventDefault();
        if (currentPage === newPage) return;
        currentPage = newPage;
        loadAnswers(true); // true = scroll
    }

    function loadAnswers(scrollToTop = false) {
        const wrapper = document.getElementById('answers-ajax-wrapper');
        wrapper.style.opacity = '0.5';

        const url = `question.php?id=${questionId}&ajax_answers=1&limit=${currentLimit}&sort=${currentSort}&page=${currentPage}`;

        // Update URL (PushState)
        const displayUrl = `question.php?id=${questionId}&limit=${currentLimit}&sort=${currentSort}&page=${currentPage}`;
        window.history.pushState({path: displayUrl}, '', displayUrl);

        fetch(url)
        .then(response => response.text())
        .then(html => {
            wrapper.innerHTML = html;
            wrapper.style.opacity = '1';

            if(scrollToTop) {
                wrapper.scrollIntoView({behavior: 'smooth'});
            }

            if (typeof Prism !== 'undefined') {
                Prism.highlightAll();
            }
            // Re-bind specific listeners if needed (like vote buttons?)
            // Vote buttons use document delegation (checked earlier), so no need to rebind.
        })
        .catch(err => {
            console.error('Error loading answers:', err);
            wrapper.style.opacity = '1';
            alert('Hiba történt a válaszok betöltésekor.');
        });
    }

    // --- REPLY VOTING ---
    function voteReply(replyId, type, btn) {
        if(btn.disabled) return;
        btn.disabled = true;

        // API Call
        const formData = new FormData();
        formData.append('reply_id', replyId);
        formData.append('type', type);

        fetch('ajax_reply_vote.php', {
            method: 'POST',
            body: formData
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Hálózati hiba: ' + res.status);
            }
            return res.text();
        })
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error("Invalid JSON:", text);
                throw new Error('Szerver hiba (érvénytelen válasz).');
            }

            btn.disabled = false;
            if(data.success) {
                // Visual Effect only on success
                createExplosion(btn, type);

                // Select specific elements by ID
                const likeBtn = document.getElementById('reply-vote-btn-like-' + replyId);
                const dislikeBtn = document.getElementById('reply-vote-btn-dislike-' + replyId);
                const likeCountSpan = document.getElementById('reply-vote-count-like-' + replyId);
                const dislikeCountSpan = document.getElementById('reply-vote-count-dislike-' + replyId);

                if (!likeBtn || !dislikeBtn || !likeCountSpan || !dislikeCountSpan) {
                     console.error("Missing DOM elements for reply vote update", {replyId});
                     return;
                }

                const likeIcon = likeBtn.querySelector('i');
                const dislikeIcon = dislikeBtn.querySelector('i');

                // Update Counts
                likeCountSpan.innerText = data.likes;
                dislikeCountSpan.innerText = data.dislikes;

                // Update Styles based on User Vote State
                const likes = parseInt(data.likes);
                const dislikes = parseInt(data.dislikes);
                const userVote = data.user_vote; // 'like', 'dislike', or null

                // Helper to set classes
                const setClasses = (el, add, remove) => {
                    remove.split(' ').forEach(c => el.classList.remove(c));
                    add.split(' ').forEach(c => el.classList.add(c));
                };

                // Styles Definitions
                const STYLES = {
                    like: {
                        activeBtn: 'bg-green-600 text-white shadow-lg scale-105 ring-2 ring-offset-1 ring-green-600 hover:bg-green-700',
                        activeIcon: 'text-white',
                        activeCount: 'bg-white/20 text-white border border-white/30',
                        passiveBtn: 'text-green-600 bg-green-50',
                        passiveIcon: 'text-green-600',
                        passiveCount: 'bg-green-100 text-green-700',
                        defaultBtn: 'text-gray-500 hover:bg-green-50 hover:text-green-600',
                        defaultIcon: '',
                        defaultCount: 'bg-gray-100 text-gray-600 group-hover:bg-green-100 group-hover:text-green-700'
                    },
                    dislike: {
                        activeBtn: 'bg-red-600 text-white shadow-lg scale-105 ring-2 ring-offset-1 ring-red-600 hover:bg-red-700',
                        activeIcon: 'text-white',
                        activeCount: 'bg-white/20 text-white border border-white/30',
                        passiveBtn: 'text-red-600 bg-red-50',
                        passiveIcon: 'text-red-600',
                        passiveCount: 'bg-red-100 text-red-700',
                        defaultBtn: 'text-gray-500 hover:bg-red-50 hover:text-red-600',
                        defaultIcon: '',
                        defaultCount: 'bg-gray-100 text-gray-600 group-hover:bg-red-100 group-hover:text-red-700'
                    }
                };

                // 1. UPDATE LIKE BUTTON
                // Remove all possible classes first to clean slate
                const allLikeClasses = [STYLES.like.activeBtn, STYLES.like.passiveBtn, STYLES.like.defaultBtn].join(' ');
                const allLikeCountClasses = [STYLES.like.activeCount, STYLES.like.passiveCount, STYLES.like.defaultCount].join(' ');

                if (userVote === 'like') {
                    // My Active Like
                    setClasses(likeBtn, STYLES.like.activeBtn, allLikeClasses);
                    setClasses(likeCountSpan, STYLES.like.activeCount, allLikeCountClasses);
                    if(likeIcon) {
                        likeIcon.classList.remove('text-green-600');
                        likeIcon.classList.add('text-white');
                    }
                } else if (likes > 0) {
                    // Has Likes
                    setClasses(likeBtn, STYLES.like.passiveBtn, allLikeClasses);
                    setClasses(likeCountSpan, STYLES.like.passiveCount, allLikeCountClasses);
                    if(likeIcon) {
                        likeIcon.classList.remove('text-white');
                        likeIcon.classList.add('text-green-600');
                    }
                } else {
                    // Default
                    setClasses(likeBtn, STYLES.like.defaultBtn, allLikeClasses);
                    setClasses(likeCountSpan, STYLES.like.defaultCount, allLikeCountClasses);
                    if(likeIcon) {
                        likeIcon.classList.remove('text-white', 'text-green-600');
                    }
                }

                // 2. UPDATE DISLIKE BUTTON
                const allDislikeClasses = [STYLES.dislike.activeBtn, STYLES.dislike.passiveBtn, STYLES.dislike.defaultBtn].join(' ');
                const allDislikeCountClasses = [STYLES.dislike.activeCount, STYLES.dislike.passiveCount, STYLES.dislike.defaultCount].join(' ');

                if (userVote === 'dislike') {
                    // My Active Dislike
                    setClasses(dislikeBtn, STYLES.dislike.activeBtn, allDislikeClasses);
                    setClasses(dislikeCountSpan, STYLES.dislike.activeCount, allDislikeCountClasses);
                    if(dislikeIcon) {
                        dislikeIcon.classList.remove('text-red-600');
                        dislikeIcon.classList.add('text-white');
                    }
                } else if (dislikes > 0) {
                    // Has Dislikes
                    setClasses(dislikeBtn, STYLES.dislike.passiveBtn, allDislikeClasses);
                    setClasses(dislikeCountSpan, STYLES.dislike.passiveCount, allDislikeCountClasses);
                    if(dislikeIcon) {
                        dislikeIcon.classList.remove('text-white');
                        dislikeIcon.classList.add('text-red-600');
                    }
                } else {
                    // Default
                    setClasses(dislikeBtn, STYLES.dislike.defaultBtn, allDislikeClasses);
                    setClasses(dislikeCountSpan, STYLES.dislike.defaultCount, allDislikeCountClasses);
                    if(dislikeIcon) {
                        dislikeIcon.classList.remove('text-white', 'text-red-600');
                    }
                }

            } else {
                alert(data.message || 'Hiba történt.');
            }
        })
        .catch(err => {
            btn.disabled = false;
            console.error(err);
            alert(err.message || 'Hiba történt.');
        });
    }

    function createExplosion(element, type) {
        const rect = element.getBoundingClientRect();
        // Calculate center relative to viewport + scroll
        const centerX = rect.left + rect.width / 2 + window.scrollX;
        const centerY = rect.top + rect.height / 2 + window.scrollY;

        const iconClass = type === 'like' ? 'fa-thumbs-up' : 'fa-thumbs-down';
        // Tailwind text colors
        const colorClass = type === 'like' ? 'text-green-500' : 'text-red-500';

        for (let i = 0; i < 15; i++) {
            const particle = document.createElement('i');
            // Add fa-solid and custom classes
            particle.className = `fa-solid ${iconClass} ${colorClass} particle text-lg`;

            // Random angle and distance
            const angle = Math.random() * Math.PI * 2;
            const velocity = 30 + Math.random() * 50;

            const tx = Math.cos(angle) * velocity;
            const ty = Math.sin(angle) * velocity;

            particle.style.setProperty('--tx', `${tx}px`);
            particle.style.setProperty('--ty', `${ty}px`);

            particle.style.left = `${centerX}px`;
            particle.style.top = `${centerY}px`;

            document.body.appendChild(particle);

            // Cleanup
            particle.addEventListener('animationend', () => {
                particle.remove();
            });
        }
    }
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Deep Linking for Replies
        const urlParams = new URLSearchParams(window.location.search);
        const answerId = urlParams.get('answer_id');
        const replyId = urlParams.get('highlight_reply');

        if (answerId && replyId) {
            const answerCard = document.getElementById('answer-card-' + answerId);
            if (answerCard) {
                // Scroll to answer card first to ensure visibility logic works if needed
                answerCard.scrollIntoView({ behavior: 'smooth', block: 'center' });

                // Load replies (wait for promise)
                // Note: check if function exists just in case
                if (typeof loadAnswerReplies === 'function') {
                    loadAnswerReplies(answerId).then(() => {
                        // Locate the reply
                        // We use the unique vote button ID to find it
                        // Retry a few times in case of rendering delay
                        let attempts = 0;
                        const findAndScroll = () => {
                            const voteBtn = document.getElementById('reply-vote-btn-like-' + replyId);
                            if (voteBtn) {
                                const replyContainer = voteBtn.closest('.bg-white'); // Assuming the wrapper class
                                if (replyContainer) {
                                    replyContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    // Flash Highlight
                                    const originalBg = replyContainer.style.backgroundColor;
                                    replyContainer.style.transition = 'background-color 0.5s';
                                    replyContainer.style.backgroundColor = '#fef9c3'; // yellow-100

                                    setTimeout(() => {
                                        replyContainer.style.backgroundColor = originalBg || '';
                                    }, 2000);
                                }
                            } else if (attempts < 5) {
                                attempts++;
                                setTimeout(findAndScroll, 200);
                            }
                        };

                        setTimeout(findAndScroll, 100);
                    });
                }
            }
        }
    });
</script>
</body>
</html>
