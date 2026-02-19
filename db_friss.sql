-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 19, 2026 at 08:06 PM
-- Server version: 10.11.11-MariaDB-0+deb12u1-log
-- PHP Version: 8.4.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blog`
--

-- --------------------------------------------------------

--
-- Table structure for table `qc_answers`
--

CREATE TABLE `qc_answers` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `is_accepted` tinyint(1) DEFAULT 0,
  `is_ai` tinyint(1) DEFAULT 0,
  `is_guest` tinyint(1) DEFAULT 0,
  `guest_name` varchar(100) DEFAULT NULL,
  `vote_score` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qc_answers`
--

INSERT INTO `qc_answers` (`id`, `question_id`, `user_id`, `content`, `is_accepted`, `is_ai`, `is_guest`, `guest_name`, `vote_score`, `created_at`) VALUES
(1, 1, 2, 'Szia! Ha a jövőállóság a cél, akkor az RX 7800 XT jó választás a 16GB VRAM miatt.', 1, 0, 0, NULL, 1, '2026-02-16 04:03:00'),
(2, 1, 3, 'Én az RTX 4070-et ajánlom a DLSS miatt.', 0, 0, 0, NULL, -1, '2026-02-16 04:03:00'),
(3, 6, 999, 'erte45t654', 0, 0, 0, NULL, 0, '2026-02-16 11:34:15'),
(4, 6, 999, 'okés !', 1, 0, 0, NULL, 0, '2026-02-16 12:03:11'),
(5, 7, 999, 'Szevasztok! Ha.9<div>Szióka :)</div>', 1, 0, 0, NULL, 0, '2026-02-16 12:04:13'),
(6, 7, 999, 'Hej', 0, 0, 0, NULL, 0, '2026-02-16 12:19:02'),
(7, 7, 999, 'uizizui', 0, 0, 0, NULL, 0, '2026-02-16 13:22:44'),
(8, 7, 999, 'zuiuzi', 0, 0, 0, NULL, 0, '2026-02-16 13:22:46'),
(9, 7, 999, 'zuizuid', 0, 0, 0, NULL, 0, '2026-02-16 13:22:48'),
(10, 7, 999, 'rftztrztr', 0, 0, 0, NULL, 0, '2026-02-16 13:22:51'),
(11, 7, 999, 'tzutdzutzu', 0, 0, 0, NULL, 0, '2026-02-16 13:22:55'),
(12, 7, 999, 'tzudtutz', 0, 0, 0, NULL, 0, '2026-02-16 13:22:58'),
(13, 7, 999, 'rtztrszrt', 0, 0, 0, NULL, 0, '2026-02-16 13:23:01'),
(14, 7, 999, 'uiuzizuiuz', 0, 0, 0, NULL, 0, '2026-02-16 13:23:03'),
(15, 7, 999, 'tzudtzutzudtz', 0, 0, 0, NULL, 0, '2026-02-16 13:23:06'),
(16, 7, 999, 'dtzutzutzutz', 0, 0, 0, NULL, 0, '2026-02-16 13:23:09'),
(17, 7, 999, 'dtutzutzdut', 0, 0, 0, NULL, 0, '2026-02-16 13:23:12'),
(18, 4, 999, 'rtretretrt', 0, 0, 0, NULL, 0, '2026-02-16 13:28:49'),
(19, 4, 999, 'rtre', 0, 0, 0, NULL, 0, '2026-02-16 16:50:00'),
(20, 4, 999, 'kacsa', 0, 0, 0, NULL, 0, '2026-02-16 17:27:10'),
(21, 2, 999, 'Hello :)', 0, 0, 0, NULL, 0, '2026-02-16 20:31:03'),
(22, 2, 999, 'ok:)', 0, 0, 0, NULL, 0, '2026-02-16 20:32:26'),
(23, 2, 999, 'Hello:)', 0, 0, 0, NULL, 0, '2026-02-16 20:32:40'),
(24, 2, 999, 'sadasda', 0, 0, 0, NULL, 0, '2026-02-16 20:33:03'),
(25, 2, 999, '55', 0, 0, 0, NULL, 0, '2026-02-16 21:10:29'),
(26, 12, NULL, '<p>Szia!</p>\n<p>Ez egy nagyon kellemetlen helyzet, de sajnos elég gyakori manapság, hogy az ember elveszíti a hozzáférését egy régi e-mail fiókhoz, ami aztán gátat szab más szolgáltatásokba való bejelentkezésnek. Lássuk, mit tehetünk a TikTok profilod visszaszerzése érdekében.</p>\n\n<p>A legfontosabb dolog, amit tudnod kell, hogy mivel az e-mail fiókhoz már nem férsz hozzá, a TikTok biztonsági rendszerénél elakadtál. A platformoknak muszáj valahogy ellenőrizniük, hogy valóban te vagy az, aki megpróbál belépni, és ha a jelszón kívül más azonosításra van szükség, azt a rendszer kéri.</p>\n\n<p><strong>A te esetedben a legjárhatóbb út a TikTok ügyfélszolgálatának elérése:</strong></p>\n<ul>\n    <li>Keresd fel a TikTok hivatalos súgóját vagy támogatási oldalát. Általában valahol a \"Súgó\" vagy \"Beállítások és adatvédelem\" (ha be tudnál lépni) résznél van egy \"Probléma jelentése\" vagy \"Kapcsolatfelvétel\" opció.</li>\n    <li>Válaszd ki azt a kategóriát, ami a leginkább illik a problémádra, például \"Bejelentkezési problémák\" vagy \"Fiók helyreállítása\".</li>\n    <li>Nagyon részletesen írd le a problémádat:\n        <ul>\n            <li>Mondd el, hogy a felhasználóneved és jelszavad jó, de e-mail igazolást kér.</li>\n            <li>Tisztázd, hogy az adott e-mail címhez már nincs hozzáférésed (és nem tudod a jelszavát).</li>\n            <li>Említsd meg, ha esetleg korábban más telefonszám vagy e-mail cím is társítva volt a fiókhoz, és azokhoz hozzáfértél.</li>\n        </ul>\n    </li>\n    <li><strong>Készülj fel arra, hogy a TikTok valószínűleg kérni fogja, hogy bizonyítsd a fiók tulajdonjogát.</strong> Ehhez a következő információk lehetnek hasznosak:\n        <ul>\n            <li>Korábbi jelszavak (ha emlékszel rájuk).</li>\n            <li>A fiókhoz eredetileg regisztrált telefonszám (ha volt ilyen).</li>\n            <li>Az a telefonszám, amihez jelenleg hozzá tudsz férni, és kérd, hogy oda küldjék az azonosítót (ha ez lehetséges).</li>\n            <li>Az, hogy milyen eszközről szoktál belépni (pl. \"Samsung Galaxy S22\", \"iPhone 14\").</li>\n            <li>Az a földrajzi hely, ahonnan legutóbb beléptél.</li>\n            <li>A fiók létrehozásának hozzávetőleges ideje (ha emlékszel rá).</li>\n            <li>Esetleg olyan ismerősök felhasználóneve, akikkel gyakran interaktáltál.</li>\n        </ul>\n    </li>\n</ul>\n\n<p><em>Fontos: Légy türelmes! Az ügyfélszolgálati válasz néha eltarthat, különösen, ha komplexebb fiók-helyreállításról van szó.</em></p>\n\n<p><strong>Egy kérdés tőlem:</strong> Nem emlékszel rá, hogy esetleg telefonszám is volt-e társítva a TikTok fiókodhoz? Néha van lehetőség SMS-ben kérni az azonosító kódot, ha az e-mail már nem elérhető.</p>\n\n<p>A jövőre nézve pedig nagyon fontos, hogy minden online fiókodhoz (beleértve a TikTokot is) olyan helyreállítási e-mail címet és telefonszámot adj meg, amihez mindig hozzá tudsz férni, és frissítsd ezeket, ha változás történik!</p>\n\n<p>Remélem, sikerül visszaszerezned a profilodat! Jelezz, ha elakadsz, vagy ha van új információd!</p>', 0, 1, 0, NULL, 0, '2026-02-16 22:29:23'),
(27, 13, NULL, '<p>Szia!</p>\n\n<p>Érthető a bosszúságod, ez egy tipikusan az a fajta probléma, ami az embert a falra kergeti, főleg, hogy \"csak úgy\" jött elő, és a legtriviálisabb megoldások (alapértelmezett programok beállítása, újratelepítés) sem segítenek. A leírásod alapján az a legfurcsább, hogy Intézőből és Total Commanderből működik minden, az Office viszont máshogy kezeli a dolgot.</p>\n\n<p>Valószínűleg az Office 2007 – amely már egy régebbi szoftver – és a modern Windows 11 operációs rendszer közötti kölcsönhatásban keresendő a hiba. Az Office a hiperhivatkozásokat gyakran URL-ként kezeli, még akkor is, ha helyi fájlra mutatnak (pl. <code>file:///C:/mappa/kép.jpg</code>). Ilyenkor előfordulhat, hogy nem a Windows fájltípus-társítását hívja meg közvetlenül a <code>ShellExecute</code> paranccsal, hanem a rendszer alapértelmezett böngészőjét bízza meg a link kezelésével. Mivel a Chrome a te alapértelmezett böngésződ, ezért ő kapja meg a feladatot, még akkor is, ha egyébként nem a Chrome az alapértelmezett képnézegető.</p>\n\n<p>Nézzük meg, mit tehetünk még, sorban haladva a kevésbé invazív megoldásoktól a komplexebbek felé:</p>\n\n<ul>\n    <li><strong>1. \"Megnyitás ezzel\" (Open With) alapos újraállítása:</strong>\n        <p>Bár már ellenőrizted az alapértelmezett alkalmazásokat, ez a módszer néha felülírja a hibás vagy \"beragadt\" asszociációkat, még akkor is, ha látszólag már be volt állítva:</p>\n        <ol>\n            <li>Nyiss meg egy Intéző ablakot.</li>\n            <li>Keress egy .jpg vagy .jpeg kiterjesztésű képfájlt (amiről szó van az albumban).</li>\n            <li>Kattints jobb egérgombbal a fájlra.</li>\n            <li>Válaszd a <strong>\"Megnyitás ezzel\"</strong> (Open With) menüpontot, majd azon belül a <strong>\"Válasszon másik alkalmazást\"</strong> (Choose another app) lehetőséget.</li>\n            <li>A megjelenő listában keresd meg a <strong>FastStone Image Viewer</strong> programot. Ha nem látod, görgess le, és kattints a <strong>\"További alkalmazások\"</strong> (More apps) vagy <strong>\"Keresés az alkalmazások között ezen a számítógépen\"</strong> (Look for another app on this PC) linkre, majd tallózd be a FastStone Viewer futtatható (.exe) fájlját (ez általában a <code>C:\\Program Files (x86)\\FastStone Image Viewer\\FSViewer.exe</code> útvonalon található, ha 64 bites rendszered van).</li>\n            <li><strong>Nagyon fontos:</strong> Mielőtt rákattintasz az \"OK\" gombra, győződj meg róla, hogy be van jelölve az <strong>\"Mindig ezt az alkalmazást használja a .jpg (és .jpeg) fájlok megnyitásához\"</strong> (Always use this app to open .jpg/jpeg files) opció.</li>\n            <li>Ismételd meg ezt a .jpeg kiterjesztésre is, ha releváns.</li>\n        </ol>\n        <p>Ez a lépés néha megerősíti a rendszer számára az alapértelmezett beállítást, még akkor is, ha látszólag már be volt állítva.</p>\n    </li>\n\n    <li><strong>2. FastStone Image Viewer belső beállításainak ellenőrzése:</strong>\n        <p>A FastStone-nak is van gyakran egy saját asszociációs kezelője. Érdemes lehet ott is megnézni:</p>\n        <ol>\n            <li>Indítsd el a FastStone Image Viewert.</li>\n            <li>Keresd meg a <strong>\"Settings\"</strong> vagy <strong>\"Beállítások\"</strong> menüpontot (általában az \"F\" billentyű lenyomásával vagy a menüsorban).</li>\n            <li>Keresd az <strong>\"Associations\"</strong> vagy <strong>\"Fájltársítások\"</strong> fület/menüpontot.</li>\n            <li>Győződj meg róla, hogy a .jpg és .jpeg fájlok be vannak jelölve, és esetleg nyomj rá egy \"Register\" vagy \"Apply\" gombra, hogy újra regisztrálja magát. Próbáld meg akár kikapcsolni, majd visszakapcsolni az asszociációkat, majd menteni.</li>\n        </ol>\n    </li>\n\n    <li><strong>3. Office 2007 Adatvédelmi központ (Trust Center) beállításai:</strong>\n        <p>Bár kisebb az esély, érdemes ránézni, hátha van valami általános biztonsági beállítás, ami a hiperhivatkozások kezelését befolyásolja, és esetleg valamilyen \"biztonságosnak ítélt\" módon (azaz böngészővel) nyitja meg a linkeket:</p>\n        <ol>\n            <li>Nyiss meg egy Excel vagy Word dokumentumot.</li>\n            <li>Kattints az <strong>Office gombra</strong> (bal felső sarokban) > <strong>Excel/Word Beállításai</strong> (Options).</li>\n            <li>Válaszd az <strong>Adatvédelmi központ</strong> (Trust Center) menüpontot, majd kattints az <strong>Adatvédelmi központ beállításai...</strong> (Trust Center Settings...) gombra.</li>\n            <li>Nézd át az \"Adatvédelmi beállítások a weben\" vagy \"Hyperlink Warning for Suspicious Sites\" (Hiperhivatkozás figyelmeztetés gyanús oldalakhoz) beállításokat. Bár ez nem közvetlenül a probléma forrása, egy általános biztonsági beállítás néha okozhat ilyen mellékhatásokat.</li>\n        </ol>\n    </li>\n\n    <li><strong>4. Böngésző (Chrome) protokollkezelő beállításai:</strong>\n        <p>Annak ellenére, hogy ellenőrizted, érdemes megnézni, nincs-e a Chrome-ban valamilyen \"protokollkezelő\" beállítás, ami a <code>file://</code> protokollhoz társította volna magát. Ez ritka, de előfordulhat, és a böngészők hajlamosak agresszívan regisztrálni magukat:</p>\n        <ol>\n            <li>Nyisd meg a Chrome-ot.</li>\n            <li>Írd be a címsorba: <pre><code>chrome://settings/handlers</code></pre></li>\n            <li>Nézd át a listát, és törölj minden olyan bejegyzést, ami potenciálisan a fájlok vagy protokollok kezelésével kapcsolatos, ha nem te állítottad be, és nem kritikus.</li>\n        </ol>\n    </li>\n\n    <li><strong>5. Rendszerleíró adatbázis (Registry) ellenőrzése (haladó felhasználóknak és óvatosan!):</strong>\n        <p>Ez már egy haladó lépés, és csak akkor javasolt, ha a fentiek nem működnek. Egy hibás módosítás komoly problémákat okozhat! Mindig készíts biztonsági mentést a módosítás előtt, vagy kérj segítséget, ha nem vagy biztos benne.</p>\n        <ol>\n            <li>Nyomd meg a <code>Win + R</code> billentyűket, írd be, hogy <pre><code>regedit</code></pre>, majd nyomj Entert.</li>\n            <li>Navigálj ide: <pre><code>HKEY_CURRENT_USER\\SOFTWARE\\Microsoft\\Windows\\Shell\\Associations\\UrlAssociations\\file</code></pre>\n            <li>Itt megpróbálhatod megnézni, milyen program van asszociálva a <code>file://</code> protokollhoz. Néha itt is eltévedhet a beállítás. Ezt a részt csak óvatosan és kellő tudással manipuláld!</li>\n        </ol>\n    </li>\n</ul>\n\n<p>A legvalószínűbb, hogy az <strong>1. pontban</strong> leírt, \"Megnyitás ezzel\" módszerrel történő alapértelmezett program megerősítése segíthet, mivel ez a legdirektebb módja a Windows fájltársításainak felülírására vagy megerősítésére. Fontos, hogy pontosan a .jpg és .jpeg fájltípusokra is elvégezd!</p>\n\n<p>Kérlek, próbáld végig ezeket a lépéseket, és jelezz vissza, hogy sikerült-e megoldani a problémát! Ha bármelyik lépésnél elakadsz, vagy kérdésed van, nyugodtan kérdezz!</p>\n\n<p>Üdvözlettel,<br>\nA SilverPC csapata</p>', 0, 1, 0, NULL, 0, '2026-02-16 22:33:03'),
(28, 14, NULL, '<p>Szia! Ó, ez egy igazán jó téma, tele \"érdekes\" emlékekkel! A \"legrosszabb\" persze sok mindentől függ: az adott kor viszonylatában, az ár-érték arányban, a megbízhatóságban vagy éppen a gyártó jövőképében betöltött szerepében. De íme néhány igazi \"gyöngyszem\" a történelemből, amik sokaknak okoztak fejtörést:</p>\n\n<ul>\n    <li><strong>Intel Pentium 4 (különösen a Prescott magosok)</strong>:\n    <p>A NetBurst architektúra, főleg a 90 nm-es Prescott maggal, egy igazi erőmű volt... a hőtermelésben és az energiafogyasztásban. A hosszú pipeline miatt lassú volt a valós teljesítménynövekedés az órajelek emelésével, és hamar falba ütközött az AMD Athlon 64 processzoraival szemben. Az ígért órajelfolyamok sosem valósultak meg, és a \"hideg és meleg\" kategóriában a \"meleg\" oldalról simán dobogós!</p></li>\n\n    <li><strong>AMD FX széria (az első generációs Bulldozer)</strong>:\n    <p>Hatalmas várakozások előzték meg, de a Bulldozer architektúra, és a rá épülő első FX processzorok, komoly csalódást okoztak. A moduláris felépítés elméletben jól hangzott, de a gyakorlatban gyenge volt az egyszálas teljesítménye (főleg az Intel Sandy Bridge és Ivy Bridge-hez képest), magas volt az energiafogyasztása és a hőtermelése. Szoftveres optimalizációra is szüksége lett volna, ami lassan érkezett. A \"hideg\" (teljesítmény) és \"meleg\" (hő) kategóriában is eléggé megérdemli a helyét.</p></li>\n\n    <li><strong>Intel Celeron (az első, L2 cache nélküli változat, pl. Covington)</strong>:\n    <p>Bár a Celeron márkanév később nagyon sikeres lett (gondoljunk csak a 300A-ra!), az első, kizárólag L1 cache-szel rendelkező Celeron modellek katasztrofális teljesítményt nyújtottak. Az L2 cache hiánya gyakorlatilag megfojtotta a processzort, extrém módon visszafogva a sebességét, miközben az Intel célja az volt, hogy \"lebutítva\" tegye olcsóbbá a Pentium II-t. Szerencsére hamar korrigálták a hibát, és a Mendocino magos Celeronok már óriási sikert arattak.</p></li>\n\n    <li><strong>VIA C3 és C7</strong>:\n    <p>Ezek a processzorok elsősorban alacsony fogyasztású, beágyazott rendszerekbe készültek, és ott megállták a helyüket. Azonban ha valaki főleg az ár miatt egy általános célú asztali gépbe rakta be őket, szembesülnie kellett a döbbenetesen gyenge teljesítménnyel. Az Intel és AMD kortárs chipjeihez képest fényévekre voltak sebességben. Nem annyira \"rosszak\" a tervezési céljukhoz képest, mint inkább az elvárásokhoz képest, ha rossz helyre kerültek. Valószínűleg sokan \"hideg\" emléket őriznek róluk, ami a sebességükre vonatkozott.</p></li>\n</ul>\n\n<p>Persze, lehetne még sorolni pár kevésbé sikeres próbálkozást (pl. bizonyos Cyrix modellek FPU-problémákkal, vagy az Intel Itanium IA-64 architektúrájának küzdelmei a piacon), de a fenti példák a legemlékezetesebb \"bukásoknak\" vagy legalábbis a legvitatottabb daraboknak számítanak a nagyközönség számára.</p>\n<p>Nektek melyik volt a legnagyobb csalódás? Kíváncsian várjuk a véleményeket! :)</p>', 0, 1, 0, NULL, 0, '2026-02-16 22:36:05'),
(29, 15, NULL, '<p>Szia! Semmi gond, senki nem születik IT zseninek, mindenki tanulja, szóval ne érezd magad rosszul, amiért segítséget kérsz! Sőt, pont ezért van a fórum, hogy segítsünk egymásnak. Nagyon jól tetted, hogy azonnal próbálkozol a hibaelhárítással, különösen, ha biztonsági mentést akarsz készíteni – ez a legfontosabb!</p>\n\n<p>Az <em>\"access denied\"</em> hiba, amit kaptál, teljesen normális, és nem a te hibád! Ez azért van, mert a <code>chkdsk</code> parancs a C: meghajtót (ami a Windows rendszerét tartalmazza) próbálja ellenőrizni és javítani. Amíg a Windows fut, addig a C: meghajtót \"használja\", és nem engedi, hogy egy másik program (jelen esetben a <code>chkdsk</code>) kizárólagosan hozzáférjen a fájlrendszerhez hibajavítás céljából.</p>\n\n<p>A megoldás az, hogy a <code>chkdsk</code> parancsot úgy kell futtatni, hogy a Windows ütemezze azt a <strong>következő újraindításra</strong>, még az operációs rendszer betöltődése előtt. Íme, hogyan csináld:</p>\n\n<ol>\n    <li>Nyisd meg a parancssort <strong>rendszergazdaként</strong>. Ehhez:\n        <ul>\n            <li>Kattints a Start menüre.</li>\n            <li>Írd be: <code>cmd</code> (vagy \"Parancssor\").</li>\n            <li>A megjelenő \"Parancssor\" vagy \"Command Prompt\" találaton kattints a <strong>jobb egérgombbal</strong>.</li>\n            <li>Válaszd az <strong>\"Indítás rendszergazdaként\"</strong> (vagy \"Run as administrator\") lehetőséget.</li>\n            <li>Ha rákérdez a felhasználói fiókok felügyelete (UAC), hagyd jóvá.</li>\n        </ul>\n    </li>\n    <li>A fekete parancssor ablakban írd be pontosan ezt a parancsot, majd nyomj Entert:\n        <pre><code>chkdsk C: /f /r</code></pre>\n    </li>\n    <li>Ekkor valószínűleg egy üzenetet fogsz látni, ami valahogy így szól:\n        <p><em>\"Chkdsk cannot run because the volume is in use by another process. Would you like to schedule this volume to be checked the next time the system restarts? (Y/N)\"</em><br>\n        Vagy magyarul:<br>\n        <em>\"A Chkdsk nem futtatható, mert a kötetet egy másik folyamat használja. Szeretné ütemezni a kötet ellenőrzését a rendszer következő újraindításakor? (I/N)\"</em></p>\n    </li>\n    <li>Írj be egy <code>I</code>-t (vagy <code>Y</code>-t, ha angol nyelvű a rendszer) és nyomj Entert.</li>\n    <li>Utána indítsd újra a számítógépedet. Az újraindítás során a Windows betöltődése előtt lefut majd a lemezellenőrzés, ami eltarthat egy darabig (akár fél óráig vagy tovább is, függően a meghajtó méretétől és a hibák számától). <strong>Fontos, hogy ne szakítsd meg!</strong> Hagyd, hogy befejezze a műveletet!</li>\n</ol>\n\n<p>A parancsban használt kapcsolók jelentése:</p>\n<ul>\n    <li><code>/f</code>: Javítja a lemezen található fájlrendszer-hibákat.</li>\n    <li><code>/r</code>: Megkeresi a sérült szektorokat és megpróbálja helyreállítani az olvasható információkat (ez egy mélyebb vizsgálat, ezért tart tovább).</li>\n</ul>\n\n<p><strong>Mi van, ha a chkdsk lefutása után is hibát jelez a meghajtó?</strong><br>\nHa a chkdsk lefutása után is hibát jelez a meghajtó, vagy továbbra sem tudsz biztonsági mentést készíteni, akkor komolyabb problémáról lehet szó. Ekkor érdemes lenne:</p>\n<ul>\n    <li>Ellenőrizni a merevlemez/SSD <strong>S.M.A.R.T.</strong> állapotát. Erre vannak ingyenes programok, pl. a <a href=\"https://crystalmark.info/en/software/crystaldiskinfo/\" target=\"_blank\">CrystalDiskInfo</a>. Ez megmutatja, hogy fizikailag mennyire van jó állapotban a meghajtó, és jelezheti, ha meghibásodás előtt áll.</li>\n    <li>Próbálkozni a chkdsk futtatásával a <strong>Windows Helyreállítási Környezetből (WinRE)</strong>. Ehhez újra kell indítani a gépet, és a bejelentkezési képernyőn (vagy a Start menüből) a <code>Shift</code> billentyűt nyomva tartva kattints az újraindításra, majd válaszd a \"Hibaelhárítás\" -> \"Speciális beállítások\" -> \"Parancssor\" lehetőséget. Itt már a C: meghajtó valószínűleg nem lesz használatban (vagy más betűjelet kap, pl. D:), és gond nélkül lefuthat a chkdsk.</li>\n</ul>\n\n<p>A biztonsági mentés nagyon fontos, abszolút jól látod! Amíg van rá mód, próbálj meg valahogy mentést készíteni a fontos adataidról, még akkor is, ha a meghajtó hibásnak tűnik. Néha egy hibás meghajtóról is le lehet menteni adatokat, mielőtt végleg tönkremenne.</p>\n\n<p>Ne csüggedj, a számítógép építése és fenntartása egy folyamatos tanulási folyamat. Jól csinálod, hogy kérdezel! Írd meg majd, hogy sikerült-e a chkdsk futtatása és mi lett az eredménye! Ha bármi további kérdésed van, nyugodtan tedd fel!</p>', 0, 1, 0, NULL, 0, '2026-02-16 23:13:44'),
(30, 16, NULL, '<p>Szia! Nagyon jól értem a frusztrációdat, és sajnos nem vagy egyedül ezzel a problémával. Valóban bosszantó lehet, ha az embernek korlátlan mobilnetje van, mégis \"ki van éhezve\" a készülék egy wifi kapcsolatra.</p>\n\n<p>Azonban van egy fontos dolog, amit tisztáznunk kell az iPad (és iPhone) biztonsági mentésével kapcsolatban. Az Apple az <em>automatikus teljes készülék biztonsági mentést</em> (amit az <strong>iCloud biztonsági mentés</strong> funkció végez) alapértelmezetten és tervezetten <strong>csak akkor indítja el, ha a készülék egyszerre három feltételnek is megfelel</strong>:</p>\n<ul>\n    <li>csatlakoztatva van tápellátáshoz (töltőre dugva van),</li>\n    <li>le van zárva (a képernyő ki van kapcsolva), és</li>\n    <li><strong>wifi hálózathoz van csatlakoztatva.</strong></li>\n</ul>\n<p>Ez a \"wifi igény\" egy fixen beépített működési elv az Apple részéről, valószínűleg azért, hogy elkerüljék az óriási, véletlen mobiladat-felhasználást (mivel egy teljes készülék backup több tíz vagy akár száz GB is lehet), illetve a mobilhálózatok ingadozó sebességéből adódó hibákat. Sajnos ezt a beállítást, ami az <em>automatikus iCloud biztonsági mentés</em> wifis működését illeti, <strong>nem lehet kikapcsolni vagy átállítani mobilnetre</strong>.</p>\n\n<p>Azonban van pár alternatíva és magyarázat, hogy miért írja ki az iPad mégis, hogy \"x ideje nem történt biztonsági mentés\", és mit tehetsz:</p>\n\n<ul>\n    <li><strong>Az \"x ideje nem történt biztonsági mentés\" üzenet:</strong> Ez az üzenet pontosan arra utal, hogy a fenti három feltétel (tápegység, lezárva, wifi) nem állt fenn egyszerre egy ideje, ezért az automatikus teljes készülék mentés elmaradt.</li>\n\n    <li><strong>Részleges adatok mentése mobilneten keresztül:</strong> Fontos megkülönböztetni a <em>teljes készülék biztonsági mentést</em> az <em>iCloud szinkronizálástól</em>. Számos iCloud szolgáltatás (pl. iCloud Drive, iCloud Fotók, Névjegyek, Naptárak) képes mobilneten is szinkronizálni, ha engedélyezed nekik. Ha ezeket beállítod, akkor legalább a fontosabb adataid (dokumentumaid, fotóid) naprakészek maradnak az iCloudban. Ennek beállítási lehetőségei:</p>\n    <ul>\n        <li>Lépj a <strong>Beállítások</strong> &gt; <strong>[A neved, vagy Apple ID-d]</strong> &gt; <strong>iCloud</strong> menüpontba. Itt láthatod, mely alkalmazások szinkronizálnak az iCloudba. Győződj meg róla, hogy az itt lévő appoknál be van kapcsolva a szinkronizálás.</li>\n        <li>Külön a fotók esetében: <strong>Beállítások</strong> &gt; <strong>Fotók</strong> &gt; <strong>Mobil adatforgalom</strong>. Itt bekapcsolhatod a <em>Mobil adatforgalom</em> opciót, sőt az <em>Korlátlan frissítések</em> opciót is, ha korlátlan neted van. Ez biztosítja, hogy a fotóid mobilneten keresztül is szinkronizálódjanak az iCloudba.</li>\n        <li>Külön az iCloud Drive esetében: <strong>Beállítások</strong> &gt; <strong>Mobil adatforgalom</strong> menüpontban görgess le az <em>iCloud Drive</em>-ig, és kapcsold be, ha azt is szeretnéd, hogy mobilneten keresztül frissüljön a felhőtárhelyen lévő dokumentumok és fájlok.</li>\n    </ul>\n\n    <li><strong>Manuális Wi-Fi alapú mentés:</strong> Ha néha mégis szükséged van a teljes készülék mentésre, keress egy ingyenes vagy otthoni Wi-Fi-t, csatlakoztasd a készüléket töltőre, zárd le a képernyőt, és indítsd el manuálisan a mentést a <strong>Beállítások</strong> &gt; <strong>[A neved]</strong> &gt; <strong>iCloud</strong> &gt; <strong>iCloud biztonsági mentés</strong> menüpontban a <em>Biztonsági mentés most</em> gombbal.</li>\n\n    <li><strong>Mentés számítógéppel:</strong> A legmegbízhatóbb és teljes körű megoldás lehet, ha számítógéppel végzed a biztonsági mentést. Ezt USB-kábellel csatlakoztatva teheted meg a Finder (Mac esetén) vagy az iTunes (Windows PC esetén) segítségével. Ez teljesen független a Wi-Fi-től és a mobilnettől, és helyben, a gépeden tárolja a mentést.</li>\n</ul>\n\n<p>Sajnálom, hogy nem tudok olyan megoldással szolgálni, ami egy gombnyomással átállítaná a teljes iCloud biztonsági mentést mobilnetre, de remélem, ezek az információk segítenek megérteni a helyzetet és megtalálni a számodra legmegfelelőbb alternatívát a fontos adataid védelmére!</p>\n\n<p>Üdvözlettel,<br>A SilverPC fórum hardver szakértője</p>', 0, 1, 0, NULL, 0, '2026-02-16 23:18:55'),
(31, 21, 999, 'oha:)', 0, 0, 0, NULL, 0, '2026-02-18 00:10:52'),
(32, 1, 2, ':)', 0, 0, 0, NULL, 1, '2026-02-18 01:11:35'),
(33, 1, 999, 'rtzrtez', 0, 0, 0, NULL, 0, '2026-02-18 10:18:16'),
(34, 1, 999, 'uuzi', 0, 0, 0, NULL, 0, '2026-02-18 20:13:26'),
(35, 14, 999, 'Nézd meg a képet ...', 0, 0, 0, NULL, 0, '2026-02-19 20:08:11');

-- --------------------------------------------------------

--
-- Table structure for table `qc_answer_edits`
--

CREATE TABLE `qc_answer_edits` (
  `id` int(11) NOT NULL,
  `answer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `edited_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qc_answer_edits`
--

INSERT INTO `qc_answer_edits` (`id`, `answer_id`, `user_id`, `content`, `edited_at`) VALUES
(1, 5, 999, 'Szevasztok!', '2026-02-16 12:04:13'),
(2, 5, 999, 'Szevasztok! Ha.9', '2026-02-16 12:04:23'),
(3, 5, 999, 'Szevasztok! Ha.9<div>Szióka :)</div>', '2026-02-16 12:04:46'),
(4, 18, 999, 'rtretre', '2026-02-16 13:28:49'),
(5, 18, 999, 'rtretretrt', '2026-02-16 16:15:10');

-- --------------------------------------------------------

--
-- Table structure for table `qc_answer_images`
--

CREATE TABLE `qc_answer_images` (
  `id` int(11) NOT NULL,
  `answer_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qc_answer_images`
--

INSERT INTO `qc_answer_images` (`id`, `answer_id`, `image_path`, `original_name`, `created_at`) VALUES
(1, 22, 'uploads/images/2026/02/16/img_699370c5030e27.88968000.webp', NULL, '2026-02-16 20:32:26'),
(2, 23, 'uploads/images/2026/02/16/img_699370d4694c58.14571404.webp', NULL, '2026-02-16 20:32:40'),
(3, 24, 'uploads/images/2026/02/16/img_699370ed2aff64.11597320.webp', NULL, '2026-02-16 20:33:03'),
(4, 25, 'uploads/images/2026/02/16/img_699379b2c2ea58.06319348.webp', NULL, '2026-02-16 21:10:29'),
(5, 35, 'uploads/images/2026/02/19/img_69975f96190698.11457653.webp', NULL, '2026-02-19 20:08:11');

-- --------------------------------------------------------

--
-- Table structure for table `qc_answer_replies`
--

CREATE TABLE `qc_answer_replies` (
  `id` int(11) NOT NULL,
  `answer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `likes` int(11) DEFAULT 0,
  `dislikes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qc_answer_replies`
--

INSERT INTO `qc_answer_replies` (`id`, `answer_id`, `user_id`, `content`, `created_at`, `likes`, `dislikes`) VALUES
(1, 18, 999, 'Oké, ez így fasza :)', '2026-02-16 16:46:38', 18, 6),
(2, 18, 999, 'és akkor?!', '2026-02-16 16:47:34', 3, 3),
(3, 19, 999, 'tzerterte4', '2026-02-16 17:09:44', 0, 0),
(4, 8, 999, 'mi a szösz!', '2026-02-16 19:33:34', 1, 0),
(5, 5, 999, '1', '2026-02-16 19:46:44', 0, 0),
(6, 5, 999, '2', '2026-02-16 19:46:46', 0, 0),
(7, 5, 999, '3', '2026-02-16 19:46:48', 0, 0),
(8, 5, 999, '4', '2026-02-16 19:46:50', 0, 0),
(9, 5, 999, '5', '2026-02-16 19:46:52', 0, 0),
(10, 5, 999, '6', '2026-02-16 19:46:55', 0, 0),
(11, 5, 999, '7', '2026-02-16 19:46:58', 0, 0),
(12, 1, 999, 'teszt', '2026-02-17 22:17:12', 1, 0),
(13, 2, 2, 'De miért?:)', '2026-02-18 01:10:42', 1, 0),
(14, 32, 999, 'ok, és?', '2026-02-18 09:51:26', 0, 0),
(15, 32, 999, 'trtr', '2026-02-18 10:18:02', 0, 0),
(16, 2, 999, 'retre', '2026-02-18 10:26:53', 0, 0),
(17, 34, 999, 'nye', '2026-02-18 20:13:34', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `qc_badges`
--

CREATE TABLE `qc_badges` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `category` varchar(50) DEFAULT NULL,
  `threshold` int(11) DEFAULT 0,
  `style` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qc_badges`
--

INSERT INTO `qc_badges` (`id`, `name`, `description`, `icon`, `created_at`, `category`, `threshold`, `style`) VALUES
(1, 'First Question', 'Asked your first question', 'fa-question-circle', '2026-02-17 23:52:30', NULL, 0, NULL),
(2, 'First Answer', 'Provided your first answer', 'fa-comment-dots', '2026-02-17 23:52:30', NULL, 0, NULL),
(3, 'Problem Solver', 'Had an answer accepted', 'fa-check-circle', '2026-02-17 23:52:30', NULL, 0, NULL),
(4, 'Expert', 'Reached 1000 reputation points', 'fa-star', '2026-02-17 23:52:30', NULL, 0, NULL),
(5, 'Kíváncsi', 'Awarded for 5+ activity.', 'fa-question', '2026-02-18 21:28:01', NULL, 0, NULL),
(6, 'Közreműködő', 'Awarded for 5+ activity.', 'fa-hands-helping', '2026-02-18 21:28:01', NULL, 0, NULL),
(7, 'Hasznos', 'Awarded for 1+ activity.', 'fa-thumbs-up', '2026-02-18 21:28:01', NULL, 0, NULL),
(8, 'Megszólaló', 'Awarded for 10+ activity.', 'fa-comment', '2026-02-18 21:28:01', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `qc_bookmarks`
--

CREATE TABLE `qc_bookmarks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qc_bookmarks`
--

INSERT INTO `qc_bookmarks` (`id`, `user_id`, `question_id`, `created_at`) VALUES
(2, 999, 6, '2026-02-18 15:26:27'),
(3, 999, 4, '2026-02-18 15:26:28'),
(4, 999, 10, '2026-02-18 15:26:29'),
(5, 999, 7, '2026-02-18 15:26:31'),
(7, 999, 18, '2026-02-18 15:26:32'),
(9, 999, 15, '2026-02-18 15:26:38'),
(10, 999, 13, '2026-02-18 15:26:38'),
(11, 999, 9, '2026-02-18 15:26:39'),
(12, 999, 3, '2026-02-18 15:26:40');

-- --------------------------------------------------------

--
-- Table structure for table `qc_categories`
--

CREATE TABLE `qc_categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon_class` varchar(50) DEFAULT NULL,
  `seo_description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qc_categories`
--

INSERT INTO `qc_categories` (`id`, `parent_id`, `name`, `slug`, `icon_class`, `seo_description`) VALUES
(1, NULL, 'SZÁMÍTÁSTECHNIKA & HARDVER', 'szamitastechnika-hardver', 'fa-solid fa-microchip', NULL),
(2, NULL, 'SZOFTVER, FEJLESZTÉS & BIZTONSÁG', 'szoftver-fejlesztes-biztonsag', 'fa-solid fa-code', NULL),
(3, NULL, 'MOBILITÁS & NAVIGÁCIÓ', 'mobilitas-navigacio', 'fa-solid fa-mobile-screen', NULL),
(4, NULL, 'SZÓRAKOZTATÓ ELEKTRONIKA & FOTÓ', 'szorakoztato-elektronika-foto', 'fa-solid fa-tv', NULL),
(5, NULL, 'IRODA & PERIFÉRIÁK', 'iroda-periferiak', 'fa-solid fa-print', NULL),
(6, NULL, 'HÁLÓZAT & INTERNET', 'halozat-internet', 'fa-solid fa-network-wired', NULL),
(7, NULL, 'HÁZTARTÁS & OTTHON', 'haztartas-otthon', 'fa-solid fa-house', NULL),
(8, NULL, 'KÖZÖSSÉG & KIKAPCSOLÓDÁS', 'kozosseg-kikapcsolodas', 'fa-solid fa-comments', NULL),
(21, 1, 'Processzorok (CPU)', 'processzorok-cpu', 'fa-solid fa-microchip', 'Intel, AMD, hűtés.'),
(22, 1, 'Videókártyák (GPU)', 'videokartyak-gpu', 'fa-solid fa-memory', 'NVIDIA, AMD, Intel, tuning.'),
(23, 1, 'Alaplapok & Memória', 'alaplapok-memoria', 'fa-solid fa-chess-board', 'BIOS, chipsetek, DDR4/DDR5.'),
(24, 1, 'Háttértár (SSD & HDD)', 'hattertar-ssd-hdd', 'fa-solid fa-hard-drive', 'Adatmentés, NVMe, SATA.'),
(25, 1, 'Számítógépházak & Tápegységek', 'szamitogephazak-tapegysegek', 'fa-solid fa-plug', 'Modding, kábelezés, teljesítmény.'),
(26, 2, 'Operációs Rendszerek', 'operacios-rendszerek', 'fab fa-windows', 'Windows, Linux, macOS telepítés és hibák.'),
(27, 2, 'Programozás & Fejlesztés', 'programozas-fejlesztes', 'fa-solid fa-code', 'Webfejlesztés, Python, C++, Java, Scriptek.'),
(28, 2, 'IT Biztonság & Vírusvédelem', 'it-biztonsag-virusvedelem', 'fa-solid fa-shield-halved', 'Tűzfalak, vírusirtók, adatvédelem, zsarolóvírusok.'),
(29, 2, 'Felhasználói Programok', 'felhasznaloi-programok', 'fa-solid fa-layer-group', 'Office, Adobe, segédprogramok, böngészők.'),
(30, 3, 'Okostelefonok', 'okostelefonok', 'fa-solid fa-mobile', 'Android, iOS, rootolás, kiegészítők.'),
(31, 3, 'Tabletek & E-book olvasók', 'tabletek-e-book-olvasok', 'fa-solid fa-tablet-screen-button', 'iPad, Android tab, Kindle.'),
(32, 3, 'Okosórák & Viselhető eszközök', 'okosorak-viselheto-eszkozok', 'fa-solid fa-stopwatch', 'Apple Watch, Garmin, fitness karkötők.'),
(33, 3, 'GPS & Navigáció', 'gps-navigacio', 'fa-solid fa-location-dot', 'Autós navigáció, térképfrissítések, túra GPS.'),
(34, 4, 'Televíziók & Házimozi', 'televiziok-hazimozi', 'fa-solid fa-tv', 'OLED, QLED, Smart TV, projektorok, hangrendszerek.'),
(35, 4, 'Konzolok & Játékgépek', 'konzolok-jatekgepek', 'fa-solid fa-gamepad', 'PlayStation, Xbox, Nintendo, kézikonzolok.'),
(36, 4, 'Fényképezőgépek & Videokamerák', 'fenykepezogepek-videokamerak', 'fa-solid fa-camera', 'DSLR, MILC, objektívek, drónok.'),
(37, 4, 'Audio & Hi-Fi', 'audio-hi-fi', 'fa-solid fa-headphones', 'Fejhallgatók, hangfalak, erősítők.'),
(38, 5, 'Monitorok & Kijelzők', 'monitorok-kijelzok', 'fa-solid fa-desktop', 'IPS, OLED, kalibrálás.'),
(39, 5, 'Billentyűzetek & Egerek', 'billentyuzetek-egerek', 'fa-solid fa-keyboard', 'Mechanikus, gamer, ergonómia.'),
(40, 5, 'Nyomtatók & Irodatechnika', 'nyomtatok-irodatechnika', 'fa-solid fa-print', 'Lézer, tintasugaras, szkennerek, fénymásolók.'),
(41, 6, 'Hálózati Eszközök', 'halozati-eszkozok', 'fa-solid fa-wifi', 'Routerek, Switch-ek, WiFi, Mesh rendszerek.'),
(42, 6, 'Internet Szolgáltatók', 'internet-szolgaltatok', 'fa-solid fa-globe', 'Digi, Telekom, Vodafone, optikai hálózatok.'),
(43, 6, 'Szerverek & NAS', 'szerverek-nas', 'fa-solid fa-server', 'Otthoni szerver, felhő tárhely, Docker.'),
(44, 7, 'Nagy Háztartási Gépek', 'nagy-haztartasi-gepek', 'fa-solid fa-plug', 'Mosógépek, hűtőszekrények, mosogatógépek.'),
(45, 7, 'Kis Háztartási Gépek', 'kis-haztartasi-gepek', 'fa-solid fa-mug-hot', 'Kávéfőzők, porszívók, konyhai eszközök.'),
(46, 7, 'Okosotthon (Smart Home)', 'okosotthon-smart-home', 'fa-solid fa-lightbulb', 'Okosvilágítás, automatizálás, biztonsági kamerák.'),
(47, 7, 'Klíma & Légtechnika', 'klima-legtechnika', 'fa-solid fa-wind', 'Légkondicionálók, párátlanítók, fűtés.'),
(48, 8, 'Csevegő & Off-topic', 'csevego-off-topic', 'fa-solid fa-coffee', 'Kötetlen beszélgetés bármiről.'),
(49, 8, 'Ismerkedés & Találkozók', 'ismerkedes-talalkozok', 'fa-solid fa-users', 'Társkeresés, közösségi programok.'),
(50, 8, 'Vélemények & Javaslatok', 'velemenyek-javaslatok', 'fa-solid fa-circle-question', 'Fórummal kapcsolatos ötletek.');

-- --------------------------------------------------------

--
-- Table structure for table `qc_login_history`
--

CREATE TABLE `qc_login_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `event` enum('login','login_failed','logout','password_change') NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qc_notifications`
--

CREATE TABLE `qc_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qc_pm_messages`
--

CREATE TABLE `qc_pm_messages` (
  `id` int(11) NOT NULL,
  `thread_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qc_pm_threads`
--

CREATE TABLE `qc_pm_threads` (
  `id` int(11) NOT NULL,
  `user_one` int(11) NOT NULL,
  `user_two` int(11) NOT NULL,
  `last_updated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qc_polls`
--

CREATE TABLE `qc_polls` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `question_text` varchar(255) NOT NULL,
  `is_closed` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qc_polls`
--

INSERT INTO `qc_polls` (`id`, `question_id`, `question_text`, `is_closed`, `created_at`) VALUES
(1, 5, 'Szerinted melyik lesz?', 0, '2026-02-16 09:40:15'),
(2, 6, 'erertgretre', 0, '2026-02-16 11:10:19');

-- --------------------------------------------------------

--
-- Table structure for table `qc_poll_options`
--

CREATE TABLE `qc_poll_options` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `vote_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qc_poll_options`
--

INSERT INTO `qc_poll_options` (`id`, `poll_id`, `option_text`, `vote_count`) VALUES
(1, 1, 'Igen, ez lesz', 2),
(2, 1, 'Nem, a másik lesz ...', 0),
(3, 1, 'Vagy talán a harmadik lesz?', 1),
(4, 2, 'retretr', 1),
(5, 2, 'ertert', 0),
(6, 2, 'ertretret', 0);

-- --------------------------------------------------------

--
-- Table structure for table `qc_poll_votes`
--

CREATE TABLE `qc_poll_votes` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qc_poll_votes`
--

INSERT INTO `qc_poll_votes` (`id`, `poll_id`, `option_id`, `user_id`, `created_at`) VALUES
(1, 1, 1, 999, '2026-02-16 09:40:22'),
(2, 1, 1, 2, '2026-02-16 09:53:57'),
(3, 1, 3, 3, '2026-02-16 09:54:36'),
(4, 2, 4, 999, '2026-02-16 11:10:33');

-- --------------------------------------------------------

--
-- Table structure for table `qc_questions`
--

CREATE TABLE `qc_questions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` enum('open','solved','closed') DEFAULT 'open',
  `vote_score` int(11) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `is_guest` tinyint(1) DEFAULT 0,
  `guest_name` varchar(100) DEFAULT NULL,
  `seo_description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qc_questions`
--

INSERT INTO `qc_questions` (`id`, `user_id`, `category_id`, `title`, `slug`, `content`, `status`, `vote_score`, `view_count`, `is_guest`, `guest_name`, `seo_description`, `created_at`) VALUES
(1, 2, 22, 'Milyen videókártyát vegyek 1440p gamingre?', 'milyen-videokartyat-vegyek-1440p-gamingre', 'Sziasztok! Építek egy új konfigot és elakadtam a videókártya választásnál. A célom a stabil 1440p felbontás és magas FPS.', 'solved', 0, 69, 0, NULL, 'Videókártya választás 1440p felbontáshoz.', '2026-02-16 04:03:00'),
(2, 3, 23, 'Hogyan lehet RAM-ot bővíteni laptopban?', 'hogyan-lehet-ram-ot-boviteni-laptopban', 'Segítséget szeretnék kérni. Van egy Lenovo Legion laptopom, és szeretnék bele még 8GB RAM-ot.', 'open', 0, 36, 0, NULL, 'RAM bővítés laptopban.', '2026-02-16 04:03:00'),
(3, 999, 26, 'Windows 11 telepítési hiba \"TPM 2.0\" hiány miatt', 'windows-11-telepitesi-hiba-tpm-2-0', 'Sziasztok! Próbálom telepíteni a Windows 11-et, de a telepítő megáll a TPM hiba miatt.', 'open', 0, 3, 0, NULL, 'Windows 11 TPM 2.0 hiba elhárítása.', '2026-02-16 04:03:00'),
(4, 999, 21, 'Ez itt a teszt és írunk egy jó nagy cikk címet mert ez lesz a képest éééééééééééééééééééééééééééééééééééééééééééé', 'ez-itt-a-teszt-es-irunk-egy-jo-nagy-cikk-cimet-mert-ez-lesz-a-kepest-eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee-c4ad8', 'Haló sziasztok!<br><br>Kellene egy kis segítség.<br><br>Csatoltam képeket.', 'open', 0, 97, 0, NULL, '', '2026-02-16 04:09:11'),
(5, 999, 22, 'rtze54z5z', 'rtze54z5z-65856', 'itt lesz szavazás ..<br><br>oké?', 'closed', 0, 24, 0, NULL, '', '2026-02-16 09:40:15'),
(6, 999, 21, 'erertgretre', 'erertgretre-617ba', 'retreterter', 'solved', 0, 31, 0, NULL, 'tretretret', '2026-02-16 11:10:19'),
(7, 999, 21, 'Legyünk kreatívak ...', 'legyunk-kreativak-a1878', 'oké<div>most</div><div>ide</div><div>fogok</div><div>írni</div><div>aha</div><div>oké</div>', 'solved', 0, 75, 0, NULL, 'röviden ...', '2026-02-16 12:04:06'),
(8, 999, 42, 'A Facebook hírfolyamon talált történetet később vissza tudom keresni valahol?', 'a-facebook-hirfolyamon-talalt-tortenetet-kesobb-vissza-tudom-keresni-valahol-2f0be', '<div>Tegnap olvastam egy érdekes történetet (olyat aminek a folytatása sok reklám után látható), szerettem volna elmenteni, vagy kimásolni, mert nagyon tetszett, ill. megosztani, de eltűnt. Sajnos nem tudom melyik csoportban volt.</div>', 'open', 0, 26, 0, NULL, '', '2026-02-16 21:59:05'),
(9, 999, 26, 'Melyik a legjobb proci 2026-ba?', 'melyik-a-legjobb-proci-2026-ba-63e3f', 'Te szerinted melyiket vegyem?', 'open', 0, 5, 0, NULL, '', '2026-02-16 22:05:06'),
(10, 999, 21, 'Szerintetek a történelem során melyek voltak a legrosszabb processzorok?', 'szerintetek-a-tortenelem-soran-melyek-voltak-a-legrosszabb-processzorok-7362e', 'Jöhet bármelyik ...  ', 'open', 0, 4, 0, NULL, '', '2026-02-16 22:10:01'),
(12, 999, 42, 'Most hogy lépjek be a TikTok profilomba?', 'most-hogy-lepjek-be-a-tiktok-profilomba-b6630', '<div>Jelszó jó a felhasználónévhez, de azt akarja, hogy igazoljam magam e-mailről. Ahhoz e-mail fiókhoz már nem férek hozzá és nem tudom a jelszót. Mit tegyek?</div><div><br></div>', 'open', 0, 5, 0, NULL, '', '2026-02-16 22:29:10'),
(13, 999, 26, 'Képmegjelenítő szoftver helyett miért a böngésző nyitja meg a képet?', 'kepmegjelenito-szoftver-helyett-miert-a-bongeszo-nyitja-meg-a-kepet-80a5e', '<div>Microsoft Office 2007-ben táblázatba beszúrt kép alá hiperhivatkozástban az adott képet (ami albumban van) kellene megjelenítenie egy képszerkesztő (Fastone Viewer) szoftvernek.</div><div><br></div><div><br></div><div>Eddig semmi baj nem volt, működött hosszú időn keresztül, majd egyik napról a másikra a Chrome böngésző kezdte megnyitni a képet, és így az album sem jelenik meg, csak az adott kép.</div><div><br></div><div><br></div><div>Kipróbáltam Word-ben is, ugyanez a helyzet. Ha Intézőben, vagy Total Commanderben nyitom meg a képet, akkor a képszerkesztő nyitja meg, ahogy kell is.</div><div><br></div><div><br></div><div>A gépházban a Jpg, és jpeg formátumra (illetve az összes többire is) a képszerkesztő van alapértelmezettként beállítva, ellenőriztem akkor is, mikor a probléma előjött. A Chrome-nál nincs se a JPg, se a JPEG beállítva.</div><div><br></div><div><br></div><div>Mit csináljak, hogy ismét a képszerkesztő jelenítse meg, és nem a Chrome?</div><div><br></div><div><br></div><div>Újraraktam a képszerkesztőt, az Office-t, a Chrome-ot is, de nincs változás.</div><div><br></div><div><br></div><div>Operációs rendszer Win 11, újonnan telepítve (nem frissítve), hivatalosan lassan 1 éve.</div><div><br></div><div><br></div><div>Nem akarok másik Office-t telepíteni.</div>', 'open', 0, 13, 0, NULL, '', '2026-02-16 22:32:37'),
(14, 999, 21, 'Melyek voltak a történelem legrosszabb processzorai?', 'melyek-voltak-a-tortenelem-legrosszabb-processzorai-9af54', 'Jöhet hideg és meleg... :)', 'open', 0, 17, 0, NULL, '', '2026-02-16 22:35:47'),
(15, 999, 26, 'Parancssorba beírtam a chkdsk C: /f /r parancsot ami elvileg kijavít C meghajtó hibákat de \" acess denied\" mi a gond?', 'parancssorba-beirtam-a-chkdsk-c-f-r-parancsot-ami-elvileg-kijavit-c-meghajto-hibakat-de-acess-denied-mi-a-gond-b63f3', '<div>biztonsági mentést akarnék csinálni de kiírta hogy hibás C meghajtó, újraindítottam ahogy kérte de nem javított ki semmit (még 2x megismételtem továbbra is fennáll a hiba)</div><div><br></div><div>a parancssorba beírtam ezt az állítólagos kódot \"chkdsk C: /f /r\", de nem enged tovább</div><div><br></div><div>rosszul adtam meg a parancsot? rendszergazdák nevethetnek és segíthetnének please</div><div><br></div><div>(1 éve van saját gépem, apa engedte és egy részét én fizettem diákmunkával. de nem vagyok pc zseni. 😥 Gigabyte kártya, és amd processzor)</div><div><br></div>', 'open', 0, 9, 0, NULL, '', '2026-02-16 23:13:25'),
(16, 999, 31, 'Hogyan kell azt elérni, hogy az iPadem b..szakodas nélkül csak úgy, kérdezés nélkül végezze el a biztonsági mentést akkor is, ha nincs a wifire kapcsolódva?', 'hogyan-kell-azt-elerni-hogy-az-ipadem-b-szakodas-nelkul-csak-ugy-kerdezes-nelkul-vegezze-el-a-biztonsagi-mentest-akkor-is-ha-nincs-a-wifire-kapcsolodva-caddc', '<div>Jelenleg ui. időnként kiírja, hogy x ideje nem történt “biztonsági mentés”. Az évek folyamán nagy nehezen kitapasztaltam, hogy azért nem történt, mert a kis hülye arra várt, hogy kapcsolódjon valamilyen wifihez. De én nem szoktam használni wifit, mert korlátlan mobil internet szolgáltatásom van. Ígh aztán szeretném megtudni, hogyan kell átállítani a gépemet úgy, hogy ne a wifi legyen alapértelmezett ezügyben, hanem csak nyugodtan mentegessen a mobil internet terhére is, engem kíméljen meg a részletektől.</div><div><br></div>', 'open', 0, 12, 0, NULL, '', '2026-02-16 23:18:37'),
(17, 999, 26, 'Képmegjelenítő szoftver helyett miért a böngésző nyitja meg a képet?', 'kepmegjelenito-szoftver-helyett-miert-a-bongeszo-nyitja-meg-a-kepet-4899f', 'Na, miért ?', 'open', 0, 4, 0, NULL, '', '2026-02-17 08:01:39'),
(18, 999, 21, 'teszt', 'teszt-315df', 'teszt', 'open', 0, 3, 0, NULL, '', '2026-02-17 08:16:07'),
(19, 999, 28, 'Van olyan antivirus vagy egyébb program amely észreveszi ha a windowson hiányoznak biztonsági frissitések?', 'van-olyan-antivirus-vagy-egyebb-program-amely-eszreveszi-ha-a-windowson-hianyoznak-biztonsagi-frissitesek-781e7', '<div>Sajnos a Windows automatikus frissités nem működik.</div>', 'open', 0, 2, 0, NULL, 'A Nessus Home vagy a Qualys Free Scan ingyenesen ellenőrzi a hiányzó frissítéseket. Nem vírusírtó, de biztonsági rést mutat.', '2026-02-17 09:35:24'),
(20, 999, 50, 'Avast mellé kell más biztonsági cucc?', 'avast-melle-kell-mas-biztonsagi-cucc-9d39a', '<div>Ajánlotok valamit?</div>', 'open', 0, 4, 0, NULL, '', '2026-02-17 09:45:49'),
(21, 999, 23, 'teszt kérdés', 'teszt-kerdes-a1cce', 'sziasztok!', 'open', 0, 10, 0, NULL, 'Tesztkörnyezet működésének ellenőrzése szükséges.', '2026-02-17 23:53:26');

-- --------------------------------------------------------

--
-- Table structure for table `qc_question_images`
--

CREATE TABLE `qc_question_images` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qc_question_images`
--

INSERT INTO `qc_question_images` (`id`, `question_id`, `image_path`, `original_name`, `created_at`) VALUES
(1, 4, 'uploads/images/2026/02/16/img_69928a19916146.23969908.webp', NULL, '2026-02-16 04:09:11'),
(2, 4, 'uploads/images/2026/02/16/img_69928a198ecf60.78383624.webp', NULL, '2026-02-16 04:09:11'),
(3, 4, 'uploads/images/2026/02/16/img_69928a19960343.64304995.webp', NULL, '2026-02-16 04:09:11');

-- --------------------------------------------------------

--
-- Table structure for table `qc_reply_votes`
--

CREATE TABLE `qc_reply_votes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reply_id` int(11) NOT NULL,
  `vote_type` enum('like','dislike') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qc_reply_votes`
--

INSERT INTO `qc_reply_votes` (`id`, `user_id`, `reply_id`, `vote_type`, `created_at`) VALUES
(11, 999, 2, 'dislike', '2026-02-16 19:13:03'),
(21, 999, 4, 'like', '2026-02-16 19:33:44'),
(25, 999, 12, 'like', '2026-02-17 22:17:22'),
(26, 999, 13, 'like', '2026-02-18 10:43:54');

-- --------------------------------------------------------

--
-- Table structure for table `qc_reports`
--

CREATE TABLE `qc_reports` (
  `id` int(11) NOT NULL,
  `target_type` enum('question','answer','pm') NOT NULL,
  `target_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `reason_category` varchar(50) NOT NULL,
  `reason_details` text DEFAULT NULL,
  `status` enum('pending','resolved') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qc_settings`
--

CREATE TABLE `qc_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qc_settings`
--

INSERT INTO `qc_settings` (`setting_key`, `setting_value`) VALUES
('use_pretty_urls', '0');

-- --------------------------------------------------------

--
-- Table structure for table `qc_users`
--

CREATE TABLE `qc_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `reputation_points` int(11) DEFAULT 0,
  `custom_title` varchar(100) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `email` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `social_links` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `status` enum('active','suspended','banned','deleted') DEFAULT 'active',
  `deleted_at` datetime DEFAULT NULL,
  `role` enum('user','moderator','admin') DEFAULT 'user',
  `questions_count` int(11) DEFAULT 0,
  `answers_count` int(11) DEFAULT 0,
  `accepted_answers_count` int(11) DEFAULT 0,
  `comments_count` int(11) DEFAULT 0,
  `reactions_count` int(11) DEFAULT 0,
  `rank_title` varchar(100) DEFAULT 'Newbie',
  `cover_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qc_users`
--

INSERT INTO `qc_users` (`user_id`, `username`, `reputation_points`, `custom_title`, `is_admin`, `created_at`, `email`, `full_name`, `password_hash`, `avatar`, `bio`, `website`, `social_links`, `location`, `last_login`, `last_login_ip`, `status`, `deleted_at`, `role`, `questions_count`, `answers_count`, `accepted_answers_count`, `comments_count`, `reactions_count`, `rank_title`, `cover_image`) VALUES
(1, 'Admin', 1000, 'Adminisztrátor', 1, '2026-02-16 04:03:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, 'admin', 0, 0, 0, 0, 0, 'Newbie', NULL),
(2, 'TechGuru', 502, 'Hardver Szakértő', 0, '2026-02-16 04:03:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, 'user', 1, 2, 1, 1, -1, 'Aktív Tag', NULL),
(3, 'Gamer99', 149, 'Haladó Tag', 0, '2026-02-16 04:03:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'active', NULL, 'user', 1, 1, 0, 0, 2, 'Newbie', NULL),
(999, 'TestUser', 50, 'Kezdő Tag', 0, '2026-02-16 04:03:00', NULL, 'Nem Titkos', NULL, 'uploads/avatars/4/999_61945_150x150.webp?v=1771529816', 'egy kis mondat ... :)', '', '[]', '', NULL, NULL, 'active', NULL, 'user', 18, 26, 2, 16, 23, 'Újonc', 'uploads/covers/4/999.webp?v=1771526094');

-- --------------------------------------------------------

--
-- Table structure for table `qc_user_badges`
--

CREATE TABLE `qc_user_badges` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `awarded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qc_user_badges`
--

INSERT INTO `qc_user_badges` (`id`, `user_id`, `badge_id`, `awarded_at`) VALUES
(1, 999, 5, '2026-02-18 21:28:01'),
(2, 999, 6, '2026-02-18 21:28:01'),
(3, 999, 7, '2026-02-18 21:28:01'),
(4, 999, 8, '2026-02-18 21:28:01'),
(5, 2, 7, '2026-02-19 07:39:10');

-- --------------------------------------------------------

--
-- Table structure for table `qc_votes`
--

CREATE TABLE `qc_votes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `target_type` enum('question','answer') NOT NULL,
  `target_id` int(11) NOT NULL,
  `vote_value` tinyint(1) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qc_votes`
--

INSERT INTO `qc_votes` (`id`, `user_id`, `target_type`, `target_id`, `vote_value`, `created_at`) VALUES
(7, 999, 'answer', 32, 1, '2026-02-18 10:44:59'),
(11, 999, 'answer', 2, -1, '2026-02-18 11:37:06'),
(13, 999, 'answer', 1, 1, '2026-02-18 16:34:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `qc_answers`
--
ALTER TABLE `qc_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `qc_answer_edits`
--
ALTER TABLE `qc_answer_edits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `answer_id` (`answer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `qc_answer_images`
--
ALTER TABLE `qc_answer_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `answer_id` (`answer_id`);

--
-- Indexes for table `qc_answer_replies`
--
ALTER TABLE `qc_answer_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `answer_id` (`answer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `qc_badges`
--
ALTER TABLE `qc_badges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `qc_bookmarks`
--
ALTER TABLE `qc_bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_question` (`user_id`,`question_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_question_id` (`question_id`);

--
-- Indexes for table `qc_categories`
--
ALTER TABLE `qc_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `qc_login_history`
--
ALTER TABLE `qc_login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ip_address` (`ip_address`);

--
-- Indexes for table `qc_notifications`
--
ALTER TABLE `qc_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `qc_pm_messages`
--
ALTER TABLE `qc_pm_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thread_id` (`thread_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `qc_pm_threads`
--
ALTER TABLE `qc_pm_threads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_one` (`user_one`),
  ADD KEY `user_two` (`user_two`);

--
-- Indexes for table `qc_polls`
--
ALTER TABLE `qc_polls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `qc_poll_options`
--
ALTER TABLE `qc_poll_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poll_id` (`poll_id`);

--
-- Indexes for table `qc_poll_votes`
--
ALTER TABLE `qc_poll_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_poll_vote` (`poll_id`,`user_id`),
  ADD KEY `option_id` (`option_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `qc_questions`
--
ALTER TABLE `qc_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `qc_question_images`
--
ALTER TABLE `qc_question_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `qc_reply_votes`
--
ALTER TABLE `qc_reply_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_reply_vote` (`user_id`,`reply_id`),
  ADD KEY `reply_id` (`reply_id`);

--
-- Indexes for table `qc_reports`
--
ALTER TABLE `qc_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reporter_id` (`reporter_id`);

--
-- Indexes for table `qc_settings`
--
ALTER TABLE `qc_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `qc_users`
--
ALTER TABLE `qc_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `qc_user_badges`
--
ALTER TABLE `qc_user_badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_badge` (`user_id`,`badge_id`),
  ADD KEY `badge_id` (`badge_id`);

--
-- Indexes for table `qc_votes`
--
ALTER TABLE `qc_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vote` (`user_id`,`target_type`,`target_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `qc_answers`
--
ALTER TABLE `qc_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `qc_answer_edits`
--
ALTER TABLE `qc_answer_edits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `qc_answer_images`
--
ALTER TABLE `qc_answer_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `qc_answer_replies`
--
ALTER TABLE `qc_answer_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `qc_badges`
--
ALTER TABLE `qc_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `qc_bookmarks`
--
ALTER TABLE `qc_bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `qc_categories`
--
ALTER TABLE `qc_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `qc_login_history`
--
ALTER TABLE `qc_login_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qc_notifications`
--
ALTER TABLE `qc_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qc_pm_messages`
--
ALTER TABLE `qc_pm_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qc_pm_threads`
--
ALTER TABLE `qc_pm_threads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qc_polls`
--
ALTER TABLE `qc_polls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `qc_poll_options`
--
ALTER TABLE `qc_poll_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `qc_poll_votes`
--
ALTER TABLE `qc_poll_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `qc_questions`
--
ALTER TABLE `qc_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `qc_question_images`
--
ALTER TABLE `qc_question_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `qc_reply_votes`
--
ALTER TABLE `qc_reply_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `qc_reports`
--
ALTER TABLE `qc_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qc_user_badges`
--
ALTER TABLE `qc_user_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `qc_votes`
--
ALTER TABLE `qc_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `qc_answers`
--
ALTER TABLE `qc_answers`
  ADD CONSTRAINT `qc_answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `qc_questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qc_answers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `qc_users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `qc_answer_edits`
--
ALTER TABLE `qc_answer_edits`
  ADD CONSTRAINT `qc_answer_edits_ibfk_1` FOREIGN KEY (`answer_id`) REFERENCES `qc_answers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qc_answer_edits_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `qc_users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `qc_answer_images`
--
ALTER TABLE `qc_answer_images`
  ADD CONSTRAINT `qc_answer_images_ibfk_1` FOREIGN KEY (`answer_id`) REFERENCES `qc_answers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_answer_replies`
--
ALTER TABLE `qc_answer_replies`
  ADD CONSTRAINT `qc_answer_replies_ibfk_1` FOREIGN KEY (`answer_id`) REFERENCES `qc_answers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qc_answer_replies_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `qc_users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `qc_categories`
--
ALTER TABLE `qc_categories`
  ADD CONSTRAINT `qc_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `qc_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_login_history`
--
ALTER TABLE `qc_login_history`
  ADD CONSTRAINT `qc_login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `qc_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_notifications`
--
ALTER TABLE `qc_notifications`
  ADD CONSTRAINT `qc_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `qc_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_pm_messages`
--
ALTER TABLE `qc_pm_messages`
  ADD CONSTRAINT `qc_pm_messages_ibfk_1` FOREIGN KEY (`thread_id`) REFERENCES `qc_pm_threads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qc_pm_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `qc_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_pm_threads`
--
ALTER TABLE `qc_pm_threads`
  ADD CONSTRAINT `qc_pm_threads_ibfk_1` FOREIGN KEY (`user_one`) REFERENCES `qc_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qc_pm_threads_ibfk_2` FOREIGN KEY (`user_two`) REFERENCES `qc_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_polls`
--
ALTER TABLE `qc_polls`
  ADD CONSTRAINT `qc_polls_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `qc_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_poll_options`
--
ALTER TABLE `qc_poll_options`
  ADD CONSTRAINT `qc_poll_options_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `qc_polls` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_poll_votes`
--
ALTER TABLE `qc_poll_votes`
  ADD CONSTRAINT `qc_poll_votes_ibfk_1` FOREIGN KEY (`poll_id`) REFERENCES `qc_polls` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qc_poll_votes_ibfk_2` FOREIGN KEY (`option_id`) REFERENCES `qc_poll_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qc_poll_votes_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `qc_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_questions`
--
ALTER TABLE `qc_questions`
  ADD CONSTRAINT `qc_questions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `qc_categories` (`id`),
  ADD CONSTRAINT `qc_questions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `qc_users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `qc_question_images`
--
ALTER TABLE `qc_question_images`
  ADD CONSTRAINT `qc_question_images_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `qc_questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_reply_votes`
--
ALTER TABLE `qc_reply_votes`
  ADD CONSTRAINT `qc_reply_votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `qc_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qc_reply_votes_ibfk_2` FOREIGN KEY (`reply_id`) REFERENCES `qc_answer_replies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_reports`
--
ALTER TABLE `qc_reports`
  ADD CONSTRAINT `qc_reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `qc_users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_user_badges`
--
ALTER TABLE `qc_user_badges`
  ADD CONSTRAINT `qc_user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `qc_users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `qc_user_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `qc_badges` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `qc_votes`
--
ALTER TABLE `qc_votes`
  ADD CONSTRAINT `qc_votes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `qc_users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
