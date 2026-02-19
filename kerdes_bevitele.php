<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser($pdo);

if (!$currentUser) {
    // Redirect to login or show error.
    // For module integration, maybe show a "Login Required" message.
    // die("Kérjük jelentkezz be!");
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentUser) {
    $title = trim($_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $content = clean_html(trim($_POST['content'] ?? ''));
    $description = trim($_POST['description'] ?? '');

    if ($title && $category_id && $content) {
        $slug = slugify($title);
        // Check for duplicate slug, append ID if needed (or rand)
        // Simple append random string to be safe
        $slug .= '-' . substr(md5(uniqid()), 0, 5);

        $stmt = $pdo->prepare("INSERT INTO qc_questions (user_id, category_id, title, slug, content, seo_description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$currentUser['user_id'], $category_id, $title, $slug, $content, $description]);

        $new_id = $pdo->lastInsertId();

        // Trigger AI Answer Generation in Background
        $ai_script = __DIR__ . '/ai/generate_answer.php';
        $log_file = __DIR__ . '/ai/debug.log';
        if (file_exists($ai_script)) {
            // Háttérben indítjuk el a folyamatot, a felhasználó nem érzékel ebből semmit (késleltetés nélkül)
            // A kimenetet logoljuk a hibakereséshez
            $cmd = "php " . escapeshellarg($ai_script) . " " . escapeshellarg($new_id) . " > " . escapeshellarg($log_file) . " 2>&1 &";
            exec($cmd);
        }

        // Handle Images
        if (isset($_POST['uploaded_images']) && is_array($_POST['uploaded_images'])) {
            $stmtImg = $pdo->prepare("INSERT INTO qc_question_images (question_id, image_path) VALUES (?, ?)");
            $imageCount = 0;
            foreach ($_POST['uploaded_images'] as $path) {
                if ($imageCount >= 3) break; // Strict limit: max 3 images

                // Basic validation of path to ensure it's in uploads directory
                if (strpos($path, 'uploads/images/') === 0) {
                     $stmtImg->execute([$new_id, $path]);
                     $imageCount++;
                }
            }
        }

        // Handle Poll
        if (isset($_POST['has_poll']) && $_POST['has_poll'] == 1) {
            $poll_question = trim($_POST['poll_question'] ?? '');
            $poll_options = $_POST['poll_options'] ?? [];

            // Filter empty options
            $poll_options = array_filter($poll_options, function($val) { return trim($val) !== ''; });

            if ($poll_question && count($poll_options) >= 2) {
                $stmtPoll = $pdo->prepare("INSERT INTO qc_polls (question_id, question_text) VALUES (?, ?)");
                $stmtPoll->execute([$new_id, $poll_question]);
                $poll_id = $pdo->lastInsertId();

                $stmtOpt = $pdo->prepare("INSERT INTO qc_poll_options (poll_id, option_text) VALUES (?, ?)");
                foreach ($poll_options as $opt_text) {
                    $stmtOpt->execute([$poll_id, trim($opt_text)]);
                }
            }
        }

        $url = generateUrl('question', $new_id, $slug);
        header("Location: " . $url);
        exit;
    } else {
        $error = "Kérjük tölts ki minden mezőt!";
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <?php
    $seoData = ['title' => 'Új kérdés | SilverPC Fórum', 'description' => 'Tedd fel kérdésedet a közösségnek.', 'og_type' => 'website'];
    renderSeoHead($seoData);
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
        .n_q_wrapper {
            width: 100%;
            margin: 0 auto;
            background: #f1f5f9; /* Az oldal háttérszíne */
            padding: 30px 20px;
            box-sizing: border-box;
        }

        /* Oldal Címsor */
        .n_q_page_header {
            margin-bottom: 25px;
            padding-left: 10px;
        }
        .n_q_page_title {
            font-size: 1.8rem;
            color: #1e293b;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .n_q_page_subtitle {
            color: #64748b;
            margin-top: 5px;
            font-size: 1rem;
        }

        /* Fő Kártya */
        .n_q_card {
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-top: 5px solid #4f46e5;
            padding: 40px;
            /* Éles szélek a kérésnek megfelelően */
            border-radius: 0; 
        }

        /* Űrlap Csoportok */
        .n_q_form_group {
            margin-bottom: 25px;
        }

        .n_q_label {
            display: block;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        .n_q_label span {
            color: #ef4444; /* Kötelező csillag */
            margin-left: 3px;
        }
        .n_q_helper_text {
            display: block;
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 5px;
        }

        /* Input mezők */
        .n_q_input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #cbd5e1;
            font-size: 1rem;
            color: #1e293b;
            box-sizing: border-box;
            transition: all 0.2s;
            border-radius: 0; /* Éles szél */
            font-family: inherit;
        }

        .n_q_input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Kategória Választó Grid */
        .n_q_category_grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .n_q_select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #cbd5e1;
            font-size: 1rem;
            color: #334155;
            background-color: white;
            cursor: pointer;
            border-radius: 0;
            appearance: none; /* Egyedi nyílhoz */
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
        }
        .n_q_select:focus {
            outline: none;
            border-color: #4f46e5;
        }
        .n_q_select:disabled {
            background-color: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
        }

        /* --- SZERKESZTŐ (Editor) --- */
        .n_q_editor_container {
            border: 1px solid #cbd5e1;
            background: #fff;
        }
        .n_q_editor_container:focus-within {
            border-color: #4f46e5;
            box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1);
        }

        .n_q_toolbar {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 8px;
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            position: relative; /* Fontos az Emoji panel pozicionálásához */
        }

        .n_q_tool_btn {
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            border: 1px solid transparent;
            background: transparent;
            color: #475569;
            cursor: pointer;
            border-radius: 3px;
            transition: all 0.2s;
        }
        .n_q_tool_btn:hover { background: #e2e8f0; color: #1e293b; }

        /* Emoji Picker Stílusok */
        .n_q_emoji_picker {
            display: none; /* Alapból rejtve */
            position: absolute;
            top: 45px;
            left: 0; /* Vagy igazíthatjuk gombhoz */
            background: white;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 10px;
            width: 260px;
            flex-wrap: wrap;
            gap: 5px;
            z-index: 100;
            border-radius: 4px;
        }
        .n_q_emoji_picker.active { display: flex; }
        .n_q_emoji_btn {
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
            transition: transform 0.2s;
            line-height: 1;
        }
        .n_q_emoji_btn:hover { transform: scale(1.2); background: #f1f5f9; border-radius: 4px; }

        /* Szerkesztő tartalom */
        .n_q_editor_content {
            min-height: 250px;
            padding: 15px;
            font-size: 1rem;
            line-height: 1.6;
            color: #334155;
            outline: none;
            overflow-y: auto;
        }
        
        /* Lista és egyéb elemek stílusa az editorban */
        .n_q_editor_content ul { list-style-type: disc; margin-left: 20px; }
        .n_q_editor_content ol { list-style-type: decimal; margin-left: 20px; }
        .n_q_editor_content blockquote { border-left: 3px solid #cbd5e1; padding-left: 10px; color: #64748b; font-style: italic; margin: 10px 0; }
        .n_q_editor_content img { max-width: 100%; border: 1px solid #e2e8f0; margin: 10px 0; }

        /* Videó konténer (reszponzív) */
        .n_q_video_container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 arány */
            height: 0;
            margin: 15px 0;
            background: #000;
            border-radius: 4px;
            overflow: hidden;
        }
        .n_q_video_container iframe {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%; border: none;
        }

        .n_q_editor_footer {
            padding: 8px 15px;
            background: #fcfcfc;
            border-top: 1px solid #f1f5f9;
            text-align: right;
            font-size: 0.85rem;
            color: #94a3b8;
        }

        /* Tipp doboz az alján */
        .n_q_tips_box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9rem;
            color: #1e3a8a;
        }
        .n_q_tips_title { font-weight: 700; display: block; margin-bottom: 5px; }

        /* Gombok */
        .n_q_actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 25px;
        }

        .n_q_btn {
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .n_q_btn_primary {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            color: white;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
        }
        .n_q_btn_primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(79, 70, 229, 0.4);
        }

        .n_q_btn_secondary {
            background: white;
            border: 1px solid #cbd5e1;
            color: #64748b;
        }
        .n_q_btn_secondary:hover {
            background: #f1f5f9;
            color: #334155;
            border-color: #94a3b8;
        }

        /* Reszponzív */
        @media (max-width: 768px) {
            .n_q_wrapper { padding: 0; }
            .n_q_card { padding: 20px; }
            .n_q_category_grid { grid-template-columns: 1fr; gap: 15px; } /* Mobilon egymás alá */
            .n_q_actions { flex-direction: column-reverse; } /* Gombok egymás alá, a fő gomb legyen felül/kézre */
            .n_q_btn { width: 100%; justify-content: center; }
            .n_q_page_header { padding: 20px; margin-bottom: 0; }
        }

        /* --- VALIDATION & TOAST STYLES --- */
        .n_q_input.valid {
            border-color: #22c55e !important;
            background-color: #f0fdf4 !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='%2322c55e'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
        }
        .n_q_input.invalid {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }

        .n_q_btn.n_q_btn_disabled {
            opacity: 0.6;
            cursor: not-allowed;
            filter: grayscale(0.5);
        }

        .n_q_toast {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.9);
            background: #1e293b;
            color: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            font-weight: 600;
            text-align: center;
            border-left: 5px solid #ef4444;
            width: 90%;
            max-width: 600px; /* Ensure text fits on desktop without awkward breaking */
            font-size: 1.1rem;
            line-height: 1.5;
        }
        .n_q_toast.show {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
            visibility: visible;
        }

        /* AI Suggestions */
        .n_q_ai_suggestions {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .n_q_ai_item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: border-color 0.2s;
        }
        .n_q_ai_item:hover {
            border-color: #4f46e5;
        }
        .n_q_ai_text {
            flex: 1;
            margin-right: 15px;
            font-size: 0.9rem;
            color: #334155;
            line-height: 1.4;
        }
        .n_q_ai_actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .n_q_ai_btn {
            cursor: pointer;
            border: none;
            background: transparent;
            font-size: 1.2rem;
            transition: transform 0.2s, color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
        }
        .n_q_ai_btn:hover {
            background-color: #f1f5f9;
            transform: scale(1.1);
        }
        .n_q_ai_btn_accept {
            color: #22c55e;
        }
        .n_q_ai_btn_reject {
            color: #ef4444;
        }
        .n_q_ai_loading {
            font-size: 0.9rem;
            color: #64748b;
            font-style: italic;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: #f8fafc;
            border-radius: 4px;
        }

        /* Moderation Feedback Styles */
        #moderation-feedback {
            color: #1e293b !important; /* Ensure text is visible (black/dark slate) */
        }
        #moderation-feedback b,
        #moderation-feedback strong {
            color: #ef4444 !important; /* Make "Elutasítva:" red */
        }

        /* Context Prompt */
        .n_q_prompt_box {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            padding: 20px 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.4);
            animation: slideDown 0.5s ease-out;
        }
        @keyframes slideDown { from { opacity:0; transform:translateY(-20px); } to { opacity:1; transform:translateY(0); } }
        .n_q_prompt_btn_yes {
            background: white;
            color: #4f46e5;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }
        .n_q_prompt_btn_yes:hover { background: #f1f5f9; transform: scale(1.05); }
        .n_q_prompt_btn_no {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.4);
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .n_q_prompt_btn_no:hover { background: rgba(255,255,255,0.3); }
    </style>
                                        
                                        
  







 <div class="n_q_wrapper">

        <div id="categoryPrompt" class="n_q_prompt_box" style="display:none;">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; align-items:center; gap:15px;">
                    <div style="background:rgba(255,255,255,0.2); padding:10px; border-radius:50%;"><i class="fa-solid fa-lightbulb" style="font-size:1.5rem; color:white;"></i></div>
                    <span id="categoryPromptText" style="font-size:1.1rem; font-weight:600;"></span>
                </div>
                <div style="display:flex; gap:10px;">
                    <button type="button" id="promptYes" class="n_q_prompt_btn_yes">Igen, kérem</button>
                    <button type="button" id="promptNo" class="n_q_prompt_btn_no">Nem, köszi</button>
                </div>
            </div>
        </div>

        <div class="n_q_page_header">
            <h1 class="n_q_page_title"><i class="fa-solid fa-pen-to-square" style="color: #4f46e5;"></i> Új kérdés feltevése</h1>
            <p class="n_q_page_subtitle">Kérj segítséget a közösségtől! Töltsd ki az alábbi űrlapot.</p>
        </div>

        <?php if (!$currentUser): ?>
            <div class="n_q_card" style="text-align:center; padding: 40px;">
                <h2 style="color: #334155;">Kérjük jelentkezz be a kérdésfeltevéshez!</h2>
            </div>
        <?php else: ?>

        <form method="POST" id="questionForm" onsubmit="return prepareSubmit()">
        <div class="n_q_card">
            
            <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

            <div class="n_q_form_group">
                <label class="n_q_label">Kategória választás <span>*</span></label>
                <div class="n_q_category_grid">
                    <div>
                        <select class="n_q_select" id="mainCategory" onchange="updateSubCategories()">
                            <option value="" disabled selected>Válassz főkategóriát...</option>
                            <?php
                            $main_cats = $pdo->query("SELECT * FROM qc_categories WHERE parent_id IS NULL ORDER BY id ASC")->fetchAll();
                            foreach ($main_cats as $mc) {
                                echo '<option value="'.$mc['id'].'">'.htmlspecialchars($mc['name']).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <select class="n_q_select" id="subCategory" name="category_id" disabled required>
                            <option value="" disabled selected>Először válassz főkategóriát...</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="n_q_form_group">
                <label class="n_q_label">Kérdés címe <span>*</span></label>
                <input type="text" name="title" class="n_q_input" placeholder="Pl.: Nem indul a gépem RAM csere után, mit tegyek?" required>
            </div>

            <div class="n_q_form_group">
                <label class="n_q_label">Rövid leírás / Összefoglaló</label>
                <input type="text" name="description" class="n_q_input" maxlength="150" placeholder="Pl.: Nem indul a gépem RAM csere után, mit tegyek?">
                <div id="ai-suggestions-container" class="n_q_ai_suggestions"></div>
            </div>

            <div class="n_q_form_group">
                <label class="n_q_label">Részletes tartalom <span>*</span></label>
                
                <div class="n_q_editor_container">
                    <div class="n_q_toolbar">
                        <button type="button" class="n_q_tool_btn" title="Félkövér" onclick="formatText('bold')"><i class="fa-solid fa-bold"></i></button>
                        <button type="button" class="n_q_tool_btn" title="Dőlt" onclick="formatText('italic')"><i class="fa-solid fa-italic"></i></button>
                        <button type="button" class="n_q_tool_btn" title="Aláhúzott" onclick="formatText('underline')"><i class="fa-solid fa-underline"></i></button>
                        <div style="width: 1px; background: #cbd5e1; margin: 0 5px;"></div>
                        <button type="button" class="n_q_tool_btn" title="Lista" onclick="formatText('insertUnorderedList')"><i class="fa-solid fa-list-ul"></i></button>
                        <button type="button" class="n_q_tool_btn" title="Számozott lista" onclick="formatText('insertOrderedList')"><i class="fa-solid fa-list-ol"></i></button>
                        <div style="width: 1px; background: #cbd5e1; margin: 0 5px;"></div>
                        <button type="button" class="n_q_tool_btn" title="Kép" onclick="insertMedia('image')"><i class="fa-regular fa-image"></i></button>
                        <button type="button" class="n_q_tool_btn" title="YouTube Videó" onclick="insertMedia('youtube')"><i class="fa-brands fa-youtube"></i></button>
                        <button type="button" class="n_q_tool_btn" title="Emoji" onclick="toggleEmojiPicker()"><i class="fa-regular fa-face-smile"></i></button>

                        <!-- Emoji Picker Panel -->
                        <div class="n_q_emoji_picker" id="emojiPicker">
                            <span class="n_q_emoji_btn" onclick="insertEmoji('😀')">😀</span>
                            <span class="n_q_emoji_btn" onclick="insertEmoji('😂')">😂</span>
                            <span class="n_q_emoji_btn" onclick="insertEmoji('😍')">😍</span>
                            <span class="n_q_emoji_btn" onclick="insertEmoji('👍')">👍</span>
                            <span class="n_q_emoji_btn" onclick="insertEmoji('👎')">👎</span>
                            <span class="n_q_emoji_btn" onclick="insertEmoji('🔥')">🔥</span>
                            <span class="n_q_emoji_btn" onclick="insertEmoji('🎉')">🎉</span>
                            <span class="n_q_emoji_btn" onclick="insertEmoji('😎')">😎</span>
                            <span class="n_q_emoji_btn" onclick="insertEmoji('🤔')">🤔</span>
                            <span class="n_q_emoji_btn" onclick="insertEmoji('😢')">😢</span>
                            <span class="n_q_emoji_btn" onclick="insertEmoji('😡')">😡</span>
                            <span class="n_q_emoji_btn" onclick="insertEmoji('✅')">✅</span>
                        </div>
                    </div>

                    <div class="n_q_editor_content" id="editor" contenteditable="true" placeholder="Írd le részletesen..."></div>
                    <input type="hidden" name="content" id="hiddenContent">
                    
                    <div class="n_q_editor_footer">
                        Karakterek: <span id="charCount">0</span>
                    </div>
                </div>
            </div>

            <!-- Képfeltöltés Szekció -->
            <div class="n_q_form_group">
                <label class="n_q_label">Képek csatolása (Max 3 db)</label>
                <?php if (has_storage_space($currentUser)): ?>
                <div class="n_q_upload_area" id="dropZone">
                    <div class="n_q_upload_placeholder">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p>Húzd ide a képeket, vagy kattints a tallózáshoz</p>
                        <span>(JPEG, PNG, WEBP, GIF - Max 10MB)</span>
                    </div>
                    <input type="file" id="fileInput" multiple accept="image/*" style="display:none;">
                </div>
                <?php else: ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-500 p-4 rounded-lg text-center font-bold mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i> A tárhelyed megtelt! Nem tölthetsz fel több képet.
                </div>
                <?php endif; ?>

                <div class="n_q_preview_container" id="previewContainer">
                    <!-- Preview items will be injected here -->
                </div>
                <!-- Hidden inputs for form submission -->
                <div id="hiddenInputsContainer"></div>
            </div>

            <style>
                .n_q_upload_area {
                    border: 2px dashed #cbd5e1;
                    padding: 30px;
                    text-align: center;
                    cursor: pointer;
                    background: #f8fafc;
                    transition: all 0.2s;
                    margin-bottom: 15px;
                }
                .n_q_upload_area:hover, .n_q_upload_area.dragover {
                    border-color: #4f46e5;
                    background: #eff6ff;
                }
                .n_q_upload_placeholder i {
                    font-size: 3rem;
                    color: #94a3b8;
                    margin-bottom: 10px;
                }
                .n_q_upload_placeholder p {
                    margin: 5px 0;
                    font-weight: 600;
                    color: #475569;
                }
                .n_q_upload_placeholder span {
                    font-size: 0.8rem;
                    color: #94a3b8;
                }

                .n_q_preview_container {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                    gap: 10px;
                }
                .n_q_preview_item {
                    position: relative;
                    border: 1px solid #e2e8f0;
                    border-radius: 4px;
                    overflow: hidden;
                    aspect-ratio: 1;
                }
                .n_q_preview_img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
                .n_q_remove_btn {
                    position: absolute;
                    top: 5px;
                    right: 5px;
                    background: rgba(255, 255, 255, 0.9);
                    border: none;
                    border-radius: 50%;
                    width: 24px; height: 24px;
                    cursor: pointer;
                    color: #ef4444;
                    display: flex; align-items: center; justify-content: center;
                    font-size: 0.8rem;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }
                .n_q_remove_btn:hover { background: #fff; transform: scale(1.1); }
            </style>

            <script>
                (function() {
                    const dropZone = document.getElementById('dropZone');
                    const fileInput = document.getElementById('fileInput');
                    const previewContainer = document.getElementById('previewContainer');
                    const hiddenInputsContainer = document.getElementById('hiddenInputsContainer');
                    const MAX_IMAGES = 3;
                    let uploadedCount = 0;

                    // Click to browse
                    dropZone.addEventListener('click', () => fileInput.click());

                    // Drag & Drop events
                    dropZone.addEventListener('dragover', (e) => {
                        e.preventDefault();
                        dropZone.classList.add('dragover');
                    });
                    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
                    dropZone.addEventListener('drop', (e) => {
                        e.preventDefault();
                        dropZone.classList.remove('dragover');
                        handleFiles(e.dataTransfer.files);
                    });

                    fileInput.addEventListener('change', (e) => {
                        handleFiles(e.target.files);
                        fileInput.value = ''; // Reset
                    });

                    function handleFiles(files) {
                        if (uploadedCount >= MAX_IMAGES) {
                            alert(`Maximum ${MAX_IMAGES} képet tölthetsz fel!`);
                            return;
                        }

                        Array.from(files).forEach(file => {
                            if (uploadedCount >= MAX_IMAGES) return;
                            if (!file.type.startsWith('image/')) {
                                alert('Csak képfájlokat tölthetsz fel!');
                                return;
                            }

                            uploadFile(file);
                        });
                    }

                    function uploadFile(file) {
                        uploadedCount++; // Optimistic count

                        // Create placeholder
                        const item = document.createElement('div');
                        item.className = 'n_q_preview_item';
                        item.innerHTML = `
                            <div style="width:100%; height:100%; background:#f1f5f9; display:flex; align-items:center; justify-content:center;">
                                <i class="fa-solid fa-spinner fa-spin" style="color:#4f46e5;"></i>
                            </div>
                        `;
                        previewContainer.appendChild(item);

                        const formData = new FormData();
                        formData.append('file', file);

                        fetch('ajax_upload_image.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Update preview
                                item.innerHTML = `
                                    <img src="${data.path}" class="n_q_preview_img">
                                    <button type="button" class="n_q_remove_btn" onclick="removeImage(this, '${data.path}')"><i class="fa-solid fa-xmark"></i></button>
                                `;

                                // Add hidden input
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'uploaded_images[]';
                                input.value = data.path;
                                hiddenInputsContainer.appendChild(input);
                            } else {
                                alert(data.message || 'Hiba a feltöltés során.');
                                item.remove();
                                uploadedCount--;
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Hálózati hiba.');
                            item.remove();
                            uploadedCount--;
                        });
                    }

                    window.removeImage = function(btn, path) {
                        const item = btn.closest('.n_q_preview_item');
                        item.remove();

                        // Remove hidden input
                        const input = hiddenInputsContainer.querySelector(`input[value="${path}"]`);
                        if(input) input.remove();

                        uploadedCount--;
                    };
                })();
            </script>

            <!-- Szavazás Szekció -->
            <div class="n_q_form_group" style="margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:15px;">
                    <input type="checkbox" id="hasPoll" name="has_poll" value="1" onchange="togglePollSection()" style="width:18px; height:18px; cursor:pointer;">
                    <label for="hasPoll" style="font-weight:700; color:#334155; cursor:pointer; user-select:none;">Szeretnék szavazást csatolni a kérdéshez</label>
                </div>

                <div id="pollSection" style="display:none; padding-left:20px; border-left:3px solid #4f46e5;">
                    <div class="n_q_form_group">
                        <label class="n_q_label">Szavazás kérdése <span>*</span></label>
                        <input type="text" name="poll_question" class="n_q_input" placeholder="Pl.: Melyik videókártyát választanád?">
                    </div>

                    <div class="n_q_form_group">
                        <label class="n_q_label">Válaszlehetőségek <span>*</span> (Min. 2)</label>
                        <div id="pollOptionsList">
                            <div class="poll-option-row" style="margin-bottom:10px;">
                                <input type="text" name="poll_options[]" class="n_q_input" placeholder="1. opció">
                            </div>
                            <div class="poll-option-row" style="margin-bottom:10px;">
                                <input type="text" name="poll_options[]" class="n_q_input" placeholder="2. opció">
                            </div>
                        </div>
                        <button type="button" class="n_q_btn n_q_btn_secondary" onclick="addPollOption()" style="font-size:0.85rem; padding:8px 15px;">
                            <i class="fa-solid fa-plus"></i> További opció hozzáadása
                        </button>
                    </div>
                </div>
            </div>

            <script>
                function togglePollSection() {
                    const cb = document.getElementById('hasPoll');
                    const section = document.getElementById('pollSection');
                    if (cb.checked) {
                        section.style.display = 'block';
                        // Auto-fill poll question with title if empty
                        const title = document.querySelector('input[name="title"]').value;
                        const pollQ = document.querySelector('input[name="poll_question"]');
                        if (title && !pollQ.value) pollQ.value = title;
                    } else {
                        section.style.display = 'none';
                    }
                }

                function addPollOption() {
                    const container = document.getElementById('pollOptionsList');
                    const count = container.children.length + 1;

                    const div = document.createElement('div');
                    div.className = 'poll-option-row';
                    div.style.marginBottom = '10px';
                    div.style.display = 'flex';
                    div.style.gap = '10px';

                    div.innerHTML = `
                        <input type="text" name="poll_options[]" class="n_q_input" placeholder="${count}. opció">
                        <button type="button" onclick="this.parentElement.remove()" style="border:none; background:none; color:#ef4444; cursor:pointer;" title="Törlés">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    `;
                    container.appendChild(div);
                }
            </script>

            <div class="n_q_tips_box">
                <span class="n_q_tips_title"><i class="fa-regular fa-lightbulb"></i> Tippek a gyors válaszhoz:</span>
                • Írd le a pontos hardver specifikációt (CPU, GPU, RAM, Táp)!<br>
                • Ha hibaüzenetet kapsz, másold be pontosan vagy csatolj róla képet.
            </div>

            <div style="margin-top: 20px; padding: 15px; border: 1px solid #e2e8f0; background: #fff; border-radius: 4px;">
                <h4 style="font-weight: 800; color: #ef4444; margin-bottom: 10px;">FONTOS SZABÁLYOK!</h4>
                <p style="font-size: 0.9rem; color: #334155; margin-bottom: 8px;">
                    <i class="fa-solid fa-triangle-exclamation" style="color: #f59e0b;"></i>
                    <b>Milyen kérdéseket NE tegyél fel?</b><br>
                    Kerüld az illegális szoftverekkel, warez tartalmakkal kapcsolatos kérdéseket! Ne spammelj, és ne hirdess termékeket!
                </p>
                <p style="font-size: 0.9rem; color: #334155;">
                    <i class="fa-solid fa-copyright" style="color: #6366f1;"></i>
                    <b>Képek feltöltése:</b><br>
                    Kizárólag <b>saját készítésű</b> képeket tölts fel, vagy győződj meg róla, hogy a feltöltött kép <b>nem jogdíjas</b> (pl. Unsplash, Pixabay). Mások szellemi tulajdonának engedély nélküli használata tilos!
                </p>
            </div>
            <div style="text-align: center; margin-top: 15px;">
                <a href="szabalyzat.php" target="_blank" style="color: #4f46e5; font-weight: 600; text-decoration: underline;">Olvasd el a teljes szabályzatot <i class="fa-solid fa-arrow-up-right-from-square"></i></a>
            </div>

            <div id="moderation-feedback" style="display:none; margin-top: 20px; padding: 15px; border-radius: 4px; font-weight: 600; font-size: 0.95rem;"></div>

            <div class="n_q_actions">
                <button type="button" class="n_q_btn n_q_btn_secondary" onclick="window.history.back()">Mégse</button>
                <button type="submit" class="n_q_btn n_q_btn_primary"><i class="fa-solid fa-paper-plane"></i> Kérdés közzététele</button>
            </div>

        </div>
        </form>
        <?php endif; ?>
    </div>

    <script>
        const subCategories = {};
        <?php
        $all_sub = $pdo->query("SELECT * FROM qc_categories WHERE parent_id IS NOT NULL ORDER BY id ASC")->fetchAll();
        foreach ($all_sub as $sub) {
            echo "if(!subCategories[".$sub['parent_id']."]) subCategories[".$sub['parent_id']."] = [];";
            echo "subCategories[".$sub['parent_id']."].push({id: ".$sub['id'].", name: ".json_encode($sub['name'])."});";
        }
        ?>

        function updateSubCategories() {
            const mainSelect = document.getElementById('mainCategory');
            const subSelect = document.getElementById('subCategory');
            const selectedValue = mainSelect.value;

            subSelect.innerHTML = '<option value="" disabled selected>Válassz alkategóriát...</option>';

            if (selectedValue && subCategories[selectedValue]) {
                subSelect.disabled = false;
                subCategories[selectedValue].forEach(sub => {
                    const option = document.createElement('option');
                    option.text = sub.name;
                    option.value = sub.id;
                    subSelect.add(option);
                });
            } else {
                subSelect.disabled = true;
                subSelect.innerHTML = '<option value="" disabled selected>Először válassz főkategóriát...</option>';
            }
        }

        // Global State for Validation
        const formState = {
            isTitleDuplicate: false,
            isTitleChecked: false,
            isModerated: false,
            isChecking: false
        };

        const submitBtn = document.querySelector('button[type="submit"]');
        const titleInput = document.querySelector('input[name="title"]');
        const mainCategory = document.getElementById('mainCategory');
        const subCategory = document.getElementById('subCategory');
        const editor = document.getElementById('editor');

        // Create Toast Element
        const toast = document.createElement('div');
        toast.className = 'n_q_toast';
        document.body.appendChild(toast);

        function showToast(message) {
            toast.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> ' + message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // General Form Validator
        function validateForm() {
            if (formState.isChecking) return false; // Ne írjuk felül a gomb állapotát ellenőrzés közben

            let isValid = true;

            // Check inputs to update button style (visual only)
            const titleVal = titleInput.value.trim();
            const mainCatVal = mainCategory.value;
            const subCatVal = subCategory.value;
            const contentVal = editor.innerText.trim();

            if (!titleVal || formState.isTitleDuplicate || !mainCatVal || !subCatVal || contentVal.length === 0) {
                isValid = false;
            }

            if (isValid) {
                submitBtn.classList.remove('n_q_btn_disabled');
            } else {
                submitBtn.classList.add('n_q_btn_disabled');
            }

            return isValid;
        }

        function prepareSubmit() {
            // This is called by onsubmit="return prepareSubmit()"
            // But we intercept click on button, so this might be redundant if we prevent default there.
            // Keeping it for safety.
            document.getElementById('hiddenContent').value = editor.innerHTML;
            return true;
        }

        // --- 2. EDITOR LOGIKA ---

        // Kurzor pozíció mentése
        let lastRange = null;

        function saveSelection() {
            const sel = window.getSelection();
            if (sel.getRangeAt && sel.rangeCount) {
                lastRange = sel.getRangeAt(0);
            }
        }

        // Eseményfigyelők a pozíció mentéséhez
        if(editor) {
            editor.addEventListener('keyup', () => {
                saveSelection();
                validateForm();
            });
            editor.addEventListener('mouseup', saveSelection);
            editor.addEventListener('focus', saveSelection);
            editor.addEventListener('input', function() {
                const text = this.innerText;
                document.getElementById('charCount').textContent = text.length;
                validateForm();
            });
        }

        function formatText(command) {
            document.execCommand(command, false, null);
            editor.focus();
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

                    const html = `<div class="n_q_video_container"><iframe src="${embedUrl}" allowfullscreen></iframe></div><br>`;
                    document.execCommand('insertHTML', false, html);
                }
            }
        }

        // Emoji Logika
        function toggleEmojiPicker() {
            const picker = document.getElementById('emojiPicker');
            picker.classList.toggle('active');
        }

        function insertEmoji(emoji) {
            editor.focus();
            if (lastRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(lastRange);
            }
            document.execCommand('insertText', false, emoji);
            saveSelection();
        }

        document.addEventListener('click', function(e) {
            const picker = document.getElementById('emojiPicker');
            const btn = document.querySelector('.n_q_tool_btn[title="Emoji"]');
            if (picker && btn && !picker.contains(e.target) && !btn.contains(e.target)) {
                picker.classList.remove('active');
            }
        });

        // AJAX Duplicate Check & Input Handling
        if (titleInput) {
            // Input changes: Reset validation states (remove green/red), check validity
            titleInput.addEventListener('input', function() {
                this.classList.remove('valid', 'invalid');
                this.style.borderColor = ''; // Reset custom inline styles
                this.style.backgroundColor = '';

                // Do not clear the error message immediately? User said: "only if user deletes a letter or modifies".
                // So yes, if input happens, we should probably hide the old error because it might not apply anymore.
                const existingError = this.parentNode.querySelector('.duplicate-error');
                if (existingError) existingError.remove();

                formState.isTitleDuplicate = false; // Reset assumption
                formState.isTitleChecked = false;

                validateForm();
            });

            // Blur: Run check
            titleInput.addEventListener('blur', function() {
                const title = this.value.trim();
                if (title.length === 0) {
                    validateForm();
                    return;
                }

                const formData = new FormData();
                formData.append('title', title);

                fetch('ajax_check_duplicate_question.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const existingError = titleInput.parentNode.querySelector('.duplicate-error');
                    if (existingError) existingError.remove();

                    if (data.found) {
                        // Duplicate
                        formState.isTitleDuplicate = true;
                        titleInput.classList.remove('valid');
                        titleInput.classList.add('invalid');

                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'duplicate-error';
                        errorDiv.style.color = '#ef4444';
                        errorDiv.style.marginTop = '5px';
                        errorDiv.style.fontSize = '0.9rem';
                        errorDiv.innerHTML = '<b>A kérdés címe már létezik!</b> <a href="' + data.url + '" target="_blank" style="text-decoration:underline; font-weight:bold;">Kattints ide a megtekintéshez</a>';

                        titleInput.parentNode.appendChild(errorDiv);
                    } else {
                        // Unique
                        formState.isTitleDuplicate = false;
                        titleInput.classList.remove('invalid');
                        titleInput.classList.add('valid');

                        // --- AI Suggestion Logic ---
                        const aiContainer = document.getElementById('ai-suggestions-container');
                        if (aiContainer) {
                            aiContainer.innerHTML = '<div class="n_q_ai_loading"><i class="fa-solid fa-spinner fa-spin"></i> Ajánlások generálása...</div>';

                            const aiFormData = new FormData();
                            aiFormData.append('title', title);

                            fetch('ai/generate_summary.php', {
                                method: 'POST',
                                body: aiFormData
                            })
                            .then(aiRes => aiRes.json())
                            .then(aiData => {
                                aiContainer.innerHTML = '';
                                if (aiData.success && Array.isArray(aiData.summaries)) {
                                    aiData.summaries.forEach(text => {
                                        const item = document.createElement('div');
                                        item.className = 'n_q_ai_item';

                                        // Create elements safely to prevent XSS
                                        const textDiv = document.createElement('div');
                                        textDiv.className = 'n_q_ai_text';
                                        textDiv.textContent = text;

                                        const actionsDiv = document.createElement('div');
                                        actionsDiv.className = 'n_q_ai_actions';

                                        // Accept Button
                                        const acceptBtn = document.createElement('button');
                                        acceptBtn.type = 'button';
                                        acceptBtn.className = 'n_q_ai_btn n_q_ai_btn_accept';
                                        acceptBtn.title = 'Elfogadás';
                                        acceptBtn.innerHTML = '<i class="fa-solid fa-check"></i>';
                                        acceptBtn.onclick = function() { acceptAiSuggestion(text); };

                                        // Reject Button
                                        const rejectBtn = document.createElement('button');
                                        rejectBtn.type = 'button';
                                        rejectBtn.className = 'n_q_ai_btn n_q_ai_btn_reject';
                                        rejectBtn.title = 'Elvetés';
                                        rejectBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                                        rejectBtn.onclick = function() { item.remove(); };

                                        actionsDiv.appendChild(acceptBtn);
                                        actionsDiv.appendChild(rejectBtn);

                                        item.appendChild(textDiv);
                                        item.appendChild(actionsDiv);

                                        aiContainer.appendChild(item);
                                    });
                                }
                            })
                            .catch(aiErr => {
                                console.error(aiErr);
                                aiContainer.innerHTML = '';
                            });
                        }
                    }
                    formState.isTitleChecked = true;
                    validateForm();
                })
                .catch(error => console.error('Error:', error));
            });
        }

        // Category Listeners
        if(mainCategory) {
            mainCategory.addEventListener('change', validateForm);
        }
        if(subCategory) {
            subCategory.addEventListener('change', validateForm);
        }

        // AI Accept Helper
        window.acceptAiSuggestion = function(text) {
            const descInput = document.querySelector('input[name="description"]');
            if (descInput) {
                descInput.value = text;
                document.getElementById('ai-suggestions-container').innerHTML = '';
            }
        };

        // Submit Button Interception
        if (submitBtn) {
            // Initial Validation
            validateForm();

            submitBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Stop default submission always

                if (formState.isChecking) return;

                if (formState.isModerated) {
                    prepareSubmit();
                    document.getElementById('questionForm').submit();
                    return;
                }

                // Collect Errors
                let errors = [];
                const titleVal = titleInput.value.trim();
                const mainCatVal = mainCategory.value;
                const subCatVal = subCategory.value;
                const contentVal = editor.innerText.trim();

                if (!titleVal) {
                    errors.push("Hiányzik a kérdés címe!");
                } else if (formState.isTitleDuplicate) {
                    errors.push("A kérdés címe már létezik!");
                }

                if (!mainCatVal) {
                    errors.push("Válassz főkategóriát!");
                } else if (!subCatVal) {
                    errors.push("Válassz alkategóriát!");
                }

                if (contentVal.length === 0) {
                    errors.push("Írd le a kérdésed részleteit!");
                }

                if (errors.length > 0) {
                    showToast(errors.join("<br>"));
                } else {
                    // Start Moderation
                    formState.isChecking = true;
                    const originalBtnContent = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Ellenőrzés';
                    submitBtn.classList.add('n_q_btn_disabled');
                    submitBtn.style.background = '#94a3b8';
                    submitBtn.style.color = '#fff';
                    submitBtn.style.cursor = 'wait';

                    const formData = new FormData();
                    formData.append('title', titleVal);
                    formData.append('content', contentVal); // contentVal is innerText

                    fetch('ai/moderate_content.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        formState.isChecking = false;
                        submitBtn.style.cursor = 'pointer';

                        if (data.success && data.approved) {
                            // Approved
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('n_q_btn_disabled');
                            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Kérdés közzététele';
                            submitBtn.style.background = '#22c55e';
                            submitBtn.style.color = '#fff';

                            formState.isModerated = true;
                            document.getElementById('moderation-feedback').style.display = 'none';
                        } else {
                            // Rejected or Error
                            submitBtn.disabled = true;
                            submitBtn.classList.add('n_q_btn_disabled');
                            submitBtn.innerHTML = '<i class="fa-solid fa-hand"></i> Elutasítva';
                            submitBtn.style.background = '#ef4444';
                            submitBtn.style.color = '#fff';

                            // Visual Feedback for Inputs
                            // Explicit styling as requested
                            titleInput.classList.remove('valid'); // Remove valid class which has !important green border
                            titleInput.style.borderColor = '#ef4444';
                            titleInput.style.backgroundColor = '#fef2f2';

                            const editorContainer = document.querySelector('.n_q_editor_container');
                            const editorContent = document.getElementById('editor');
                            if(editorContainer) editorContainer.style.borderColor = '#ef4444';
                            if(editorContent) editorContent.style.backgroundColor = '#fef2f2';

                            const feedback = document.getElementById('moderation-feedback');
                            feedback.style.display = 'block';
                            if (data.reason) {
                                feedback.innerHTML = data.reason;
                            } else {
                                feedback.innerHTML = '<b>Hiba:</b> A moderáció sikertelen. Kérlek próbáld újra később.';
                            }

                            formState.isModerated = false;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        formState.isChecking = false;
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('n_q_btn_disabled');
                        submitBtn.innerHTML = originalBtnContent;
                        submitBtn.style.background = '';
                        submitBtn.style.color = '';
                        submitBtn.style.cursor = 'pointer';
                        showToast('Hiba történt az ellenőrzés során. Próbáld újra.');
                    });
                }
            });

            // Reset moderation state on edit
            function resetModeration() {
                if (formState.isChecking) return;

                // Reset visual feedback
                titleInput.style.borderColor = '';
                titleInput.style.backgroundColor = '';

                const editorContainer = document.querySelector('.n_q_editor_container');
                const editorContent = document.getElementById('editor');
                if(editorContainer) editorContainer.style.borderColor = '';
                if(editorContent) editorContent.style.backgroundColor = '';

                // Reset only if needed (if moderated or stopped)
                if (formState.isModerated || submitBtn.innerHTML.includes('Elutasítva')) {
                    formState.isModerated = false;

                    submitBtn.disabled = false;
                    submitBtn.classList.remove('n_q_btn_disabled');
                    submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kérdés közzététele';
                    submitBtn.removeAttribute('style');

                    document.getElementById('moderation-feedback').style.display = 'none';

                    validateForm();
                }
            }

            titleInput.addEventListener('input', resetModeration);
            editor.addEventListener('input', resetModeration);
        }

        // Context Awareness Logic
        (function() {
            const urlParams = new URLSearchParams(window.location.search);
            const fromCatId = urlParams.get('from_cat');

            if (fromCatId) {
                let categoryName = '';
                let isMain = false;
                let isSub = false;
                let mainId = null;
                let subId = null;

                // Check Main Categories
                const mainSelect = document.getElementById('mainCategory');
                if (mainSelect) {
                    for (let i = 0; i < mainSelect.options.length; i++) {
                        if (mainSelect.options[i].value == fromCatId) {
                            categoryName = mainSelect.options[i].text;
                            isMain = true;
                            mainId = fromCatId;
                            break;
                        }
                    }
                }

                // Check Sub Categories
                if (!categoryName && typeof subCategories !== 'undefined') {
                    for (const parentId in subCategories) {
                        const subs = subCategories[parentId];
                        const found = subs.find(s => s.id == fromCatId);
                        if (found) {
                            categoryName = found.name;
                            isSub = true;
                            subId = fromCatId;
                            mainId = parentId;
                            break;
                        }
                    }
                }

                if (categoryName) {
                    const prompt = document.getElementById('categoryPrompt');
                    const textSpan = document.getElementById('categoryPromptText');

                    let promptText = `Mivel a(z) "${categoryName}" kategóriából érkeztél, szeretnéd, hogy automatikusan beállítsuk?`;

                    textSpan.textContent = promptText;
                    prompt.style.display = 'block';

                    document.getElementById('promptYes').addEventListener('click', function() {
                        if (mainId && mainSelect) {
                            mainSelect.value = mainId;
                            // Trigger change manually to populate subcategories
                            updateSubCategories();

                            if (isSub && subId) {
                                const subSelect = document.getElementById('subCategory');
                                if(subSelect) subSelect.value = subId;
                            }
                        }
                        prompt.style.display = 'none';
                    });

                    document.getElementById('promptNo').addEventListener('click', function() {
                        prompt.style.display = 'none';
                    });
                }
            }
        })();
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