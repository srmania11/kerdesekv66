<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser($pdo);
// Update Activity
updateUserActivity($pdo);

// AJAX User Search
if (isset($_GET['action']) && $_GET['action'] === 'search_users' && isset($_GET['q'])) {
    $search = trim($_GET['q']);
    if (strlen($search) >= 2 && $currentUser) {
        $stmt = $pdo->prepare("SELECT username FROM qc_users WHERE username LIKE ? AND user_id != ? LIMIT 10");
        $stmt->execute(["%$search%", $currentUser['user_id']]);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    } else {
        echo json_encode([]);
        exit;
    }
}

$recipient_value = isset($_GET['to']) ? trim($_GET['to']) : '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_name = trim($_POST['recipient'] ?? '');
    $content = clean_html(trim($_POST['content'] ?? ''));

    if (!$recipient_name || !$content) {
        $error = "Kérjük tölts ki minden mezőt!";
    } else {
        // Find recipient
        $stmt = $pdo->prepare("SELECT user_id FROM qc_users WHERE username = ?");
        $stmt->execute([$recipient_name]);
        $recipient_id = $stmt->fetchColumn();

        if (!$recipient_id) {
            $error = "A felhasználó nem található.";
        } elseif ($recipient_id == $currentUser['user_id']) {
            $error = "Nem küldhetsz üzenetet magadnak.";
        } else {
            // Check for existing thread
            $t_stmt = $pdo->prepare("SELECT id FROM qc_pm_threads WHERE (user_one = ? AND user_two = ?) OR (user_one = ? AND user_two = ?)");
            $t_stmt->execute([$currentUser['user_id'], $recipient_id, $recipient_id, $currentUser['user_id']]);
            $thread_id = $t_stmt->fetchColumn();

            if (!$thread_id) {
                $c_stmt = $pdo->prepare("INSERT INTO qc_pm_threads (user_one, user_two, last_updated) VALUES (?, ?, NOW())");
                $c_stmt->execute([$currentUser['user_id'], $recipient_id]);
                $thread_id = $pdo->lastInsertId();
            } else {
                $u_stmt = $pdo->prepare("UPDATE qc_pm_threads SET last_updated = NOW() WHERE id = ?");
                $u_stmt->execute([$thread_id]);
            }

            // Insert Message
            $m_stmt = $pdo->prepare("INSERT INTO qc_pm_messages (thread_id, sender_id, content, is_read) VALUES (?, ?, ?, 0)");
            $m_stmt->execute([$thread_id, $currentUser['user_id'], $content]);

            header("Location: private_messages.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <?php
    $seoData = ['title' => 'Új üzenet | SilverPC Fórum', 'description' => 'Küldj privát üzenetet.', 'og_type' => 'website'];
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
    <style>
        /* Reuse styles from private_messages.php for consistency */
        .p_m_wrapper { width: 100%; margin: 0 auto; background: #f1f5f9; padding: 20px; box-sizing: border-box; min-height: 100vh; }
        .p_m_header { margin-bottom: 20px; }
        .p_m_title { font-size: 1.5rem; color: #1e293b; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 10px; }
        .p_m_card { background: white; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); padding: 30px; max-width: 800px; margin: 0 auto; border: 1px solid #e2e8f0; }

        .p_m_form_group { margin-bottom: 20px; }
        .p_m_label { display: block; font-weight: 700; color: #64748b; margin-bottom: 8px; font-size: 0.9rem; }
        /* Fix text color visibility */
        .p_m_input { width: 100%; padding: 10px 15px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem; box-sizing: border-box; color: #000000 !important; font-weight: bold; background-color: #ffffff !important; }

        /* Editor Styles */
        .p_m_input_area { border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; background: #fff; }
        .p_m_editor_toolbar { padding: 8px 15px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; gap: 5px; align-items: center; position: relative; }
        .p_m_tool_btn { width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border: none; background: transparent; color: #64748b; cursor: pointer; border-radius: 4px; font-size: 0.9rem; }
        .p_m_tool_btn:hover { background: #e2e8f0; color: #334155; }
        .p_m_separator { width: 1px; height: 18px; background: #cbd5e1; margin: 0 5px; }
        .p_m_editor_content { min-height: 200px; padding: 15px; outline: none; font-size: 1rem; line-height: 1.5; color: #000000 !important; background-color: #ffffff !important; overflow-y: auto; }

        .p_m_btn { padding: 10px 20px; border-radius: 6px; font-weight: 600; cursor: pointer; border: none; font-size: 0.95rem; transition: all 0.2s; }
        .p_m_btn_primary { background: #4f46e5; color: white; }
        .p_m_btn_primary:hover { background: #4338ca; }
        .p_m_btn_secondary { background: #f1f5f9; color: #64748b; margin-right: 10px; }
        .p_m_btn_secondary:hover { background: #e2e8f0; }

        /* Emoji Picker */
        .p_m_emoji_picker { display: none; position: absolute; top: 40px; left: 0; background: white; border: 1px solid #cbd5e1; box-shadow: 0 4px 10px rgba(0,0,0,0.1); padding: 10px; width: 240px; flex-wrap: wrap; gap: 5px; z-index: 100; border-radius: 6px; }
        .p_m_emoji_picker.active { display: flex; }
        .p_m_emoji_btn { font-size: 1.4rem; cursor: pointer; padding: 4px; transition: transform 0.2s; }
        .p_m_emoji_btn:hover { transform: scale(1.2); }

        /* Autocomplete suggestions text color */
        #recipientSuggestions div { color: #000 !important; }
    </style>
</head>
<body class="min-h-screen relative">
<?php
// header menü beillesztése
require_once __DIR__ . '/../inc/header_menu.php'; ?>
<!-- CONTENT PLACEHOLDER -->
<main class="header-content-wrapper mt-0 pb-0">

<main id="main" class="site-main">
    <div class="cikk-fo-container" itemscope itemtype="http://schema.org/NewsArticle">
        <div class="cikk-fo-layout">
            <div class="cikk-fo-col-left" style="background: linear-gradient(135deg, rgba(11, 25, 30, 0.9), rgba(10, 15, 20, 0.9));">

                <div class="p_m_wrapper">

                    <div class="p_m_header" style="max-width: 800px; margin: 0 auto 20px auto;">
                        <h1 class="p_m_title"><i class="fa-solid fa-pen-to-square"></i> Új üzenet írása</h1>
                    </div>

                    <div class="p_m_card">
                        <?php if (!$currentUser): ?>
                            <div style="text-align: center; padding: 40px; color: #64748b;">
                                <i class="fa-solid fa-lock" style="font-size: 3rem; margin-bottom: 20px; color: #94a3b8;"></i>
                                <h2 style="font-size: 1.5rem; margin-bottom: 10px; color: #334155;">Bejelentkezés Szükséges</h2>
                                <p>Ennek a funkciónak a használatához be kell jelentkezned!</p>
                            </div>
                        <?php else: ?>
                            <?php if($error): ?>
                                <div style="background: #fef2f2; color: #ef4444; padding: 10px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #fca5a5;">
                                    <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" onsubmit="return prepareSubmit()">
                                <div class="p_m_form_group" style="position: relative;">
                                    <label class="p_m_label">Címzett felhasználóneve:</label>
                                    <input type="text" name="recipient" id="recipientInput" class="p_m_input" placeholder="Kezdd el írni a nevet..." value="<?php echo htmlspecialchars($recipient_value); ?>" autocomplete="off" required>
                                    <div id="recipientSuggestions" style="position: absolute; top: 100%; left: 0; width: 100%; background: white; border: 1px solid #cbd5e1; border-radius: 0 0 6px 6px; z-index: 1000; max-height: 200px; overflow-y: auto; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
                                </div>

                                <div class="p_m_form_group">
                                    <label class="p_m_label">Üzenet:</label>
                                    <div class="p_m_input_area">
                                        <div class="p_m_editor_toolbar">
                                            <button type="button" class="p_m_tool_btn" onclick="formatText('bold')"><i class="fa-solid fa-bold"></i></button>
                                            <button type="button" class="p_m_tool_btn" onclick="formatText('italic')"><i class="fa-solid fa-italic"></i></button>
                                            <button type="button" class="p_m_tool_btn" onclick="formatText('underline')"><i class="fa-solid fa-underline"></i></button>
                                            <div class="p_m_separator"></div>
                                            <button type="button" class="p_m_tool_btn" onclick="formatText('insertUnorderedList')"><i class="fa-solid fa-list-ul"></i></button>
                                            <div class="p_m_separator"></div>
                                            <button type="button" class="p_m_tool_btn" onclick="insertMedia('image')"><i class="fa-regular fa-image"></i></button>
                                            <button type="button" class="p_m_tool_btn" onclick="insertMedia('youtube')"><i class="fa-brands fa-youtube"></i></button>
                                            <button type="button" class="p_m_tool_btn" onclick="toggleEmojiPicker()"><i class="fa-regular fa-face-smile"></i></button>

                                            <div class="p_m_emoji_picker" id="emojiPicker">
                                                <span class="p_m_emoji_btn" onclick="insertEmoji('😀')">😀</span>
                                                <span class="p_m_emoji_btn" onclick="insertEmoji('😂')">😂</span>
                                                <span class="p_m_emoji_btn" onclick="insertEmoji('👍')">👍</span>
                                                <span class="p_m_emoji_btn" onclick="insertEmoji('😎')">😎</span>
                                                <span class="p_m_emoji_btn" onclick="insertEmoji('🤔')">🤔</span>
                                                <span class="p_m_emoji_btn" onclick="insertEmoji('😢')">😢</span>
                                            </div>
                                        </div>
                                        <div class="p_m_editor_content" id="editor" contenteditable="true" placeholder="Írd ide az üzeneted..."></div>
                                        <input type="hidden" name="content" id="hiddenContent">
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: flex-end;">
                                    <button type="button" class="p_m_btn p_m_btn_secondary" onclick="window.history.back()">Mégse</button>
                                    <button type="submit" class="p_m_btn p_m_btn_primary"><i class="fa-solid fa-paper-plane"></i> Küldés</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>

                </div>

            </div> <!-- End Left Column -->

            <?php include __DIR__ . '/includes/sidebar.php'; ?>

        </div> <!-- End Layout -->
    </div> <!-- End Container -->
</main>

</main>

<script>
    const editor = document.getElementById('editor');

    function prepareSubmit() {
        document.getElementById('hiddenContent').value = editor.innerHTML;
        if(!editor.innerText.trim() && !editor.innerHTML.includes('<img') && !editor.innerHTML.includes('<iframe')) {
            alert("Az üzenet nem lehet üres!");
            return false;
        }
        return true;
    }

    function formatText(command, value = null) {
        document.execCommand(command, false, value);
        editor.focus();
    }

    function insertMedia(type) {
        editor.focus();
        let url;
        if (type === 'image') {
            url = prompt("Kérlek add meg a kép URL címét:");
            if (url) document.execCommand('insertImage', false, url);
        } else if (type === 'youtube') {
            url = prompt("Kérlek add meg a YouTube videó URL címét:");
            if (url) {
                let videoId = url.split('v=')[1];
                if (!videoId) videoId = url.split('/').pop();
                const embedUrl = `https://www.youtube.com/embed/${videoId}`;
                const html = `<div style="position:relative;width:100%;padding-bottom:56.25%;height:0;background:#000;margin:10px 0;"><iframe src="${embedUrl}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" allowfullscreen></iframe></div><br>`;
                document.execCommand('insertHTML', false, html);
            }
        }
    }

    function toggleEmojiPicker() {
        document.getElementById('emojiPicker').classList.toggle('active');
    }

    function insertEmoji(emoji) {
        editor.focus();
        document.execCommand('insertText', false, emoji);
    }

    document.addEventListener('click', function(e) {
        const picker = document.getElementById('emojiPicker');
        const btn = document.querySelector('.p_m_tool_btn[onclick="toggleEmojiPicker()"]');
        if (picker && !picker.contains(e.target) && !btn.contains(e.target)) {
            picker.classList.remove('active');
        }
    });

    // Autocomplete Logic
    const recipientInput = document.getElementById('recipientInput');
    const suggestionsBox = document.getElementById('recipientSuggestions');

    recipientInput.addEventListener('input', function() {
        const query = this.value;
        if (query.length < 2) {
            suggestionsBox.style.display = 'none';
            return;
        }

        fetch('?action=search_users&q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                suggestionsBox.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(user => {
                        const div = document.createElement('div');
                        div.style.padding = '10px';
                        div.style.cursor = 'pointer';
                        div.style.borderBottom = '1px solid #f1f5f9';
                        div.style.color = '#000'; // Force black text
                        div.textContent = user;
                        div.onmouseover = () => div.style.background = '#f8fafc';
                        div.onmouseout = () => div.style.background = 'white';
                        div.onclick = () => {
                            recipientInput.value = user;
                            suggestionsBox.style.display = 'none';
                        };
                        suggestionsBox.appendChild(div);
                    });
                    suggestionsBox.style.display = 'block';
                } else {
                    suggestionsBox.style.display = 'none';
                }
            });
    });

    document.addEventListener('click', function(e) {
        if (!recipientInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
