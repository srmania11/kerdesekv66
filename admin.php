<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser($pdo);
if (!$currentUser || !$currentUser['is_admin']) {
    die("Hozzáférés megtagadva.");
}

// Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete_q') {
        $pdo->prepare("DELETE FROM qc_questions WHERE id = ?")->execute([$id]);
        header("Location: admin.php"); exit;
    }
    if ($_GET['action'] === 'close_q') {
        $pdo->prepare("UPDATE qc_questions SET status = 'closed' WHERE id = ?")->execute([$id]);
        header("Location: admin.php"); exit;
    }
    if ($_GET['action'] === 'resolve_report') {
        $pdo->prepare("UPDATE qc_reports SET status = 'resolved' WHERE id = ?")->execute([$id]);
        header("Location: admin.php"); exit;
    }
}

// Fetch Reports
$reports = $pdo->query("SELECT r.*, u.username FROM qc_reports r JOIN qc_users u ON r.reporter_id = u.user_id WHERE r.status = 'pending'")->fetchAll();

// Fetch Latest Questions
$questions = $pdo->query("SELECT * FROM qc_questions ORDER BY created_at DESC LIMIT 20")->fetchAll();
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <?php renderSeoHead(['title' => 'Admin Panel', 'description' => '', 'og_type' => 'website']); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.ico" sizes="any">
	<link rel="icon" href="/icon.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,700;1,700&family=Inter:wght@400;600;800&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
	<link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/layout.css">
	<link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/article.css">
	<link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/widget.css">
</head>
<body class="min-h-screen relative">
<?php require_once __DIR__ . '/../inc/header_menu.php'; ?>
<main class="header-content-wrapper mt-0 pb-0">
<main id="main" class="site-main">
    <div class="cikk-fo-container">
        <div class="cikk-fo-layout">
            <div class="cikk-fo-col-left" style="background: linear-gradient(135deg, rgba(11, 25, 30, 0.9), rgba(10, 15, 20, 0.9)); padding: 20px; color: white;">

                <h1 class="text-3xl font-bold mb-6">Adminisztrációs Panel</h1>

                <div class="bg-white text-black p-6 rounded shadow mb-8">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Jelentések (<?php echo count($reports); ?>)</h2>
                    <?php if ($reports): ?>
                        <ul class="space-y-3">
                        <?php foreach($reports as $r): ?>
                            <li class="bg-red-50 p-3 rounded border border-red-200">
                                <div class="flex justify-between">
                                    <strong><?php echo htmlspecialchars($r['reason_category']); ?></strong>
                                    <span class="text-sm text-gray-500"><?php echo $r['created_at']; ?></span>
                                </div>
                                <p class="text-sm"><?php echo htmlspecialchars($r['reason_details']); ?></p>
                                <p class="text-xs text-gray-500 mt-1">Jelentő: <?php echo htmlspecialchars($r['username']); ?> | Típus: <?php echo $r['target_type']; ?> ID: <?php echo $r['target_id']; ?></p>
                                <div class="mt-2">
                                    <a href="?action=resolve_report&id=<?php echo $r['id']; ?>" class="text-blue-600 hover:underline text-sm">Megoldva</a>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-gray-500">Nincs függőben lévő jelentés.</p>
                    <?php endif; ?>
                </div>

                <div class="bg-white text-black p-6 rounded shadow">
                    <h2 class="text-xl font-bold mb-4 border-b pb-2">Legutóbbi Kérdések</h2>
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2">Cím</th>
                                <th class="py-2">Státusz</th>
                                <th class="py-2">Művelet</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($questions as $q): ?>
                            <tr class="border-b">
                                <td class="py-2"><a href="<?php echo generateUrl('question', $q['id'], $q['slug']); ?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($q['title']); ?></a></td>
                                <td class="py-2"><?php echo $q['status']; ?></td>
                                <td class="py-2 flex gap-2">
                                    <?php if($q['status'] !== 'closed'): ?>
                                        <a href="?action=close_q&id=<?php echo $q['id']; ?>" class="text-orange-600 hover:underline">Lezárás</a>
                                    <?php endif; ?>
                                    <a href="?action=delete_q&id=<?php echo $q['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Biztos törlöd?')">Törlés</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
            <aside class="cikk-fo-col-right">
                <!-- Empty Sidebar -->
            </aside>
        </div>
    </div>
</main>
</main>
</body>
</html>
