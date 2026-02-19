<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

// Check if requested via AJAX or at least GET
$answer_id = isset($_GET['answer_id']) ? (int)$_GET['answer_id'] : 0;

if ($answer_id <= 0) {
    echo '<div class="text-red-500 p-4">Érvénytelen válasz azonosító.</div>';
    exit;
}

$currentUser = getCurrentUser($pdo);

// Fetch edits
$stmt = $pdo->prepare("
    SELECT ae.*, u.username, u.custom_title
    FROM qc_answer_edits ae
    LEFT JOIN qc_users u ON ae.user_id = u.user_id
    WHERE ae.answer_id = ?
    ORDER BY ae.edited_at ASC
");
$stmt->execute([$answer_id]);
$edits = $stmt->fetchAll();

// If no edits found (should not happen if button is shown), fallback to current
if (empty($edits)) {
    // Fetch current version
    $stmtCurrent = $pdo->prepare("
        SELECT a.*, u.username, u.custom_title
        FROM qc_answers a
        LEFT JOIN qc_users u ON a.user_id = u.user_id
        WHERE a.id = ?
    ");
    $stmtCurrent->execute([$answer_id]);
    $current = $stmtCurrent->fetch();

    if ($current) {
        $edits[] = [
            'username' => $current['username'],
            'edited_at' => $current['created_at'],
            'content' => $current['content'],
            'user_id' => $current['user_id']
        ];
    } else {
        echo '<div class="text-red-500 p-4">A válasz nem található.</div>';
        exit;
    }
}
?>
<div class="space-y-6 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
        <h3 class="text-lg font-bold text-gray-800"><i class="fa-solid fa-clock-rotate-left text-indigo-500 mr-2"></i> Válasz előzményei</h3>
        <button onclick="closeAnswerHistory(<?php echo $answer_id; ?>)" class="text-sm text-gray-500 hover:text-gray-800 transition-colors">
            <i class="fa-solid fa-xmark"></i> Bezárás
        </button>
    </div>

    <div class="relative border-l-2 border-indigo-200 ml-3 space-y-8 pl-6 pb-2">
        <?php
        $total_versions = count($edits);
        $version_count = 1;
        foreach ($edits as $edit):
            $is_current = ($version_count === $total_versions);
            $is_original = ($version_count === 1);
        ?>
            <div class="relative group">
                <span class="absolute -left-[33px] top-0 flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 ring-4 ring-white group-hover:bg-indigo-200 transition-colors">
                    <?php if ($is_current): ?>
                        <i class="fa-solid fa-check text-indigo-600 text-xs"></i>
                    <?php else: ?>
                        <span class="text-xs font-bold text-indigo-600"><?php echo $version_count; ?></span>
                    <?php endif; ?>
                </span>

                <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 flex-wrap">
                    <span class="font-bold text-gray-900"><?php echo htmlspecialchars($edit['username'] ?: 'Ismeretlen'); ?></span>
                    <span class="text-xs">•</span>
                    <time datetime="<?php echo $edit['edited_at']; ?>" class="text-xs"><?php echo time_elapsed_string($edit['edited_at'], true); ?></time>

                    <?php if ($is_original): ?>
                        <span class="ml-2 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 border border-gray-200">Eredeti</span>
                    <?php endif; ?>

                    <?php if ($is_current): ?>
                        <span class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-700 border border-emerald-200">Jelenlegi verzió</span>
                    <?php endif; ?>
                </div>

                <div class="rounded-lg border <?php echo $is_current ? 'border-emerald-200 bg-white ring-1 ring-emerald-100' : 'border-gray-200 bg-gray-50'; ?> p-4 text-sm text-gray-700 shadow-sm hover:shadow-md transition-shadow">
                    <div class="q_v_text text-gray-800" style="font-size: 0.95rem;">
                        <?php echo process_content_for_display($edit['content'], $currentUser); ?>
                    </div>
                </div>
            </div>
        <?php
            $version_count++;
        endforeach;
        ?>
    </div>

    <div class="text-center pt-2">
        <button onclick="closeAnswerHistory(<?php echo $answer_id; ?>)" class="inline-flex items-center gap-2 rounded-md bg-white px-5 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-all hover:shadow">
            <i class="fa-solid fa-arrow-left"></i> Vissza a válaszhoz
        </button>
    </div>
</div>

<style>
    .animate-fade-in { animation: fadeIn 0.3s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>
