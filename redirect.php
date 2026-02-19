<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$url = isset($_GET['url']) ? $_GET['url'] : '';

// Basic validation to prevent open redirect to malicious data protocols, though we warn the user anyway.
if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    die("Érvénytelen URL.");
}

// Decode if it was encoded
$displayUrl = htmlspecialchars($url);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Külső hivatkozás - SilverPC Fórum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .redirect-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
            padding: 40px;
            text-align: center;
            border-top: 5px solid #4f46e5;
        }
        .icon-box {
            width: 80px;
            height: 80px;
            background: #eff6ff;
            color: #4f46e5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 25px auto;
        }
        .url-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 10px;
            border-radius: 6px;
            font-family: monospace;
            color: #475569;
            word-break: break-all;
            margin: 20px 0;
            font-size: 0.9rem;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: #4f46e5;
            color: white;
        }
        .btn-primary:hover {
            background: #4338ca;
        }
        .btn-secondary {
            background: white;
            border: 1px solid #cbd5e1;
            color: #475569;
            margin-right: 10px;
        }
        .btn-secondary:hover {
            background: #f1f5f9;
            color: #1e293b;
        }
    </style>
</head>
<body>

    <div class="redirect-card">
        <div class="icon-box">
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Külső oldalra lépsz</h1>
        <p class="text-slate-600 mb-6">
            Éppen elhagyni készülsz a SilverPC Fórum oldalát. Az alábbi hivatkozás egy külső weboldalra mutat:
        </p>

        <div class="url-box">
            <?php echo $displayUrl; ?>
        </div>

        <p class="text-slate-500 text-sm mb-8">
            Nem vállalunk felelősséget a külső oldalak tartalmáért. Csak akkor folytasd, ha megbízol a forrásban!
        </p>

        <div class="flex justify-center">
            <button onclick="window.close()" class="btn btn-secondary">Mégse</button>
            <a href="<?php echo htmlspecialchars($url); ?>" rel="nofollow noopener noreferrer" class="btn btn-primary">Tovább az oldalra <i class="fa-solid fa-arrow-right ml-2"></i></a>
        </div>
    </div>

</body>
</html>
