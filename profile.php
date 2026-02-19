<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/gamification.php';

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP']))
        $ipaddress = $_SERVER['HTTP_CF_CONNECTING_IP'];
    else if (isset($_SERVER['HTTP_X_REAL_IP']))
        $ipaddress = $_SERVER['HTTP_X_REAL_IP'];
    else if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// 1. Identify Current User (Logged In)
$currentUser = getCurrentUser($pdo);
$currentUserId = $currentUser ? $currentUser['user_id'] : 0;
$isAdmin = $currentUser && !empty($currentUser['is_admin']);

// 2. Identify Profile User (Target)
$profileId = isset($_GET['id']) ? (int)$_GET['id'] : $currentUserId;

// If no ID provided and not logged in, redirect to login
if ($profileId <= 0) {
    header("Location: login.php");
    exit;
}

// 3. Fetch Profile User Data
$stmt = $pdo->prepare("SELECT * FROM qc_users WHERE user_id = ?");
$stmt->execute([$profileId]);
$user = $stmt->fetch();

// Handle 404 - User Not Found
if (!$user) {
    http_response_code(404);
    die("A felhasználó nem található.");
}

// Sync Gamification (Rank & Badges)
syncUserGamification($pdo, $profileId, $user);

// Refresh local rank data for display
$rankData = getRank($user['reputation_points'] ?? 0);
$user['rank_title'] = $rankData['name'];

// Calculate Level (1-8)
$allRanks = [
    'Látogató', 'Újonc', 'Felfedező', 'Aktív Tag',
    'Veterán', 'Mentor', 'Mester', 'Cyber Legenda'
];
$userLevel = array_search($rankData['name'], $allRanks);
$userLevel = ($userLevel === false) ? 1 : $userLevel + 1;

// Prepare Badge Styles Map
$badgeDefs = getBadgeDefinitions();
$badgeStyles = [];
foreach ($badgeDefs as $cat => $list) {
    foreach ($list as $b) {
        $badgeStyles[$b['name']] = $b;
    }
}

$isOwner = ($currentUserId == $user['user_id']);

// 4. Update Activity (if it's the owner viewing their own profile)
if ($isOwner) {
    updateUserActivity($pdo);
}

// 5. Handle Tabs & Fetch Data
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';
$validTabs = ['overview', 'questions', 'answers', 'badges'];
if (!in_array($tab, $validTabs)) $tab = 'overview';

// Common Data (Badges are always shown in header)
$badgesStmt = $pdo->prepare("
    SELECT b.name, b.description, b.icon
    FROM qc_badges b
    JOIN qc_user_badges ub ON b.id = ub.badge_id
    WHERE ub.user_id = ?
");
$badgesStmt->execute([$profileId]);
$badges = $badgesStmt->fetchAll();

// Tab Specific Data
$activities = [];
$topAnswers = [];
$userQuestions = [];
$userAnswers = [];

if ($tab === 'overview') {
    // Recent Activity (Union of Questions, Answers, Replies, Reply Votes, Answer Votes)
    // Using ... to prevent "Illegal mix of collations" errors
    $activityStmt = $pdo->prepare("
        (SELECT 'question' as type, id, CONVERT(title USING utf8mb4) as content, created_at, slug, id as question_id, NULL as answer_id, NULL as reply_id, vote_score, 0 as accepted, NULL as vote_type
         FROM qc_questions
         WHERE user_id = ?)
        UNION
        (SELECT 'answer' as type, a.id, CONVERT(q.title USING utf8mb4) as content, a.created_at, q.slug, q.id as question_id, a.id as answer_id, NULL as reply_id, a.vote_score, a.is_accepted as accepted, NULL as vote_type
         FROM qc_answers a
         JOIN qc_questions q ON a.question_id = q.id
         WHERE a.user_id = ?)
        UNION
        (SELECT 'reply' as type, r.id, CONVERT(a.content USING utf8mb4) as content, r.created_at, q.slug, q.id as question_id, a.id as answer_id, r.id as reply_id, 0 as vote_score, 0 as accepted, NULL as vote_type
         FROM qc_answer_replies r
         JOIN qc_answers a ON r.answer_id = a.id
         JOIN qc_questions q ON a.question_id = q.id
         WHERE r.user_id = ?)
        UNION
        (SELECT 'reply_vote' as type, v.id,
                CONVERT(r.content USING utf8mb4) as content,
                v.created_at, q.slug, q.id as question_id, a.id as answer_id, r.id as reply_id, 0 as vote_score, 0 as accepted, v.vote_type
         FROM qc_reply_votes v
         JOIN qc_answer_replies r ON v.reply_id = r.id
         JOIN qc_answers a ON r.answer_id = a.id
         JOIN qc_questions q ON a.question_id = q.id
         WHERE v.user_id = ?)
        UNION
        (SELECT 'answer_vote' as type, v.id,
                CONVERT(a.content USING utf8mb4) as content,
                v.created_at, q.slug, q.id as question_id, a.id as answer_id, NULL as reply_id, 0 as vote_score, 0 as accepted,
                CASE WHEN v.vote_value > 0 THEN 'like' ELSE 'dislike' END as vote_type
         FROM qc_votes v
         JOIN qc_answers a ON v.target_id = a.id
         JOIN qc_questions q ON a.question_id = q.id
         WHERE v.user_id = ? AND v.target_type = 'answer')
        ORDER BY created_at DESC
        LIMIT 6
    ");
    $activityStmt->execute([$profileId, $profileId, $profileId, $profileId, $profileId]);
    $activities = $activityStmt->fetchAll();

    $showActivityButton = false;
    if (count($activities) > 5) {
        $showActivityButton = true;
        array_pop($activities); // Remove the 6th item
    }

    // Top Answers (Showcase)
    $topAnswersStmt = $pdo->prepare("
        SELECT a.*, q.title as question_title, q.slug as question_slug
        FROM qc_answers a
        JOIN qc_questions q ON a.question_id = q.id
        WHERE a.user_id = ?
        ORDER BY a.vote_score DESC, a.is_accepted DESC
        LIMIT 2
    ");
    $topAnswersStmt->execute([$profileId]);
    $topAnswers = $topAnswersStmt->fetchAll();
} elseif ($tab === 'questions') {
    $qCountStmt = $pdo->prepare("SELECT COUNT(*) FROM qc_questions WHERE user_id = ?");
    $qCountStmt->execute([$profileId]);
    $totalQuestions = $qCountStmt->fetchColumn();
    $totalQuestionPages = ceil($totalQuestions / 5);

    $qStmt = $pdo->prepare("
        SELECT id, CONVERT(title USING utf8mb4) as title, slug, created_at, view_count, vote_score,
        (SELECT COUNT(*) FROM qc_answers WHERE question_id = qc_questions.id) as answer_count
        FROM qc_questions
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $qStmt->execute([$profileId]);
    $userQuestions = $qStmt->fetchAll();
} elseif ($tab === 'answers') {
    $aCountStmt = $pdo->prepare("SELECT COUNT(*) FROM qc_answers WHERE user_id = ?");
    $aCountStmt->execute([$profileId]);
    $totalAnswers = $aCountStmt->fetchColumn();
    $totalAnswerPages = ceil($totalAnswers / 5);

    $aStmt = $pdo->prepare("
        SELECT a.*, q.title as question_title, q.slug as question_slug
        FROM qc_answers a
        JOIN qc_questions q ON a.question_id = q.id
        WHERE a.user_id = ?
        ORDER BY a.created_at DESC
        LIMIT 5
    ");
    $aStmt->execute([$profileId]);
    $userAnswers = $aStmt->fetchAll();
}

// 6. Helpers & Formatting
$avatarUrl = !empty($user['avatar']) ? $user['avatar'] : "https://ui-avatars.com/api/?name=" . urlencode($user['username']) . "&background=6366f1&color=fff&size=256";

// Fix Logic for BG Image
if (!empty($user['cover_image'])) {
    $bgImageStyle = "background-image: url('" . htmlspecialchars($user['cover_image']) . "'); background-size: cover; background-position: center;";
} else {
    $bgImageStyle = "background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); background-repeat: repeat;";
}

// Online Status Check (5 minutes threshold)
$lastActiveTime = !empty($user['last_login']) ? strtotime($user['last_login']) : 0;
$isOnline = ($lastActiveTime > (time() - 300));
$statusText = $isOnline ? 'ONLINE' : 'OFFLINE';
$statusClass = $isOnline ? 'text-emerald-400 border-emerald-500/30' : 'text-slate-400 border-slate-500/30';
$statusDotClass = $isOnline ? 'text-emerald-400 animate-pulse' : 'text-slate-500';

// Stats Formatting
function formatStat($num) {
    if ($num >= 1000) return number_format($num / 1000, 1) . 'k';
    return $num;
}

// 7. Fetch User Bookmarks (Latest 5)
$bookmarks = [];
if ($isOwner) {
    // Only fetch if table exists (safe fallback for initial run)
    // Actually we assume table exists now.
    try {
        $bmStmt = $pdo->prepare("
            SELECT b.id as bookmark_id, q.id as question_id, q.title, q.slug
            FROM qc_bookmarks b
            JOIN qc_questions q ON b.question_id = q.id
            WHERE b.user_id = ?
            ORDER BY b.created_at DESC
            LIMIT 5
        ");
        $bmStmt->execute([$currentUserId]);
        $bookmarks = $bmStmt->fetchAll();
    } catch (PDOException $e) {
        // Table might not exist yet
        $bookmarks = [];
    }
}

?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <title><?php echo htmlspecialchars($user['username']); ?> - Felhasználói Profil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#008080">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,700;1,700&family=Inter:wght@400;600;800&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/layout.css">
    <link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/article.css">
    <link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/widget.css">
</head>
<body class="min-h-screen relative">
<?php
$headerPath = __DIR__ . '/../inc/header_menu.php';
if (file_exists($headerPath)) {
    require_once $headerPath;
}
?>

<main class="header-content-wrapper mt-0 pb-0">
<main id="main" class="site-main">

    <div class="cikk-fo-container" itemscope itemtype="http://schema.org/ProfilePage">

        <div class="cikk-fo-layout">

            <!-- BAL OSZLOP (Profil Tartalom) -->
            <div class="cikk-fo-col-left" style="background: linear-gradient(135deg, rgba(11, 25, 30, 0.9), rgba(10, 15, 20, 0.9));">

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-5px); } 100% { transform: translateY(0px); } }
    .badge-item:hover { animation: float 2s ease-in-out infinite; }
    .neon-text { text-shadow: 0 0 5px rgba(255, 255, 255, 0.5), 0 0 10px rgba(255, 255, 255, 0.3); }
</style>

<div class="profile-container text-white font-['Inter']">

    <!-- Header Section -->
    <div class="relative rounded-none overflow-hidden shadow-2xl bg-slate-800 border border-slate-700">
        <!-- Banner -->
        <div class="h-32 md:h-48 bg-gradient-to-r from-indigo-900 via-purple-900 to-slate-900 relative">
             <div class="absolute inset-0 opacity-30" style="<?php echo $bgImageStyle; ?>"></div>
             <div class="absolute bottom-4 right-4 flex gap-3">
                 <span class="px-3 py-1 bg-black/50 backdrop-blur-md rounded-full text-xs font-mono <?php echo $statusClass; ?> shadow-lg border">
                    <i class="fas fa-circle text-[8px] mr-1 <?php echo $statusDotClass; ?>"></i> <?php echo $statusText; ?>
                 </span>
             </div>
        </div>

        <!-- Info Bar -->
        <div class="px-4 md:px-8 pb-6 relative">
            <div class="flex flex-col md:flex-row items-end gap-6 -mt-12 md:-mt-16 mb-4">
                <!-- Avatar -->
                <div class="relative group mx-auto md:mx-0">
                    <div class="w-24 h-24 md:w-32 md:h-32 rounded-2xl bg-slate-900 p-1 ring-4 ring-slate-800 shadow-xl overflow-hidden relative z-10">
                        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar" class="w-full h-full object-cover rounded-xl group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="absolute -bottom-2 -right-2 bg-gradient-to-br from-amber-400 to-orange-600 w-8 h-8 md:w-10 md:h-10 rounded-lg flex items-center justify-center text-white text-sm md:text-lg font-bold shadow-lg border-2 border-slate-800 z-20" title="Szint: <?php echo $userLevel; ?>">
                        <?php echo $userLevel; ?>
                    </div>
                </div>

                <!-- Text Info -->
                <div class="flex-1 mb-2 text-center md:text-left w-full">
                    <div class="flex flex-col md:flex-row items-center md:items-end gap-2 md:gap-3 mb-1 justify-center md:justify-start">
                        <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white font-['Orbitron']"><?php echo htmlspecialchars($user['username']); ?></h1>
                        <?php if ($user['is_admin']): ?>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 mb-1 md:mb-0">Admin</span>
                        <?php elseif (isset($user['role']) && $user['role'] === 'moderator'): ?>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-green-500/20 text-green-300 border border-green-500/30 mb-1 md:mb-0">Moderátor</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-sm font-bold flex items-center justify-center md:justify-start gap-2 <?php echo $rankData['style']; ?>">
                        <i class="fas <?php echo $rankData['icon']; ?>"></i>
                        <?php echo htmlspecialchars($rankData['name']); ?>
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mb-2 w-full md:w-auto justify-center">
                    <?php if ($isOwner): ?>
                     <a href="profile_edit.php" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold transition-colors shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                        <i class="fas fa-edit"></i> <span class="hidden sm:inline">Profil Szerkesztése</span>
                     </a>
                    <?php elseif ($currentUserId > 0): ?>
                     <button class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold transition-colors shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                        <i class="fas fa-user-plus"></i> <span class="hidden sm:inline">Követés</span>
                     </button>
                     <a href="send_private_message.php?recipient=<?php echo urlencode($user['username']); ?>" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold transition-colors border border-slate-600 flex items-center gap-2">
                        <i class="fas fa-envelope"></i> <span class="hidden sm:inline">Üzenet</span>
                     </a>
                     <button class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-colors border border-slate-700">
                        <i class="fas fa-ellipsis-h"></i>
                     </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Gamification Badges Row -->
            <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-4 pt-4 border-t border-slate-700/50">
                <?php if (!empty($badges)): ?>
                    <?php foreach ($badges as $badge):
                        $style = $badgeStyles[$badge['name']] ?? ['color' => 'text-slate-400', 'bg' => 'bg-slate-500/10', 'border' => 'border-slate-500/20'];
                    ?>
                    <div class="badge-item <?php echo $style['bg']; ?> <?php echo $style['color']; ?> border <?php echo $style['border']; ?> px-3 py-1 rounded-md text-xs font-bold flex items-center gap-2 cursor-help transition-colors hover:brightness-125" title="<?php echo htmlspecialchars($badge['description']); ?>">
                        <i class="fas <?php echo htmlspecialchars($badge['icon'] ?? 'fa-star'); ?>"></i> <?php echo htmlspecialchars($badge['name']); ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="text-xs text-slate-500 italic">Még nincsenek jelvények.</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Wrapper to restore padding for content below header -->
    <div class="p-4 md:px-6 md:pt-6 md:pb-0">

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4 mb-0">
        <!-- Reputation -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-none flex flex-col items-center justify-center hover:bg-slate-800 transition-colors group relative overflow-hidden">
            <div class="absolute inset-0 bg-amber-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="text-2xl md:text-3xl font-black text-amber-400 mb-1 font-['Chakra_Petch'] group-hover:scale-110 transition-transform relative z-10"><?php echo formatStat($user['reputation_points'] ?? 0); ?></div>
            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold relative z-10">Reputáció</div>
        </div>
         <!-- Questions -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-none flex flex-col items-center justify-center hover:bg-slate-800 transition-colors group">
            <div class="text-2xl md:text-3xl font-black text-blue-400 mb-1 font-['Chakra_Petch'] group-hover:scale-110 transition-transform"><?php echo formatStat($user['questions_count'] ?? 0); ?></div>
            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Kérdés</div>
        </div>
         <!-- Answers -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-none flex flex-col items-center justify-center hover:bg-slate-800 transition-colors group">
            <div class="text-2xl md:text-3xl font-black text-emerald-400 mb-1 font-['Chakra_Petch'] group-hover:scale-110 transition-transform"><?php echo formatStat($user['answers_count'] ?? 0); ?></div>
            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Válasz</div>
        </div>
         <!-- Accepted -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-none flex flex-col items-center justify-center hover:bg-slate-800 transition-colors group">
            <div class="text-2xl md:text-3xl font-black text-green-400 mb-1 font-['Chakra_Petch'] group-hover:scale-110 transition-transform"><?php echo formatStat($user['accepted_answers_count'] ?? 0); ?></div>
            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Elfogadva</div>
        </div>
         <!-- Comments -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-none flex flex-col items-center justify-center hover:bg-slate-800 transition-colors group">
            <div class="text-2xl md:text-3xl font-black text-purple-400 mb-1 font-['Chakra_Petch'] group-hover:scale-110 transition-transform"><?php echo formatStat($user['comments_count'] ?? 0); ?></div>
            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Komment</div>
        </div>
         <!-- Reactions -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-none flex flex-col items-center justify-center hover:bg-slate-800 transition-colors group">
            <div class="text-2xl md:text-3xl font-black text-rose-400 mb-1 font-['Chakra_Petch'] group-hover:scale-110 transition-transform"><?php echo formatStat($user['reactions_count'] ?? 0); ?></div>
            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Reakció</div>
        </div>
    </div>

    </div> <!-- Close Wrapper -->

    <div class="p-0 md:px-6 md:pt-4 md:pb-6">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        <!-- Left Column: Details -->
        <div class="lg:col-span-1 space-y-6">

            <!-- About Card -->
            <div class="bg-slate-800/50 border border-slate-700 rounded-none p-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-indigo-500 to-purple-500"></div>
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-user-circle text-indigo-400"></i> Névjegy
                </h3>

                <div class="space-y-4">
                    <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-800">
                        <span class="text-xs text-slate-500 uppercase font-bold block mb-2">Bemutatkozás</span>
                        <p class="text-sm text-slate-300 leading-relaxed italic relative pl-3 border-l-2 border-slate-700">
                            <?php echo !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : '"A felhasználó még nem írt bemutatkozást."'; ?>
                        </p>
                    </div>

                    <ul class="space-y-3 pt-2">
                        <?php if (($isOwner || $isAdmin) && !empty($user['email'])): ?>
                         <li class="flex items-center gap-3 text-sm text-slate-300 hover:text-white transition-colors">
                            <div class="w-8 h-8 rounded bg-slate-700/50 flex items-center justify-center text-slate-400 border border-slate-600/50">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <span class="text-indigo-300"><?php echo htmlspecialchars($user['email']); ?></span>
                         </li>
                        <?php endif; ?>

                        <?php if (!empty($user['location'])): ?>
                         <li class="flex items-center gap-3 text-sm text-slate-300 hover:text-white transition-colors">
                            <div class="w-8 h-8 rounded bg-slate-700/50 flex items-center justify-center text-slate-400 border border-slate-600/50">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <span><?php echo htmlspecialchars($user['location']); ?></span>
                         </li>
                        <?php endif; ?>

                        <?php if (!empty($user['website'])): ?>
                         <li class="flex items-center gap-3 text-sm text-slate-300 hover:text-white transition-colors">
                            <div class="w-8 h-8 rounded bg-slate-700/50 flex items-center justify-center text-slate-400 border border-slate-600/50">
                                <i class="fas fa-globe"></i>
                            </div>
                            <a href="<?php echo htmlspecialchars($user['website']); ?>" target="_blank" rel="nofollow" class="text-indigo-400 hover:text-indigo-300 transition-colors line-clamp-1"><?php echo htmlspecialchars($user['website']); ?></a>
                         </li>
                        <?php endif; ?>

                         <li class="flex items-center gap-3 text-sm text-slate-300 hover:text-white transition-colors">
                            <div class="w-8 h-8 rounded bg-slate-700/50 flex items-center justify-center text-slate-400 border border-slate-600/50">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <span>Tag mióta: <span class="text-white font-medium"><?php echo date('Y. M. d.', strtotime($user['created_at'])); ?></span></span>
                         </li>
                         <li class="flex items-center gap-3 text-sm text-slate-300 hover:text-white transition-colors">
                            <div class="w-8 h-8 rounded bg-slate-700/50 flex items-center justify-center text-slate-400 border border-slate-600/50">
                                <i class="far fa-clock"></i>
                            </div>
                            <span>Utoljára itt: <span class="<?php echo $isOnline ? 'text-emerald-400 animate-pulse' : 'text-slate-400'; ?> font-bold"><?php echo !empty($user['last_login']) ? time_elapsed_string($user['last_login']) : 'Régen'; ?></span></span>
                         </li>
                    </ul>

                     <!-- Social Links (Placeholder logic) -->
                     <?php if (!empty($user['social_links'])):
                         $socials = json_decode($user['social_links'], true);
                         if ($socials):
                     ?>
                     <div class="flex gap-2 pt-4 justify-center lg:justify-start">
                        <?php foreach ($socials as $key => $link): ?>
                        <a href="<?php echo htmlspecialchars($link); ?>" class="w-10 h-10 rounded-lg bg-slate-800 hover:bg-indigo-600 text-slate-400 hover:text-white flex items-center justify-center transition-all border border-slate-700 hover:border-transparent hover:scale-110 shadow-lg">
                            <i class="fab fa-<?php echo htmlspecialchars($key); ?>"></i>
                        </a>
                        <?php endforeach; ?>
                     </div>
                     <?php endif; endif; ?>
                </div>
            </div>

            <!-- Bookmarks / Saved Questions -->
            <?php if ($isOwner): ?>
            <div id="sidebar-bookmarks-wrapper" class="mt-6">
                <div id="sidebar-bookmarks-widget" class="bg-slate-800/50 border border-slate-700 rounded-none p-3 relative overflow-hidden">
                    <h3 class="text-base font-bold text-white mb-3 flex items-center gap-2 px-1">
                        <i class="fas fa-bookmark text-orange-400"></i> Könyvjelzők
                    </h3>
                    <ul class="space-y-1" id="profile-bookmarks-list">
                        <?php if (!empty($bookmarks)): ?>
                            <?php foreach ($bookmarks as $bm): ?>
                             <li class="group flex items-center justify-between gap-2 px-3 py-1.5 rounded-md bg-orange-900/10 border border-orange-500/10 hover:border-orange-500/30 hover:bg-orange-900/20 transition-all">
                                <a href="<?php echo generateUrl('question', $bm['question_id'], $bm['slug']); ?>" class="text-xs md:text-sm text-orange-200/80 group-hover:text-orange-100 truncate transition-colors flex-1" title="<?php echo htmlspecialchars($bm['title']); ?>">
                                    <?php echo htmlspecialchars($bm['title']); ?>
                                </a>
                                <button onclick="deleteBookmark(<?php echo $bm['question_id']; ?>, this)" class="text-slate-500 hover:text-red-400 transition-colors p-0.5 shrink-0" title="Törlés">
                                    <i class="fas fa-times"></i>
                                </button>
                             </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="text-xs text-slate-500 px-3 italic">Még nincs mentett könyvjelződ.</li>
                        <?php endif; ?>
                    </ul>
                    <div class="mt-3 text-center">
                        <button onclick="loadAllBookmarks()" class="text-[10px] uppercase font-bold tracking-wider text-slate-500 hover:text-orange-400 transition-colors flex items-center justify-center gap-1 w-full">
                            Összes könyvjelző megtekintése <i class="fas fa-chevron-right text-[8px]"></i>
                        </button>
                    </div>
                </div>
                <!-- Hidden Mobile Full View Container -->
                <div id="mobile-bookmarks-view" class="hidden"></div>
            </div>
            <?php endif; ?>

            <!-- STORAGE WIDGET START -->
            <?php if ($isOwner):
                // Storage Calculation Logic
                $totalStorage = calculate_user_storage_limit($user);
                $usedStorage = $user['storage_used'] ?? 0;

                $storagePercent = ($totalStorage > 0) ? ($usedStorage / $totalStorage) * 100 : 0;
                $storagePercent = min(100, max(0, $storagePercent)); // Clamp

                // Formatting
                $totalMB = number_format($totalStorage / 1024 / 1024, 0);
                $usedMB = number_format($usedStorage / 1024 / 1024, 2);

                // Color Logic
                $barColor = 'bg-emerald-500';
                $textColor = 'text-emerald-400';
                $statusText = 'Optimális';
                $shadowColor = 'shadow-emerald-500/50';

                if ($storagePercent >= 90) {
                    $barColor = 'bg-red-600 animate-pulse';
                    $textColor = 'text-red-500';
                    $statusText = 'MEGTELT!';
                    $shadowColor = 'shadow-red-500/50';
                } elseif ($storagePercent >= 75) {
                    $barColor = 'bg-orange-500';
                    $textColor = 'text-orange-400';
                    $statusText = 'Kevés hely';
                    $shadowColor = 'shadow-orange-500/50';
                }

                // Milestones
                $qTiers = [5, 30, 100, 200, 400, 500, 700, 1000];
                $aTiers = [10, 60, 200, 400, 800, 1000, 2000, 4000];

                $qCount = $user['questions_count'] ?? 0;
                $qNext = 1000;
                foreach($qTiers as $t) { if($qCount < $t) { $qNext = $t; break; } }
                $qNeeded = max(0, $qNext - $qCount);
                $qPrev = 0;
                foreach($qTiers as $t) { if($qCount >= $t) $qPrev = $t; else break; }
                $qProgress = ($qNext > $qPrev) ? (($qCount - $qPrev) / ($qNext - $qPrev)) * 100 : 100;

                $aCount = $user['answers_count'] ?? 0;
                $aNext = 4000;
                foreach($aTiers as $t) { if($aCount < $t) { $aNext = $t; break; } }
                $aNeeded = max(0, $aNext - $aCount);
                $aPrev = 0;
                foreach($aTiers as $t) { if($aCount >= $t) $aPrev = $t; else break; }
                $aProgress = ($aNext > $aPrev) ? (($aCount - $aPrev) / ($aNext - $aPrev)) * 100 : 100;
            ?>
            <div class="mt-6 bg-slate-900/80 backdrop-blur-md border border-slate-700 rounded-none overflow-hidden relative group">
                <!-- Background Effects -->
                <div class="absolute top-0 right-0 -mr-10 -mt-10 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl pointer-events-none group-hover:bg-blue-500/20 transition-all duration-700"></div>
                <div class="absolute bottom-0 left-0 -ml-10 -mb-10 w-32 h-32 bg-purple-500/10 rounded-full blur-3xl pointer-events-none group-hover:bg-purple-500/20 transition-all duration-700"></div>

                <div class="p-4 relative z-10">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <i class="fas fa-hdd text-cyan-400 drop-shadow-[0_0_5px_rgba(34,211,238,0.5)]"></i>
                            <span class="bg-clip-text text-transparent bg-gradient-to-r from-cyan-400 to-blue-500">Tárhely</span>
                        </h3>
                        <span class="text-xs font-bold uppercase tracking-wider <?php echo $textColor; ?> bg-slate-800/80 px-2 py-1 rounded border border-slate-700/50 shadow-sm">
                            <?php echo $statusText; ?>
                        </span>
                    </div>

                    <!-- Main Storage Bar -->
                    <div class="mb-1 flex justify-between text-xs font-medium text-slate-300">
                        <span>Használt: <span class="text-white"><?php echo $usedMB; ?> MB</span></span>
                        <span>Összes: <span class="text-cyan-300"><?php echo $totalMB; ?> MB</span></span>
                    </div>
                    <div class="h-4 bg-slate-800 rounded-full overflow-hidden border border-slate-700/50 shadow-inner relative">
                        <!-- Grid lines for visual flair -->
                        <div class="absolute inset-0 flex justify-between px-1 opacity-20 pointer-events-none">
                            <div class="w-px h-full bg-slate-500"></div>
                            <div class="w-px h-full bg-slate-500"></div>
                            <div class="w-px h-full bg-slate-500"></div>
                            <div class="w-px h-full bg-slate-500"></div>
                        </div>
                        <div class="h-full <?php echo $barColor; ?> transition-all duration-1000 ease-out shadow-[0_0_15px_rgba(var(--tw-color-emerald-500),0.5)] relative" style="width: <?php echo $storagePercent; ?>%">
                            <div class="absolute inset-0 bg-white/20" style="background-image: linear-gradient(45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent); background-size: 1rem 1rem;"></div>
                        </div>
                    </div>
                    <div class="text-right text-[10px] text-slate-500 mt-1 font-mono"><?php echo number_format($storagePercent, 1); ?>%</div>

                    <!-- Milestones / Timeline -->
                    <div class="mt-5 space-y-4">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest border-b border-slate-700/50 pb-2 mb-3 flex justify-between">
                            Következő Szint
                        </h4>

                        <!-- Questions Milestone -->
                        <div class="relative pl-4 border-l-2 border-slate-700 group/item hover:border-blue-500 transition-colors">
                            <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-slate-700 group-hover/item:bg-blue-500 transition-colors shadow-[0_0_10px_rgba(59,130,246,0.5)]"></div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs text-slate-300">Még <strong class="text-blue-400"><?php echo $qNeeded; ?> kérdés</strong></span>
                                <span class="text-[10px] text-slate-500"><?php echo $qCount; ?> / <?php echo $qNext; ?></span>
                            </div>
                            <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full transition-all duration-700 shadow-[0_0_8px_rgba(59,130,246,0.3)]" style="width: <?php echo $qProgress; ?>%"></div>
                            </div>
                        </div>

                        <!-- Answers Milestone -->
                        <div class="relative pl-4 border-l-2 border-slate-700 group/item hover:border-purple-500 transition-colors">
                            <div class="absolute -left-[5px] top-0 w-2 h-2 rounded-full bg-slate-700 group-hover/item:bg-purple-500 transition-colors shadow-[0_0_10px_rgba(168,85,247,0.5)]"></div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-xs text-slate-300">Még <strong class="text-purple-400"><?php echo $aNeeded; ?> válasz</strong></span>
                                <span class="text-[10px] text-slate-500"><?php echo $aCount; ?> / <?php echo $aNext; ?></span>
                            </div>
                            <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-purple-500 rounded-full transition-all duration-700 shadow-[0_0_8px_rgba(168,85,247,0.3)]" style="width: <?php echo $aProgress; ?>%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Link -->
                    <div class="mt-4 pt-3 border-t border-slate-700/50 text-center">
                        <a href="#" class="text-[10px] text-slate-500 hover:text-cyan-400 transition-colors flex items-center justify-center gap-1 group/link">
                            <i class="fas fa-info-circle"></i> Mit kell tudni a tárhelyről?
                            <i class="fas fa-chevron-right text-[8px] opacity-0 group-hover/link:opacity-100 transform group-hover/link:translate-x-1 transition-all"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <!-- STORAGE WIDGET END -->

        </div>

        <!-- Right Column: Content -->
        <div class="lg:col-span-2">

            <div id="profile-bookmarks-view" class="hidden"></div>

            <div id="profile-main-content" class="space-y-6">

            <!-- Tabs -->
            <div class="flex border-b border-slate-700/50 mb-2 overflow-x-auto no-scrollbar">
                <a href="?id=<?php echo $profileId; ?>&tab=overview" class="px-6 py-3 font-bold text-sm rounded-t-lg whitespace-nowrap transition-colors border-b-2 <?php echo ($tab === 'overview') ? 'text-indigo-400 border-indigo-500 bg-slate-800/30' : 'text-slate-400 hover:text-white border-transparent hover:border-slate-600 hover:bg-slate-800/30'; ?>">
                    Áttekintés
                </a>
                <a href="?id=<?php echo $profileId; ?>&tab=questions" class="px-6 py-3 font-medium text-sm rounded-t-lg whitespace-nowrap transition-colors border-b-2 <?php echo ($tab === 'questions') ? 'text-indigo-400 border-indigo-500 bg-slate-800/30' : 'text-slate-400 hover:text-white border-transparent hover:border-slate-600 hover:bg-slate-800/30'; ?>">
                    Kérdések (<?php echo $user['questions_count'] ?? 0; ?>)
                </a>
                <a href="?id=<?php echo $profileId; ?>&tab=answers" class="px-6 py-3 font-medium text-sm rounded-t-lg whitespace-nowrap transition-colors border-b-2 <?php echo ($tab === 'answers') ? 'text-indigo-400 border-indigo-500 bg-slate-800/30' : 'text-slate-400 hover:text-white border-transparent hover:border-slate-600 hover:bg-slate-800/30'; ?>">
                    Válaszok (<?php echo $user['answers_count'] ?? 0; ?>)
                </a>
                 <a href="?id=<?php echo $profileId; ?>&tab=badges" class="px-6 py-3 font-medium text-sm rounded-t-lg whitespace-nowrap transition-colors border-b-2 <?php echo ($tab === 'badges') ? 'text-indigo-400 border-indigo-500 bg-slate-800/30' : 'text-slate-400 hover:text-white border-transparent hover:border-slate-600 hover:bg-slate-800/30'; ?>">
                    Jelvények
                </a>
            </div>

            <?php if ($tab === 'overview'): ?>
                <!-- Recent Activity Block -->
                <div class="bg-slate-800/30 border border-slate-700 rounded-none p-6">
                     <h4 class="text-white font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-history text-indigo-400"></i> Legutóbbi Aktivitás
                     </h4>

                     <div id="activity-list" class="space-y-4">
                        <?php if (!empty($activities)): ?>
                            <?php foreach ($activities as $act): ?>
                                <?php
                                    // URL Generation
                                    $baseUrl = generateUrl('question', $act['question_id'], $act['slug']);
                                    $url = $baseUrl;
                                    if ($act['type'] === 'answer') {
                                        $url .= "#answer-card-" . $act['answer_id'];
                                    } elseif ($act['type'] === 'reply') {
                                        $url .= "&answer_id=" . $act['answer_id'] . "&highlight_reply=" . $act['reply_id'] . "#answer-card-" . $act['answer_id'];
                                    } elseif ($act['type'] === 'reply_vote') {
                                        $url .= "&answer_id=" . $act['answer_id'] . "&highlight_reply=" . $act['reply_id'] . "#answer-card-" . $act['answer_id'];
                                    } elseif ($act['type'] === 'answer_vote') {
                                        $url .= "#answer-card-" . $act['answer_id'];
                                    }

                                    // Display Logic
                                    $iconClass = 'fa-question text-blue-400';
                                    $bgClass = 'group-hover:border-blue-500/30';
                                    $barColor = 'bg-blue-500';
                                    $typeLabel = 'Új kérdés:';

                                    // Content Truncation logic (same as API)
                                    $rawContent = strip_tags($act['content']);
                                    $displayContent = mb_substr($rawContent, 0, 80) . (mb_strlen($rawContent) > 80 ? '...' : '');
                                    $contentHtml = htmlspecialchars($displayContent);

                                    if ($act['type'] === 'answer') {
                                        $iconClass = 'fa-reply text-indigo-400';
                                        $bgClass = 'group-hover:border-indigo-500/30';
                                        $barColor = 'bg-indigo-500';
                                        $typeLabel = 'Válaszolt erre:';
                                    } elseif ($act['type'] === 'reply') {
                                        $iconClass = 'fa-comments text-purple-400';
                                        $bgClass = 'group-hover:border-purple-500/30';
                                        $barColor = 'bg-purple-500';
                                        $typeLabel = 'Válaszolt erre:';
                                        // Snippet for reply
                                        $snippet = mb_substr(strip_tags($act['content']), 0, 80) . (mb_strlen($act['content']) > 80 ? '...' : '');
                                        $contentHtml = '<span class="italic text-slate-400">"' . htmlspecialchars($snippet) . '"</span>';
                                    } elseif ($act['type'] === 'reply_vote') {
                                        if ($act['vote_type'] === 'like') {
                                            $iconClass = 'fa-thumbs-up text-green-400';
                                            $bgClass = 'group-hover:border-green-500/30';
                                            $barColor = 'bg-green-500';
                                            $typeLabel = 'Kedvelte a választ:';
                                        } else {
                                            $iconClass = 'fa-thumbs-down text-red-400';
                                            $bgClass = 'group-hover:border-red-500/30';
                                            $barColor = 'bg-red-500';
                                            $typeLabel = 'Nem kedvelte a választ:';
                                        }
                                        $contentHtml = '<span class="italic text-slate-400">"' . htmlspecialchars($displayContent) . '"</span>';
                                    } elseif ($act['type'] === 'answer_vote') {
                                        if ($act['vote_type'] === 'like') {
                                            $iconClass = 'fa-circle-up text-green-400';
                                            $bgClass = 'group-hover:border-green-500/30';
                                            $barColor = 'bg-green-500';
                                            $typeLabel = 'Pozitívan értékelte a választ:';
                                        } else {
                                            $iconClass = 'fa-circle-down text-red-400';
                                            $bgClass = 'group-hover:border-red-500/30';
                                            $barColor = 'bg-red-500';
                                            $typeLabel = 'Negatívan értékelte a választ:';
                                        }
                                        $contentHtml = '<span class="italic text-slate-400">"' . htmlspecialchars($displayContent) . '"</span>';
                                    }
                                ?>
                                <div class="flex gap-4 p-4 rounded-lg bg-slate-900/40 border border-slate-800 <?php echo $bgClass; ?> transition-all group cursor-pointer relative overflow-hidden" onclick="window.location.href='<?php echo $url; ?>'">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 <?php echo $barColor; ?> rounded-l-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700 group-hover:bg-opacity-80 transition-colors">
                                        <i class="fas <?php echo $iconClass; ?>"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-slate-300 mb-1 group-hover:text-white transition-colors">
                                            <?php echo $typeLabel; ?>
                                            <a href="<?php echo htmlspecialchars($url); ?>" class="font-semibold hover:underline text-indigo-300">
                                                <?php echo $contentHtml ?: htmlspecialchars($act['content']); ?>
                                            </a>
                                        </p>
                                        <div class="text-xs text-slate-500 flex items-center gap-2">
                                            <i class="far fa-clock"></i> <?php echo time_elapsed_string($act['created_at']); ?>
                                            <?php if ($act['accepted']): ?>
                                            • <span class="text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20"><i class="fas fa-check"></i> Elfogadott válasz</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-slate-500 py-4" id="no-activity-msg">Nincs legutóbbi aktivitás.</div>
                        <?php endif; ?>
                     </div>

                     <!-- Pagination / Load More Container -->
                     <div class="mt-4 text-center" id="activity-pagination-container">
                        <?php if ($showActivityButton): ?>
                        <button id="show-all-activity-btn" onclick="initPagination()" class="text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-white transition-colors p-2">
                            Összes aktivitás mutatása <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        <?php endif; ?>
                     </div>
                </div>

                <!-- Top Answers / Showcase -->
                <div class="bg-slate-800/30 border border-slate-700 rounded-none p-6">
                    <h4 class="text-white font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-star text-amber-400"></i> Kiemelt Megoldások
                     </h4>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php if (!empty($topAnswers)): ?>
                            <?php foreach ($topAnswers as $ans): ?>
                            <div class="p-4 rounded-lg bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-700 hover:border-amber-500/50 transition-all group cursor-pointer relative">
                                <div class="flex justify-between items-start mb-2">
                                    <?php
                                    $score = $ans['vote_score'];
                                    if ($score < 0) {
                                        $scoreClass = 'text-red-500';
                                        $scoreText = $score;
                                    } elseif ($score == 0) {
                                        $scoreClass = 'text-slate-400';
                                        $scoreText = $score;
                                    } elseif ($score <= 5) {
                                        $scoreClass = 'text-green-500';
                                        $scoreText = '+' . $score;
                                    } elseif ($score <= 10) {
                                        $scoreClass = 'text-cyan-400';
                                        $scoreText = '+' . $score;
                                    } else {
                                        $scoreClass = 'text-amber-400';
                                        $scoreText = '+' . $score;
                                    }
                                    ?>
                                    <div class="<?php echo $scoreClass; ?> text-lg font-bold group-hover:scale-110 transition-transform"><?php echo $scoreText; ?></div>
                                    <i class="fas fa-check-circle text-emerald-500 text-xl shadow-emerald-500/20 drop-shadow-lg"></i>
                                </div>
                                <h5 class="text-white font-bold text-sm mb-2 line-clamp-2 group-hover:text-indigo-300 transition-colors"><?php echo htmlspecialchars($ans['question_title']); ?></h5>
                                <p class="text-xs text-slate-400 line-clamp-3 mb-3 leading-relaxed">
                                    <?php echo htmlspecialchars(mb_substr(strip_tags($ans['content']), 0, 100)) . '...'; ?>
                                </p>
                                <a href="<?php echo generateUrl('question', $ans['question_id'], $ans['question_slug']); ?>" class="absolute inset-0 z-10"></a>
                                <span class="text-xs text-indigo-400 font-bold uppercase group-hover:text-indigo-300 flex items-center gap-1">Tovább olvasom <i class="fas fa-arrow-right transform group-hover:translate-x-1 transition-transform"></i></span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-span-2 text-center text-slate-500 py-4">Nincs megjeleníthető kiemelt válasz.</div>
                        <?php endif; ?>
                     </div>
                </div>
            <?php elseif ($tab === 'questions'): ?>
                <div class="bg-slate-800/30 border border-slate-700 rounded-none p-6">
                    <h4 class="text-white font-bold mb-4 flex items-center gap-2"><i class="fas fa-question-circle text-blue-400"></i> Kérdések</h4>
                    <div class="space-y-4" id="questions-list">
                        <?php if (!empty($userQuestions)): ?>
                            <?php foreach ($userQuestions as $q): ?>
                                <div class="flex gap-4 p-4 rounded-lg bg-slate-900/40 border border-slate-800 hover:border-blue-500/30 transition-all group relative overflow-hidden">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 rounded-l-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700 group-hover:bg-opacity-80 transition-colors">
                                        <i class="fas fa-question text-blue-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <a href="<?php echo generateUrl('question', $q['id'], $q['slug']); ?>" class="text-indigo-300 font-semibold hover:underline block mb-1 text-sm md:text-base">
                                            <?php echo htmlspecialchars($q['title']); ?>
                                        </a>
                                        <div class="flex flex-wrap gap-3 text-xs text-slate-500 mt-2">
                                            <span class="flex items-center gap-1"><i class="far fa-clock"></i> <?php echo time_elapsed_string($q['created_at']); ?></span>
                                            <span class="flex items-center gap-1 text-slate-400"><i class="far fa-eye"></i> <?php echo $q['view_count']; ?></span>
                                            <span class="flex items-center gap-1 <?php echo ($q['vote_score'] > 0) ? 'text-green-400' : 'text-slate-400'; ?>"><i class="fas fa-chevron-up"></i> <?php echo $q['vote_score']; ?></span>
                                            <span class="flex items-center gap-1 text-indigo-400"><i class="fas fa-comment-dots"></i> <?php echo $q['answer_count']; ?> válasz</span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-slate-500 text-center py-4">Nincs feltett kérdés.</div>
                        <?php endif; ?>
                    </div>
                    <!-- Pagination -->
                    <div class="mt-4 text-center" id="questions-pagination-container">
                        <?php if ($totalQuestionPages > 1): ?>
                            <div class="flex justify-center gap-2">
                                <?php
                                $range = [];
                                if ($totalQuestionPages <= 7) {
                                    for ($i = 1; $i <= $totalQuestionPages; $i++) $range[] = $i;
                                } else {
                                    $range = [1, 2, 3, 4, 5, '...', $totalQuestionPages];
                                }
                                foreach ($range as $p):
                                    if ($p === '...'): ?>
                                        <span class="px-3 py-1 text-slate-500">...</span>
                                    <?php else: ?>
                                        <button onclick="changeQuestionPage(<?php echo $p; ?>)" class="px-3 py-1 border rounded transition-colors <?php echo ($p == 1) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700'; ?>"><?php echo $p; ?></button>
                                    <?php endif;
                                endforeach;
                                if ($totalQuestionPages > 1): ?>
                                    <button onclick="changeQuestionPage(2)" class="px-3 py-1 bg-slate-800 border border-slate-700 text-slate-300 rounded hover:bg-slate-700 transition-colors"><i class="fas fa-chevron-right"></i></button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($tab === 'answers'): ?>
                <div class="bg-slate-800/30 border border-slate-700 rounded-none p-6">
                    <h4 class="text-white font-bold mb-4 flex items-center gap-2"><i class="fas fa-reply text-emerald-400"></i> Válaszok</h4>
                    <div class="space-y-4" id="answers-list">
                        <?php if (!empty($userAnswers)): ?>
                            <?php foreach ($userAnswers as $a): ?>
                                <div class="flex gap-4 p-4 rounded-lg bg-slate-900/40 border border-slate-800 hover:border-emerald-500/30 transition-all group relative overflow-hidden">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500 rounded-l-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700 group-hover:bg-opacity-80 transition-colors">
                                        <i class="fas fa-reply text-emerald-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm text-slate-300 mb-2">
                                            Válasz erre: <a href="<?php echo generateUrl('question', $a['question_id'], $a['question_slug']); ?>" class="text-emerald-400 hover:underline font-medium"><?php echo htmlspecialchars($a['question_title']); ?></a>
                                        </div>
                                        <p class="text-xs text-slate-400 line-clamp-2 mb-2 italic">
                                            "<?php echo htmlspecialchars(mb_substr(strip_tags($a['content']), 0, 150)); ?>..."
                                        </p>
                                        <div class="flex flex-wrap gap-3 text-xs text-slate-500">
                                            <span class="flex items-center gap-1"><i class="far fa-clock"></i> <?php echo time_elapsed_string($a['created_at']); ?></span>
                                            <span class="flex items-center gap-1 <?php echo ($a['vote_score'] > 0) ? 'text-green-400' : 'text-slate-400'; ?>"><i class="fas fa-chevron-up"></i> <?php echo $a['vote_score']; ?></span>
                                            <?php if ($a['is_accepted']): ?>
                                                <span class="text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20"><i class="fas fa-check"></i> Elfogadott</span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?php echo generateUrl('question', $a['question_id'], $a['question_slug']) . "#answer-card-" . $a['id']; ?>" class="absolute inset-0 z-10"></a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-slate-500 text-center py-4">Nincs válasz.</div>
                        <?php endif; ?>
                    </div>
                     <!-- Pagination -->
                    <div class="mt-4 text-center" id="answers-pagination-container">
                        <?php if ($totalAnswerPages > 1): ?>
                            <div class="flex justify-center gap-2">
                                <?php
                                $range = [];
                                if ($totalAnswerPages <= 7) {
                                    for ($i = 1; $i <= $totalAnswerPages; $i++) $range[] = $i;
                                } else {
                                    $range = [1, 2, 3, 4, 5, '...', $totalAnswerPages];
                                }
                                foreach ($range as $p):
                                    if ($p === '...'): ?>
                                        <span class="px-3 py-1 text-slate-500">...</span>
                                    <?php else: ?>
                                        <button onclick="changeAnswerPage(<?php echo $p; ?>)" class="px-3 py-1 border rounded transition-colors <?php echo ($p == 1) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700'; ?>"><?php echo $p; ?></button>
                                    <?php endif;
                                endforeach;
                                if ($totalAnswerPages > 1): ?>
                                    <button onclick="changeAnswerPage(2)" class="px-3 py-1 bg-slate-800 border border-slate-700 text-slate-300 rounded hover:bg-slate-700 transition-colors"><i class="fas fa-chevron-right"></i></button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($tab === 'badges'): ?>
                <div class="bg-slate-800/30 border border-slate-700 rounded-none p-6">
                    <h4 class="text-white font-bold mb-4">Jelvények</h4>
                    <div class="flex flex-wrap gap-4">
                        <?php if (!empty($badges)): ?>
                            <?php foreach ($badges as $badge): ?>
                                <div class="badge-item bg-slate-900 border border-slate-700 px-4 py-3 rounded-xl flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-amber-500/20 flex items-center justify-center text-amber-400">
                                        <i class="fas <?php echo htmlspecialchars($badge['icon'] ?? 'fa-trophy'); ?>"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-white text-sm"><?php echo htmlspecialchars($badge['name']); ?></div>
                                        <div class="text-xs text-slate-500"><?php echo htmlspecialchars($badge['description']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-slate-500">Nincsenek jelvények.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            </div> <!-- End profile-main-content -->

        </div>

    </div>

    </div> <!-- Close Wrapper -->

</div>

        </div>
            <!-- SIDEBAR HELYE (Jobb oszlop) -->
             <aside class="cikk-fo-col-right">
                <div class="theiaStickySidebar">

    <div class="tech-tip-widget glass-panel-raw w-full p-5 flex flex-col gap-4 rounded-none" style="margin-bottom: 20px;">
        <div class="flex items-center gap-3 border-b border-white/20 pb-3">
            <div class="w-8 h-8 flex items-center justify-center relative neon-yellow shrink-0">
            <svg viewBox="0 0 24 24" fill="none"
            xmlns="http://www.w3.org/2000/svg"
            class="w-full h-full text-yellow-400">
        <path d="M9 21H15M12 21V23"
              stroke="currentColor"
              stroke-width="1.5"
              stroke-linecap="round"/>
        <path d="M12 3C7.58172 3 4 6.58172 4 11C4 13.5 5.5 15.5 7 17H17C18.5 15.5 20 13.5 20 11C20 6.58172 16.4183 3 12 3Z"
              stroke="currentColor"
              stroke-width="1.5"/>
        <circle cx="12" cy="11" r="2.5"
                stroke="currentColor"
                stroke-width="1.5"
                class="text-yellow-300"/>
        <path d="M12 8V9M12 13V14M15 11H14M10 11H9"
              stroke="currentColor"
              stroke-width="1.5"
              class="text-yellow-300"/>
            </svg>
        </div>
        <h3 class="flex items-center gap-2 font-bold text-sm tracking-wide leading-tight text-transparent bg-clip-text bg-gradient-to-r from-white to-cyan-100 drop-shadow-sm"><span>Hasznos tudnivaló</span></h3>
    </div>
    <div class="inner-glass-raw rounded-xl p-4">
            <p class="text-white/90 text-sm font-light leading-relaxed">
                Hirtelen lelassult a gép? Nézd meg a <span class="text-emerald-300 font-normal">Feladatkezelőben</span>, melyik app használ 100% CPU-t.
            </p>
        </div>
    </div>
    <div class="relative w-full bg-slate-900 overflow-hidden shadow-[0_0_50px_rgba(79,70,229,0.3)] border border-slate-700 group perspective-1000" style="margin-bottom: 20px;">
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute inset-0 opacity-20"
                 style="background-image: linear-gradient(rgba(56, 189, 248, 0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(56, 189, 248, 0.1) 1px, transparent 1px); background-size: 30px 30px;">
            </div>
            <i class="fas fa-virus absolute -top-10 -right-10 text-9xl text-red-600/10 sec_widget_floating_virus blur-sm"></i>
            <i class="fas fa-biohazard absolute bottom-20 -left-10 text-8xl text-indigo-600/10 sec_widget_floating_virus_2"></i>
            <i class="fas fa-shield-virus absolute top-1/2 right-10 text-6xl text-emerald-500/5 sec_widget_floating_virus" style="animation-duration: 25s;"></i>
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-indigo-500/10 via-transparent to-slate-900/90 mix-blend-overlay"></div>
        </div>
        <div class="relative z-10 p-6 pb-2">
            <div class="flex justify-between items-center mb-4 border-b border-slate-700/50 pb-4">
                <div>
                    <h2 class="sec_widget_font_tech text-2xl font-black text-white tracking-wider flex items-center gap-2">
                        <span class="text-cyan-400"><i class="fas fa-shield-alt"></i></span> SEC<span class="text-indigo-500">2026</span>
                    </h2>
                </div>
            </div>
            <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-3 mb-2 backdrop-blur-md flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-red-500 mt-1 animate-pulse"></i>
                <div>
                    <h3 class="sec_widget_font_tech text-red-400 text-sm font-bold">KRITIKUS FENYEGETÉS</h3>
                    <p class="sec_widget_font_body text-slate-300 text-xs leading-tight">Új AI-alapú adathalász hullám terjed Magyarországon. Ne kattints gyanús SMS linkekre!</p>
                </div>
            </div>
        </div>
        <div class="relative z-10 px-4 pb-6 space-y-3 h-[380px] overflow-y-auto sec_widget_no_scrollbar">
            <a href="#" class="group/item block bg-slate-800/40 hover:bg-slate-700/60 border border-slate-700/50 hover:border-cyan-500/50 rounded-xl p-3 transition-all duration-300 backdrop-blur-sm relative overflow-hidden">
                <div class="absolute inset-0 w-1 bg-cyan-500 transition-all duration-300 -translate-x-full group-hover/item:translate-x-0"></div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-cyan-900 to-slate-800 flex items-center justify-center text-cyan-400 group-hover/item:text-white group-hover/item:shadow-[0_0_15px_rgba(34,211,238,0.5)] transition-all">
                        <i class="fas fa-medal text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <span class="text-[10px] font-bold text-cyan-500 uppercase tracking-wide bg-cyan-900/30 px-2 py-0.5 rounded-full">Toplista</span>
                        <h4 class="sec_widget_font_tech text-white text-sm font-bold mt-1 leading-tight group-hover/item:text-cyan-300 transition-colors">Ingyenes Vírusirtók 2026</h4>
                        <p class="sec_widget_font_body text-slate-400 text-xs mt-1">Melyik védi legjobban a géped lassítás nélkül?</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-600 group-hover/item:text-white group-hover/item:translate-x-1 transition-all"></i>
                </div>
            </a>
            <a href="#" class="group/item block bg-slate-800/40 hover:bg-slate-700/60 border border-slate-700/50 hover:border-purple-500/50 rounded-xl p-3 transition-all duration-300 backdrop-blur-sm relative overflow-hidden">
                <div class="absolute inset-0 w-1 bg-purple-500 transition-all duration-300 -translate-x-full group-hover/item:translate-x-0"></div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-900 to-slate-800 flex items-center justify-center text-purple-400 group-hover/item:text-white group-hover/item:shadow-[0_0_15px_rgba(168,85,247,0.5)] transition-all">
                        <i class="fas fa-brain text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <span class="text-[10px] font-bold text-purple-500 uppercase tracking-wide bg-purple-900/30 px-2 py-0.5 rounded-full">Mesterséges Intelligencia</span>
                        <h4 class="sec_widget_font_tech text-white text-sm font-bold mt-1 leading-tight group-hover/item:text-purple-300 transition-colors">AI Deepfake Csalások</h4>
                        <p class="sec_widget_font_body text-slate-400 text-xs mt-1">Így ismerd fel, ha nem valódi emberrel beszélsz.</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-600 group-hover/item:text-white group-hover/item:translate-x-1 transition-all"></i>
                </div>
            </a>
            <a href="#" class="group/item block bg-slate-800/40 hover:bg-slate-700/60 border border-slate-700/50 hover:border-green-500/50 rounded-xl p-3 transition-all duration-300 backdrop-blur-sm relative overflow-hidden">
                <div class="absolute inset-0 w-1 bg-green-500 transition-all duration-300 -translate-x-full group-hover/item:translate-x-0"></div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-900 to-slate-800 flex items-center justify-center text-green-400 group-hover/item:text-white group-hover/item:shadow-[0_0_15px_rgba(34,197,94,0.5)] transition-all">
                        <i class="fas fa-home text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <span class="text-[10px] font-bold text-green-500 uppercase tracking-wide bg-green-900/30 px-2 py-0.5 rounded-full">Otthon</span>
                        <h4 class="sec_widget_font_tech text-white text-sm font-bold mt-1 leading-tight group-hover/item:text-green-300 transition-colors">Az Okosotthon Veszélyei</h4>
                        <p class="sec_widget_font_body text-slate-400 text-xs mt-1">Kamerák és porszívók: Ki figyel téged?</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-600 group-hover/item:text-white group-hover/item:translate-x-1 transition-all"></i>
                </div>
            </a>
            <a href="#" class="group/item block bg-slate-800/40 hover:bg-slate-700/60 border border-slate-700/50 hover:border-yellow-500/50 rounded-xl p-3 transition-all duration-300 backdrop-blur-sm relative overflow-hidden">
                <div class="absolute inset-0 w-1 bg-yellow-500 transition-all duration-300 -translate-x-full group-hover/item:translate-x-0"></div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-yellow-900 to-slate-800 flex items-center justify-center text-yellow-400 group-hover/item:text-white group-hover/item:shadow-[0_0_15px_rgba(234,179,8,0.5)] transition-all">
                        <i class="fas fa-key text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <span class="text-[10px] font-bold text-yellow-500 uppercase tracking-wide bg-yellow-900/30 px-2 py-0.5 rounded-full">Trend</span>
                        <h4 class="sec_widget_font_tech text-white text-sm font-bold mt-1 leading-tight group-hover/item:text-yellow-300 transition-colors">Passkey vs Jelszó</h4>
                        <p class="sec_widget_font_body text-slate-400 text-xs mt-1">2026 végére eltűnnek a hagyományos jelszavak?</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-600 group-hover/item:text-white group-hover/item:translate-x-1 transition-all"></i>
                </div>
            </a>
            <a href="#" class="group/item block bg-slate-800/40 hover:bg-slate-700/60 border border-slate-700/50 hover:border-red-500/50 rounded-xl p-3 transition-all duration-300 backdrop-blur-sm relative overflow-hidden">
                <div class="absolute inset-0 w-1 bg-red-500 transition-all duration-300 -translate-x-full group-hover/item:translate-x-0"></div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-red-900 to-slate-800 flex items-center justify-center text-red-400 group-hover/item:text-white group-hover/item:shadow-[0_0_15px_rgba(239,68,68,0.5)] transition-all">
                        <i class="fas fa-bug text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <span class="text-[10px] font-bold text-red-500 uppercase tracking-wide bg-red-900/30 px-2 py-0.5 rounded-full">Malware</span>
                        <h4 class="sec_widget_font_tech text-white text-sm font-bold mt-1 leading-tight group-hover/item:text-red-300 transition-colors">Zsarolóvírusok 2.0</h4>
                        <p class="sec_widget_font_body text-slate-400 text-xs mt-1">Az új titkosítási eljárások, amik ellen nincs kulcs.</p>
                    </div>
                    <i class="fas fa-chevron-right text-slate-600 group-hover/item:text-white group-hover/item:translate-x-1 transition-all"></i>
                </div>
            </a>
        </div>
        <div class="relative z-10 p-4 bg-gradient-to-t from-slate-900 to-transparent">
            <button class="w-full py-3 bg-gradient-to-r from-indigo-600 to-cyan-600 rounded-lg text-white sec_widget_font_tech font-bold uppercase tracking-wider text-sm hover:from-indigo-500 hover:to-cyan-500 shadow-lg shadow-cyan-500/20 transition-all flex items-center justify-center gap-2 group/btn">
                <span>Teljes Biztonsági Jelentés</span>
                <i class="fas fa-arrow-right group-hover/btn:translate-x-1 transition-transform"></i>
            </button>
        </div>
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-cyan-500/5 to-transparent h-[10%] w-full z-20 pointer-events-none" style="animation: sec_widget_scanline 3s linear infinite;"></div>
    </div>






  <!-- WIDGET KEZDETE -->
    <div class="szavazas_widget_wrapper" id="szavazas_widget_app">
        <div class="szavazas_widget_glow"></div>

        <div class="szavazas_widget_card">

            <!-- Fejléc -->
            <div class="szavazas_widget_header">
                <h2 class="szavazas_widget_title">Végzet vagy Megváltás?</h2>
                <p class="szavazas_widget_desc">
                    Szerinted milyen hatással lesz a Mesterséges Intelligencia az emberiség jövőjére 2050-ig?
                </p>
            </div>

            <!-- Opciók helye (JS tölti be) -->
            <div class="szavazas_widget_options" id="szavazas_widget_options_container">
                <!-- Buttons will be injected here -->
            </div>

            <!-- Lábléc -->
            <div class="szavazas_widget_footer">
                <div class="szavazas_widget_total">
                    Összesen: <strong id="szavazas_widget_total_count">0</strong> voks
                </div>
                <!-- Megosztás gomb eltávolítva -->
            </div>

        </div>
    </div>
    <!-- WIDGET VÉGE -->










<!-- szavazás widget vége -->







 <!-- mgid hirdetés -->












 <!-- mgid hirdetés vége -->









 </div> <!-- a ragadós div -->

            </aside>
        </div>
    </div>

</main>
</main>

<!-- Lábléc -->
  <!-- ======================== ÚJ FOOTER START ======================== -->
    <footer class="footer_alul_wrapper">

        <!-- Díszítő vonal a footer tetején (a header effektje) -->
        <div class="gradient-border-bottom"></div>

        <div class="header-content-wrapper">
            <div class="footer_alul_grid">

                <!-- 1. Oszlop: Bemutatkozás -->
                <div class="footer_alul_column">
                    <div class="footer_alul_heading">Rólunk</div>
                    <div class="logo-font text-2xl font-bold mb-4 text-white">
                        SILVER<span style="color: var(--win98-teal);">PC</span><span style="color: var(--cyber-teal); font-size: 0.6em;">.HU</span>
                    </div>
                    <p class="footer_alul_text">
                        A <span class="footer_alul_about_highlight">SilverPC</span> Magyarország egyik vezető hardver és szoftver hírportálja. Célunk a legfrissebb IT technológiák bemutatása, tesztelése és a gaming kultúra népszerűsítése.
                    </p>
                    <div class="footer_alul_socials">
                        <a href="#" class="footer_alul_social_btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="footer_alul_social_btn"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="footer_alul_social_btn"><i class="fab fa-discord"></i></a>
                        <a href="#" class="footer_alul_social_btn"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>

                <!-- 2. Oszlop: Fontos Linkek -->
                <div class="footer_alul_column">
                    <div class="footer_alul_heading">Navigáció</div>
                    <ul class="footer_alul_links">
                        <li><a href="#" class="footer_alul_link_item"><i class="fas fa-chevron-right"></i> Főoldal</a></li>
                        <li><a href="#" class="footer_alul_link_item"><i class="fas fa-chevron-right"></i> Hírek</a></li>
                        <li><a href="#" class="footer_alul_link_item"><i class="fas fa-chevron-right"></i> Tesztek</a></li>
                        <li><a href="#" class="footer_alul_link_item"><i class="fas fa-chevron-right"></i> Fórum</a></li>
                        <li><a href="#" class="footer_alul_link_item"><i class="fas fa-chevron-right"></i> Videók</a></li>
                        <li><a href="#" class="footer_alul_link_item"><i class="fas fa-chevron-right"></i> Kapcsolat</a></li>
                    </ul>
                </div>

                <!-- 3. Oszlop: Információk -->
                <div class="footer_alul_column">
                    <div class="footer_alul_heading">Információk</div>
                    <ul class="footer_alul_links">
                        <li><a href="#" class="footer_alul_link_item"><i class="fas fa-lock"></i> Adatvédelmi Nyilatkozat</a></li>
                        <li><a href="#" class="footer_alul_link_item"><i class="fas fa-file-contract"></i> Általános Szerződési Feltételek</a></li>
                        <li><a href="#" class="footer_alul_link_item"><i class="fas fa-cookie-bite"></i> Cookie Kezelés</a></li>
                        <li><a href="#" class="footer_alul_link_item"><i class="fas fa-bullhorn"></i> Hirdetési lehetőségek</a></li>
                        <li><a href="#" class="footer_alul_link_item"><i class="fas fa-users"></i> Impresszum</a></li>
                    </ul>
                </div>

                <!-- 4. Oszlop: Tech Stats (Betöltési sebesség) -->
                <div class="footer_alul_column">
                    <div class="footer_alul_heading">Rendszerállapot</div>
                    <p class="footer_alul_text">Szerverünk folyamatosan monitorozva van a maximális teljesítmény érdekében.</p>

                    <div class="footer_alul_speed_box">
                        <div class="footer_alul_speed_label">
                            <span>Oldal Betöltés</span>
                            <span class="footer_alul_indicator active"></span>
                        </div>
                        <!-- Ezt a JS fogja kitölteni -->
                        <div id="page-speed-display" class="footer_alul_speed_value">...</div>
                        <div style="font-size: 0.7rem; color: #666; margin-top: 5px;">
                            <i class="fas fa-server mr-1"></i> Budapest, HU-Central-1
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Alsó záró sáv -->
        <div class="footer_alul_copyright_section">
            <div class="header-content-wrapper">

                <!-- A kért jogi szöveg -->
                <p class="footer_alul_legal_text">
                    Az oldalon megjelenő minden cikk, kép és egyéb tartalom a SilverPC.hu tulajdonát képezi, felhasználásuk kizárólag az eredeti forrás pontos és jól látható feltüntetésével engedélyezett.
                </p>

                <div class="footer_alul_separator_line"></div>

                <div class="footer_alul_bottom_flex">
                    <!-- Bal oldal: Dátum (PHP logika JS-ben szimulálva) -->
                    <div class="footer_alul_year">
                        <span class="footer_alul_logo_sm">SilverPC©</span>
                        <!-- PHP KÓD HELYE: <?php echo date("Y") == 2017 ? "2017" : "2017 - " . date("Y"); ?> -->
                        <span id="copyright-year">2017 - 2026</span> Minden jog fenntartva.
                    </div>

                    <!-- Jobb oldal: Design Credit -->
                    <div style="font-size: 0.8rem; color: #444; font-family: 'Courier New', monospace;">
                        <i class="fas fa-code text-[--matrix-green]"></i> DESIGNED FOR PERFORMANCE
                    </div>
                </div>
            </div>
        </div>

    </footer>

<!-- Scripts -->
<script>
    let currentPage = 1;
    const limit = 5;
    const userId = <?php echo $profileId; ?>;


    function initPagination() {
        changeActivityPage(1);
    }

    function changeActivityPage(page) {
        currentPage = page;
        const offset = (page - 1) * limit;
        const container = document.getElementById('activity-list');
        const paginationContainer = document.getElementById('activity-pagination-container');

        // Show loading
        container.innerHTML = '<div class="text-center p-8 text-slate-500"><i class="fas fa-spinner fa-spin text-2xl"></i><br>Betöltés...</div>';
        paginationContainer.innerHTML = '';

        fetch(`ajax_get_user_activity.php?user_id=${userId}&offset=${offset}&limit=${limit}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    container.innerHTML = ''; // Clear loading

                    if (data.activities.length > 0) {
                        data.activities.forEach(act => {
                            let iconClass = 'fa-question text-blue-400';
                            let bgClass = 'group-hover:border-blue-500/30';
                            let barColor = 'bg-blue-500';
                            let typeLabel = 'Új kérdés:';
                            let contentHtml = act.content;

                            if (act.type === 'answer') {
                                iconClass = 'fa-reply text-indigo-400';
                                bgClass = 'group-hover:border-indigo-500/30';
                                barColor = 'bg-indigo-500';
                                typeLabel = 'Válaszolt erre:';
                            } else if (act.type === 'reply') {
                                iconClass = 'fa-comments text-purple-400';
                                bgClass = 'group-hover:border-purple-500/30';
                                barColor = 'bg-purple-500';
                                typeLabel = 'Válaszolt erre:';
                                contentHtml = `<span class="italic text-slate-400">"${act.content}"</span>`;
                            } else if (act.type === 'reply_vote') {
                                if (act.vote_type === 'like') {
                                    iconClass = 'fa-thumbs-up text-green-400';
                                    bgClass = 'group-hover:border-green-500/30';
                                    barColor = 'bg-green-500';
                                    typeLabel = 'Kedvelte a választ:';
                                } else {
                                    iconClass = 'fa-thumbs-down text-red-400';
                                    bgClass = 'group-hover:border-red-500/30';
                                    barColor = 'bg-red-500';
                                    typeLabel = 'Nem kedvelte a választ:';
                                }
                                contentHtml = `<span class="italic text-slate-400">"${act.content}"</span>`;
                            } else if (act.type === 'answer_vote') {
                                if (act.vote_type === 'like') {
                                    iconClass = 'fa-circle-up text-green-400';
                                    bgClass = 'group-hover:border-green-500/30';
                                    barColor = 'bg-green-500';
                                    typeLabel = 'Pozitívan értékelte a választ:';
                                } else {
                                    iconClass = 'fa-circle-down text-red-400';
                                    bgClass = 'group-hover:border-red-500/30';
                                    barColor = 'bg-red-500';
                                    typeLabel = 'Negatívan értékelte a választ:';
                                }
                                contentHtml = `<span class="italic text-slate-400">"${act.content}"</span>`;
                            }

                            const acceptedHtml = act.accepted ? '• <span class="text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20"><i class="fas fa-check"></i> Elfogadott válasz</span>' : '';

                            const html = `
                                <div class="flex gap-4 p-4 rounded-lg bg-slate-900/40 border border-slate-800 ${bgClass} transition-all group cursor-pointer relative overflow-hidden animate-fade-in" onclick="window.location.href='${act.url}'">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 ${barColor} rounded-l-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700 group-hover:bg-opacity-80 transition-colors">
                                        <i class="fas ${iconClass}"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-slate-300 mb-1 group-hover:text-white transition-colors">
                                            ${typeLabel}
                                            <a href="${act.url}" class="font-semibold hover:underline text-indigo-300">${contentHtml}</a>
                                        </p>
                                        <div class="text-xs text-slate-500 flex items-center gap-2">
                                            <i class="far fa-clock"></i> ${act.time_elapsed}
                                            ${acceptedHtml}
                                        </div>
                                    </div>
                                </div>
                            `;
                            container.insertAdjacentHTML('beforeend', html);
                        });
                    } else {
                        container.innerHTML = '<div class="text-center text-slate-500 py-4">Nincs megjeleníthető aktivitás.</div>';
                    }

                    renderPagination(data.total_pages, data.current_page);
                } else {
                    container.innerHTML = '<div class="text-red-500 text-center">Hiba történt.</div>';
                }
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<div class="text-red-500 text-center">Hálózati hiba.</div>';
            });
    }

    function renderPagination(totalPages, currentPage) {
        const container = document.getElementById('activity-pagination-container');
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="flex justify-center gap-2">';

        if (currentPage > 1) {
            html += `<button onclick="changeActivityPage(${currentPage - 1})" class="px-3 py-1 bg-slate-800 border border-slate-700 text-slate-300 rounded hover:bg-slate-700 transition-colors"><i class="fas fa-chevron-left"></i></button>`;
        }

        const range = [];
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) range.push(i);
        } else {
            if (currentPage <= 4) {
                range.push(1, 2, 3, 4, 5, '...', totalPages);
            } else if (currentPage >= totalPages - 3) {
                range.push(1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages);
            } else {
                range.push(1, '...', currentPage - 1, currentPage, currentPage + 1, '...', totalPages);
            }
        }

        range.forEach(p => {
            if (p === '...') {
                html += `<span class="px-3 py-1 text-slate-500">...</span>`;
            } else {
                const activeClass = (p === currentPage) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700';
                html += `<button onclick="changeActivityPage(${p})" class="px-3 py-1 border rounded transition-colors ${activeClass}">${p}</button>`;
            }
        });

        if (currentPage < totalPages) {
            html += `<button onclick="changeActivityPage(${currentPage + 1})" class="px-3 py-1 bg-slate-800 border border-slate-700 text-slate-300 rounded hover:bg-slate-700 transition-colors"><i class="fas fa-chevron-right"></i></button>`;
        }

        html += '</div>';
        container.innerHTML = html;
    }

    function changeQuestionPage(page) {
        const container = document.getElementById('questions-list');
        const paginationContainer = document.getElementById('questions-pagination-container');
        const offset = (page - 1) * limit;

        container.innerHTML = '<div class="text-center p-8 text-slate-500"><i class="fas fa-spinner fa-spin text-2xl"></i><br>Betöltés...</div>';
        paginationContainer.innerHTML = '';

        fetch(`ajax_get_user_questions.php?user_id=${userId}&offset=${offset}&limit=${limit}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    container.innerHTML = '';
                    if (data.data.length > 0) {
                        data.data.forEach(q => {
                            const voteColor = q.vote_score > 0 ? 'text-green-400' : 'text-slate-400';
                            const html = `
                                <div class="flex gap-4 p-4 rounded-lg bg-slate-900/40 border border-slate-800 hover:border-blue-500/30 transition-all group relative overflow-hidden animate-fade-in">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 rounded-l-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700 group-hover:bg-opacity-80 transition-colors">
                                        <i class="fas fa-question text-blue-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <a href="${q.url}" class="text-indigo-300 font-semibold hover:underline block mb-1 text-sm md:text-base">
                                            ${q.title}
                                        </a>
                                        <div class="flex flex-wrap gap-3 text-xs text-slate-500 mt-2">
                                            <span class="flex items-center gap-1"><i class="far fa-clock"></i> ${q.created_at}</span>
                                            <span class="flex items-center gap-1 text-slate-400"><i class="far fa-eye"></i> ${q.view_count}</span>
                                            <span class="flex items-center gap-1 ${voteColor}"><i class="fas fa-chevron-up"></i> ${q.vote_score}</span>
                                            <span class="flex items-center gap-1 text-indigo-400"><i class="fas fa-comment-dots"></i> ${q.answer_count} válasz</span>
                                        </div>
                                    </div>
                                </div>
                            `;
                            container.insertAdjacentHTML('beforeend', html);
                        });
                    } else {
                        container.innerHTML = '<div class="text-slate-500 text-center py-4">Nincs feltett kérdés.</div>';
                    }
                    renderGenericPagination(data.total_pages, data.current_page, 'questions-pagination-container', 'changeQuestionPage');
                } else {
                    container.innerHTML = '<div class="text-red-500 text-center">Hiba történt.</div>';
                }
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<div class="text-red-500 text-center">Hálózati hiba.</div>';
            });
    }

    function changeAnswerPage(page) {
        const container = document.getElementById('answers-list');
        const paginationContainer = document.getElementById('answers-pagination-container');
        const offset = (page - 1) * limit;

        container.innerHTML = '<div class="text-center p-8 text-slate-500"><i class="fas fa-spinner fa-spin text-2xl"></i><br>Betöltés...</div>';
        paginationContainer.innerHTML = '';

        fetch(`ajax_get_user_answers.php?user_id=${userId}&offset=${offset}&limit=${limit}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    container.innerHTML = '';
                    if (data.data.length > 0) {
                        data.data.forEach(a => {
                            const voteColor = a.vote_score > 0 ? 'text-green-400' : 'text-slate-400';
                            const acceptedHtml = a.is_accepted ? '<span class="text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20"><i class="fas fa-check"></i> Elfogadott</span>' : '';
                            const html = `
                                <div class="flex gap-4 p-4 rounded-lg bg-slate-900/40 border border-slate-800 hover:border-emerald-500/30 transition-all group relative overflow-hidden animate-fade-in">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500 rounded-l-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700 group-hover:bg-opacity-80 transition-colors">
                                        <i class="fas fa-reply text-emerald-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm text-slate-300 mb-2">
                                            Válasz erre: <a href="${a.question_url}" class="text-emerald-400 hover:underline font-medium">${a.question_title}</a>
                                        </div>
                                        <p class="text-xs text-slate-400 line-clamp-2 mb-2 italic">
                                            "${a.content}..."
                                        </p>
                                        <div class="flex flex-wrap gap-3 text-xs text-slate-500">
                                            <span class="flex items-center gap-1"><i class="far fa-clock"></i> ${a.created_at}</span>
                                            <span class="flex items-center gap-1 ${voteColor}"><i class="fas fa-chevron-up"></i> ${a.vote_score}</span>
                                            ${acceptedHtml}
                                        </div>
                                        <a href="${a.answer_url}" class="absolute inset-0 z-10"></a>
                                    </div>
                                </div>
                            `;
                            container.insertAdjacentHTML('beforeend', html);
                        });
                    } else {
                        container.innerHTML = '<div class="text-slate-500 text-center py-4">Nincs válasz.</div>';
                    }
                    renderGenericPagination(data.total_pages, data.current_page, 'answers-pagination-container', 'changeAnswerPage');
                } else {
                    container.innerHTML = '<div class="text-red-500 text-center">Hiba történt.</div>';
                }
            })
            .catch(err => {
                console.error(err);
                container.innerHTML = '<div class="text-red-500 text-center">Hálózati hiba.</div>';
            });
    }

    function renderGenericPagination(totalPages, currentPage, containerId, pageFunction) {
        const container = document.getElementById(containerId);
        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<div class="flex justify-center gap-2">';

        if (currentPage > 1) {
            html += `<button onclick="${pageFunction}(${currentPage - 1})" class="px-3 py-1 bg-slate-800 border border-slate-700 text-slate-300 rounded hover:bg-slate-700 transition-colors"><i class="fas fa-chevron-left"></i></button>`;
        }

        const range = [];
        if (totalPages <= 7) {
            for (let i = 1; i <= totalPages; i++) range.push(i);
        } else {
            if (currentPage <= 4) {
                range.push(1, 2, 3, 4, 5, '...', totalPages);
            } else if (currentPage >= totalPages - 3) {
                range.push(1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages);
            } else {
                range.push(1, '...', currentPage - 1, currentPage, currentPage + 1, '...', totalPages);
            }
        }

        range.forEach(p => {
            if (p === '...') {
                html += `<span class="px-3 py-1 text-slate-500">...</span>`;
            } else {
                const activeClass = (p === currentPage) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700';
                html += `<button onclick="${pageFunction}(${p})" class="px-3 py-1 border rounded transition-colors ${activeClass}">${p}</button>`;
            }
        });

        if (currentPage < totalPages) {
            html += `<button onclick="${pageFunction}(${currentPage + 1})" class="px-3 py-1 bg-slate-800 border border-slate-700 text-slate-300 rounded hover:bg-slate-700 transition-colors"><i class="fas fa-chevron-right"></i></button>`;
        }

        html += '</div>';
        container.innerHTML = html;
    }

    // --- BOOKMARK FUNCTIONS ---
    let bookmarksPage = 1;
    let bookmarksLimit = 5;
    let bookmarksSearch = '';

    function loadAllBookmarks() {
        // Detect Mobile (< 1024px)
        const isMobile = window.innerWidth < 1024;
        let viewContainer;

        if (isMobile) {
            // Mobile Logic: Toggle Left Column Widget
            const sidebarWidget = document.getElementById('sidebar-bookmarks-widget');
            const mobileView = document.getElementById('mobile-bookmarks-view');
            const desktopView = document.getElementById('profile-bookmarks-view');

            // Clear desktop view to avoid ID conflicts
            if (desktopView) desktopView.innerHTML = '';

            sidebarWidget.classList.add('hidden');
            mobileView.classList.remove('hidden');
            viewContainer = mobileView;
        } else {
            // Desktop Logic: Toggle Right Column Content
            const mainContent = document.getElementById('profile-main-content');
            const desktopView = document.getElementById('profile-bookmarks-view');
            const mobileView = document.getElementById('mobile-bookmarks-view');
            const sidebarWidget = document.getElementById('sidebar-bookmarks-widget'); // Ensure widget is visible if resizing

            // Clear mobile view to avoid ID conflicts
            if (mobileView) {
                mobileView.innerHTML = '';
                mobileView.classList.add('hidden');
            }
            if (sidebarWidget) sidebarWidget.classList.remove('hidden');

            mainContent.classList.add('hidden');
            desktopView.classList.remove('hidden');
            viewContainer = desktopView;
        }

        // Render Structure
        viewContainer.innerHTML = `
            <div class="bg-slate-800/30 border border-slate-700 rounded-none p-6 relative">
                <button onclick="closeBookmarksView()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-800 border border-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors shadow-lg z-10" title="Bezárás">
                    <i class="fas fa-times"></i>
                </button>

                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 pr-10">
                    <h4 class="text-white font-bold flex items-center gap-2">
                        <i class="fas fa-bookmark text-orange-400"></i> Könyvjelzők
                    </h4>
                    <div class="relative w-full md:w-64">
                        <input type="text" id="bookmark-search" placeholder="Keresés..."
                               class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:border-orange-500 transition-colors"
                               onkeyup="handleBookmarkSearch(this.value)">
                        <i class="fas fa-search absolute left-3 top-3 text-slate-500"></i>
                    </div>
                </div>

                <div id="all-bookmarks-list" class="space-y-3 min-h-[200px]">
                    <div class="text-center text-slate-500 py-8"><i class="fas fa-spinner fa-spin text-2xl"></i><br>Betöltés...</div>
                </div>

                <div id="all-bookmarks-pagination" class="mt-6 text-center"></div>
            </div>
        `;

        fetchBookmarks(1);
    }

    function closeBookmarksView() {
        const isMobile = window.innerWidth < 1024;

        if (isMobile) {
            // Restore Mobile
            const sidebarWidget = document.getElementById('sidebar-bookmarks-widget');
            const mobileView = document.getElementById('mobile-bookmarks-view');

            mobileView.innerHTML = ''; // Cleanup
            mobileView.classList.add('hidden');
            sidebarWidget.classList.remove('hidden');
        } else {
            // Restore Desktop
            const mainContent = document.getElementById('profile-main-content');
            const desktopView = document.getElementById('profile-bookmarks-view');

            desktopView.innerHTML = ''; // Cleanup
            desktopView.classList.add('hidden');
            mainContent.classList.remove('hidden');
        }
    }

    let searchTimeout;
    function handleBookmarkSearch(value) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            bookmarksSearch = value;
            fetchBookmarks(1);
        }, 300);
    }

    function fetchBookmarks(page) {
        bookmarksPage = page;
        const listContainer = document.getElementById('all-bookmarks-list');
        const offset = (page - 1) * bookmarksLimit;

        if (!listContainer) return;

        // Visual fade effect
        listContainer.style.opacity = '0.5';

        fetch(`ajax_get_bookmarks.php?user_id=${userId}&offset=${offset}&limit=${bookmarksLimit}&search=${encodeURIComponent(bookmarksSearch)}`)
            .then(res => res.json())
            .then(data => {
                listContainer.style.opacity = '1';

                if (data.success) {
                    if (data.bookmarks.length > 0) {
                        let html = '';
                        data.bookmarks.forEach(bm => {
                            // XSS Protection: Escape title
                            const safeTitle = bm.title.replace(/&/g, "&amp;")
                                .replace(/</g, "&lt;")
                                .replace(/>/g, "&gt;")
                                .replace(/"/g, "&quot;")
                                .replace(/'/g, "&#039;");

                            html += `
                                <div class="flex items-center justify-between gap-4 p-4 rounded-lg bg-orange-900/10 border border-orange-500/10 hover:border-orange-500/30 hover:bg-orange-900/20 transition-all group animate-fade-in">
                                    <div class="flex-1 min-w-0">
                                        <a href="${bm.url}" class="text-orange-200/80 group-hover:text-orange-100 font-semibold transition-colors block mb-1 truncate" title="${safeTitle}">
                                            ${safeTitle}
                                        </a>
                                        <div class="text-xs text-slate-500 flex items-center gap-3">
                                            <span class="flex items-center gap-1"><i class="far fa-clock"></i> Mentve: ${bm.time_elapsed}</span>
                                        </div>
                                    </div>
                                    <button onclick="deleteBookmark(${bm.question_id}, this, true)" class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-slate-500 hover:text-red-400 hover:bg-slate-700 transition-colors border border-slate-700 shadow-lg" title="Törlés a könyvjelzők közül">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            `;
                        });
                        listContainer.innerHTML = html;
                    } else {
                        listContainer.innerHTML = '<div class="text-center text-slate-500 py-8 italic">Nincs találat.</div>';
                    }

                    // Render Pagination
                    const totalPages = Math.ceil(data.total_count / bookmarksLimit);
                    renderGenericPagination(totalPages, bookmarksPage, 'all-bookmarks-pagination', 'fetchBookmarks');

                } else {
                    listContainer.innerHTML = '<div class="text-red-500 text-center">Hiba történt.</div>';
                }
            })
            .catch(err => {
                console.error(err);
                listContainer.innerHTML = '<div class="text-red-500 text-center">Hálózati hiba.</div>';
            });
    }

    // Common Delete Function
    function deleteBookmark(questionId, btn, refreshList = false) {
        // Prevent accidental double clicks
        if (btn.disabled) return;

        // Confirmation is nice but maybe annoying for bookmarks? The prompt asked for "törölhető is legyen".
        // Let's just do it with a nice animation.

        btn.disabled = true;
        const originalContent = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        const formData = new FormData();
        formData.append('question_id', questionId);

        fetch('ajax_bookmark.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.status === 'removed') {
                if (refreshList) {
                    // If in "All bookmarks" view, refresh the list to maintain pagination
                    // Or remove element visually first
                    const item = btn.closest('.animate-fade-in');
                    if (item) {
                        item.style.transition = 'all 0.3s';
                        item.style.opacity = '0';
                        item.style.transform = 'translateX(20px)';
                        setTimeout(() => fetchBookmarks(bookmarksPage), 300);
                    } else {
                        fetchBookmarks(bookmarksPage);
                    }
                } else {
                    // Sidebar widget logic
                    // Remove from DOM
                    const li = btn.closest('li');
                    if (li) {
                        li.style.transition = 'all 0.3s';
                        li.style.opacity = '0';
                        li.style.transform = 'translateX(20px)';
                        setTimeout(() => li.remove(), 300);
                    }
                }
            } else {
                alert('Hiba történt: ' + (data.message || 'Ismeretlen hiba'));
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = originalContent;
        });
    }

    // Mapping for sidebar calls
</script>
<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
</style>
</body>
</html>