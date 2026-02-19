<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

// 1. Validate Input
$answer_id = isset($_GET['answer_id']) ? (int)$_GET['answer_id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

if ($answer_id <= 0) {
    echo '<div class="text-red-500 p-4">Érvénytelen válasz azonosító.</div>';
    exit;
}

if ($page < 1) $page = 1;
if (!in_array($limit, [5, 10, 15])) $limit = 5;

$currentUser = getCurrentUser($pdo);
$offset = ($page - 1) * $limit;

// 2. Fetch Total Count
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM qc_answer_replies WHERE answer_id = ?");
$stmtCount->execute([$answer_id]);
$total_replies = $stmtCount->fetchColumn();
$total_pages = ceil($total_replies / $limit);

// 3. Fetch Replies (with User Vote Status)
$userId = $currentUser ? $currentUser['user_id'] : 0;

$stmt = $pdo->prepare("
    SELECT r.*, u.username, u.custom_title, u.reputation_points, u.is_admin,
    COALESCE(v.vote_type, NULL) as user_vote
    FROM qc_answer_replies r
    LEFT JOIN qc_users u ON r.user_id = u.user_id
    LEFT JOIN qc_reply_votes v ON r.id = v.reply_id AND v.user_id = :user_id
    WHERE r.answer_id = :answer_id
    ORDER BY r.created_at ASC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
$stmt->bindValue(':answer_id', $answer_id, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$replies = $stmt->fetchAll();

?>
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
        <h3 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-comments text-indigo-500 mr-2"></i> Válaszok erre a válaszra (<?php echo $total_replies; ?>)</h3>
        <button onclick="closeAnswerReplies(<?php echo $answer_id; ?>)" class="text-sm text-gray-500 hover:text-gray-800 transition-colors">
            <i class="fa-solid fa-xmark"></i> Bezárás
        </button>
    </div>

    <!-- Replies List -->
    <div class="space-y-4">
        <?php if (empty($replies)): ?>
            <div class="text-center p-5 bg-gray-50 rounded-lg text-gray-500 italic">
                Még nem érkezett válasz erre a hozzászólásra. Legyél te az első!
            </div>
        <?php else: ?>
            <?php foreach ($replies as $reply): ?>
                <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-2">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($reply['username'] ?: 'Vendég'); ?>&background=random&color=fff&size=24" class="rounded-full w-6 h-6">
                            <span class="font-bold text-gray-900 text-sm"><?php echo htmlspecialchars($reply['username'] ?: 'Vendég'); ?></span>
                            <?php if (!empty($reply['custom_title'])): ?>
                                <span class="text-xs text-gray-500">• <?php echo htmlspecialchars($reply['custom_title']); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="text-xs text-gray-400"><?php echo time_elapsed_string($reply['created_at']); ?></span>
                    </div>

                    <div class="text-gray-800 text-sm leading-relaxed q_v_text">
                        <?php echo process_content_for_display($reply['content'], $currentUser); ?>
                    </div>

                    <!-- Like / Dislike Buttons -->
                    <div class="flex justify-start md:justify-end items-center gap-4 mt-3 pt-3 border-t border-gray-100">
                        <?php
                            $likes = (int)($reply['likes'] ?? 0);
                            $dislikes = (int)($reply['dislikes'] ?? 0);
                            $userVote = $reply['user_vote'] ?? null;

                            // DEFAULT STYLES
                            $likeBtnClass = 'text-gray-500 hover:bg-green-50 hover:text-green-600';
                            $likeIconClass = '';
                            $likeCountClass = 'bg-gray-100 text-gray-600 group-hover:bg-green-100 group-hover:text-green-700';

                            $dislikeBtnClass = 'text-gray-500 hover:bg-red-50 hover:text-red-600';
                            $dislikeIconClass = '';
                            $dislikeCountClass = 'bg-gray-100 text-gray-600 group-hover:bg-red-100 group-hover:text-red-700';

                            // LOGIC: My Vote overrides General Counts

                            // 1. LIKE BUTTON
                            if ($userVote === 'like') {
                                // MY ACTIVE LIKE
                                $likeBtnClass = 'bg-green-600 text-white shadow-lg scale-105 ring-2 ring-offset-1 ring-green-600 hover:bg-green-700';
                                $likeIconClass = 'text-white';
                                $likeCountClass = 'bg-white/20 text-white border border-white/30';
                            } elseif ($likes > 0) {
                                // HAS LIKES (BUT NOT MINE)
                                $likeBtnClass = 'text-green-600 bg-green-50';
                                $likeIconClass = 'text-green-600';
                                $likeCountClass = 'bg-green-100 text-green-700';
                            }

                            // 2. DISLIKE BUTTON
                            if ($userVote === 'dislike') {
                                // MY ACTIVE DISLIKE
                                $dislikeBtnClass = 'bg-red-600 text-white shadow-lg scale-105 ring-2 ring-offset-1 ring-red-600 hover:bg-red-700';
                                $dislikeIconClass = 'text-white';
                                $dislikeCountClass = 'bg-white/20 text-white border border-white/30';
                            } elseif ($dislikes > 0) {
                                // HAS DISLIKES (BUT NOT MINE)
                                $dislikeBtnClass = 'text-red-600 bg-red-50';
                                $dislikeIconClass = 'text-red-600';
                                $dislikeCountClass = 'bg-red-100 text-red-700';
                            }
                        ?>

                        <!-- Like Button -->
                        <button id="reply-vote-btn-like-<?php echo $reply['id']; ?>" onclick="voteReply(<?php echo $reply['id']; ?>, 'like', this)" class="group flex items-center gap-2 transition-all duration-200 focus:outline-none rounded-full px-2 py-1 <?php echo $likeBtnClass; ?>" title="Tetszik">
                            <div class="relative flex items-center justify-center w-6 h-6">
                                <i class="fa-regular fa-thumbs-up text-lg group-hover:scale-110 transition-transform <?php echo $likeIconClass; ?>"></i>
                            </div>
                            <span id="reply-vote-count-like-<?php echo $reply['id']; ?>" class="font-bold text-xs px-2.5 py-1 rounded-md transition-colors reply-likes-count min-w-[30px] text-center shadow-sm <?php echo $likeCountClass; ?>">
                                <?php echo $likes; ?>
                            </span>
                        </button>

                        <!-- Dislike Button -->
                        <button id="reply-vote-btn-dislike-<?php echo $reply['id']; ?>" onclick="voteReply(<?php echo $reply['id']; ?>, 'dislike', this)" class="group flex items-center gap-2 transition-all duration-200 focus:outline-none rounded-full px-2 py-1 <?php echo $dislikeBtnClass; ?>" title="Nem tetszik">
                            <div class="relative flex items-center justify-center w-6 h-6">
                                <i class="fa-regular fa-thumbs-down text-lg group-hover:scale-110 transition-transform <?php echo $dislikeIconClass; ?>"></i>
                            </div>
                            <span id="reply-vote-count-dislike-<?php echo $reply['id']; ?>" class="font-bold text-xs px-2.5 py-1 rounded-md transition-colors reply-dislikes-count min-w-[30px] text-center shadow-sm <?php echo $dislikeCountClass; ?>">
                                <?php echo $dislikes; ?>
                            </span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center gap-2 mt-4">
        <?php if ($page > 1): ?>
            <button onclick="changeReplyPage(<?php echo $answer_id; ?>, <?php echo $page - 1; ?>)" class="px-3 py-1 bg-white border border-gray-300 text-gray-600 rounded text-sm hover:bg-gray-50">Előző</button>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <button onclick="changeReplyPage(<?php echo $answer_id; ?>, <?php echo $i; ?>)" class="px-3 py-1 border border-gray-300 rounded text-sm <?php echo ($i == $page) ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 hover:bg-gray-50'; ?>">
                <?php echo $i; ?>
            </button>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <button onclick="changeReplyPage(<?php echo $answer_id; ?>, <?php echo $page + 1; ?>)" class="px-3 py-1 bg-white border border-gray-300 text-gray-600 rounded text-sm hover:bg-gray-50">Következő</button>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Reply Form -->
    <div class="mt-6 border-t border-gray-200 pt-6">
        <?php if ($currentUser): ?>
            <form onsubmit="submitReply(event, <?php echo $answer_id; ?>)">
                <div class="mb-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Válasz írása</label>
                    <textarea id="reply-content-<?php echo $answer_id; ?>" class="w-full border border-gray-300 rounded-lg p-3 text-sm text-gray-900 focus:ring-indigo-500 focus:border-indigo-500" rows="3" placeholder="Írd ide a válaszodat..." required></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeAnswerReplies(<?php echo $answer_id; ?>)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Mégse</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fa-solid fa-paper-plane mr-1"></i> Küldés
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="bg-orange-50 border-l-4 border-orange-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-orange-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-orange-700">
                            A válaszadás funkció csak bejelentkezett felhasználók számára elérhető.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
