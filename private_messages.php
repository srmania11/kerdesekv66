<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser($pdo);
// Update Activity
updateUserActivity($pdo);

// Handle AJAX Mark Read
if (isset($_GET['action']) && $_GET['action'] === 'mark_read' && $currentUser && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $thread_id = (int)($data['thread_id'] ?? 0);
    if ($thread_id) {
        $stmt = $pdo->prepare("UPDATE qc_pm_messages SET is_read = 1 WHERE thread_id = ? AND sender_id != ?");
        $stmt->execute([$thread_id, $currentUser['user_id']]);
        echo json_encode(['status' => 'success']);
        exit;
    }
}

// Handle AJAX Send
if (isset($_GET['action']) && $_GET['action'] === 'send_ajax' && $currentUser && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $recipient_name = trim($data['recipient'] ?? '');
    $content = clean_html(trim($data['content'] ?? ''));

    if ($recipient_name && $content) {
        $stmt = $pdo->prepare("SELECT user_id FROM qc_users WHERE username = ?");
        $stmt->execute([$recipient_name]);
        $recipient_id = $stmt->fetchColumn();

        if ($recipient_id) {
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

            $m_stmt = $pdo->prepare("INSERT INTO qc_pm_messages (thread_id, sender_id, content, is_read) VALUES (?, ?, ?, 0)");
            $m_stmt->execute([$thread_id, $currentUser['user_id'], $content]);

            echo json_encode(['status' => 'success', 'time' => seconds_to_time_elapsed(0)]);
            exit;
        }
    }
    echo json_encode(['status' => 'error']);
    exit;
}

// Handle Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['thread_id'])) {
    $thread_id = (int)$_GET['thread_id'];
    $check = $pdo->prepare("SELECT id FROM qc_pm_threads WHERE id = ? AND (user_one = ? OR user_two = ?)");
    $check->execute([$thread_id, $currentUser['user_id'], $currentUser['user_id']]);
    if ($check->fetch()) {
        $del = $pdo->prepare("DELETE FROM qc_pm_threads WHERE id = ?");
        $del->execute([$thread_id]);
    }
    header("Location: private_messages.php");
    exit;
}

// Fetch Threads
$threads = [];
if ($currentUser) {
    $stmt = $pdo->prepare("
        SELECT t.id, t.user_one, t.user_two, t.last_updated,
               TIMESTAMPDIFF(SECOND, t.last_updated, NOW()) as last_updated_seconds,
               u1.username as u1_name, TIMESTAMPDIFF(SECOND, u1.last_active, NOW()) as u1_active_seconds,
               u2.username as u2_name, TIMESTAMPDIFF(SECOND, u2.last_active, NOW()) as u2_active_seconds
        FROM qc_pm_threads t
        JOIN qc_users u1 ON t.user_one = u1.user_id
        JOIN qc_users u2 ON t.user_two = u2.user_id
        WHERE t.user_one = ? OR t.user_two = ?
        ORDER BY t.last_updated DESC
    ");
    try {
        $stmt->execute([$currentUser['user_id'], $currentUser['user_id']]);
        $threads_raw = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback
        $stmt = $pdo->prepare("
            SELECT t.id, t.user_one, t.user_two, t.last_updated,
                   u1.username as u1_name,
                   u2.username as u2_name
            FROM qc_pm_threads t
            JOIN qc_users u1 ON t.user_one = u1.user_id
            JOIN qc_users u2 ON t.user_two = u2.user_id
            WHERE t.user_one = ? OR t.user_two = ?
            ORDER BY t.last_updated DESC
        ");
        $stmt->execute([$currentUser['user_id'], $currentUser['user_id']]);
        $threads_raw = $stmt->fetchAll();
    }

    foreach ($threads_raw as $row) {
        $partner_id = ($row['user_one'] == $currentUser['user_id']) ? $row['user_two'] : $row['user_one'];
        $partner_name = ($row['user_one'] == $currentUser['user_id']) ? $row['u2_name'] : $row['u1_name'];

        $partner_active_seconds = null;
        if (isset($row['u1_active_seconds']) && $row['user_one'] != $currentUser['user_id']) $partner_active_seconds = $row['u1_active_seconds'];
        if (isset($row['u2_active_seconds']) && $row['user_two'] != $currentUser['user_id']) $partner_active_seconds = $row['u2_active_seconds'];

        $status = 'Offline';
        if ($partner_active_seconds !== null && $partner_active_seconds < 300) { // 5 mins
            $status = 'Online';
        }

        // Fetch messages
        $all_msgs_stmt = $pdo->prepare("SELECT content as text, created_at as time, TIMESTAMPDIFF(SECOND, created_at, NOW()) as seconds_ago, sender_id, is_read FROM qc_pm_messages WHERE thread_id = ? ORDER BY created_at ASC");
        $all_msgs_stmt->execute([$row['id']]);
        $all_msgs = $all_msgs_stmt->fetchAll();

        $formatted_msgs = [];
        $last_msg_text = '';
        $last_msg_time = '';
        $unread_count = 0;

        foreach ($all_msgs as $msg) {
            $type = ($msg['sender_id'] == $currentUser['user_id']) ? 'sent' : 'received';
            $elapsed = seconds_to_time_elapsed($msg['seconds_ago']);
            $formatted_msgs[] = [
                'type' => $type,
                'text' => $msg['text'],
                'time' => $elapsed,
                'is_read' => $msg['is_read']
            ];
            $last_msg_text = $msg['text'];
            $last_msg_time = $elapsed;
            if ($type == 'received' && !$msg['is_read']) $unread_count++;
        }

        $threads[$partner_name] = [
            'id' => $row['id'],
            'partner_id' => $partner_id,
            'partner_name' => $partner_name,
            'last_message' => $last_msg_text,
            'last_time' => $last_msg_time,
            'is_read' => ($unread_count == 0),
            'messages' => $formatted_msgs,
            'status' => $status
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <?php
    $seoData = ['title' => 'Üzenetek | SilverPC Fórum', 'description' => 'Privát üzeneteid.', 'og_type' => 'website'];
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
<?php require_once __DIR__ . '/../inc/header_menu.php'; ?>
<main class="header-content-wrapper mt-0 pb-0">
<main id="main" class="site-main">
    <div class="cikk-fo-container" itemscope itemtype="http://schema.org/NewsArticle">
        <div class="cikk-fo-layout">
            <div class="cikk-fo-col-left" style="background: linear-gradient(135deg, rgba(11, 25, 30, 0.9), rgba(10, 15, 20, 0.9));">

<style>
    /* CSS Styles */
    .p_m_wrapper { width: 100%; margin: 0 auto; background: #f1f5f9; padding: 20px; box-sizing: border-box; height: 100vh; display: flex; flex-direction: column; }
    .p_m_header { margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
    .p_m_title { font-size: 1.5rem; color: #1e293b; font-weight: 800; margin: 0; display: flex; align-items: center; gap: 10px; }
    .p_m_new_btn { background: #4f46e5; color: white; border: none; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; text-decoration: none; font-size: 0.9rem; }
    .p_m_new_btn:hover { background: #4338ca; }
    .p_m_container { flex: 1; background: #ffffff; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); display: flex; overflow: hidden; border: 1px solid #e2e8f0; }
    .p_m_sidebar { width: 350px; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; background: #ffffff; }
    .p_m_sidebar_header { padding: 15px; border-bottom: 1px solid #f1f5f9; }
    .p_m_search_box { position: relative; margin-bottom: 15px; }
    .p_m_search_input { width: 100%; padding: 10px 10px 10px 35px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 0.9rem; background: #f8fafc; color: #000; font-weight: bold; }
    .p_m_search_icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .p_m_tabs { display: flex; gap: 10px; }
    .p_m_tab { background: none; border: none; font-weight: 600; color: #64748b; padding-bottom: 5px; cursor: pointer; border-bottom: 2px solid transparent; font-size: 0.9rem; }
    .p_m_tab.active { color: #4f46e5; border-bottom-color: #4f46e5; }
    .p_m_list { flex: 1; overflow-y: auto; }
    .p_m_item { display: flex; padding: 15px; cursor: pointer; transition: background 0.2s; border-bottom: 1px solid #f8fafc; position: relative; }
    .p_m_item:hover { background: #f8fafc; }
    .p_m_item.active { background: #eff6ff; border-left: 3px solid #4f46e5; }
    .p_m_item.unread { background: #fff; }
    .p_m_item.unread .p_m_name { font-weight: 800; color: #0f172a; }
    .p_m_item.unread .p_m_preview { color: #334155; font-weight: 600; }
    .p_m_unread_dot { width: 10px; height: 10px; background: #4f46e5; border-radius: 50%; position: absolute; right: 15px; top: 50%; transform: translateY(-50%); }
    .p_m_avatar_wrapper { position: relative; margin-right: 12px; }
    .p_m_avatar { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }
    .p_m_status_indicator { width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; position: absolute; bottom: 0; right: 0; }
    .p_m_status_online { background: #10b981; }
    .p_m_status_offline { background: #94a3b8; }
    .p_m_info { flex: 1; min-width: 0; }
    .p_m_top_row { display: flex; justify-content: space-between; margin-bottom: 3px; }
    .p_m_name { font-size: 0.95rem; font-weight: 600; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .p_m_time { font-size: 0.75rem; color: #94a3b8; white-space: nowrap; }
    .p_m_preview { font-size: 0.85rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
    .p_m_chat_area { flex: 1; display: flex; flex-direction: column; background: #fff; position: relative; }
    .p_m_chat_header { padding: 15px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #fff; }
    .p_m_chat_user { display: flex; align-items: center; gap: 10px; }
    .p_m_header_name { font-weight: 700; color: #1e293b; font-size: 1.1rem; }
    .p_m_header_status { font-size: 0.8rem; color: #10b981; }
    .p_m_chat_actions { position: relative; color: #94a3b8; font-size: 1.2rem; cursor: pointer; padding: 5px; }
    .p_m_chat_actions:hover { color: #64748b; }
    .p_m_chat_menu { display: none; position: absolute; top: 35px; right: 0; background: white; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 6px; width: 220px; z-index: 20; overflow: hidden; }
    .p_m_chat_menu.active { display: block; }
    .p_m_menu_item { padding: 12px 15px; font-size: 0.9rem; color: #334155; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: background 0.2s; }
    .p_m_menu_item:hover { background: #f8fafc; }
    .p_m_menu_item.text-danger { color: #ef4444; }
    .p_m_menu_item.text-danger:hover { background: #fef2f2; }
    .p_m_back_btn { display: none; margin-right: 10px; color: #64748b; font-size: 1.2rem; cursor: pointer; }
    .p_m_messages { flex: 1; padding: 20px; overflow-y: auto; background: #f8fafc; display: flex; flex-direction: column; gap: 15px; }
    .p_m_date_divider { text-align: center; font-size: 0.75rem; color: #94a3b8; margin: 10px 0; position: relative; }
    .p_m_date_divider::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; border-bottom: 1px solid #e2e8f0; z-index: 0; }
    .p_m_date_divider span { background: #f8fafc; padding: 0 10px; position: relative; z-index: 1; }
    .p_m_msg { max-width: 85%; padding: 10px 15px; border-radius: 12px; font-size: 0.95rem; line-height: 1.5; position: relative; word-wrap: break-word; }
    .p_m_msg_received { align-self: flex-start; background: #ffffff; border: 1px solid #e2e8f0; color: #334155; border-bottom-left-radius: 2px; }
    .p_m_msg_sent { align-self: flex-end; background: #4f46e5; color: white; border-bottom-right-radius: 2px; box-shadow: 0 2px 5px rgba(79, 70, 229, 0.2); }
    .p_m_msg_time { font-size: 0.7rem; margin-top: 4px; text-align: right; opacity: 0.7; display: block; }
    .p_m_msg iframe { max-width: 100%; height: auto; aspect-ratio: 16/9; }
    .p_m_input_area { padding: 0; border-top: 1px solid #e2e8f0; background: #fff; display: flex; flex-direction: column; }
    .p_m_editor_toolbar { padding: 8px 15px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; gap: 5px; align-items: center; position: relative; }
    .p_m_tool_btn { width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border: none; background: transparent; color: #64748b; cursor: pointer; border-radius: 4px; font-size: 0.9rem; }
    .p_m_tool_btn:hover { background: #e2e8f0; color: #334155; }
    .p_m_separator { width: 1px; height: 18px; background: #cbd5e1; margin: 0 5px; }
    .p_m_emoji_picker { display: none; position: absolute; bottom: 45px; left: 10px; background: white; border: 1px solid #cbd5e1; box-shadow: 0 4px 15px rgba(0,0,0,0.1); padding: 10px; width: 240px; flex-wrap: wrap; gap: 5px; z-index: 100; border-radius: 6px; }
    .p_m_emoji_picker.active { display: flex; }
    .p_m_emoji_btn { font-size: 1.4rem; cursor: pointer; padding: 4px; transition: transform 0.2s; }
    .p_m_emoji_btn:hover { transform: scale(1.2); }
    .p_m_editor_wrapper { display: flex; padding: 10px 15px; gap: 10px; align-items: flex-end; }
    .p_m_editor_content { flex: 1; min-height: 40px; max-height: 120px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-family: inherit; font-size: 0.95rem; overflow-y: auto; outline: none; color: #000000 !important; background-color: #ffffff !important; }
    .p_m_editor_content:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1); }
    .p_m_editor_content ul { list-style-type: disc; padding-left: 20px; margin: 5px 0; }
    .p_m_editor_content ol { list-style-type: decimal; padding-left: 20px; margin: 5px 0; }
    .p_m_editor_content img { max-width: 100%; border-radius: 4px; border: 1px solid #eee; margin: 5px 0; }
    .p_m_video_container { position: relative; width: 100%; padding-bottom: 56.25%; height: 0; background: #000; border-radius: 4px; overflow: hidden; margin: 5px 0; }
    .p_m_video_container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; }
    .p_m_send_btn { width: 42px; height: 42px; border-radius: 50%; background: #4f46e5; color: white; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s; flex-shrink: 0; }
    .p_m_send_btn:hover { transform: scale(1.05); background: #4338ca; }
    @media (max-width: 768px) {
        .p_m_msg { max-width: 95%; }
        .p_m_wrapper { padding: 0; max-width: 100%; height: 100vh; }
        .p_m_header { padding: 15px; margin-bottom: 0; background: #fff; border-bottom: 1px solid #e2e8f0; }
        .p_m_container { border-radius: 0; border: none; }
        .p_m_sidebar { width: 100%; display: flex; }
        .p_m_chat_area { display: none; width: 100%; position: absolute; top: 0; bottom: 0; left: 0; right: 0; z-index: 10; height: 100%; }
        .p_m_container.chat-active .p_m_sidebar { display: none; }
        .p_m_container.chat-active .p_m_chat_area { display: flex; }
        .p_m_back_btn { display: block; }
    }
</style>

<?php if (!$currentUser): ?>
    <div style="text-align: center; padding: 40px; color: #64748b; background: white; border-radius: 8px; margin: 20px;">
        <i class="fa-solid fa-lock" style="font-size: 3rem; margin-bottom: 20px; color: #94a3b8;"></i>
        <h2 style="font-size: 1.5rem; margin-bottom: 10px; color: #334155;">Bejelentkezés Szükséges</h2>
        <p>Ennek a funkciónak a használatához be kell jelentkezned!</p>
    </div>
<?php else: ?>
    <div class="p_m_wrapper">

        <div class="p_m_header">
            <h1 class="p_m_title"><i class="fa-regular fa-envelope"></i> Üzenetek</h1>
            <a href="send_private_message.php" class="p_m_new_btn"><i class="fa-solid fa-pen-to-square"></i> Új üzenet</a>
        </div>

        <div class="p_m_container" id="messagesContainer">

            <!-- LEFT SIDEBAR -->
            <div class="p_m_sidebar">
                <div class="p_m_sidebar_header">
                    <div class="p_m_search_box">
                        <i class="fa-solid fa-magnifying-glass p_m_search_icon"></i>
                        <input type="text" class="p_m_search_input" id="searchChats" placeholder="Keresés...">
                    </div>
                    <div class="p_m_tabs">
                        <button class="p_m_tab active" onclick="filterChats('all', this)">Összes</button>
                        <button class="p_m_tab" onclick="filterChats('unread', this)">Olvasatlan</button>
                    </div>
                </div>

                <div class="p_m_list" id="chatList">
                    <?php if ($currentUser && !empty($threads)): ?>
                        <?php foreach ($threads as $name => $data): ?>
                        <div class="p_m_item <?php echo $data['is_read'] ? '' : 'unread'; ?>"
                             id="chat_<?php echo htmlspecialchars($name); ?>"
                             onclick="openChat('<?php echo htmlspecialchars($name); ?>')">
                            <div class="p_m_avatar_wrapper">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($name); ?>&background=random&color=fff" class="p_m_avatar">
                                <div class="p_m_status_indicator <?php echo ($data['status'] === 'Online') ? 'p_m_status_online' : 'p_m_status_offline'; ?>"></div>
                            </div>
                            <div class="p_m_info">
                                <div class="p_m_top_row">
                                    <span class="p_m_name"><?php echo htmlspecialchars($name); ?></span>
                                    <span class="p_m_time"><?php echo $data['last_time']; ?></span>
                                </div>
                                <span class="p_m_preview"><?php echo htmlspecialchars(mb_substr(strip_tags($data['last_message']), 0, 30)) . '...'; ?></span>
                            </div>
                            <?php if (!$data['is_read']): ?><div class="p_m_unread_dot"></div><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding:20px; text-align:center; color:#64748b;">Nincs üzeneted.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT CHAT AREA -->
            <div class="p_m_chat_area">

                <div class="p_m_chat_header">
                    <div class="p_m_chat_user">
                        <i class="fa-solid fa-arrow-left p_m_back_btn" onclick="closeChat()"></i>
                        <img src="" class="p_m_avatar" id="activeAvatar" style="width: 40px; height: 40px; display:none;">
                        <div>
                            <div class="p_m_header_name" id="activeName"></div>
                            <div class="p_m_header_status" id="activeStatus"></div>
                        </div>
                    </div>

                    <div class="p_m_chat_actions" style="position: relative;">
                        <i class="fa-solid fa-ellipsis-vertical" onclick="toggleChatMenu()"></i>
                        <div class="p_m_chat_menu" id="chatMenu">
                            <div class="p_m_menu_item"><i class="fa-regular fa-envelope-open"></i> Megjelölés olvasottként</div>
                            <div class="p_m_menu_item text-danger" id="deleteChatBtn"><i class="fa-regular fa-trash-can"></i> Beszélgetés törlése</div>
                        </div>
                    </div>
                </div>

                <div class="p_m_messages" id="messageList">
                    <div style="text-align:center;color:#94a3b8;margin-top:50px;">Válassz egy beszélgetést bal oldalon.</div>
                </div>

                <div class="p_m_input_area" id="inputArea" style="display:none;">
                    <div class="p_m_editor_toolbar">
                        <button class="p_m_tool_btn" title="Félkövér" onclick="formatText('bold')"><i class="fa-solid fa-bold"></i></button>
                        <button class="p_m_tool_btn" title="Dőlt" onclick="formatText('italic')"><i class="fa-solid fa-italic"></i></button>
                        <button class="p_m_tool_btn" title="Aláhúzott" onclick="formatText('underline')"><i class="fa-solid fa-underline"></i></button>
                        <div class="p_m_separator"></div>
                        <button class="p_m_tool_btn" title="Lista" onclick="formatText('insertUnorderedList')"><i class="fa-solid fa-list-ul"></i></button>
                        <div class="p_m_separator"></div>
                        <button class="p_m_tool_btn" title="Kép" onclick="insertMedia('image')"><i class="fa-regular fa-image"></i></button>
                        <button class="p_m_tool_btn" title="YouTube" onclick="insertMedia('youtube')"><i class="fa-brands fa-youtube"></i></button>
                        <button class="p_m_tool_btn" title="Emoji" onclick="toggleEmojiPicker()"><i class="fa-regular fa-face-smile"></i></button>

                        <div class="p_m_emoji_picker" id="emojiPicker">
                            <span class="p_m_emoji_btn" onclick="insertEmoji('😀')">😀</span>
                            <span class="p_m_emoji_btn" onclick="insertEmoji('😂')">😂</span>
                            <span class="p_m_emoji_btn" onclick="insertEmoji('👍')">👍</span>
                            <span class="p_m_emoji_btn" onclick="insertEmoji('😎')">😎</span>
                            <span class="p_m_emoji_btn" onclick="insertEmoji('🤔')">🤔</span>
                        </div>
                    </div>
                    
                    <div class="p_m_editor_wrapper">
                        <div class="p_m_editor_content" id="chatEditor" contenteditable="true" placeholder="Írj üzenetet..."></div>
                        <button class="p_m_send_btn" onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script>
        // PHP DATA
        const messagesData = <?php echo json_encode(array_combine(array_column($threads, 'partner_name'), array_column($threads, 'messages')) ?: (object)[]); ?>;
        const userData = <?php echo json_encode(array_combine(array_column($threads, 'partner_name'), $threads) ?: (object)[]); ?>;
        let currentChatUser = null;

        function openChat(userName) {
            currentChatUser = userName;
            document.getElementById('inputArea').style.display = 'flex';
            document.getElementById('activeAvatar').style.display = 'block';

            // Active Class
            document.querySelectorAll('.p_m_item').forEach(el => el.classList.remove('active'));
            const activeItem = document.getElementById(`chat_${userName}`);
            if (activeItem) {
                activeItem.classList.add('active');
                if(activeItem.classList.contains('unread')) {
                    activeItem.classList.remove('unread');
                    const dot = activeItem.querySelector('.p_m_unread_dot');
                    if(dot) dot.remove();

                    const user = userData[userName];
                    fetch('?action=mark_read', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({thread_id: user.id})
                    });
                }
            }

            // Header Info
            document.getElementById('activeName').textContent = userName;
            const user = userData[userName];
            document.getElementById('activeStatus').textContent = user.status;
            document.getElementById('activeAvatar').src = `https://ui-avatars.com/api/?name=${userName}&background=random&color=fff`;

            // Delete Link
            const deleteBtn = document.getElementById('deleteChatBtn');
            deleteBtn.onclick = function() {
                if(confirm('Biztosan törölni szeretnéd ezt a beszélgetést?')) {
                    window.location.href = '?action=delete&thread_id=' + user.id;
                }
            };

            // Messages
            const msgContainer = document.getElementById('messageList');
            msgContainer.innerHTML = '';

            const msgs = messagesData[userName] || [];
            if(msgs.length === 0) {
                msgContainer.innerHTML = '<div style="text-align:center;color:#94a3b8;margin-top:20px;">Nincs még üzenet.</div>';
            } else {
                msgs.forEach(msg => {
                    const msgClass = msg.type === 'received' ? 'p_m_msg_received' : 'p_m_msg_sent';
                    let statusIcon = '';
                    if (msg.type === 'sent') {
                         if(msg.is_read) {
                             statusIcon = '<i class="fa-solid fa-check-double" title="Olvasva" style="font-size:0.7em; margin-left:5px; color:#fff;"></i>';
                         } else {
                             statusIcon = '<i class="fa-solid fa-check" title="Elküldve" style="font-size:0.7em; margin-left:5px; color:#ddd;"></i>';
                         }
                    }
                    msgContainer.innerHTML += `
                        <div class="p_m_msg ${msgClass}">
                            ${msg.text}
                            <span class="p_m_msg_time">${msg.time} ${statusIcon}</span>
                        </div>`;
                });
            }
            msgContainer.scrollTop = msgContainer.scrollHeight;

            if (window.innerWidth <= 768) {
                document.getElementById('messagesContainer').classList.add('chat-active');
            }
        }

        function closeChat() {
            document.getElementById('messagesContainer').classList.remove('chat-active');
        }

        function sendMessage() {
            if(!currentChatUser) return;
            const editor = document.getElementById('chatEditor');
            const content = editor.innerHTML;
            const textContent = editor.innerText.trim();

            if(!textContent && !content.includes('<img') && !content.includes('<iframe')) return;

            const tempId = 'msg-' + Date.now();
            const msgContainer = document.getElementById('messageList');
            const newMsgHtml = `
                <div class="p_m_msg p_m_msg_sent" id="${tempId}" style="opacity:0.7">
                    ${content}
                    <span class="p_m_msg_time">Küldés... <i class="fa-solid fa-spinner fa-spin" style="margin-left:5px;"></i></span>
                </div>`;
            msgContainer.insertAdjacentHTML('beforeend', newMsgHtml);
            msgContainer.scrollTop = msgContainer.scrollHeight;
            editor.innerHTML = '';

            // Update Sidebar (Move to top)
            const chatItem = document.getElementById('chat_' + currentChatUser);
            if (chatItem) {
                const parent = chatItem.parentNode;
                parent.insertBefore(chatItem, parent.firstChild);
                chatItem.querySelector('.p_m_preview').innerHTML = '<em>Te:</em> ' + (textContent.substring(0, 30) || 'Média tartalom') + '...';
                chatItem.querySelector('.p_m_time').textContent = 'épp most';
            }

            // AJAX Send
            fetch('?action=send_ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ recipient: currentChatUser, content: content })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    const msgEl = document.getElementById(tempId);
                    if(msgEl) {
                        msgEl.style.opacity = '1';
                        msgEl.querySelector('.p_m_msg_time').innerHTML = data.time + ' <i class="fa-solid fa-check" title="Elküldve" style="font-size:0.7em; margin-left:5px; color:#ddd;"></i>';
                    }
                } else {
                    alert("Hiba történt az üzenet küldésekor.");
                    const msgEl = document.getElementById(tempId);
                    if(msgEl) msgEl.remove();
                }
            });
        }

        // --- FILTER & SEARCH ---
        document.getElementById('searchChats').addEventListener('input', function(e) {
            const val = e.target.value.toLowerCase();
            document.querySelectorAll('.p_m_item').forEach(el => {
                const name = el.querySelector('.p_m_name').textContent.toLowerCase();
                el.style.display = name.includes(val) ? 'flex' : 'none';
            });
        });

        function filterChats(type, btn) {
            document.querySelectorAll('.p_m_tab').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            document.querySelectorAll('.p_m_item').forEach(el => {
                if (type === 'unread') {
                    el.style.display = el.classList.contains('unread') ? 'flex' : 'none';
                } else {
                    el.style.display = 'flex';
                }
            });
        }

        // --- EDITOR LOGIKA ---
        const editor = document.getElementById('chatEditor');
        let lastRange = null;
        function saveSelection() {
            const sel = window.getSelection();
            if (sel.getRangeAt && sel.rangeCount) lastRange = sel.getRangeAt(0);
        }
        editor.addEventListener('keyup', saveSelection);
        editor.addEventListener('mouseup', saveSelection);
        editor.addEventListener('focus', saveSelection);

        function formatText(command, value = null) {
            document.execCommand(command, false, value);
            editor.focus();
        }
        function insertMedia(type) {
            let url;
            if (type === 'image') {
                url = prompt("Kép URL:");
                if (url) formatText('insertImage', url);
            } else if (type === 'youtube') {
                url = prompt("YouTube URL:");
                if (url) {
                    let videoId = url.split('v=')[1] || url.split('/').pop();
                    const embedUrl = `https://www.youtube.com/embed/${videoId}`;
                    document.execCommand('insertHTML', false, `<div class="p_m_video_container"><iframe src="${embedUrl}" allowfullscreen></iframe></div><br>`);
                }
            }
        }
        function toggleEmojiPicker() { document.getElementById('emojiPicker').classList.toggle('active'); }
        function insertEmoji(emoji) {
            editor.focus();
            document.execCommand('insertText', false, emoji);
            saveSelection();
        }
        function toggleChatMenu() {
            event.stopPropagation();
            document.getElementById('chatMenu').classList.toggle('active');
        }

        document.addEventListener('click', function(e) {
            const emojiPicker = document.getElementById('emojiPicker');
            const emojiBtn = document.querySelector('.p_m_tool_btn[title="Emoji"]');
            if (emojiPicker && !emojiPicker.contains(e.target) && !emojiBtn.contains(e.target)) emojiPicker.classList.remove('active');

            const chatMenu = document.getElementById('chatMenu');
            const menuBtn = document.querySelector('.p_m_chat_actions i');
            if (chatMenu && chatMenu.classList.contains('active') && !chatMenu.contains(e.target) && !menuBtn.contains(e.target)) chatMenu.classList.remove('active');
        });

        // Desktop Init
        if (window.innerWidth > 768) {
            const users = Object.keys(userData);
            if (users.length > 0) openChat(users[0]);
        }
    </script>
<?php endif; ?>

            </div> <!-- col-left end -->
            <?php include __DIR__ . '/includes/sidebar.php'; ?>
        </div> <!-- layout end -->
    </div> <!-- container end -->
</main>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
