<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser($pdo);
$q_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM qc_questions WHERE id = ?");
$stmt->execute([$q_id]);
$question = $stmt->fetch();

if (!$question) {
    // die("Kérdés nem található.");
}

// Handle Answer Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['answer_content']) && $currentUser) {
    $content = clean_html(trim($_POST['answer_content']));
    if ($content) {
        $stmt = $pdo->prepare("INSERT INTO qc_answers (question_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$q_id, $currentUser['user_id'], $content]);
        // Refresh to show answer
        header("Location: " . generateUrl('question', $q_id, $question['slug']));
        exit;
    }
}

// Handle Voting / Accepting (Simplified: using GET or hidden forms would be better, sticking to structure)
// Ideally AJAX. I will rely on the forms or simple links with params if I can modify them.
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <?php
    if ($question) {
        $seoData = getSeoData($pdo, 'question', $q_id);
        // JSON-LD
        $jsonLd = [
            "@context" => "https://schema.org",
            "@type" => "QAPage",
            "mainEntity" => [
                "@type" => "Question",
                "name" => $question['title'],
                "text" => strip_tags($question['content']),
                "answerCount" => 0, // Update later
                "upvoteCount" => $question['vote_score'],
                "author" => [
                    "@type" => "Person",
                    "name" => $question['guest_name'] ?? 'User' // Fetch real user if needed
                ]
            ]
        ];
        // Note: Author fetching omitted for brevity in head block, will be in body.

        renderSeoHead($seoData);
        echo '<script type="application/ld+json">' . json_encode($jsonLd) . '</script>';
    } else {
        echo '<title>Kérdés nem található</title>';
    }
    ?>
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
<!--
================================================================================
   SILVER PC - MASTER CIKK MODUL (FINAL SEO + MOBILE FIX)
   - Visszakerült az ASIDE tag (SEO Best Practice)
   - VISSZAKERÜLT AZ ÖSSZES INLINE SEO ATTRIBÚTUM (itemprop, articleBody, stb.)
   - CSS Reset a theme ütközések ellen
   - Javított Flexbox elrendezés mobilon
================================================================================
-->
<!-- HTML KEZDETE -->
<main id="main" class="site-main">

    <!-- SEO: Fő konténer mint NewsArticle -->
    <div class="cikk-fo-container" itemscope itemtype="http://schema.org/NewsArticle">
































































        <!-- SEO: URL és Dátum meta adatok (A kérésednek megfelelően itt vannak!) -->
        <meta itemprop="mainEntityOfPage" itemType="https://schema.org/WebPage" itemid="JELENLEGI_OLDAL_URL_JE"/>



        <div class="cikk-fo-layout">

            <!-- BAL OSZLOP -->
            <div class="cikk-fo-col-left" style="background: linear-gradient(135deg, rgba(11, 25, 30, 0.9), rgba(10, 15, 20, 0.9));">













































































   




















 








  

















 <style>

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

        .q_v_video_container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 arány */
            height: 0;
            margin: 15px 0;
            background: #000;
            border-radius: 4px;
            overflow: hidden;
        }
        .q_v_video_container iframe {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%; border: none;
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

        /* RESPONSIVE JAVÍTÁSOK */
        @media (max-width: 768px) {
            .q_v_wrapper { padding: 0; background: white; }
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
            
            .q_v_vote_box { width: 40px; padding-top: 10px; }
            .q_v_vote_btn { font-size: 1.2rem; }
            .q_v_vote_count { font-size: 1rem; }
            
            .q_v_q_footer { flex-direction: column; }
            .q_v_btn { width: 100%; justify-content: center; }

            /* MOBIL VÁLASZ META JAVÍTÁS */
            .q_v_answer_meta {
                flex-direction: column; /* Egymás alá */
                align-items: flex-start;
                gap: 8px;
            }
            
            /* Hogy a dátum ne csússzon be a név alá túl szorosan */
            .q_v_answer_meta > span { /* Dátum */
                font-size: 0.75rem;
                margin-top: 0;
            }
        }

    </style>











<div class="q_v_wrapper">
    <?php if ($question):
        $cat_stmt = $pdo->prepare("SELECT * FROM qc_categories WHERE id = ?");
        $cat_stmt->execute([$question['category_id']]);
        $cat = $cat_stmt->fetch();

        $asker_name = $question['guest_name'] ?? 'Vendég';
        if ($question['user_id']) {
            $u_stmt = $pdo->prepare("SELECT * FROM qc_users WHERE user_id = ?");
            $u_stmt->execute([$question['user_id']]);
            $asker = $u_stmt->fetch();
            if ($asker) $asker_name = $asker['username'];
        }

        $ans_stmt = $pdo->prepare("SELECT * FROM qc_answers WHERE question_id = ? ORDER BY is_accepted DESC, vote_score DESC, created_at ASC");
        $ans_stmt->execute([$q_id]);
        $answers = $ans_stmt->fetchAll();
    ?>

    <div class="q_v_breadcrumb">
        <a href="index.php">Főoldal</a> <span>/</span>
        <?php if($cat): ?><a href="<?php echo generateUrl('category', $cat['id'], $cat['slug']); ?>"><?php echo htmlspecialchars($cat['name']); ?></a> <span>/</span><?php endif; ?>
        <?php echo htmlspecialchars($question['title']); ?>
    </div>

    <div class="q_v_question_card">

        <div class="q_v_q_header">
            <div class="q_v_q_meta_top">
                <?php if($question['status'] == 'solved'): ?>
                    <span class="q_v_badge q_v_badge_solved"><i class="fa-solid fa-check"></i> Megoldva</span>
                <?php elseif($question['status'] == 'closed'): ?>
                    <span class="q_v_badge q_v_badge_closed"><i class="fa-solid fa-lock"></i> Lezárva</span>
                <?php else: ?>
                    <span class="q_v_badge q_v_badge_waiting"><i class="fa-solid fa-question"></i> Nyitott</span>
                <?php endif; ?>
                <span class="q_v_q_date"><i class="fa-regular fa-clock"></i> <?php echo time_elapsed_string($question['created_at']); ?></span>
                <span class="q_v_q_date"><i class="fa-regular fa-eye"></i> <?php echo $question['view_count']; ?> megtekintés</span>
            </div>
            <h1 class="q_v_q_title"><?php echo htmlspecialchars($question['title']); ?></h1>
        </div>

        <div class="q_v_q_body_grid">
            <div class="q_v_q_content">
                <div class="q_v_text" id="mainQuestionText">
                    <?php echo $question['content']; ?>
                </div>
                <button class="q_v_read_more_btn" onclick="toggleReadMore()">
                    <i class="fa-solid fa-angles-down"></i> Teljes tartalom megjelenítése
                </button>
            </div>

            <div class="q_v_user_sidebar">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($asker_name); ?>&background=4f46e5&color=fff" alt="Avatar" class="q_v_user_avatar">
                <div class="q_v_mobile_user_details">
                    <a href="#" class="q_v_user_name"><?php echo htmlspecialchars($asker_name); ?></a>
                    <span class="q_v_user_rank"><?php echo isset($asker) ? htmlspecialchars($asker['custom_title'] ?? 'Tag') : 'Vendég'; ?></span>
                    <?php if($question['is_guest']): ?><span class="q_v_op_badge">Vendég</span><?php endif; ?>
                </div>
                <?php if(isset($asker)): ?>
                <div class="q_v_user_stats">
                    <span class="q_v_stat_item"><b><?php echo $asker['reputation_points']; ?></b>Pont</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="q_v_q_footer">
            <a href="tartalom_jelentese.php?type=question&id=<?php echo $question['id']; ?>" class="q_v_btn q_v_btn_danger"><i class="fa-solid fa-flag"></i> Jelentés</a>
            <button class="q_v_btn q_v_btn_primary" onclick="document.getElementById('replyEditor').scrollIntoView({behavior: 'smooth'})">
                <i class="fa-solid fa-reply"></i> Válasz írása
            </button>
        </div>
    </div>

    <div class="q_v_answers_section">
        <div class="q_v_answers_header">
            <div class="q_v_answers_count"><?php echo count($answers); ?> Válasz</div>
        </div>

        <?php foreach ($answers as $ans):
            $ans_user_name = $ans['guest_name'] ?? 'Vendég';
            $ans_user_title = 'Tag';
            $ans_user_color = '76a91d';
            if ($ans['user_id']) {
                $au_stmt = $pdo->prepare("SELECT * FROM qc_users WHERE user_id = ?");
                $au_stmt->execute([$ans['user_id']]);
                $a_user = $au_stmt->fetch();
                if ($a_user) {
                    $ans_user_name = $a_user['username'];
                    $ans_user_title = $a_user['custom_title'] ?? 'Tag';
                }
            }
            $is_solution = $ans['is_accepted'];
            $card_class = $is_solution ? 'q_v_answer_solution' : '';
            if ($ans['is_ai']) $card_class .= ' q_v_answer_ai';
        ?>
        <div class="q_v_answer_card <?php echo $card_class; ?>">
            <?php if($is_solution): ?>
                <div class="q_v_solution_banner"><i class="fa-solid fa-check"></i> Megoldás</div>
            <?php endif; ?>

            <div class="q_v_vote_box">
                <a href="#" class="q_v_vote_btn"><i class="fa-solid fa-caret-up"></i></a>
                <span class="q_v_vote_count"><?php echo $ans['vote_score']; ?></span>
                <a href="#" class="q_v_vote_btn"><i class="fa-solid fa-caret-down"></i></a>
            </div>

            <div class="q_v_answer_main">
                <div class="q_v_answer_meta">
                    <div class="q_v_answer_user">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($ans_user_name); ?>&background=<?php echo $ans_user_color; ?>&color=fff" class="q_v_answer_avatar">
                        <span class="q_v_user_name" style="font-size: 0.95rem;"><?php echo htmlspecialchars($ans_user_name); ?></span>
                        <span style="color:#64748b; font-size: 0.8rem;">• <?php echo htmlspecialchars($ans_user_title); ?></span>
                    </div>
                    <span style="color:#94a3b8; font-size: 0.8rem;"><?php echo time_elapsed_string($ans['created_at']); ?></span>
                </div>

                <div class="q_v_text">
                    <?php echo $ans['content']; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="q_v_editor_section" id="replyEditor">
        <span class="q_v_editor_title">Válasz írása</span>
        <form method="POST" onsubmit="return prepareAnswerSubmit()">
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

            <div class="q_v_editor_content" id="editorContent" contenteditable="true" placeholder="Írd ide a válaszod..."></div>
            <input type="hidden" name="answer_content" id="hiddenAnswerContent">

            <div class="q_v_editor_footer">
                <span class="q_v_char_count" id="charCount">0 / 2500 karakter</span>
                <button type="submit" class="q_v_btn q_v_btn_primary">Válasz elküldése</button>
            </div>
        </form>
    </div>

    <?php else: ?>
        <p>A kérdés nem található.</p>
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

        /* --- VISUAL EDITOR LOGIKA --- */
        const editor = document.getElementById('editorContent');
        const countDisplay = document.getElementById('charCount');
        const maxChars = 2500;
        
        let lastRange = null;

        function saveSelection() {
            const sel = window.getSelection();
            if (sel.getRangeAt && sel.rangeCount) {
                lastRange = sel.getRangeAt(0);
            }
        }

        if(editor) {
            editor.addEventListener('keyup', saveSelection);
            editor.addEventListener('mouseup', saveSelection);
            editor.addEventListener('focus', saveSelection);

            editor.addEventListener('input', function() {
                const currentLength = this.innerText.length;
                countDisplay.textContent = `${currentLength} / ${maxChars} karakter`;

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
            if(editor) editor.focus();
        }

        function insertMedia(type) {
            let url;
            if (type === 'image') {
                url = prompt("Kérlek add meg a kép URL címét:");
                if (url) {
                    formatText('insertImage', url);
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
            if(picker) picker.classList.toggle('active');
        }

        function insertEmoji(emoji) {
            if(editor) editor.focus();
            if (lastRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(lastRange);
            }
            document.execCommand('insertText', false, emoji);
            saveSelection();
        }

        // Klickre záródjon az emoji panel ha máshova kattintunk
        document.addEventListener('click', function(e) {
            const picker = document.getElementById('emojiPicker');
            const btn = document.querySelector('.q_v_tool_btn[title="Emoji"]');
            if (picker && btn && !picker.contains(e.target) && !btn.contains(e.target)) {
                picker.classList.remove('active');
            }
        });

        function prepareAnswerSubmit() {
            document.getElementById('hiddenAnswerContent').value = document.getElementById('editorContent').innerHTML;
            return true;
        }
    </script>








































































































                <!-- SEO: ARTICLE BODY - Itt a tartalom -->
                <div>




<?php // include __DIR__ . '/inc/widget/cikk_torzs_hasonlo_cikkek.php'; ?>

<!-- **Fontos:**
Ez a sor a `cikk.php` fájlban **azután** legyen, hogy a `$result` változót már lekérted az adatbázisból (a fájlod elején lévő `// 3. FŐ LEKÉRDEZÉS` rész után), mert a widget a `$result['post_date']` és `$result['slug']` adatokat használja a mappa megtalálásához.

### Miért jobb ez így?
1.  **Szeparáció:** A `cikk.php` tiszta marad, nem rondítja el 200 sornyi logika.
2.  **Sebesség:** 0 adatbázis lekérdezés történik a cikk megjelenítésekor (az ajánló miatt).
3.  **Biztonság:** Ha a generátor még nem futott le, vagy hibás a fájl, a `if (!empty($related_data))` feltétel miatt **semmi** nem kerül ki a kódba, nem lesz üres HTML doboz.

                -->





























































































                </div>





































		</div>
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























         <script>
        (function() {
            // --- ADATOK és KONFIGURÁCIÓ ---

            // Ikonok SVG formátumban
            const icons = {
                zap: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
                cpu: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>',
                handshake: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg>',
                triangle: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
                skull: '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"></circle><circle cx="15" cy="12" r="1"></circle><path d="M8 20v2h8v-2"></path><path d="M12.5 17l-.5-4"></path><path d="M16 20a2 2 0 0 0 1.56-3.25 8 8 0 1 0-11.12 0A2 2 0 0 0 8 20"></path></svg>'
            };

            // Kezdeti állapot
            let state = {
                hasVoted: false,
                selectedId: null,
                totalVotes: 3428,
                options: [
                    { id: 1, text: "Aranykor: Az AI megoldja a gondokat.", votes: 850, icon: icons.zap, colorClass: "sw_color_emerald", bgClass: "sw_bg_emerald" },
                    { id: 2, text: "Eszköz marad: Csak egy okos asszisztens.", votes: 1120, icon: icons.cpu, colorClass: "sw_color_blue", bgClass: "sw_bg_blue" },
                    { id: 3, text: "Szimbiózis: Ember és gép egybeolvad.", votes: 640, icon: icons.handshake, colorClass: "sw_color_violet", bgClass: "sw_bg_violet" },
                    { id: 4, text: "Gazdasági válság: Elveszi a munkánkat.", votes: 590, icon: icons.triangle, colorClass: "sw_color_amber", bgClass: "sw_bg_amber" },
                    { id: 5, text: "Ítéletnap: Az AI átveszi a hatalmat.", votes: 228, icon: icons.skull, colorClass: "sw_color_red", bgClass: "sw_bg_red" }
                ]
            };

            // DOM elemek
            const container = document.getElementById('szavazas_widget_options_container');
            const totalCountEl = document.getElementById('szavazas_widget_total_count');

            // --- FÜGGVÉNYEK ---

            function render() {
                // Törlés
                container.innerHTML = '';

                // Összesítés frissítése
                totalCountEl.textContent = state.totalVotes.toLocaleString();

                // Renderelés
                state.options.forEach((option) => {
                    const percent = state.totalVotes === 0 ? 0 : ((option.votes / state.totalVotes) * 100).toFixed(1);
                    const isSelected = state.selectedId === option.id;

                    // Gomb létrehozása
                    const btn = document.createElement('button');
                    btn.className = `szavazas_widget_btn ${isSelected ? 'active' : ''} ${state.hasVoted ? 'disabled show-results' : ''}`;
                    if (state.hasVoted) btn.disabled = true;

                    // Click esemény
                    btn.onclick = () => handleVote(option.id);

                    // HTML tartalom felépítése
                    btn.innerHTML = `
                        <!-- Progress háttér -->
                        <div class="szavazas_widget_progress ${option.bgClass}" style="width: ${state.hasVoted ? percent : 0}%"></div>

                        <!-- Tartalom -->
                        <div class="szavazas_widget_content">
                            <div class="szavazas_widget_icon_box ${state.hasVoted ? option.colorClass : 'sw_text_slate'}">
                                ${option.icon}
                            </div>
                            <div class="szavazas_widget_text_col">
                                <div class="szavazas_widget_row_top">
                                    <span class="szavazas_widget_label">${option.text.split(':')[0]}</span>
                                    <span class="szavazas_widget_percent">${percent}%</span>
                                </div>
                                <div class="szavazas_widget_desc_text" style="font-size: 0.75rem; color: #64748b; ${state.hasVoted ? 'display:none' : 'display:block'}">
                                     ${option.text.split(':')[1] || ''}
                                </div>
                                <div class="szavazas_widget_count">
                                    ${option.votes.toLocaleString()} szavazat
                                </div>
                            </div>
                        </div>
                    `;
                    container.appendChild(btn);
                });
            }

            function handleVote(id) {
                if (state.hasVoted) return;

                // Szavazat hozzáadása
                state.options = state.options.map(opt => {
                    if (opt.id === id) {
                        return { ...opt, votes: opt.votes + 1 };
                    }
                    return opt;
                });

                state.totalVotes++;
                state.selectedId = id;
                state.hasVoted = true;

                // ÁTRENDEZÉS (Legtöbb szavazat felül)
                // Egy kis késleltetéssel, hogy lássa a klikkelést
                render(); // Először rendereljük a klikkelést (progress bar elindul)

                setTimeout(() => {
                    // Adatok rendezése csökkenő sorrendbe
                    state.options.sort((a, b) => b.votes - a.votes);

                    // Újrarenderelés a rendezett listával
                    // Itt egy "fade" effektet is lehetne csinálni, de az egyszerűség kedvéért most újraépítjük
                    render();
                }, 600); // 600ms után rendeződik át
            }

            // Indítás
            render();

        })();
    </script>
































   <!-- alsó ajánló -->




  <!--
      FŐ KONTÉNER
      Szögletes (rounded-none), lila háttérrel (bg-[#1e1b4b])
    -->
      <style>
.elvalaszto_also_ {
    border: 0;
    height: 2px;
    width: 100%;
margin-top: 2px;
    background-color: #007bff; /* Kék szín */
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 123, 255, 0.5); /* Ragyogás effekt */
}
} </style>


<style>
/* Ez a mozgó doboz */
.theiaStickySidebar {
    position: relative;
    width: 100%;       /* Kitölti a sínt */
    padding-bottom: 1px; /* Trükk: néha a marginok összeolvadnak, ez megakadályozza */

    /* Hardveres gyorsítás a remegés ellen */
    will-change: transform;
    transform: translate3d(0, 0, 0);
    transition: none; /* TILOS transition-t tenni rá, attól késik/remeg! */
}


</style>


<script>
document.addEventListener("DOMContentLoaded", function() {

    const CONFIG = {
        sidebarSelector: '.cikk-fo-col-right',
        contentSelector: '.theiaStickySidebar',
        paddingTop: 20,
        paddingBottom: 40,
        minWidth: 768
    };

    const sidebar = document.querySelector(CONFIG.sidebarSelector);
    const sidebarContent = document.querySelector(CONFIG.contentSelector);

    if (!sidebar || !sidebarContent) return;

    // --- CACHE VÁLTOZÓK (Hogy ne kelljen mindig mérni) ---
    let state = {
        sidebarTop: 0,       // A sáv abszolút pozíciója az oldal tetejétől
        sidebarHeight: 0,    // A sáv magassága
        contentHeight: 0,    // A tartalom magassága
        maxTranslate: 0,     // Mennyit mozoghat maximum
        viewportHeight: 0    // Ablak magassága
    };

    let lastScrollTop = window.scrollY || document.documentElement.scrollTop;
    let currentTranslateY = 0;
    let isTicking = false;

    // 1. MÉRETEZÉS (Csak akkor fut, ha kell)
    function measureDimensions() {
        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const sidebarRect = sidebar.getBoundingClientRect();

        state.sidebarHeight = sidebar.offsetHeight;
        state.contentHeight = sidebarContent.offsetHeight;
        state.viewportHeight = window.innerHeight;

        // Kiszámoljuk a sáv oldalhoz viszonyított fix helyét
        state.sidebarTop = sidebarRect.top + scrollTop;

        // A maximum mozgástér
        state.maxTranslate = state.sidebarHeight - state.contentHeight - CONFIG.paddingBottom;

        // Biztonsági frissítés, ha esetleg a tartalom közben megváltozott volna és túllógnánk
        if (currentTranslateY > state.maxTranslate) {
            currentTranslateY = state.maxTranslate;
            sidebarContent.style.transform = `translate3d(0, ${currentTranslateY}px, 0)`;
        }
    }

    // 2. MOZGATÁS (Ez fut a görgetéskor - villámgyors matek)
    function onScroll() {
        if (window.innerWidth < CONFIG.minWidth) {
            sidebarContent.style.transform = '';
            isTicking = false;
            return;
        }

        const scrollTop = window.scrollY || document.documentElement.scrollTop;
        const scrollDiff = scrollTop - lastScrollTop;

        // Ha a tartalom kisebb, mint a sáv, nincs dolgunk (reset)
        if (state.contentHeight >= state.sidebarHeight) {
            sidebarContent.style.transform = '';
            lastScrollTop = scrollTop;
            isTicking = false;
            return;
        }

        // --- A LOGIKA (Ugyanaz, csak optimalizált változókkal) ---

        if (scrollDiff > 0) {
            // LEFELÉ (Down)
            const contentBottom = currentTranslateY + state.contentHeight;
            // Itt a trükk: Nem mérünk getBoundingClientRect-et, hanem számolunk:
            // Hol van a sáv teteje a képernyőhöz képest? -> (state.sidebarTop - scrollTop)
            const sidebarRectTop = state.sidebarTop - scrollTop;
            const viewportBottomInSidebar = -sidebarRectTop + state.viewportHeight;

            if (contentBottom < viewportBottomInSidebar - CONFIG.paddingBottom) {
                 currentTranslateY = Math.min(currentTranslateY + scrollDiff, state.maxTranslate);
            }

            // Korrekció "lépcsős" görgetésnél (egérgörgő)
            const minNeeded = viewportBottomInSidebar - state.contentHeight - CONFIG.paddingBottom;
            if(currentTranslateY < minNeeded) {
                currentTranslateY = Math.min(minNeeded, state.maxTranslate);
            }

        } else if (scrollDiff < 0) {
            // FELFELÉ (Up)
            const sidebarRectTop = state.sidebarTop - scrollTop;
            const viewportTopInSidebar = -sidebarRectTop;

            if (currentTranslateY > viewportTopInSidebar + CONFIG.paddingTop) {
                currentTranslateY = Math.max(currentTranslateY + scrollDiff, 0);
            }

            // Korrekció
             const maxAllowed = viewportTopInSidebar + CONFIG.paddingTop;
             if (currentTranslateY > maxAllowed) {
                 currentTranslateY = Math.max(maxAllowed, 0);
             }
        }

        // Végső határok
        currentTranslateY = Math.max(0, Math.min(currentTranslateY, state.maxTranslate));

        // Renderelés
        sidebarContent.style.transform = `translate3d(0, ${currentTranslateY}px, 0)`;

        lastScrollTop = scrollTop;
        isTicking = false;
    }

    // --- ESEMÉNYKEZELŐK ---

    // Kezdő mérés
    measureDimensions();

    window.addEventListener('scroll', function() {
        if (!isTicking) {
            window.requestAnimationFrame(onScroll);
            isTicking = true;
        }
    }, { passive: true }); // A passive: true segít a böngészőnek a simaságban

    window.addEventListener('resize', () => {
        measureDimensions();
        onScroll();
    });

    // Ha a tartalom mérete változik (pl. kinyílik egy doboz, betölt egy reklám)
    const observer = new ResizeObserver(() => {
        measureDimensions();
        onScroll(); // Azonnali korrekció
    });
    observer.observe(sidebar);
    observer.observe(sidebarContent);
});</script>









































      <hr class="elvalaszto_also_">
    <div class="w-full bg-[#1e1b4b] rounded-none shadow-2xl overflow-hidden border border-white/5">

        <!--
            ÚJ HEADER DESIGN
            Kompakt, letisztult, alacsonyabb magasság.
        -->
<?php
// 1. Bemenet tisztítása: Szóközök eltávolítása az elejéről és végéről
// A htmlspecialchars itt nem kell a logikához, csak ha kiírnánk a képernyőre.
$input_kategoria = isset($cikk_kategoriak) ? trim($cikk_kategoriak) : '';

// 2. Kategória és fájlnév összerendelés (Whitelist)
// Kulcs: Amit a $cikk_kategoriak tartalmaz (pl. "Szoftver")
// Érték: A fájl neve kiterjesztés nélkül (pl. "szoftver")
$fajl_map = [
    'Hírek'    => 'hirek',
    'Tech'     => 'tech',
    'Hardver'  => 'hardver',
    'Szoftver' => 'szoftver',
    'Mobil'    => 'mobil',
    'Gamer'    => 'gamer',
    'Játék'    => 'jatek',
    'Web'      => 'web',
    'Tudomány' => 'tudomany',
    'Egyéb'    => 'egyeb',
];

// 3. A megfelelő fájlnév kiválasztása
// Ha a kategória létezik a tömbben, visszaadja az értéket.
// Ha NEM létezik (??), akkor a 'hirek'-et adja vissza alapértelmezetten.
$fajl_nev = $fajl_map[$input_kategoria] ?? 'hirek';

// 4. A teljes elérési út összeállítása
$fajl_eleres = __DIR__ . '/inc/widget/cikk_ajanlo_kategoria/' . $fajl_nev . '.html';

// 5. Biztonságos beillesztés
// Ellenőrizzük, hogy a fájl fizikailag létezik-e, mielőtt behívjuk, hogy elkerüljük a hibákat.
if (file_exists($fajl_eleres)) {
    include $fajl_eleres;
} else {
    // Opcionális: Ha még a hirek.html sem létezne, itt kezelheted a hibát.
    // De a fenti logika szerint ez csak akkor fordulhat elő, ha hiányzik a fájl a szerverről.
}
?>





  <!-- alsó ajánló vége -->







</main>




 <!-- HTML Elemek (Csak a gomb és a csík) -->
    <div class="ugras_top_progress_container">
        <div id="ugras_top_bar" class="ugras_top_progress_bar"></div>
    </div>

    <div id="ugras_top_wrapper" class="ugras_top_button_wrapper">
        <button id="ugras_top_btn" class="ugras_top_btn" title="Vissza az oldal tetejére">
            <!-- Inline SVG Nyíl - Nem kell hozzá külső ikonkönyvtár -->
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="19" x2="12" y2="5"></line>
                <polyline points="5 12 12 5 19 12"></polyline>
            </svg>
        </button>
    </div>

    <!-- JavaScript Logika -->
    <!-- ================================================================= -->
    <!-- KÓD VÉGE -->
    <!-- ================================================================= -->






         <!-- KONTÉNER HELYE
        <div class="w-full border-2 border-dashed border-gray-800 rounded-lg h-64 flex items-center justify-center text-gray-600 font-mono">



          </div>






                    -->











    </main>










   <!-- másolás button -->
                  <div id="select_tooltip" class="select_tooltip_container">
  <button id="select_copy_btn" class="select_copy_btn">
    <svg class="select_icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
    </svg>
    <span id="select_btn_text">Másolás</span>
  </button>
  <div class="select_arrow"></div>
</div>
         <div id="copy_notification" class="copy_notification">
  <div class="notification_content">
    <svg class="notification_icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
      <polyline points="22 4 12 14.01 9 11.01"></polyline>
    </svg>
    <div class="notification_text">
      <strong>Sikeres másolás!</strong>
      <span>Most már beillesztheted bárhová (Ctrl+V).</span>
    </div>
  </div>
</div>








   <!-- másolás button vége -->



























  <!-- footer kezdet -->


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

    <!-- SCRIPTS -->
    <script>
        // 1. ÉVSZÁM GENERÁLÁS (PHP helyett, hogy a demó működjön)
        document.addEventListener('DOMContentLoaded', function() {
            const currentYear = new Date().getFullYear();
            const yearElement = document.getElementById('copyright-year');
            // Ez szimulálja azt a logikát: 2017 - [Aktuális Év]
            yearElement.textContent = `2017 - ${currentYear}`;
        });

        // 2. OLDAL BETÖLTÉSI SEBESSÉG SZIMULÁTOR
        window.addEventListener('load', function() {
            setTimeout(function() {
                // Lekérjük a navigációs időzítést
                const perfData = window.performance.timing;
                const pageLoadTime = (perfData.loadEventEnd - perfData.navigationStart) / 1000;

                // Formázás: pl. 0.12s
                const formattedTime = pageLoadTime.toFixed(3) + 's';

                const speedDisplay = document.getElementById('page-speed-display');
                speedDisplay.innerHTML = `<i class="fas fa-tachometer-alt" style="font-size: 0.8em; margin-right:8px; color: var(--cyber-teal);"></i>${formattedTime}`;

                // Szín változtatása sebesség alapján
                if(pageLoadTime < 0.5) {
                    speedDisplay.style.color = "var(--matrix-green)"; // Gyors
                } else if (pageLoadTime < 1.0) {
                    speedDisplay.style.color = "var(--cyber-teal)"; // Átlagos
                } else {
                    speedDisplay.style.color = "#ffcc00"; // Lassúbb
                }
            }, 100); // Pici késleltetés, hogy biztosan meglegyen az adat
        });
    </script>



  <!-- footer vége -->










<!-- CIKK GALÉRIA KÉPEK -->
<!-- CIKK GALÉRIA KÉPEK VÉGE -->







<script src="https://blog.silverpc.hu/v2/assets/js/share.js"></script>
<script src="https://blog.silverpc.hu/v2/assets/js/ol.js"></script>
<script src="https://blog.silverpc.hu/v2/assets/js/top-up-btn.js"></script>
<script src="https://blog.silverpc.hu/v2/assets/js/copy-btn.js"></script>
<script src="https://blog.silverpc.hu/v2/assets/js/galeria.js"></script>
<script src="https://blog.silverpc.hu/v2/assets/js/search-effect.js"></script>
</body>
</html>