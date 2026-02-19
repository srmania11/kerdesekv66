<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if ($query) {
    $stmt = $pdo->prepare("SELECT * FROM qc_questions WHERE title LIKE ? OR content LIKE ? ORDER BY created_at DESC LIMIT 20");
    $term = "%$query%";
    $stmt->execute([$term, $term]);
    $results = $stmt->fetchAll();
}

$currentUser = getCurrentUser($pdo);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <?php renderSeoHead(['title' => 'Keresés: ' . htmlspecialchars($query), 'description' => 'Keresési eredmények.', 'og_type' => 'website']); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,700;1,700&family=Inter:wght@400;600;800&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
	<link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/layout.css">
	<link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/article.css">
	<link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/widget.css">

    <!-- Reuse Kategoria Styles -->
    <style>
        .q_p_wrapper { width: 100%; margin: 0 auto; background: #ffffff; box-shadow: 0 10px 40px rgba(0,0,0,0.06); padding-bottom: 20px; }
        .q_p_category_header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; padding: 35px 30px; }
        .q_p_cat_title { font-size: 2rem; font-weight: 800; margin: 0; }
        .q_p_card { background: #ffffff; border-bottom: 1px solid #e2e8f0; }
        .q_p_summary { padding: 20px 25px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; }
        .q_p_summary:hover { background-color: #f8fafc; }
        .q_p_title { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
        .q_p_title a { text-decoration: none; color: inherit; }
        .q_p_meta { font-size: 0.85rem; color: #64748b; margin-top: 5px; }
    </style>
</head>
<body class="min-h-screen relative">
<?php require_once __DIR__ . '/../inc/header_menu.php'; ?>
<main class="header-content-wrapper mt-0 pb-0">
<main id="main" class="site-main">
    <div class="cikk-fo-container">
        <div class="cikk-fo-layout">
            <div class="cikk-fo-col-left" style="background: linear-gradient(135deg, rgba(11, 25, 30, 0.9), rgba(10, 15, 20, 0.9));">

                <div class="q_p_wrapper">
                    <div class="q_p_category_header">
                        <h1 class="q_p_cat_title">Keresés: "<?php echo htmlspecialchars($query); ?>"</h1>
                        <p style="margin-top:10px; opacity:0.8;"><?php echo count($results); ?> találat</p>
                    </div>

                    <div style="padding: 20px;">
                        <form method="GET" action="search.php" style="display:flex; gap:10px;">
                            <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Keresés..." style="flex:1; padding:10px; border:1px solid #cbd5e1;">
                            <button type="submit" style="padding:10px 20px; background:#4f46e5; color:white; border:none;">Keresés</button>
                        </form>
                    </div>

                    <?php if ($results): ?>
                        <?php foreach ($results as $q):
                            $url = generateUrl('question', $q['id'], $q['slug']);
                        ?>
                        <div class="q_p_card">
                            <div class="q_p_summary" onclick="window.location.href='<?php echo $url; ?>'">
                                <div>
                                    <div class="q_p_title">
                                        <a href="<?php echo $url; ?>"><?php echo htmlspecialchars($q['title']); ?></a>
                                    </div>
                                    <div class="q_p_meta">
                                        <?php echo time_elapsed_string($q['created_at']); ?> • <?php echo $q['status']; ?>
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-right" style="color:#cbd5e1;"></i>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding:30px; text-align:center; color:#64748b;">Nincs találat.</div>
                    <?php endif; ?>
                </div>

            </div>
            <aside class="cikk-fo-col-right">
            </aside>
        </div>
    </div>
</main>
</main>
</body>
</html>
