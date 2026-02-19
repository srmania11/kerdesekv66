# Felhasználói Profil Adatbázis Dokumentáció (USER_PROFILE_DB.md)

Ez a dokumentum részletesen leírja a `qc_users` tábla bővítését és a kapcsolódó új táblákat, amelyek a teljes felhasználói rendszer alapját képezik.

## 1. Áttekintés

A rendszer célja, hogy teljes körű felhasználókezelést biztosítson, beleértve a regisztrációt, profilt, aktivitás követést, és biztonsági funkciókat, miközben megőrzi a kompatibilitást a meglévő rendszerrel.

## 2. Adatbázis Struktúra

### `qc_users` Tábla (Bővített)

A felhasználók alapvető adatait tárolja.

| Oszlop | Típus | Leírás |
| :--- | :--- | :--- |
| `user_id` | INT (PK) | Egyedi azonosító (meglévő). |
| `username` | VARCHAR(100) | Felhasználónév (meglévő). |
| `email` | VARCHAR(255) | E-mail cím (EGYEDI). |
| `full_name` | VARCHAR(255) | Teljes név. |
| `password_hash` | VARCHAR(255) | Jelszó hash (Argon2 vagy Bcrypt ajánlott). |
| `avatar` | VARCHAR(255) | Profilkép URL vagy fájl útvonal. |
| `bio` | TEXT | Bemutatkozás. |
| `website` | VARCHAR(255) | Weboldal link. |
| `social_links` | TEXT (JSON) | Social media linkek JSON formátumban. |
| `location` | VARCHAR(100) | Tartózkodási hely. |
| `created_at` | DATETIME | Regisztráció ideje (meglévő). |
| `last_login` | DATETIME | Utolsó sikeres bejelentkezés ideje. |
| `last_login_ip` | VARCHAR(45) | Utolsó bejelentkezési IP cím (IPv6 kompatibilis). |
| `status` | ENUM | Fiók státusza: `active`, `suspended`, `banned`, `deleted`. |
| `deleted_at` | DATETIME | Soft delete esetén a törlés ideje. |
| `role` | ENUM | Szerepkör: `user`, `moderator`, `admin`. |
| `reputation_points` | INT | Reputációs pontszám (meglévő). |
| `rank_title` | VARCHAR(100) | Rang megnevezése (pl. "Newbie"). |
| `questions_count` | INT | Feltett kérdések száma (gyorsítótárazott). |
| `answers_count` | INT | Válaszok száma. |
| `accepted_answers_count` | INT | Elfogadott válaszok száma. |
| `comments_count` | INT | Hozzászólások száma. |
| `reactions_count` | INT | Kapott reakciók (like-ok) száma. |

### `qc_login_history` Tábla (Új)

Biztonsági naplózás a bejelentkezésekről.

| Oszlop | Típus | Leírás |
| :--- | :--- | :--- |
| `id` | INT (PK) | Egyedi azonosító. |
| `user_id` | INT (FK) | Felhasználó azonosítója. |
| `ip_address` | VARCHAR(45) | IP cím. |
| `event` | ENUM | Esemény: `login`, `login_failed`, `logout`, `password_change`. |
| `user_agent` | VARCHAR(255) | Böngésző/Eszköz információ. |
| `created_at` | DATETIME | Esemény ideje. |

### `qc_badges` Tábla (Új)

Jelvények definíciói.

| Oszlop | Típus | Leírás |
| :--- | :--- | :--- |
| `id` | INT (PK) | Egyedi azonosító. |
| `name` | VARCHAR(100) | Jelvény neve. |
| `description` | TEXT | Leírás. |
| `icon` | VARCHAR(100) | Ikon osztály (pl. FontAwesome). |

### `qc_user_badges` Tábla (Új)

Felhasználók által megszerzett jelvények.

| Oszlop | Típus | Leírás |
| :--- | :--- | :--- |
| `id` | INT (PK) | Egyedi azonosító. |
| `user_id` | INT (FK) | Felhasználó azonosítója. |
| `badge_id` | INT (FK) | Jelvény azonosítója. |
| `awarded_at` | DATETIME | Megszerzés ideje. |

## 3. Működési Elvek

### Biztonság

1.  **Jelszókezelés**: A jelszavakat SOHA ne tárolja nyílt szövegként. Használja a PHP `password_hash()` és `password_verify()` függvényeit.
2.  **IP Cím Kezelés (GDPR)**: Az IP címek személyes adatnak minősülnek. A `qc_login_history` tábla segít a gyanús tevékenységek (pl. brute force) észlelésében. Javasolt az audit logokat bizonyos idő után (pl. 1 év) anonimizálni vagy törölni.
3.  **Soft Delete**: Ha egy felhasználó törli a fiókját, a `status` mezőt állítsa `deleted`-re és a `deleted_at`-et a törlés idejére. Így az adatok (pl. kérdések) megmaradnak, de a felhasználó "eltűnik". Egy cron job később véglegesen törölheti vagy anonimizálhatja ezeket.

### Teljesítmény

*   **Indexelés**: A `setup_profile_db.php` szkript automatikusan létrehozza a szükséges indexeket a gyakran használt mezőkre (`email`, `user_id`, `status`), így a lekérdezések még 10 millió felhasználó esetén is gyorsak maradnak.
*   **Számlálók**: A `questions_count`, `answers_count` stb. mezők arra szolgálnak, hogy ne kelljen minden oldalletöltéskor `COUNT(*)` lekérdezéseket futtatni. Ezeket az értékeket frissíteni kell, amikor a felhasználó új tartalmat hoz létre (pl. triggerrel vagy alkalmazás logikával).

### Telepítés / Frissítés

1.  Futtassa a `setup_profile_db.php` fájlt a böngészőben vagy parancssorból (`php setup_profile_db.php`).
2.  A szkript automatikusan felismeri, ha az oszlopok már léteznek, így biztonságosan futtatható többször is.
3.  A szkript kiszámolja és kitölti a statisztikai mezőket a meglévő felhasználók számára.
4.  Ellenőrzéshez használja a `verify_schema.php` fájlt.
