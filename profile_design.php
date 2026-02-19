<!DOCTYPE html>
<html lang="hu">
<head>
    <title>Felhasználói Profil - Design Terv</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#008080">
    <!-- Tailwind CSS (CDN for prototype) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,400;0,700;1,700&family=Inter:wght@400;600;800&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <!-- Site Styles -->
	<link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/layout.css">
	<link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/article.css">
	<link rel="stylesheet" href="https://blog.silverpc.hu/v2/assets/css/widget.css">
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
    /* Custom Scrollbar for horizontal tabs */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Custom Animations */
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-5px); }
        100% { transform: translateY(0px); }
    }

    .badge-item:hover {
        animation: float 2s ease-in-out infinite;
    }

    /* Neon Glow Effects */
    .neon-text {
        text-shadow: 0 0 5px rgba(255, 255, 255, 0.5), 0 0 10px rgba(255, 255, 255, 0.3);
    }

    /* Glass Panel Override/Enhancement */
    .glass-panel-custom {
        background: rgba(30, 41, 59, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
</style>
<div class="profile-container p-4 md:p-6 text-white font-['Inter']">

    <!-- Header Section -->
    <div class="relative rounded-2xl overflow-hidden mb-8 shadow-2xl bg-slate-800 border border-slate-700">
        <!-- Banner -->
        <div class="h-32 md:h-48 bg-gradient-to-r from-indigo-900 via-purple-900 to-slate-900 relative">
             <div class="absolute inset-0 opacity-30" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');"></div>
             <div class="absolute bottom-4 right-4 flex gap-3">
                 <span class="px-3 py-1 bg-black/50 backdrop-blur-md rounded-full text-xs font-mono text-emerald-400 border border-emerald-500/30 shadow-lg">
                    <i class="fas fa-circle text-[8px] mr-1 animate-pulse"></i> ONLINE
                 </span>
             </div>
        </div>

        <!-- Info Bar -->
        <div class="px-4 md:px-8 pb-6 relative">
            <div class="flex flex-col md:flex-row items-end gap-6 -mt-12 md:-mt-16 mb-4">
                <!-- Avatar -->
                <div class="relative group mx-auto md:mx-0">
                    <div class="w-24 h-24 md:w-32 md:h-32 rounded-2xl bg-slate-900 p-1 ring-4 ring-slate-800 shadow-xl overflow-hidden relative z-10">
                        <img src="https://ui-avatars.com/api/?name=Cyber+User&background=6366f1&color=fff&size=256" alt="Avatar" class="w-full h-full object-cover rounded-xl group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="absolute -bottom-2 -right-2 bg-gradient-to-br from-amber-400 to-orange-600 w-8 h-8 md:w-10 md:h-10 rounded-lg flex items-center justify-center text-white text-sm md:text-lg font-bold shadow-lg border-2 border-slate-800 z-20" title="Rank Level">
                        55
                    </div>
                </div>

                <!-- Text Info -->
                <div class="flex-1 mb-2 text-center md:text-left w-full">
                    <div class="flex flex-col md:flex-row items-center md:items-end gap-2 md:gap-3 mb-1 justify-center md:justify-start">
                        <h1 class="text-2xl md:text-3xl font-black tracking-tight text-white font-['Orbitron']">CyberUser_99</h1>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 mb-1 md:mb-0">Admin</span>
                    </div>
                    <p class="text-slate-400 text-sm font-medium">Full Stack Developer & Hardware Enthusiast</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 mb-2 w-full md:w-auto justify-center">
                     <button class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold transition-colors shadow-lg shadow-indigo-500/20 flex items-center gap-2">
                        <i class="fas fa-user-plus"></i> <span class="hidden sm:inline">Követés</span>
                     </button>
                     <button class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white text-sm font-bold transition-colors border border-slate-600 flex items-center gap-2">
                        <i class="fas fa-envelope"></i> <span class="hidden sm:inline">Üzenet</span>
                     </button>
                     <button class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-colors border border-slate-700">
                        <i class="fas fa-ellipsis-h"></i>
                     </button>
                </div>
            </div>

            <!-- Gamification Badges Row -->
            <div class="flex flex-wrap justify-center md:justify-start gap-2 mt-4 pt-4 border-t border-slate-700/50">
                 <div class="badge-item bg-amber-500/10 text-amber-400 border border-amber-500/20 px-3 py-1 rounded-md text-xs font-bold flex items-center gap-2 cursor-help transition-colors hover:bg-amber-500/20" title="Korai Hozzáférés">
                    <i class="fas fa-star"></i> Alapító
                 </div>
                 <div class="badge-item bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 px-3 py-1 rounded-md text-xs font-bold flex items-center gap-2 cursor-help transition-colors hover:bg-cyan-500/20" title="100+ Hozzászólás">
                    <i class="fas fa-comments"></i> Dumagép
                 </div>
                 <div class="badge-item bg-rose-500/10 text-rose-400 border border-rose-500/20 px-3 py-1 rounded-md text-xs font-bold flex items-center gap-2 cursor-help transition-colors hover:bg-rose-500/20" title="Megbízható Tag">
                    <i class="fas fa-shield-alt"></i> Verifikált
                 </div>
                 <div class="badge-item bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-3 py-1 rounded-md text-xs font-bold flex items-center gap-2 cursor-help transition-colors hover:bg-emerald-500/20" title="Megoldóember">
                    <i class="fas fa-check-double"></i> Problem Solver
                 </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 md:gap-4 mb-8">
        <!-- Reputation -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-xl flex flex-col items-center justify-center hover:bg-slate-800 transition-colors group relative overflow-hidden">
            <div class="absolute inset-0 bg-amber-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <div class="text-2xl md:text-3xl font-black text-amber-400 mb-1 font-['Chakra_Petch'] group-hover:scale-110 transition-transform relative z-10">12.5k</div>
            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold relative z-10">Reputáció</div>
        </div>
         <!-- Questions -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-xl flex flex-col items-center justify-center hover:bg-slate-800 transition-colors group">
            <div class="text-2xl md:text-3xl font-black text-blue-400 mb-1 font-['Chakra_Petch'] group-hover:scale-110 transition-transform">42</div>
            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Kérdés</div>
        </div>
         <!-- Answers -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-xl flex flex-col items-center justify-center hover:bg-slate-800 transition-colors group">
            <div class="text-2xl md:text-3xl font-black text-emerald-400 mb-1 font-['Chakra_Petch'] group-hover:scale-110 transition-transform">158</div>
            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Válasz</div>
        </div>
         <!-- Accepted -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-xl flex flex-col items-center justify-center hover:bg-slate-800 transition-colors group">
            <div class="text-2xl md:text-3xl font-black text-green-400 mb-1 font-['Chakra_Petch'] group-hover:scale-110 transition-transform">89</div>
            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Elfogadva</div>
        </div>
         <!-- Comments -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-xl flex flex-col items-center justify-center hover:bg-slate-800 transition-colors group">
            <div class="text-2xl md:text-3xl font-black text-purple-400 mb-1 font-['Chakra_Petch'] group-hover:scale-110 transition-transform">1.2k</div>
            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Komment</div>
        </div>
         <!-- Reactions -->
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-4 rounded-xl flex flex-col items-center justify-center hover:bg-slate-800 transition-colors group">
            <div class="text-2xl md:text-3xl font-black text-rose-400 mb-1 font-['Chakra_Petch'] group-hover:scale-110 transition-transform">450</div>
            <div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold">Reakció</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Left Column: Details -->
        <div class="lg:col-span-1 space-y-6">

            <!-- About Card -->
            <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-6 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1 h-full bg-gradient-to-b from-indigo-500 to-purple-500"></div>
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-user-circle text-indigo-400"></i> Névjegy
                </h3>

                <div class="space-y-4">
                    <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-800">
                        <span class="text-xs text-slate-500 uppercase font-bold block mb-2">Bemutatkozás</span>
                        <p class="text-sm text-slate-300 leading-relaxed italic relative pl-3 border-l-2 border-slate-700">
                            "Az informatika nem csak munka, hanem szenvedély. Szeretem a kihívásokat és a tiszta kódot. Főként webfejlesztéssel és Linux rendszergazdai feladatokkal foglalkozom."
                        </p>
                    </div>

                    <ul class="space-y-3 pt-2">
                         <li class="flex items-center gap-3 text-sm text-slate-300 hover:text-white transition-colors">
                            <div class="w-8 h-8 rounded bg-slate-700/50 flex items-center justify-center text-slate-400 border border-slate-600/50">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <span>Budapest, Hungary</span>
                         </li>
                         <li class="flex items-center gap-3 text-sm text-slate-300 hover:text-white transition-colors">
                            <div class="w-8 h-8 rounded bg-slate-700/50 flex items-center justify-center text-slate-400 border border-slate-600/50">
                                <i class="fas fa-globe"></i>
                            </div>
                            <a href="#" class="text-indigo-400 hover:text-indigo-300 transition-colors">silverpc.hu</a>
                         </li>
                         <li class="flex items-center gap-3 text-sm text-slate-300 hover:text-white transition-colors">
                            <div class="w-8 h-8 rounded bg-slate-700/50 flex items-center justify-center text-slate-400 border border-slate-600/50">
                                <i class="far fa-calendar-alt"></i>
                            </div>
                            <span>Tag mióta: <span class="text-white font-medium">2020. Jan. 15.</span></span>
                         </li>
                         <li class="flex items-center gap-3 text-sm text-slate-300 hover:text-white transition-colors">
                            <div class="w-8 h-8 rounded bg-slate-700/50 flex items-center justify-center text-slate-400 border border-slate-600/50">
                                <i class="far fa-clock"></i>
                            </div>
                            <span>Utoljára itt: <span class="text-emerald-400 font-bold animate-pulse">Most</span></span>
                         </li>
                    </ul>

                     <!-- Social Links -->
                     <div class="flex gap-2 pt-4 justify-center lg:justify-start">
                        <a href="#" class="w-10 h-10 rounded-lg bg-slate-800 hover:bg-[#1877F2] text-slate-400 hover:text-white flex items-center justify-center transition-all border border-slate-700 hover:border-transparent hover:scale-110 shadow-lg">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-slate-800 hover:bg-[#1DA1F2] text-slate-400 hover:text-white flex items-center justify-center transition-all border border-slate-700 hover:border-transparent hover:scale-110 shadow-lg">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-slate-800 hover:bg-[#333] text-slate-400 hover:text-white flex items-center justify-center transition-all border border-slate-700 hover:border-transparent hover:scale-110 shadow-lg">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-lg bg-slate-800 hover:bg-[#5865F2] text-slate-400 hover:text-white flex items-center justify-center transition-all border border-slate-700 hover:border-transparent hover:scale-110 shadow-lg">
                            <i class="fab fa-discord"></i>
                        </a>
                     </div>
                </div>
            </div>

            <!-- Security Info (Visible only to Admin/Owner) -->
            <div class="bg-red-950/20 border border-red-500/20 rounded-xl p-6 relative overflow-hidden backdrop-blur-sm">
                <div class="absolute top-0 right-0 w-24 h-24 bg-red-500/5 rounded-bl-full -mr-10 -mt-10 pointer-events-none"></div>
                <h3 class="text-lg font-bold text-red-400 mb-4 flex items-center gap-2">
                    <i class="fas fa-user-shield"></i> Biztonsági Adatok
                </h3>
                <div class="space-y-3 text-sm">
                     <div class="flex justify-between items-center border-b border-red-500/10 pb-2">
                        <span class="text-red-300/70">Jelenlegi IP</span>
                        <span class="font-mono text-red-200 bg-red-950/40 px-2 py-0.5 rounded border border-red-500/10">192.168.1.XX</span>
                     </div>
                     <div class="flex justify-between items-center border-b border-red-500/10 pb-2">
                        <span class="text-red-300/70">Utolsó Belépés IP</span>
                        <span class="font-mono text-red-200 bg-red-950/40 px-2 py-0.5 rounded border border-red-500/10">84.2.XX.XX</span>
                     </div>
                     <div class="flex justify-between items-center border-b border-red-500/10 pb-2">
                        <span class="text-red-300/70">Fiók Státusz</span>
                        <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-xs font-bold border border-emerald-500/20 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> AKTÍV
                        </span>
                     </div>
                     <div class="flex justify-between items-center">
                        <span class="text-red-300/70">Jogosultság</span>
                        <span class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-300 text-xs font-bold border border-indigo-500/20">ADMINISTRATOR</span>
                     </div>
                </div>
                <div class="mt-4 pt-3 border-t border-red-500/10 text-center">
                     <a href="#" class="text-xs text-red-400/80 hover:text-red-300 underline decoration-red-500/30">Biztonsági napló megtekintése</a>
                </div>
            </div>

        </div>

        <!-- Right Column: Content -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Tabs -->
            <div class="flex border-b border-slate-700/50 mb-6 overflow-x-auto no-scrollbar">
                <button class="px-6 py-3 text-indigo-400 border-b-2 border-indigo-500 font-bold text-sm bg-slate-800/30 rounded-t-lg whitespace-nowrap">
                    Áttekintés
                </button>
                <button class="px-6 py-3 text-slate-400 hover:text-white font-medium text-sm transition-colors whitespace-nowrap hover:bg-slate-800/30 rounded-t-lg border-b-2 border-transparent hover:border-slate-600">
                    Kérdések (42)
                </button>
                <button class="px-6 py-3 text-slate-400 hover:text-white font-medium text-sm transition-colors whitespace-nowrap hover:bg-slate-800/30 rounded-t-lg border-b-2 border-transparent hover:border-slate-600">
                    Válaszok (158)
                </button>
                 <button class="px-6 py-3 text-slate-400 hover:text-white font-medium text-sm transition-colors whitespace-nowrap hover:bg-slate-800/30 rounded-t-lg border-b-2 border-transparent hover:border-slate-600">
                    Jelvények
                </button>
            </div>

            <!-- Recent Activity Block -->
            <div class="bg-slate-800/30 border border-slate-700 rounded-xl p-6">
                 <h4 class="text-white font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-history text-indigo-400"></i> Legutóbbi Aktivitás
                 </h4>

                 <div class="space-y-4">

                    <!-- Activity Item 1 -->
                    <div class="flex gap-4 p-4 rounded-lg bg-slate-900/40 border border-slate-800 hover:border-indigo-500/30 transition-all group cursor-pointer relative overflow-hidden">
                         <div class="absolute left-0 top-0 bottom-0 w-1 bg-indigo-500 rounded-l-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-indigo-400 shrink-0 border border-slate-700 group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                            <i class="fas fa-reply"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-300 mb-1 group-hover:text-white transition-colors">Válaszolt erre: <a href="#" class="font-semibold text-indigo-400 hover:underline">Hogyan lehet optimalizálni a PHP kódot nagy terhelés alatt?</a></p>
                            <div class="text-xs text-slate-500 flex items-center gap-2">
                                <i class="far fa-clock"></i> 2 órája • <span class="text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20"><i class="fas fa-check"></i> Elfogadott válasz</span>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Item 2 -->
                    <div class="flex gap-4 p-4 rounded-lg bg-slate-900/40 border border-slate-800 hover:border-blue-500/30 transition-all group cursor-pointer relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-blue-500 rounded-l-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-blue-400 shrink-0 border border-slate-700 group-hover:bg-blue-500 group-hover:text-white transition-colors">
                            <i class="fas fa-question"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-300 mb-1 group-hover:text-white transition-colors">Új kérdés: <a href="#" class="font-semibold text-blue-400 hover:underline">Melyik a legjobb Linux disztribúció kezdőknek 2024-ben?</a></p>
                            <div class="text-xs text-slate-500 flex items-center gap-2">
                                <i class="far fa-clock"></i> Tegnap • <span class="text-slate-400">Szoftver / Linux</span>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Item 3 -->
                    <div class="flex gap-4 p-4 rounded-lg bg-slate-900/40 border border-slate-800 hover:border-amber-500/30 transition-all group cursor-pointer relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-500 rounded-l-lg opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-amber-400 shrink-0 border border-slate-700 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-300 mb-1 group-hover:text-white transition-colors">Megszerzett jelvény: <strong class="text-amber-400">Heti Hős</strong> - A héten a legtöbb elfogadott válasszal.</p>
                            <div class="text-xs text-slate-500 flex items-center gap-2">
                                <i class="far fa-clock"></i> 3 napja
                            </div>
                        </div>
                    </div>

                 </div>

                 <div class="mt-4 text-center">
                    <button class="text-xs font-bold uppercase tracking-wider text-slate-500 hover:text-white transition-colors p-2">
                        Összes aktivitás mutatása <i class="fas fa-chevron-down ml-1"></i>
                    </button>
                 </div>
            </div>

            <!-- Top Answers / Showcase -->
            <div class="bg-slate-800/30 border border-slate-700 rounded-xl p-6">
                <h4 class="text-white font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-star text-amber-400"></i> Kiemelt Megoldások
                 </h4>
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-lg bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-700 hover:border-amber-500/50 transition-all group cursor-pointer">
                        <div class="flex justify-between items-start mb-2">
                            <div class="text-amber-400 text-lg font-bold group-hover:scale-110 transition-transform">+45</div>
                            <i class="fas fa-check-circle text-emerald-500 text-xl shadow-emerald-500/20 drop-shadow-lg"></i>
                        </div>
                        <h5 class="text-white font-bold text-sm mb-2 line-clamp-2 group-hover:text-indigo-300 transition-colors">Python async/await helyes használata hálózati kéréseknél</h5>
                        <p class="text-xs text-slate-400 line-clamp-3 mb-3 leading-relaxed">
                            A leggyakoribb hiba, amit kezdők elkövetnek, hogy nem használják az aiohttp könyvtárat, hanem a requests-et próbálják...
                        </p>
                        <span class="text-xs text-indigo-400 font-bold uppercase group-hover:text-indigo-300 flex items-center gap-1">Tovább olvasom <i class="fas fa-arrow-right transform group-hover:translate-x-1 transition-transform"></i></span>
                    </div>
                    <div class="p-4 rounded-lg bg-gradient-to-br from-slate-900 to-slate-800 border border-slate-700 hover:border-amber-500/50 transition-all group cursor-pointer">
                        <div class="flex justify-between items-start mb-2">
                            <div class="text-amber-400 text-lg font-bold group-hover:scale-110 transition-transform">+28</div>
                            <i class="fas fa-check-circle text-emerald-500 text-xl shadow-emerald-500/20 drop-shadow-lg"></i>
                        </div>
                        <h5 class="text-white font-bold text-sm mb-2 line-clamp-2 group-hover:text-indigo-300 transition-colors">React useEffect végtelen ciklus elkerülése</h5>
                        <p class="text-xs text-slate-400 line-clamp-3 mb-3 leading-relaxed">
                            A dependency array helyes kitöltése kritikus. Ha objektumot adsz át, mindig használj useMemo-t a szülőben, hogy elkerüld a referenciális egyenlőség problémáját...
                        </p>
                         <span class="text-xs text-indigo-400 font-bold uppercase group-hover:text-indigo-300 flex items-center gap-1">Tovább olvasom <i class="fas fa-arrow-right transform group-hover:translate-x-1 transition-transform"></i></span>
                    </div>
                 </div>
            </div>

        </div>

    </div>

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