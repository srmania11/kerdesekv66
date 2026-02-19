<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

if (!function_exists('get_client_ip')) {
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
}

// Check user login
$currentUser = getCurrentUser($pdo);
if (!$currentUser) {
    header("Location: login.php");
    exit;
}

$userId = $currentUser['user_id'];
$user = $currentUser; // Use current user data

// Fetch current social links
$socialLinks = !empty($user['social_links']) ? json_decode($user['social_links'], true) : [];

// Scan for default avatars
$defaultAvatarsDir = __DIR__ . '/assets/default_avatars';
$defaultAvatars = [];
if (is_dir($defaultAvatarsDir)) {
    $files = scandir($defaultAvatarsDir);
    foreach ($files as $file) {
        // Filter for specific naming convention (150x150.webp)
        if (strpos($file, '_150x150.webp') !== false) {
            $defaultAvatars[] = 'assets/default_avatars/' . $file;
        }
    }
}

// Page Title
$pageTitle = "Profil Szerkesztése";
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <title><?php echo $pageTitle; ?> - SilverPC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#008080">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/layout.css">
    <style>
        /* Custom Styles for File Upload */
        .file-drop-zone {
            border: 2px dashed #475569;
            transition: all 0.3s ease;
        }
        .file-drop-zone:hover, .file-drop-zone.drag-over {
            border-color: #6366f1;
            background-color: rgba(99, 102, 241, 0.1);
        }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-300 min-h-screen font-['Inter']">

<?php
$headerPath = __DIR__ . '/../inc/header_menu.php';
if (file_exists($headerPath)) {
    require_once $headerPath;
}
?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex flex-col md:flex-row gap-8">

        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-64 flex-shrink-0">
            <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden sticky top-24">
                <div class="p-6 border-b border-slate-700">
                    <h2 class="text-white font-bold text-lg">Beállítások</h2>
                    <p class="text-xs text-slate-500 mt-1">Kezeld a profilod adatait</p>
                </div>
                <nav class="p-2 space-y-1">
                    <button onclick="switchTab('basic')" id="tab-btn-basic" class="w-full text-left px-4 py-3 rounded-lg flex items-center gap-3 text-sm font-medium transition-colors bg-indigo-600 text-white">
                        <i class="fas fa-user-circle w-5 text-center"></i> Alapadatok
                    </button>
                    <button onclick="switchTab('images')" id="tab-btn-images" class="w-full text-left px-4 py-3 rounded-lg flex items-center gap-3 text-sm font-medium transition-colors text-slate-400 hover:bg-slate-700 hover:text-white">
                        <i class="fas fa-image w-5 text-center"></i> Profilképek
                    </button>
                    <button onclick="switchTab('social')" id="tab-btn-social" class="w-full text-left px-4 py-3 rounded-lg flex items-center gap-3 text-sm font-medium transition-colors text-slate-400 hover:bg-slate-700 hover:text-white">
                        <i class="fas fa-share-alt w-5 text-center"></i> Közösségi Linkek
                    </button>
                    <button onclick="switchTab('security')" id="tab-btn-security" class="w-full text-left px-4 py-3 rounded-lg flex items-center gap-3 text-sm font-medium transition-colors text-slate-400 hover:bg-slate-700 hover:text-white">
                        <i class="fas fa-shield-alt w-5 text-center"></i> Biztonság
                    </button>
                </nav>
                <div class="p-4 border-t border-slate-700 mt-2">
                    <a href="profile.php" class="text-slate-500 hover:text-white text-xs flex items-center gap-2 transition-colors">
                        <i class="fas fa-arrow-left"></i> Vissza a profilhoz
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 min-w-0">
            <form id="profile-form" onsubmit="return false;">

                <!-- Feedback Area -->
                <div id="form-message" class="hidden mb-6 p-4 rounded-lg text-sm font-bold"></div>

                <!-- SECTION: BASIC INFO -->
                <div id="tab-content-basic" class="tab-content space-y-6">
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 md:p-8">
                        <h3 class="text-xl font-bold text-white mb-6 border-b border-slate-700 pb-4">Alapadatok</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Full Name -->
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Teljes Név</label>
                                <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors" placeholder="Pl. Kovács János">
                                <p class="text-xs text-slate-500 mt-1">Maximum 3 szó (pl. Nagy János Béla).</p>
                            </div>

                            <!-- Email -->
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-2">E-mail Cím</label>
                                <div class="relative">
                                    <i class="fas fa-envelope absolute left-4 top-3.5 text-slate-500"></i>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors" placeholder="pelda@email.com">
                                </div>
                            </div>

                            <!-- Username (Read Only) -->
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Felhasználónév (Nem módosítható)</label>
                                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled class="w-full bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-slate-500 cursor-not-allowed">
                            </div>

                            <!-- Bio -->
                            <div class="col-span-2">
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Bemutatkozás</label>
                                <textarea name="bio" rows="4" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors" placeholder="Írj magadról pár mondatot..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                <p class="text-xs text-slate-500 mt-1 text-right">Max 500 karakter.</p>
                            </div>

                            <!-- Location -->
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Tartózkodási Hely</label>
                                <div class="relative">
                                    <i class="fas fa-map-marker-alt absolute left-4 top-3.5 text-slate-500"></i>
                                    <input type="text" name="location" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors" placeholder="Budapest, Hungary">
                                </div>
                            </div>

                            <!-- Website -->
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Weboldal</label>
                                <div class="relative">
                                    <i class="fas fa-globe absolute left-4 top-3.5 text-slate-500"></i>
                                    <input type="url" name="website" value="<?php echo htmlspecialchars($user['website'] ?? ''); ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors" placeholder="https://pelda.hu">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION: IMAGES -->
                <div id="tab-content-images" class="tab-content space-y-6 hidden">
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 md:p-8">
                        <h3 class="text-xl font-bold text-white mb-6 border-b border-slate-700 pb-4">Profilképek</h3>

                        <!-- Avatar Upload -->
                        <div class="mb-8">
                            <label class="block text-sm font-bold text-white mb-4">Profilkép</label>
                            <div class="flex items-center gap-6">
                                <div class="relative w-24 h-24 md:w-32 md:h-32 rounded-2xl bg-slate-900 border-2 border-slate-700 overflow-hidden shrink-0 group">
                                    <img id="avatar-preview" src="<?php echo !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['username']) . '&background=6366f1&color=fff&size=256'; ?>" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                        <i class="fas fa-camera text-white text-2xl"></i>
                                    </div>
                                    <button type="button" id="delete-avatar-btn" onclick="deleteAvatar()" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors z-20 shadow-md <?php echo empty($user['avatar']) ? 'hidden' : ''; ?>" title="Törlés">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                                <div class="flex-1">
                                    <?php if (has_storage_space($currentUser)): ?>
                                    <div class="file-drop-zone rounded-lg p-6 text-center cursor-pointer relative" id="avatar-drop-zone">
                                        <input type="file" id="avatar-input" accept="image/png, image/jpeg, image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-slate-500 mb-2"></i>
                                        <p class="text-sm font-medium text-slate-300">Húzd ide a képet vagy kattints a feltöltéshez</p>
                                        <p class="text-xs text-slate-500 mt-1">JPG, PNG, WEBP (Max 5MB)</p>
                                    </div>
                                    <?php else: ?>
                                    <div class="bg-red-500/10 border border-red-500/30 text-red-500 p-6 rounded-lg text-center font-bold">
                                        <i class="fas fa-exclamation-triangle text-2xl mb-2 block"></i> A tárhelyed megtelt! Nem tölthetsz fel új profilképet.
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Default Avatars Selection -->
                            <div class="mt-4 pt-4 border-t border-slate-700/50 w-full overflow-hidden">
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-3">Vagy válassz az alapértelmezett képek közül:</label>
                                <div id="default-avatars-container" class="flex gap-4 overflow-x-auto no-scrollbar cursor-grab active:cursor-grabbing pb-2 select-none touch-pan-x max-w-full"
                                     onmousedown="startDrag(event)" onmouseleave="stopDrag()" onmouseup="stopDrag()" onmousemove="drag(event)">

                                    <!-- No Image Option -->
                                    <div onclick="selectDefaultAvatar('')" class="shrink-0 w-16 h-16 rounded-lg bg-slate-800 border-2 border-slate-600 hover:border-indigo-500 flex items-center justify-center cursor-pointer transition-all default-avatar-item group" data-src="">
                                        <i class="fas fa-ban text-slate-400 group-hover:text-white transition-colors text-xl"></i>
                                        <span class="sr-only">Nincs kép</span>
                                    </div>

                                    <?php foreach ($defaultAvatars as $avatarPath): ?>
                                    <div onclick="selectDefaultAvatar('<?php echo htmlspecialchars($avatarPath); ?>')" class="shrink-0 w-16 h-16 rounded-lg bg-slate-900 border-2 border-slate-700 hover:border-indigo-500 overflow-hidden cursor-pointer transition-all default-avatar-item group relative" data-src="<?php echo htmlspecialchars($avatarPath); ?>">
                                        <img src="<?php echo htmlspecialchars($avatarPath); ?>" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity pointer-events-none">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1"><i class="fas fa-info-circle"></i> Húzd jobbra-balra a listát a görgetéshez.</p>
                            </div>
                        </div>

                        <!-- Cover Upload -->
                        <div>
                            <label class="block text-sm font-bold text-white mb-4">Borítókép</label>
                            <div class="relative w-full h-40 rounded-xl bg-slate-900 border-2 border-slate-700 overflow-hidden mb-4 group">
                                <img id="cover-preview" src="<?php echo !empty($user['cover_image']) ? htmlspecialchars($user['cover_image']) : 'https://www.transparenttextures.com/patterns/cubes.png'; ?>" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                    <i class="fas fa-camera text-white text-3xl"></i>
                                </div>
                                <button type="button" onclick="deleteCover()" class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-red-600 transition-colors z-20 shadow-md" title="Törlés">
                                    <i class="fas fa-times text-sm"></i>
                                </button>
                            </div>
                            <?php if (has_storage_space($currentUser)): ?>
                            <div class="file-drop-zone rounded-lg p-6 text-center cursor-pointer relative" id="cover-drop-zone">
                                <input type="file" id="cover-input" accept="image/png, image/jpeg, image/webp" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                <i class="fas fa-image text-3xl text-slate-500 mb-2"></i>
                                <p class="text-sm font-medium text-slate-300">Borítókép cseréje (Ajánlott: 1920x400)</p>
                                <p class="text-xs text-slate-500 mt-1">JPG, PNG, WEBP (Max 5MB)</p>
                            </div>
                            <?php else: ?>
                            <div class="bg-red-500/10 border border-red-500/30 text-red-500 p-6 rounded-lg text-center font-bold">
                                <i class="fas fa-exclamation-triangle text-2xl mb-2 block"></i> A tárhelyed megtelt! Nem tölthetsz fel új borítóképet.
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <!-- SECTION: SOCIAL LINKS -->
                <div id="tab-content-social" class="tab-content space-y-6 hidden">
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 md:p-8">
                        <h3 class="text-xl font-bold text-white mb-6 border-b border-slate-700 pb-4">Közösségi Média</h3>
                        <p class="text-sm text-slate-400 mb-6">Add meg az elérhetőségeidet, hogy mások könnyebben megtaláljanak.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <?php
                            $platforms = [
                                'facebook' => ['icon' => 'fab fa-facebook', 'label' => 'Facebook', 'placeholder' => 'https://facebook.com/username'],
                                'twitter' => ['icon' => 'fab fa-twitter', 'label' => 'Twitter / X', 'placeholder' => 'https://twitter.com/username'],
                                'instagram' => ['icon' => 'fab fa-instagram', 'label' => 'Instagram', 'placeholder' => 'https://instagram.com/username'],
                                'linkedin' => ['icon' => 'fab fa-linkedin', 'label' => 'LinkedIn', 'placeholder' => 'https://linkedin.com/in/username'],
                                'github' => ['icon' => 'fab fa-github', 'label' => 'GitHub', 'placeholder' => 'https://github.com/username'],
                                'discord' => ['icon' => 'fab fa-discord', 'label' => 'Discord', 'placeholder' => 'Discord ID vagy Invite Link'],
                                'youtube' => ['icon' => 'fab fa-youtube', 'label' => 'YouTube', 'placeholder' => 'https://youtube.com/@channel'],
                                'tiktok' => ['icon' => 'fab fa-tiktok', 'label' => 'TikTok', 'placeholder' => 'https://tiktok.com/@username'],
                            ];

                            foreach ($platforms as $key => $data):
                                $val = isset($socialLinks[$key]) ? $socialLinks[$key] : '';
                            ?>
                            <div class="col-span-2 md:col-span-1">
                                <label class="block text-xs font-bold uppercase text-slate-500 mb-2"><i class="<?php echo $data['icon']; ?> mr-1"></i> <?php echo $data['label']; ?></label>
                                <input type="text" name="social_links[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($val); ?>" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors" placeholder="<?php echo $data['placeholder']; ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- SECTION: SECURITY -->
                <div id="tab-content-security" class="tab-content space-y-6 hidden">
                    <div class="bg-slate-800 border border-slate-700 rounded-xl p-6 md:p-8">
                        <h3 class="text-xl font-bold text-white mb-6 border-b border-slate-700 pb-4">Biztonsági Beállítások</h3>

                        <div class="space-y-6 max-w-2xl">
                            <!-- Password Change -->
                            <div>
                                <h4 class="text-lg font-semibold text-white mb-4">Jelszó Módosítása</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Jelenlegi Jelszó</label>
                                        <input type="password" name="current_password" id="current_password" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                                    </div>
                                     <div>
                                        <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Jelenlegi Jelszó Megerősítése</label>
                                        <input type="password" name="current_password_confirm" id="current_password_confirm" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                                        <p class="text-xs text-slate-500 mt-1">Biztonsági okokból kérjük, add meg kétszer a jelenlegi jelszavad.</p>
                                    </div>
                                    <div class="pt-4 border-t border-slate-700/50">
                                        <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Új Jelszó</label>
                                        <input type="password" name="new_password" id="new_password" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                                         <p class="text-xs text-slate-500 mt-1">Min. 8 karakter, 1 nagybetű, 1 speciális karakter.</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-500 mb-2">Új Jelszó Megerősítése</label>
                                        <input type="password" name="new_password_confirm" id="new_password_confirm" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors">
                                    </div>
                                </div>
                            </div>

                            <!-- Account Security Info -->
                            <div class="pt-8 border-t border-slate-700/50 w-full">
                                <div class="bg-red-950/20 border border-red-500/20 rounded-xl p-6 relative overflow-hidden backdrop-blur-sm w-full">
                                    <div class="absolute top-0 right-0 w-24 h-24 bg-red-500/5 rounded-bl-full -mr-10 -mt-10 pointer-events-none"></div>
                                    <h3 class="text-lg font-bold text-red-400 mb-4 flex items-center gap-2">
                                        <i class="fas fa-user-shield"></i> Biztonsági Áttekintés
                                    </h3>
                                    <div class="space-y-3 text-sm">
                                        <div class="flex justify-between items-center border-b border-red-500/10 pb-2">
                                            <span class="text-red-300/70">Jelenlegi IP</span>
                                            <span class="font-mono text-red-200 bg-red-950/40 px-2 py-0.5 rounded border border-red-500/10"><?php echo get_client_ip(); ?></span>
                                        </div>
                                        <?php if (!empty($user['last_login_ip'])): ?>
                                        <div class="flex justify-between items-center border-b border-red-500/10 pb-2">
                                            <span class="text-red-300/70">Utolsó Belépés IP</span>
                                            <span class="font-mono text-red-200 bg-red-950/40 px-2 py-0.5 rounded border border-red-500/10"><?php echo htmlspecialchars($user['last_login_ip']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="flex justify-between items-center border-b border-red-500/10 pb-2">
                                            <span class="text-red-300/70">Fiók Státusz</span>
                                            <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-xs font-bold border border-emerald-500/20 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> <?php echo !empty($user['status']) ? strtoupper(htmlspecialchars($user['status'])) : 'AKTÍV'; ?>
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-red-300/70">Jogosultság</span>
                                            <?php
                                            $roleDisplay = 'FELHASZNÁLÓ';
                                            $roleClass = 'bg-indigo-500/10 text-indigo-300 border-indigo-500/20';

                                            if (!empty($user['role'])) {
                                                if ($user['role'] === 'admin') {
                                                    $roleDisplay = 'ADMINISZTRÁTOR';
                                                    $roleClass = 'bg-red-500/10 text-red-300 border-red-500/20';
                                                } elseif ($user['role'] === 'moderator') {
                                                    $roleDisplay = 'MODERÁTOR';
                                                    $roleClass = 'bg-green-500/10 text-green-300 border-green-500/20';
                                                }
                                            } elseif (!empty($user['is_admin'])) {
                                                $roleDisplay = 'ADMINISZTRÁTOR';
                                                $roleClass = 'bg-red-500/10 text-red-300 border-red-500/20';
                                            }
                                            ?>
                                            <span class="px-2 py-0.5 rounded <?php echo $roleClass; ?> text-xs font-bold border"><?php echo $roleDisplay; ?></span>
                                        </div>
                                    </div>
                                    <div class="mt-4 pt-3 border-t border-red-500/10 text-center">
                                        <a href="javascript:void(0)" onclick="toggleSecurityLog()" class="text-xs text-red-400/80 hover:text-red-300 underline decoration-red-500/30">Biztonsági napló megtekintése</a>
                                    </div>
                                </div>
                            </div>

                            <!-- Security Log -->
                            <div id="security-log-container" class="hidden mt-4 bg-slate-900/40 border border-slate-800 rounded-xl p-4 animate-fade-in relative overflow-hidden">
                                <div class="absolute top-0 left-0 w-1 h-full bg-red-500/50"></div>
                                <h4 class="text-white font-bold mb-3 flex items-center gap-2 text-sm">
                                    <i class="fas fa-clipboard-list text-red-400"></i> Biztonsági Napló (Utolsó 10 aktivitás)
                                </h4>

                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs text-slate-400">
                                        <thead class="text-slate-500 uppercase font-bold border-b border-slate-800">
                                            <tr>
                                                <th class="py-2 px-2">Időpont</th>
                                                <th class="py-2 px-2">Esemény</th>
                                                <th class="py-2 px-2">IP Cím</th>
                                                <th class="py-2 px-2">Eszköz</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-800/50">
                                            <?php
                                            try {
                                                // Using $userId here instead of $profileId
                                                $logStmt = $pdo->prepare("SELECT * FROM qc_login_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
                                                $logStmt->execute([$userId]);
                                                $logs = $logStmt->fetchAll();

                                                if ($logs):
                                                    foreach ($logs as $log):
                                                        $eventColor = 'text-slate-300';
                                                        $eventIcon = 'fa-info-circle';
                                                        if ($log['event'] === 'login') {
                                                            $eventColor = 'text-emerald-400';
                                                            $eventIcon = 'fa-right-to-bracket';
                                                        } elseif ($log['event'] === 'login_failed') {
                                                            $eventColor = 'text-red-400';
                                                            $eventIcon = 'fa-triangle-exclamation';
                                                        } elseif ($log['event'] === 'password_change') {
                                                            $eventColor = 'text-amber-400';
                                                            $eventIcon = 'fa-key';
                                                        }
                                            ?>
                                            <tr class="hover:bg-slate-800/30 transition-colors">
                                                <td class="py-2 px-2 whitespace-nowrap font-mono text-slate-500"><?php echo $log['created_at']; ?></td>
                                                <td class="py-2 px-2 <?php echo $eventColor; ?> font-medium">
                                                    <i class="fas <?php echo $eventIcon; ?> mr-1 opacity-70"></i> <?php echo strtoupper(htmlspecialchars($log['event'])); ?>
                                                </td>
                                                <td class="py-2 px-2 font-mono"><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                                <td class="py-2 px-2 truncate max-w-[150px]" title="<?php echo htmlspecialchars($log['user_agent']); ?>">
                                                    <?php echo htmlspecialchars($log['user_agent']); ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; else: ?>
                                            <tr>
                                                <td colspan="4" class="py-4 text-center text-slate-500 italic">Nincs rögzített biztonsági esemény.</td>
                                            </tr>
                                            <?php endif;
                                            } catch (Exception $e) {
                                                echo '<tr><td colspan="4" class="py-4 text-center text-red-500">Hiba a napló betöltésekor.</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="sticky bottom-0 bg-slate-900/90 backdrop-blur border-t border-slate-800 p-4 mt-8 flex justify-end gap-4 rounded-xl z-40">
                    <a href="profile.php" class="px-6 py-3 rounded-lg border border-slate-700 text-slate-300 font-bold hover:bg-slate-800 transition-colors">Mégse</a>
                    <button type="button" onclick="saveProfile()" class="px-6 py-3 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold shadow-lg shadow-indigo-500/20 transition-colors flex items-center gap-2">
                        <i class="fas fa-save"></i> Változtatások Mentése
                    </button>
                </div>

            </form>
        </div>
    </div>

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
    function toggleSecurityLog() {
        const container = document.getElementById('security-log-container');
        if (container) {
            container.classList.toggle('hidden');
        }
    }

    // Tab Switching Logic
    function switchTab(tabId) {
        // Hide all contents
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        // Show selected content
        document.getElementById('tab-content-' + tabId).classList.remove('hidden');

        // Reset buttons
        document.querySelectorAll('nav button').forEach(btn => {
            btn.classList.remove('bg-indigo-600', 'text-white');
            btn.classList.add('text-slate-400', 'hover:bg-slate-700', 'hover:text-white');
        });

        // Activate button
        const activeBtn = document.getElementById('tab-btn-' + tabId);
        activeBtn.classList.remove('text-slate-400', 'hover:bg-slate-700', 'hover:text-white');
        activeBtn.classList.add('bg-indigo-600', 'text-white');

        // Scroll to top of form on mobile
        if (window.innerWidth < 768) {
            document.getElementById('profile-form').scrollIntoView({behavior: 'smooth'});
        }
    }

    // Image Upload Logic
    function handleImageUpload(inputId, type, previewId) {
        const input = document.getElementById(inputId);
        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // Reset deferred flags when uploading manually
                if (type === 'avatar') {
                    deleteAvatarFlag = false;
                    selectedAvatarPath = '';
                    // Clear list selection visual
                    document.querySelectorAll('.default-avatar-item').forEach(el => {
                         el.classList.remove('border-indigo-500', 'ring-2', 'ring-indigo-500');
                         el.classList.add('border-slate-700', 'border-slate-600');
                    });
                } else if (type === 'cover') {
                    deleteCoverFlag = false;
                }

                const file = this.files[0];
                const formData = new FormData();
                formData.append('file', file);
                formData.append('type', type);

                // Show loading state (optional)
                const previewImg = document.getElementById(previewId);
                const originalSrc = previewImg.src;
                previewImg.style.opacity = '0.5';

                fetch('ajax_profile_upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update preview with new URL (add timestamp to bust cache)
                        previewImg.src = data.url; // URL already has timestamp from PHP or we can add it here

                        // Show delete button
                        if (type === 'avatar') {
                            const delBtn = document.getElementById('delete-avatar-btn');
                            if (delBtn) delBtn.classList.remove('hidden');
                        }

                        // Show success toast (simple alert for now or custom toast)
                        showMessage('success', 'Kép sikeresen feltöltve!');
                    } else {
                        showMessage('error', data.message || 'Hiba a feltöltés során.');
                        previewImg.src = originalSrc;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('error', 'Hálózati hiba történt.');
                    previewImg.src = originalSrc;
                })
                .finally(() => {
                    previewImg.style.opacity = '1';
                });
            }
        });
    }

    handleImageUpload('avatar-input', 'avatar', 'avatar-preview');
    handleImageUpload('cover-input', 'cover', 'cover-preview');

    // Global state for deferred actions
    let deleteAvatarFlag = false;
    let selectedAvatarPath = '';
    let deleteCoverFlag = false;

    // Drag to scroll logic
    let isDown = false;
    let startX;
    let scrollLeft;

    function startDrag(e) {
        const slider = document.getElementById('default-avatars-container');
        if (!slider) return;
        isDown = true;
        slider.classList.add('cursor-grabbing');
        slider.classList.remove('cursor-grab');
        startX = e.pageX - slider.offsetLeft;
        scrollLeft = slider.scrollLeft;
    }

    function stopDrag() {
        const slider = document.getElementById('default-avatars-container');
        if (!slider) return;
        isDown = false;
        slider.classList.add('cursor-grab');
        slider.classList.remove('cursor-grabbing');
    }

    function drag(e) {
        const slider = document.getElementById('default-avatars-container');
        if (!isDown || !slider) return;
        e.preventDefault();
        const x = e.pageX - slider.offsetLeft;
        const walk = (x - startX) * 2; // Scroll-fast
        slider.scrollLeft = scrollLeft - walk;
    }

    // Wheel support
    document.addEventListener('DOMContentLoaded', () => {
        const slider = document.getElementById('default-avatars-container');
        if(slider) {
            slider.addEventListener('wheel', (evt) => {
                evt.preventDefault();
                slider.scrollLeft += evt.deltaY;
            });
        }
    });

    function selectDefaultAvatar(path) {
        selectedAvatarPath = path;
        deleteAvatarFlag = false;

        // Update visual selection state
        document.querySelectorAll('.default-avatar-item').forEach(el => {
            el.classList.remove('border-indigo-500', 'ring-2', 'ring-indigo-500');
            el.classList.add('border-slate-700', 'border-slate-600');

            // Check if this is the selected item
            if(el.dataset.src === path) {
                el.classList.remove('border-slate-700', 'border-slate-600');
                el.classList.add('border-indigo-500', 'ring-2', 'ring-indigo-500');
            }
        });

        // Update preview
        const preview = document.getElementById('avatar-preview');
        const delBtn = document.getElementById('delete-avatar-btn');

        if (path) {
            preview.src = path;
            if (delBtn) delBtn.classList.remove('hidden');
        } else {
            // Selected "No Image" -> effectively delete
            deleteAvatarFlag = true;
            // Use placeholder
            preview.src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent('<?php echo $user['username']; ?>') + '&background=6366f1&color=fff&size=256';
            if (delBtn) delBtn.classList.add('hidden');
        }
    }

    function deleteAvatar() {
        deleteAvatarFlag = true;
        selectedAvatarPath = '';

        // Reset selection
        document.querySelectorAll('.default-avatar-item').forEach(el => {
             el.classList.remove('border-indigo-500', 'ring-2', 'ring-indigo-500');
             el.classList.add('border-slate-700', 'border-slate-600');
        });

        document.getElementById('avatar-preview').src = 'https://ui-avatars.com/api/?name=' + encodeURIComponent('<?php echo $user['username']; ?>') + '&background=6366f1&color=fff&size=256';

        // Hide delete button
        const delBtn = document.getElementById('delete-avatar-btn');
        if (delBtn) delBtn.classList.add('hidden');
    }

    function deleteCover() {
        deleteCoverFlag = true;
        document.getElementById('cover-preview').src = 'https://www.transparenttextures.com/patterns/cubes.png';
    }

    // Profile Save Logic
    function saveProfile() {
        const form = document.getElementById('profile-form');
        const formData = new FormData(form);

        // --- Frontend Validation ---

        // 1. Full Name Validation
        const fullNameInput = document.getElementById('full_name');
        if (fullNameInput) {
            const fullName = fullNameInput.value.trim();
            if (fullName) {
                // Remove known titles to count "real" words
                const titles = ['dr.', 'dr', 'prof.', 'prof', 'id.', 'id', 'ifj.', 'ifj', 'özv.', 'özv'];
                const words = fullName.split(/\s+/).filter(word => {
                    return !titles.includes(word.toLowerCase());
                });

                if (words.length > 3) {
                    showMessage('error', 'A teljes név maximum 3 szóból állhat (plusz titulusok).');
                    return;
                }
            }
        }

        // 2. Password Validation
        const currentPass = document.getElementById('current_password').value;
        const currentPassConfirm = document.getElementById('current_password_confirm').value;
        const newPass = document.getElementById('new_password').value;
        const newPassConfirm = document.getElementById('new_password_confirm').value;

        // Only validate if any password field is filled
        if (currentPass || currentPassConfirm || newPass || newPassConfirm) {
            if (!currentPass) {
                showMessage('error', 'A jelszó módosításához add meg a jelenlegi jelszavad!');
                return;
            }
            if (currentPass !== currentPassConfirm) {
                showMessage('error', 'A jelenlegi jelszó két megadása nem egyezik!');
                return;
            }
            if (!newPass) {
                showMessage('error', 'Add meg az új jelszót!');
                return;
            }
            if (newPass !== newPassConfirm) {
                showMessage('error', 'Az új jelszavak nem egyeznek!');
                return;
            }

            // Strong password check: Min 8 chars, 1 Uppercase, 1 Special
            const strongRegex = /^(?=.*[A-Z])(?=.*[\W_]).{8,}$/;
            if (!strongRegex.test(newPass)) {
                showMessage('error', 'A jelszónak legalább 8 karakterből kell állnia, és tartalmaznia kell egy nagybetűt és egy speciális karaktert!');
                return;
            }
        }

        // --- End Validation ---

        // Convert FormData to JSON object structure
        const data = {};
        const socialLinks = {};

        formData.forEach((value, key) => {
            if (key.startsWith('social_links[')) {
                // Extract key name: social_links[facebook] -> facebook
                const socialKey = key.match(/\[(.*?)\]/)[1];
                if (value.trim() !== '') {
                    socialLinks[socialKey] = value;
                }
            } else {
                data[key] = value;
            }
        });

        data.social_links = socialLinks;

        // Add deferred image actions
        if (deleteAvatarFlag) {
            data.delete_avatar = true;
        } else if (selectedAvatarPath) {
            data.selected_avatar = selectedAvatarPath;
        }

        if (deleteCoverFlag) {
            data.delete_cover = true;
        }

        // Show saving state
        const saveBtn = document.querySelector('button[onclick="saveProfile()"]');
        const originalText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mentés...';

        fetch('ajax_profile_update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showMessage('success', result.message);
                // Clear password fields on success
                document.getElementById('current_password').value = '';
                document.getElementById('current_password_confirm').value = '';
                document.getElementById('new_password').value = '';
                document.getElementById('new_password_confirm').value = '';
            } else {
                showMessage('error', result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Hálózati hiba történt.');
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        });
    }

    function showMessage(type, text) {
        const msgDiv = document.getElementById('form-message');
        msgDiv.classList.remove('hidden', 'bg-emerald-500/20', 'text-emerald-400', 'border-emerald-500/30', 'bg-red-500/20', 'text-red-400', 'border-red-500/30');

        if (type === 'success') {
            msgDiv.classList.add('bg-emerald-500/20', 'text-emerald-400', 'border', 'border-emerald-500/30');
            msgDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i> ' + text;
        } else {
            msgDiv.classList.add('bg-red-500/20', 'text-red-400', 'border', 'border-red-500/30');
            msgDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i> ' + text;
        }

        msgDiv.classList.remove('hidden');

        // Auto hide after 5 seconds
        setTimeout(() => {
            msgDiv.classList.add('hidden');
        }, 5000);
    }

    // Drag and Drop Visual Feedback
    ['avatar-drop-zone', 'cover-drop-zone'].forEach(id => {
        const zone = document.getElementById(id);
        const input = zone.querySelector('input');

        ['dragenter', 'dragover'].forEach(eventName => {
            zone.addEventListener(eventName, (e) => {
                e.preventDefault();
                zone.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            zone.addEventListener(eventName, (e) => {
                e.preventDefault();
                zone.classList.remove('drag-over');
            });
        });

        zone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length) {
                input.files = files;
                // Trigger change event manually
                // Note: Manually setting input.files and dispatching change works,
                // but we must ensure it bubbles or is captured if the listener is elsewhere.
                // The listener is attached directly to the input, so dispatching on input is correct.
                const event = new Event('change', { bubbles: true });
                input.dispatchEvent(event);
            }
        });
    });

</script>
</body>
</html>
