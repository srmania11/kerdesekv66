# Felhasználói Profil Design Dokumentáció (`profile_design.md`)

Ez a dokumentum részletesen leírja a `profile_design.php` fájlban található új profil oldal felépítését, az egyes elemek funkcióját, és útmutatást ad a dinamikus adatok (PHP) beillesztéséhez.

## Áttekintés

A profil oldal modern, reszponzív (mobilbarát) elrendezést használ, amely a **Tailwind CSS** keretrendszerre épül. A design sötét témát (dark mode) követ, neon és üveg (glassmorphism) effektekkel, hogy illeszkedjen a "Gamer / Tech" stílushoz.

A teljes tartalom a `.cikk-fo-col-left` konténerben helyezkedik el, így a jobb oldali sáv (widgetek) érintetlen marad.

---

## 1. Fejléc (Header Section)

Ez a legfelső, látványos sáv, amely a felhasználó legfontosabb adatait tartalmazza.

**Helye a kódban:**
Keresd a `<!-- Header Section -->` kommentet.

### Elemek és Adatforrások:

*   **Borítókép (Banner):**
    *   **HTML:** `<div class="h-32 md:h-48 bg-gradient-to-r ...">`
    *   **Mit írj ide:** Jelenleg egy CSS gradiens és egy textúra van beállítva. Ha a felhasználónak van egyedi borítóképe, a `style="background-image: url('...')" ` attribútumot kell dinamikusan beállítani.
    *   **Online Státusz:** A jobb alsó sarokban lévő "ONLINE" felirat.
        *   **Logika:** Ha a user online (pl. `last_login` < 5 perc), akkor jelenítsd meg ezt a `<span>`-t. Ha offline, elrejtheted vagy átírhatod "OFFLINE"-ra (szürke színnel).

*   **Profilkép (Avatar):**
    *   **HTML:** `<img src="..." alt="Avatar" class="...">`
    *   **Adat:** A `src` attribútumba kell beilleszteni a felhasználó avatarjának URL-jét (pl. `$user['avatar']`). Ha nincs, használhatsz egy alapértelmezett képet (mint most a UI Avatars).
    *   **Rang Szint (Level):** A profilkép jobb alsó sarkában lévő szám (pl. "55").
        *   **Adat:** Ez lehet a felhasználó szintje vagy a reputációból számolt érték.

*   **Felhasználónév és Rang:**
    *   **HTML:** `<h1>CyberUser_99</h1>` és `<span class="...">Admin</span>`
    *   **Adat:** `$user['username']` és `$user['role']` (vagy `$user['rank_title']`).
    *   **Stílus:** A rang címkéjének színét (pl. `bg-indigo-500`) a rang típusától függően változtathatod (pl. Admin = piros, Moderátor = zöld).

*   **Alcím (Bio rövid):**
    *   **HTML:** `<p class="text-slate-400 ...">Full Stack Developer...</p>`
    *   **Adat:** Ez egy rövid leírás, amit a `$user['bio']` mezőből vehetsz, vagy ha az hosszú, akkor csak az első mondatát.

*   **Gombok (Követés, Üzenet):**
    *   **Funkció:** Ezek a gombok interakciókhoz kellenek. A "Követés" gombhoz kell majd egy AJAX hívás vagy űrlap, az "Üzenet" gomb pedig a privát üzenet küldő oldalra (`send_private_message.php`) irányítson.

---

## 2. Jelvények (Badges)

A fejléc alján található kis ikonok sora.

**Helye a kódban:**
Keresd a `<!-- Gamification Badges Row -->` kommentet.

*   **Működés:** Ez egy `flex` konténer.
*   **Adat:** Itt egy `foreach` ciklussal kell végigmenni a felhasználó megszerzett jelvényein (`qc_user_badges` tábla).
*   **Elem:** Minden jelvény egy `<div class="badge-item ...">`.
    *   **Szín:** A `bg-amber-500/10 text-amber-400` osztályokat cserélheted a jelvény ritkasága alapján (pl. Arany = amber, Ezüst = slate, Bronz = orange).
    *   **Tooltip:** A `title="..."` attribútumba írd a jelvény nevét vagy leírását, ami akkor jelenik meg, ha ráviszik az egeret.

---

## 3. Statisztikák (Stats Grid)

A 6 dobozból álló rács a számadatokkal.

**Helye a kódban:**
Keresd a `<!-- Stats Grid -->` kommentet.

**Adatmezők:**
Minden dobozban van egy nagy szám és alatta a felirat.
1.  **Reputáció:** `$user['reputation_points']` (vagy hasonló kalkulált érték).
2.  **Kérdés:** `$user['questions_count']`.
3.  **Válasz:** `$user['answers_count']`.
4.  **Elfogadva:** `$user['accepted_answers_count']`.
5.  **Komment:** `$user['comments_count']`.
6.  **Reakció:** `$user['reactions_count']`.

*Tipp:* Használd a `number_format()` függvényt a nagy számok formázására (pl. 12500 -> 12.5k).

---

## 4. Bal Oszlop: Részletek (Details Column)

A kétharmados osztás bal oldala.

### Névjegy (About Card)
*   **Bemutatkozás:**
    *   **HTML:** `<p class="...">...</p>` (idézőjelek között).
    *   **Adat:** `$user['bio']`. Ha üres, írj ki egy alapértelmezett szöveget ("A felhasználó még nem írt bemutatkozást.").
*   **Adatok Lista (`<ul>`):**
    *   **Hely:** `$user['location']`.
    *   **Weboldal:** `$user['website']`. (Linkként: `<a href="...">`).
    *   **Regisztráció:** `$user['created_at']` dátum formázva.
    *   **Utoljára itt:** `$user['last_login']`. Használhatod a `time_elapsed_string()` függvényt ("2 órája").

### Social Linkek
*   **HTML:** Ikonok (`<i class="fab fa-facebook-f"></i>`, stb.).
*   **Logika:** Csak azokat az ikonokat jelenítsd meg, amelyekhez a felhasználó megadott linket a beállításokban.

### Biztonsági Adatok (Security Info)
**FONTOS:** Ezt a dobozt **CSAK** akkor rendereld ki, ha a jelenlegi látogató (aki nézi az oldalt) **ADMINisztrátor** vagy **maga a tulajdonos**!

*   **Helye a kódban:** `<!-- Security Info (Visible only to Admin/Owner) -->`.
*   **Adatok:**
    *   Jelenlegi IP, Utolsó Belépés IP.
    *   Státusz (Aktív/Bannolt).
    *   Jogosultság (User/Admin).
*   **Stílus:** Pirosas (`bg-red-950/20`), hogy figyelmeztesse a nézőt, ezek érzékeny adatok.

---

## 5. Jobb Oszlop: Tartalom (Content Column)

A kétharmados osztás jobb oldala.

### Fülek (Tabs)
*   Jelenleg ezek csak linkek/gombok.
*   **Működés:** Később JavaScripttel vagy PHP `$_GET` paraméterrel (`?tab=questions`) lehet váltogatni a tartalmat.
*   **Jelenlegi tartalom:** "Áttekintés" (Overview).

### Legutóbbi Aktivitás (Recent Activity)
*   Ez egy lista a legutóbbi cselekvésekről.
*   **Adat:** Lekérdezés a `qc_questions`, `qc_answers`, `qc_user_badges` táblákból, időrendben összefésülve (UNION).
*   **Típusok:**
    *   *Válasz:* `<i class="fas fa-reply"></i>` ikon.
    *   *Kérdés:* `<i class="fas fa-question"></i>` ikon.
    *   *Jelvény:* `<i class="fas fa-trophy"></i>` ikon.

### Kiemelt Megoldások (Showcase)
*   Ez opcionális. Itt megjelenítheted a felhasználó legtöbb szavazatot kapott (vagy elfogadott) válaszait.
*   **Adat:** `SELECT * FROM qc_answers WHERE user_id = ? AND is_accepted = 1 ORDER BY vote_score DESC LIMIT 2`.

---

## Stílus Testreszabása (CSS)

A design **Tailwind CSS**-t használ. Néhány fontos osztály:

*   **Háttérszínek:** `bg-slate-800`, `bg-slate-900` (sötét panelek).
*   **Szövegszínek:** `text-white`, `text-slate-400` (szürke), `text-indigo-400` (kiemelés).
*   **Szegélyek:** `border-slate-700` (vékony szürke keretek).
*   **Effektek:**
    *   `backdrop-blur`: Üveghatás.
    *   `shadow-xl`: Árnyékok.
    *   `animate-pulse`: Villogás (pl. az ONLINE pötty).

Ha színeket akarsz cserélni, keress rá a `indigo`, `purple`, `cyan` szavakra a kódban, és cseréld őket más Tailwind színre (pl. `red`, `green`, `blue`).

---

## Beillesztés a PHP Rendszerbe

1.  Nyisd meg a `profile_design.php` fájlt.
2.  A fájl elején lévő PHP blokkba (`$currentUser = ...`) írd meg a valós adatbázis lekérdezéseket a `$pdo` objektummal.
3.  Töltsd fel a változókat a lekérdezett adatokkal.
4.  A HTML részben cseréld le a statikus szövegeket (pl. "12.5k") a változókra (pl. `<?php echo $stats['reputation']; ?>`).
5.  Ne felejtsd el a `htmlspecialchars()` használatát minden felhasználó által megadott szövegnél a biztonság érdekében!
