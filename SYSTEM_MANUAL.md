# SilverPC Q&A System - System Manual

## 1. Installation

1.  **Database:** Import `db_schema.sql` into your MySQL database. This file creates all necessary tables (`qc_*`) and inserts dummy data.
2.  **Configuration:** Edit `config.php`:
    *   Set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
    *   Set `DEV_MODE` to `false` for production usage.
3.  **Permissions:** Ensure the `/cache` directory (created automatically) is writable by the web server.

## 2. Configuration

*   **DEV_MODE:** When set to `true` in `config.php`, the system simulates a logged-in user (`$usr_id = 999`) if the host system doesn't provide one.
*   **Pretty URLs:** Toggle `$use_pretty_urls` in `config.php` (or database `qc_settings`).
    *   `false`: URLs look like `?id=123`.
    *   `true`: URLs look like `/kerdes/123/slug`. (Requires .htaccess configuration).

## 3. Features Overview

### Authentication
The system integrates with an existing user base via global variables `$usr_id` and `$usr_username`. No internal login system is implemented, as requested.

### SEO Engine
*   **Dynamic Metadata:** `<title>` and `<meta name="description">` are generated based on the content (Category name, Question title).
*   **Open Graph:** `og:title`, `og:description`, `og:type` tags are included for social sharing.
*   **Structured Data (JSON-LD):** Questions include `Schema.org/QAPage` markup for Google Rich Results.

### Caching
*   The system implements file-based caching logic.
*   Cache files are stored in `cache/YYYY/MM/DD/`.
*   Note: For dynamic user-specific views (like "My Profile" or notification counts), strict caching is disabled to prevent stale data for logged-in users.

### Admin Panel
*   Accessible at `admin.php`.
*   Requires a user with `is_admin = 1` in `qc_users`.
*   Allows deleting/closing questions and resolving reports.

## 4. Developer Notes

*   **Injection Point:** All functionality is strictly injected into `<div class="cikk-fo-col-left">`.
*   **Styling:** Original CSS classes (`c_l_wrapper`, `q_p_card`, etc.) are preserved to maintain the visual design.
*   **Database Connection:** Uses `PDO` in `db_connect.php`.

## 5. Files
*   `index.php`: Main category list.
*   `kategoria.php`: Question list for a category.
*   `kerdes_bevitele.php`: Form to ask a new question.
*   `kerdes_bejelentkezett_nezet.php`: Question view for logged-in users (with editor).
*   `kerdes_nem_bejelentkezett_nezet.php`: Question view for guests.
*   `private_messages.php`: PM system.
*   `admin.php`: Moderation dashboard.
*   `search.php`: Search interface.
