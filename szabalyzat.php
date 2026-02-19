<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

$currentUser = getCurrentUser($pdo);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <?php
    $seoData = ['title' => 'Működési Szabályzat és Irányelvek | SilverPC Fórum', 'description' => 'A SilverPC közösségi fórum részletes működési szabályzata, kategóriák leírása és közösségi irányelvek.', 'og_type' => 'website'];
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
</head>
<body class="min-h-screen relative">
<?php require_once __DIR__ . '/../inc/header_menu.php'; ?>

<main class="header-content-wrapper mt-0 pb-0">
    <div class="cikk-fo-container" itemscope itemtype="http://schema.org/WebPage">

        <div class="cikk-fo-layout">
            <div class="cikk-fo-col-left bg-white p-10 shadow-lg rounded-none border border-gray-200">

                <h1 class="text-4xl font-extrabold text-slate-800 mb-8 text-center border-b-4 border-indigo-600 pb-4 tracking-tight">
                    <i class="fa-solid fa-scale-balanced text-indigo-600 mr-3"></i> <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">KÖZÖSSÉGI MŰKÖDÉSI SZABÁLYZAT</span>
                </h1>

                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 border-l-4 border-indigo-500 p-6 mb-10 shadow-sm rounded-r-lg">
                    <p class="text-indigo-900 italic text-center font-medium text-lg leading-relaxed">
                        <i class="fa-solid fa-circle-info mr-2 text-indigo-600"></i>
                        Kérjük, figyelmesen olvasd el az alábbi irányelveket. A fórum használatával (regisztrációval, tartalom megtekintésével) <span class="font-bold underline decoration-indigo-400 decoration-2">automatikusan és visszavonhatatlanul elfogadod</span> ezeket a feltételeket. A szabályzat nem ismerete nem mentesít a felelősségre vonás alól.
                    </p>
                </div>

                <!-- 1. BEVEZETÉS -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-slate-800 border-l-8 border-cyan-500 pl-4 mb-6 uppercase tracking-wide">
                        1. Bevezetés és Küldetésünk
                    </h2>
                    <p class="mb-4 leading-relaxed text-gray-700 text-lg">
                        A <b class="text-indigo-700">SilverPC Fórum</b> Magyarország egyik dinamikusan fejlődő technológiai közössége. Célunk egy olyan <span class="bg-emerald-100 text-emerald-800 px-1 py-0.5 rounded border-b-2 border-emerald-300 font-bold">biztonságos, szakmai és támogató környezet</span> fenntartása, ahol kezdők és profik egyaránt választ kaphatnak kérdéseikre.
                    </p>
                    <div class="p-6 bg-slate-50 border border-slate-200 rounded-lg shadow-sm">
                        <p class="leading-relaxed text-gray-700 font-medium">
                            <i class="fa-solid fa-quote-left text-slate-300 text-2xl mr-2"></i>
                            Hiszünk a <span class="text-indigo-600 font-extrabold">tudásmegosztás szabadságában</span>, de abban is, hogy ez csak kölcsönös tisztelet mellett működhet. Közösségünk ereje a tagokban rejlik, ezért mindenkitől elvárjuk a <span class="underline decoration-yellow-400 decoration-4">konstruktív hozzáállást</span>.
                        </p>
                    </div>
                </div>

                <!-- 2. KATEGÓRIÁK -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-slate-800 border-l-8 border-purple-500 pl-4 mb-6 uppercase tracking-wide">
                        2. Kategóriarendszer és Használatuk
                    </h2>
                    <p class="mb-6 text-gray-600 bg-yellow-50 p-3 border-l-4 border-yellow-400 rounded-r">
                        <i class="fa-solid fa-triangle-exclamation text-yellow-600 mr-2"></i>
                        A fórum átláthatósága érdekében kérjük, hogy mindig a legmegfelelőbb témakörbe írd a kérdésedet. A rossz helyre nyitott témákat a moderátorok figyelmeztetés nélkül áthelyezhetik.
                    </p>

                    <div class="grid grid-cols-1 gap-6">

                        <!-- CAT 1 -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-indigo-600 hover:shadow-lg transition-shadow duration-300">
                            <h3 class="font-extrabold text-xl text-indigo-700 mb-3 flex items-center">
                                <span class="bg-indigo-100 p-2 rounded-full mr-3 w-10 h-10 flex items-center justify-center"><i class="fa-solid fa-microchip"></i></span>
                                1. SZÁMÍTÁSTECHNIKA & HARDVER
                            </h3>
                            <ul class="list-none space-y-2 text-gray-700 ml-12">
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-indigo-400 before:font-bold"><b>Processzorok (CPU):</b> Intel, AMD processzorok, hűtési megoldások, tuning kérdések.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-indigo-400 before:font-bold"><b>Videókártyák (GPU):</b> NVIDIA GeForce, AMD Radeon, Intel Arc kártyák, driver gondok, teljesítmény.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-indigo-400 before:font-bold"><b>Alaplapok & Memória:</b> BIOS frissítés, chipset kompatibilitás, DDR4/DDR5 RAM kérdések, XMP profilok.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-indigo-400 before:font-bold"><b>Háttértár (SSD & HDD):</b> NVMe SSD-k, merevlemezek, particionálás, adatmentés és SMART adatok elemzése.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-indigo-400 before:font-bold"><b>Számítógépházak & Tápegységek:</b> Gépház választás, légáramlás (airflow), tápegység méretezés és minősítés (80+).</li>
                            </ul>
                        </div>

                        <!-- CAT 2 -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-blue-600 hover:shadow-lg transition-shadow duration-300">
                            <h3 class="font-extrabold text-xl text-blue-700 mb-3 flex items-center">
                                <span class="bg-blue-100 p-2 rounded-full mr-3 w-10 h-10 flex items-center justify-center"><i class="fa-solid fa-code"></i></span>
                                2. SZOFTVER, FEJLESZTÉS & BIZTONSÁG
                            </h3>
                            <ul class="list-none space-y-2 text-gray-700 ml-12">
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-blue-400 before:font-bold"><b>Operációs Rendszerek:</b> Windows 10/11 telepítés, hibaüzenetek, Linux disztribúciók, macOS.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-blue-400 before:font-bold"><b>Programozás & Fejlesztés:</b> Webfejlesztés (HTML/CSS/JS/PHP), Python, C++, Java, scriptelési kérdések.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-blue-400 before:font-bold"><b>IT Biztonság & Vírusvédelem:</b> Vírusirtás, tűzfalak, VPN, zsarolóvírusok elleni védekezés, adatbiztonság.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-blue-400 before:font-bold"><b>Felhasználói Programok:</b> Microsoft Office, Adobe Creative Cloud, böngészők, segédprogramok.</li>
                            </ul>
                        </div>

                        <!-- CAT 3 -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-orange-500 hover:shadow-lg transition-shadow duration-300">
                            <h3 class="font-extrabold text-xl text-orange-600 mb-3 flex items-center">
                                <span class="bg-orange-100 p-2 rounded-full mr-3 w-10 h-10 flex items-center justify-center"><i class="fa-solid fa-mobile-screen"></i></span>
                                3. MOBILITÁS & NAVIGÁCIÓ
                            </h3>
                            <ul class="list-none space-y-2 text-gray-700 ml-12">
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-orange-400 before:font-bold"><b>Okostelefonok:</b> Android és iOS készülékek, ROM csere, akkumulátor kérdések.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-orange-400 before:font-bold"><b>Tabletek & E-book olvasók:</b> iPad, Samsung Tab, Kindle eszközök használata.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-orange-400 before:font-bold"><b>Okosórák & Viselhető eszközök:</b> Apple Watch, Garmin, fitness trackerek.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-orange-400 before:font-bold"><b>GPS & Navigáció:</b> Autós navigáció, térképfrissítések (iGO, Google Maps).</li>
                            </ul>
                        </div>

                        <!-- CAT 4 -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-pink-500 hover:shadow-lg transition-shadow duration-300">
                            <h3 class="font-extrabold text-xl text-pink-600 mb-3 flex items-center">
                                <span class="bg-pink-100 p-2 rounded-full mr-3 w-10 h-10 flex items-center justify-center"><i class="fa-solid fa-tv"></i></span>
                                4. SZÓRAKOZTATÓ ELEKTRONIKA & FOTÓ
                            </h3>
                            <ul class="list-none space-y-2 text-gray-700 ml-12">
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-pink-400 before:font-bold"><b>Televíziók & Házimozi:</b> OLED/QLED TV-k, projektorok, hangprojektorok beállítása.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-pink-400 before:font-bold"><b>Konzolok & Játékgépek:</b> PlayStation 5, Xbox Series X/S, Nintendo Switch, Steam Deck.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-pink-400 before:font-bold"><b>Fényképezőgépek & Videokamerák:</b> DSLR, MILC vázak, objektívek, drónok (DJI).</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-pink-400 before:font-bold"><b>Audio & Hi-Fi:</b> Audiophile fejhallgatók, erősítők (DAC/AMP), hangfalak.</li>
                            </ul>
                        </div>

                        <!-- CAT 5 -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-teal-500 hover:shadow-lg transition-shadow duration-300">
                            <h3 class="font-extrabold text-xl text-teal-600 mb-3 flex items-center">
                                <span class="bg-teal-100 p-2 rounded-full mr-3 w-10 h-10 flex items-center justify-center"><i class="fa-solid fa-print"></i></span>
                                5. IRODA & PERIFÉRIÁK
                            </h3>
                            <ul class="list-none space-y-2 text-gray-700 ml-12">
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-teal-400 before:font-bold"><b>Monitorok & Kijelzők:</b> IPS/VA/TN panelek, színkalibrálás, Hz és válaszidő.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-teal-400 before:font-bold"><b>Billentyűzetek & Egerek:</b> Mechanikus billentyűzetek, gamer egerek, ergonómia.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-teal-400 before:font-bold"><b>Nyomtatók & Irodatechnika:</b> Lézer és tintasugaras nyomtatók, szkennerek, patronok.</li>
                            </ul>
                        </div>

                        <!-- CAT 6 -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-cyan-600 hover:shadow-lg transition-shadow duration-300">
                            <h3 class="font-extrabold text-xl text-cyan-700 mb-3 flex items-center">
                                <span class="bg-cyan-100 p-2 rounded-full mr-3 w-10 h-10 flex items-center justify-center"><i class="fa-solid fa-network-wired"></i></span>
                                6. HÁLÓZAT & INTERNET
                            </h3>
                            <ul class="list-none space-y-2 text-gray-700 ml-12">
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-cyan-400 before:font-bold"><b>Hálózati Eszközök:</b> Routerek beállítása, Port Forwarding, WiFi 6/7, Mesh rendszerek.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-cyan-400 before:font-bold"><b>Internet Szolgáltatók:</b> Digi, Telekom, Vodafone szolgáltatási kérdések, sebességmérés.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-cyan-400 before:font-bold"><b>Szerverek & NAS:</b> Synology, QNAP, TrueNAS, otthoni szerver építés.</li>
                            </ul>
                        </div>

                        <!-- CAT 7 -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-green-600 hover:shadow-lg transition-shadow duration-300">
                            <h3 class="font-extrabold text-xl text-green-700 mb-3 flex items-center">
                                <span class="bg-green-100 p-2 rounded-full mr-3 w-10 h-10 flex items-center justify-center"><i class="fa-solid fa-house"></i></span>
                                7. HÁZTARTÁS & OTTHON
                            </h3>
                            <ul class="list-none space-y-2 text-gray-700 ml-12">
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-green-400 before:font-bold"><b>Nagy & Kis Háztartási Gépek:</b> Mosógépek, hűtők, kávéfőzők, robotporszívók.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-green-400 before:font-bold"><b>Okosotthon (Smart Home):</b> Zigbee, Z-Wave, Home Assistant, okosvilágítás (Philips Hue).</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-green-400 before:font-bold"><b>Klíma & Légtechnika:</b> Légkondicionálók, párátlanítók, fűtési rendszerek vezérlése.</li>
                            </ul>
                        </div>

                        <!-- CAT 8 -->
                        <div class="bg-white p-6 rounded-lg shadow-md border-t-4 border-yellow-500 hover:shadow-lg transition-shadow duration-300">
                            <h3 class="font-extrabold text-xl text-yellow-700 mb-3 flex items-center">
                                <span class="bg-yellow-100 p-2 rounded-full mr-3 w-10 h-10 flex items-center justify-center"><i class="fa-solid fa-comments"></i></span>
                                8. KÖZÖSSÉG & KIKAPCSOLÓDÁS
                            </h3>
                            <ul class="list-none space-y-2 text-gray-700 ml-12">
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-yellow-400 before:font-bold"><b>Csevegő & Off-topic:</b> Kötetlen beszélgetés technológián túli témákról.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-yellow-400 before:font-bold"><b>Ismerkedés & Találkozók:</b> Közösségszervezés, lanpartyk.</li>
                                <li class="relative pl-4 before:content-['•'] before:absolute before:left-0 before:text-yellow-400 before:font-bold"><b>Vélemények & Javaslatok:</b> Visszajelzés az oldal működésével kapcsolatban.</li>
                            </ul>
                        </div>

                    </div>
                </div>

                <!-- 3. KÉRDÉS FELTEVÉSE -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-slate-800 border-l-8 border-green-500 pl-4 mb-6 uppercase tracking-wide">
                        3. Kérdésfeltevés Szabályai
                    </h2>
                    <p class="mb-6 text-gray-700 text-lg">A hatékony segítségnyújtás alapja a jól megfogalmazott kérdés. Kérjük, tartsd be az alábbi formai követelményeket:</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div class="bg-green-50 border-2 border-green-200 p-6 rounded-lg shadow-sm">
                            <h4 class="font-bold text-green-800 text-lg mb-3 flex items-center"><i class="fa-solid fa-pen-nib mr-2"></i> Címadás</h4>
                            <p class="text-sm text-green-900 mb-4">A cím legyen informatív és tömör. Kerüld a "Segítség!", "Sürgős!", "Valaki?" kifejezéseket.</p>
                            <div class="bg-white p-3 rounded border border-green-100">
                                <p class="text-green-600 font-bold text-sm mb-1"><i class="fa-solid fa-check mr-1"></i> Helyes:</p>
                                <p class="text-gray-600 text-xs">"Kékhalál (BSOD) 0x0000001E hiba Windows 10 frissítés után"</p>
                            </div>
                            <div class="bg-white p-3 rounded border border-red-100 mt-2">
                                <p class="text-red-500 font-bold text-sm mb-1"><i class="fa-solid fa-xmark mr-1"></i> Helytelen:</p>
                                <p class="text-gray-600 text-xs">"Nem megy a gép, HELP!!"</p>
                            </div>
                        </div>
                        <div class="bg-blue-50 border-2 border-blue-200 p-6 rounded-lg shadow-sm">
                            <h4 class="font-bold text-blue-800 text-lg mb-3 flex items-center"><i class="fa-solid fa-magnifying-glass mr-2"></i> Duplikáció Ellenőrzése</h4>
                            <p class="text-sm text-blue-900 mb-4">Mielőtt kérdeznél, <b class="text-blue-700">mindig használd a keresőt!</b> Nagy esély van rá, hogy valaki már feltette ugyanazt a kérdést, és született rá megoldás.</p>
                            <div class="bg-white p-3 rounded border border-blue-100">
                                <p class="text-sm text-gray-600 italic"><i class="fa-solid fa-triangle-exclamation text-yellow-500 mr-1"></i> A szándékos duplikálás a téma törlésével jár.</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-6">
                        <h4 class="font-bold text-xl text-slate-800 mb-4 flex items-center"><i class="fa-solid fa-list-check text-slate-500 mr-2"></i> Kötelező Mezők Kitöltése</h4>
                        <ul class="list-none space-y-4 text-gray-700">
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold mr-3">1</div>
                                <div>
                                    <span class="font-bold text-slate-900 block mb-1">Rövid Összefoglaló (Summary):</span>
                                    A kérdésfeltevésnél találsz egy rövid leírás mezőt (max 150 karakter). Ide a probléma lényegét írd, ez jelenik meg a listákban és a keresőkben. Legyen figyelemfelkeltő, de pontos!
                                </div>
                            </li>
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold mr-3">2</div>
                                <div>
                                    <span class="font-bold text-slate-900 block mb-1">Részletes Leírás:</span>
                                    Itt fejtsd ki bőven a problémát. Írd le a hardver specifikációkat (CPU, GPU, RAM, Táp, OS verzió), a hibaüzenet pontos szövegét, és azt, hogy mikor jelentkezik a hiba.
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- 4. AI ÉS AUTOMATIZÁCIÓ -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-slate-800 border-l-8 border-blue-500 pl-4 mb-6 uppercase tracking-wide">
                        4. Mesterséges Intelligencia (AI) Használata
                    </h2>

                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6 mb-6 shadow-sm">
                        <h4 class="font-bold text-blue-800 mb-3 flex items-center text-lg"><i class="fa-solid fa-robot mr-2 text-2xl"></i> Hivatalos álláspontunk az AI-ról</h4>
                        <p class="text-blue-900 text-base mb-3 leading-relaxed">
                            A technológia fejlődésével az AI (ChatGPT, Claude, Gemini stb.) a mindennapok része lett. Oldalunk <span class="bg-blue-200 text-blue-900 px-1 rounded font-bold">nem tiltja</span> az AI használatát a válaszadásban, AMENNYIBEN az hozzáadott értéket képvisel és szakmailag ellenőrzött.
                        </p>
                        <p class="text-indigo-800 font-bold border-t border-blue-200 pt-3 mt-3">
                            <i class="fa-solid fa-bolt text-yellow-500 mr-1"></i> Ugyanakkor bevezettük saját automatikus AI válaszadó rendszerünket!
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="fa-solid fa-gears text-slate-400 mt-1 mr-3"></i>
                            <p class="text-gray-700"><b>Automatikus AI Válasz:</b> Minden új kérdésre a rendszerünk automatikusan generál egy előzetes AI választ. Ennek célja, hogy a kérdező azonnal kapjon valamilyen iránymutatást, amíg a közösség szakértői megérkeznek. Ezért kérjük, <b class="text-red-600">NE másolj be</b> egyszerű ChatGPT válaszokat csak azért, hogy legyen válaszod, mert a rendszer ezt már megteszi helyetted!</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fa-solid fa-triangle-exclamation text-slate-400 mt-1 mr-3"></i>
                            <p class="text-gray-700"><b>Ellenőrizetlen tartalom:</b> Szigorúan tilos az AI által generált szövegek ellenőrzés nélküli bemásolása (hallucinációk veszélye). Ha AI-t használsz, olvasd át, javítsd, és egészítsd ki saját tapasztalataiddal.</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fa-solid fa-ban text-slate-400 mt-1 mr-3"></i>
                            <p class="text-gray-700"><b>Spam elkerülése:</b> Az "AI copy-paste" áradat elkerülése végett a moderátorok törölhetik azokat a válaszokat, amelyek nyilvánvalóan csak generált szövegek és nem adnak többet a rendszer saját AI válaszánál.</p>
                        </div>
                    </div>
                </div>

                <!-- 5. KÉP, VIDEÓ ÉS MÉDIA -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-slate-800 border-l-8 border-red-500 pl-4 mb-6 uppercase tracking-wide">
                        5. Média Tartalmak (Kép & Videó)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="font-bold text-slate-800 mb-3 text-lg border-b-2 border-red-200 pb-1">Videók Kezelése</h4>
                            <p class="mb-4 text-gray-700 text-sm italic">
                                Szerverünk kapacitásának védelme és a jogi kockázatok elkerülése érdekében <b>videófájlok közvetlen feltöltése az oldalra NEM engedélyezett.</b>
                            </p>
                            <ul class="list-disc pl-5 text-gray-700 space-y-2">
                                <li><b class="text-red-600"><i class="fa-brands fa-youtube"></i> YouTube:</b> A YouTube linkeket a rendszer automatikusan felismeri és beágyazza a bejegyzésbe. Ez a javasolt módszer videók megosztására.</li>
                                <li><b>Egyéb megosztók:</b> Vimeo, Streamable vagy egyéb linkek megosztása engedélyezett, amennyiben a tartalom nem sérti a szabályzatot.</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 mb-3 text-lg border-b-2 border-red-200 pb-1">Képfeltöltési Szabályok</h4>
                            <ul class="list-disc pl-5 text-gray-700 space-y-2">
                                <li>Max. fájlméret: <b class="bg-gray-200 px-1 rounded">10 MB</b>.</li>
                                <li>Támogatott formátumok: <span class="text-xs font-mono bg-gray-100 p-1">JPG, PNG, WEBP, GIF</span>.</li>
                                <li><b>Szerzői jog:</b> Csak saját készítésű képernyőképet vagy fotót tölts fel. Mások szellemi tulajdonát képező képek (pl. fizetős tutorialok scanjei) feltöltése tilos.</li>
                                <li><b>NSFW (Not Safe For Work):</b> Szigorúan <span class="bg-red-600 text-white px-1 font-bold rounded">TILOS</span> pornográf, erőszakos, sokkoló vagy gyűlöletkeltő képi anyagok közzététele. Ez azonnali és végleges kitiltást von maga után, valamint szükség esetén hatósági feljelentést.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 6. LINKEK ÉS FORRÁSOK -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-slate-800 border-l-8 border-yellow-500 pl-4 mb-6 uppercase tracking-wide">
                        6. Külső Hivatkozások és Források
                    </h2>
                    <p class="mb-4 text-gray-700">Külső weboldalakra mutató linkek elhelyezése engedélyezett, de szigorú feltételekhez kötött:</p>
                    <div class="bg-yellow-50 p-5 rounded border border-yellow-200">
                        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <li class="flex items-center text-gray-800"><i class="fa-solid fa-shield-halved text-green-500 mr-3 text-xl"></i> <span><b>Megbízhatóság:</b> Csak ismert, biztonságos oldalra linkelj.</span></li>
                            <li class="flex items-center text-gray-800"><i class="fa-solid fa-link text-blue-500 mr-3 text-xl"></i> <span><b>Ellenőrzés:</b> Győződj meg róla, hogy a link működik.</span></li>
                            <li class="flex items-center text-gray-800"><i class="fa-solid fa-bullhorn text-orange-500 mr-3 text-xl"></i> <span><b>Reklám tilalom:</b> Saját oldalad öncélú promózása tilos.</span></li>
                            <li class="flex items-center text-gray-800"><i class="fa-solid fa-skull-crossbones text-red-500 mr-3 text-xl"></i> <span><b>Warez / Crack:</b> Illegális letöltések linkelése tilos.</span></li>
                        </ul>
                    </div>
                </div>

                <!-- 7. MÁSOLÁS (PLÁGIUM) -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-slate-800 border-l-8 border-gray-600 pl-4 mb-6 uppercase tracking-wide">
                        7. Szerzői Jogok és Másolás
                    </h2>
                    <div class="bg-gray-100 p-6 rounded-lg border-l-4 border-gray-800">
                        <p class="mb-4 text-gray-800 font-bold text-lg"><i class="fa-solid fa-copyright mr-2"></i> Tilos más weboldalakról származó cikkek, leírások, hírek teljes egészében történő bemásolása (Copy-Paste)!</p>
                        <div class="ml-6 space-y-2 text-gray-700">
                            <p><b>Miért?</b></p>
                            <ul class="list-disc pl-5">
                                <li>Sérti az eredeti szerző jogait.</li>
                                <li>A keresőmotorok (Google) büntetik a duplikált tartalmat, ami árt az oldalunknak.</li>
                                <li>Nem ad hozzáadott értéket a közösséghez.</li>
                            </ul>
                        </div>
                        <div class="mt-4 bg-white p-3 rounded shadow-inner text-gray-700 border border-gray-300">
                            <i class="fa-solid fa-thumbs-up text-green-600 mr-2"></i> <b>Helyes eljárás:</b> Másold be a cikk egy rövid részletét (idézet), majd add meg a forrás linkjét, és fűzd hozzá a <b>saját véleményedet</b> vagy kérdésedet.
                        </div>
                    </div>
                </div>

                <!-- 8. BIZTONSÁG ÉS SCRIPTEK -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-slate-800 border-l-8 border-red-700 pl-4 mb-6 uppercase tracking-wide">
                        8. IT Biztonság és Kódhasználat
                    </h2>
                    <p class="mb-4 text-gray-700">Mivel technológiai fórum vagyunk, gyakran osztunk meg kódrészleteket. A biztonság azonban elsődleges.</p>
                    <div class="space-y-4">
                        <div class="bg-red-50 border border-red-200 p-5 rounded-lg shadow-sm">
                            <h4 class="font-bold text-red-800 mb-2 flex items-center"><i class="fa-solid fa-bug mr-2"></i> Ártalmas kódok</h4>
                            <p class="text-red-900 text-sm leading-relaxed">
                                Szigorúan <b class="underline decoration-red-500">TILOS</b> bármilyen kártékony kód (malware, virus, spyware, ransomware), exploit, vagy rendszerkárosító script közzététele, még oktatási célból is, kivéve biztonságos, zárt környezetben történő elemzés céljából, és csak megfelelő figyelmeztetéssel (de inkább kerüld).
                                <br><span class="font-bold mt-2 block">A szándékos károkozásra alkalmas parancsok (pl. <code>rm -rf /</code>, fork bombák) "viccből" történő megosztása azonnali kitiltást von maga után.</span>
                            </p>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 p-5 rounded-lg shadow-sm">
                            <h4 class="font-bold text-yellow-800 mb-2 flex items-center"><i class="fa-solid fa-user-secret mr-2"></i> Obfuszkált kódok</h4>
                            <p class="text-yellow-900 text-sm leading-relaxed">
                                Ne ossz meg szándékosan olvashatatlanná tett (obfuscated) kódot, hacsak nem a visszafejtés a téma. A közösségnek látnia kell, mit futtat.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 9. FIÓKKEZELÉS -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-slate-800 border-l-8 border-emerald-500 pl-4 mb-6 uppercase tracking-wide">
                        9. Regisztráció és Fiókok
                    </h2>
                    <div class="flex items-center mb-4">
                        <span class="bg-emerald-100 text-emerald-800 font-bold px-3 py-1 rounded-full text-sm mr-3">Egy felhasználó = Egy fiók</span>
                    </div>
                    <ul class="list-disc pl-5 text-gray-700 space-y-2">
                        <li><b>Többszörös regisztráció (Multiaccount):</b> Szigorúan tilos több felhasználói fiókot létrehozni ugyanazon személynek. Ennek célja általában a manipuláció (saját kérdések felpontozása, viták szítása, kitiltás kijátszása).</li>
                        <li><span class="text-red-600 font-bold">SZANKCIÓ:</span> Ha a rendszer vagy a moderátorok többszörös regisztrációt észlelnek (IP egyezés, böngésző ujjlenyomat stb. alapján), <b>az ÖSSZES érintett fiók végleges törlésre kerül</b>, beleértve a fő fiókot is.</li>
                        <li><b>Profil megosztás:</b> A fiókodat nem adhatod át másnak. A fiókoddal végzett minden tevékenységért te vagy a felelős.</li>
                    </ul>
                </div>

                <!-- 10. VISELKEDÉS ÉS PRIVÁT ÜZENETEK -->
                <div class="mb-12">
                    <h2 class="text-2xl font-bold text-slate-800 border-l-8 border-orange-500 pl-4 mb-6 uppercase tracking-wide">
                        10. Viselkedési Kultúra és Privát Üzenetek
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <h4 class="font-bold text-slate-800 mb-3 text-lg border-b-2 border-orange-200 pb-1">Kommunikációs Stílus</h4>
                            <ul class="space-y-2 text-gray-700">
                                <li class="flex items-start"><i class="fa-solid fa-ban text-red-500 mt-1 mr-2"></i> Tilos a trágár, obszcén kifejezések használata.</li>
                                <li class="flex items-start"><i class="fa-solid fa-ban text-red-500 mt-1 mr-2"></i> Tilos a személyeskedés, mások sértegetése vallási, etnikai, politikai vagy egyéb alapon (Gyűlöletbeszéd = Azonnali ban).</li>
                                <li class="flex items-start"><i class="fa-solid fa-handshake text-green-500 mt-1 mr-2"></i> Kerüld a politikai vitákat, ez egy szakmai fórum.</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 mb-3 text-lg border-b-2 border-orange-200 pb-1">Privát Üzenetek (PM) Szabályai</h4>
                            <p class="mb-2 text-sm text-gray-600 italic">A privát üzenet funkció nem mentes a szabályoktól:</p>
                            <ul class="list-disc pl-5 text-gray-700 space-y-1">
                                <li><b>Zaklatás:</b> Tilos más tagokat privátban zaklatni, kéretlen ajánlatokkal bombázni.</li>
                                <li><b>Segítségkérés privátban:</b> Ne írj a moderátoroknak vagy szakértő tagoknak privátban technikai kérdéssel. A kérdéseket a fórumba írd ki!</li>
                                <li><b>Spam:</b> Tilos reklámokat, láncleveleket küldeni privátban.</li>
                                <li><b>Jelentés:</b> A sértő privát üzenetek jelenthetők.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- 11. ZÁRSZÓ -->
                <div class="mt-16 text-center pt-10 border-t-2 border-gray-100">
                    <p class="text-2xl font-bold text-indigo-700 mb-2">Köszönjük, hogy elolvastad és betartod a szabályzatot!</p>
                    <p class="text-gray-500 mb-8 font-medium">Együtt egy jobb és okosabb közösséget építünk.</p>

                    <a href="index.php" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-full hover:from-indigo-700 hover:to-purple-700 transition duration-300 shadow-lg hover:shadow-2xl transform hover:-translate-y-1 text-lg">
                        <i class="fa-solid fa-arrow-left mr-3"></i> Vissza a főoldalra
                    </a>
                </div>

            </div>

            <aside class="cikk-fo-col-right space-y-6">
                 <!-- Widget -->
                 <div class="bg-white p-6 shadow-md rounded border border-gray-200">
                    <h3 class="font-bold text-lg text-slate-800 mb-4 border-b pb-2">Gyorslinkek</h3>
                    <ul class="space-y-3">
                        <li><a href="index.php" class="flex items-center text-indigo-600 hover:text-indigo-800 font-medium transition"><i class="fa-solid fa-house w-6"></i> Főoldal</a></li>
                        <li><a href="kerdes_bevitele.php" class="flex items-center text-indigo-600 hover:text-indigo-800 font-medium transition"><i class="fa-solid fa-plus w-6"></i> Új kérdés</a></li>
                        <li><a href="#" class="flex items-center text-indigo-600 hover:text-indigo-800 font-medium transition"><i class="fa-solid fa-envelope w-6"></i> Kapcsolat</a></li>
                    </ul>
                 </div>

                 <div class="bg-blue-50 p-6 shadow-md rounded border border-blue-100">
                    <h3 class="font-bold text-lg text-blue-800 mb-2"><i class="fa-solid fa-shield-cat"></i> Moderátorok</h3>
                    <p class="text-sm text-blue-900 leading-relaxed">
                        A szabályzat betartását moderátoraink felügyelik. Döntéseikkel szemben a <a href="#" class="underline font-bold">Kapcsolat</a> menüpontban élhetsz panasszal, de a nyilvános vitát a moderációról kérjük mellőzni.
                    </p>
                 </div>
            </aside>
        </div>
    </div>
</main>

<footer class="footer_alul_wrapper">
    <div class="gradient-border-bottom"></div>
    <div class="header-content-wrapper">
        <div class="footer_alul_grid">
            <div class="footer_alul_column">
                <div class="footer_alul_heading">Rólunk</div>
                <div class="logo-font text-2xl font-bold mb-4 text-white">
                    SILVER<span style="color: var(--win98-teal);">PC</span><span style="color: var(--cyber-teal); font-size: 0.6em;">.HU</span>
                </div>
                <p class="footer_alul_text">
                    A <span class="footer_alul_about_highlight">SilverPC</span> Magyarország egyik vezető hardver és szoftver hírportálja.
                </p>
            </div>
             <div class="footer_alul_column">
                    <div class="footer_alul_heading">Navigáció</div>
                    <ul class="footer_alul_links">
                        <li><a href="index.php" class="footer_alul_link_item"><i class="fas fa-chevron-right"></i> Főoldal</a></li>
                        <li><a href="#" class="footer_alul_link_item"><i class="fas fa-chevron-right"></i> Hírek</a></li>
                    </ul>
                </div>
        </div>
    </div>

    <div class="footer_alul_copyright_section">
        <div class="header-content-wrapper">
             <p class="footer_alul_legal_text">
                    Minden jog fenntartva.
            </p>
        </div>
    </div>
</footer>

</body>
</html>