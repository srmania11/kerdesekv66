CREATE TABLE IF NOT EXISTS qc_users (
    user_id INT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    reputation_points INT DEFAULT 0,
    custom_title VARCHAR(100),
    is_admin TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS qc_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT DEFAULT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    icon_class VARCHAR(50),
    seo_description VARCHAR(255),
    FOREIGN KEY (parent_id) REFERENCES qc_categories(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS qc_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('open', 'solved', 'closed') DEFAULT 'open',
    vote_score INT DEFAULT 0,
    view_count INT DEFAULT 0,
    is_guest TINYINT(1) DEFAULT 0,
    guest_name VARCHAR(100) DEFAULT NULL,
    seo_description VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES qc_categories(id),
    FOREIGN KEY (user_id) REFERENCES qc_users(user_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS qc_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    content TEXT NOT NULL,
    is_accepted TINYINT(1) DEFAULT 0,
    is_ai TINYINT(1) DEFAULT 0,
    is_guest TINYINT(1) DEFAULT 0,
    guest_name VARCHAR(100) DEFAULT NULL,
    vote_score INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES qc_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES qc_users(user_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS qc_pm_threads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_one INT NOT NULL,
    user_two INT NOT NULL,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_one) REFERENCES qc_users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (user_two) REFERENCES qc_users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS qc_pm_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    thread_id INT NOT NULL,
    sender_id INT NOT NULL,
    content TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (thread_id) REFERENCES qc_pm_threads(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES qc_users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS qc_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    target_type ENUM('question', 'answer', 'pm') NOT NULL,
    target_id INT NOT NULL,
    reporter_id INT NOT NULL,
    reason_category VARCHAR(50) NOT NULL,
    reason_details TEXT,
    status ENUM('pending', 'resolved') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES qc_users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS qc_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES qc_users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS qc_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS qc_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    target_type ENUM('question', 'answer') NOT NULL,
    target_id INT NOT NULL,
    vote_value TINYINT(1) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote (user_id, target_type, target_id),
    FOREIGN KEY (user_id) REFERENCES qc_users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Insert dummy users (IDs match common wp user ids for simplicity in dev mode)
INSERT INTO qc_users (user_id, username, reputation_points, custom_title, is_admin) VALUES
(1, 'Admin', 1000, 'Adminisztrátor', 1),
(999, 'TestUser', 50, 'Kezdő Tag', 0),
(2, 'TechGuru', 500, 'Hardver Szakértő', 0),
(3, 'Gamer99', 150, 'Haladó Tag', 0);


-- Generated Category Inserts
DELETE FROM qc_categories;
ALTER TABLE qc_categories AUTO_INCREMENT = 1;
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (1, NULL, 'SZÁMÍTÁSTECHNIKA & HARDVER', 'szamitastechnika-hardver', 'fa-solid fa-microchip', NULL);
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (2, NULL, 'SZOFTVER, FEJLESZTÉS & BIZTONSÁG', 'szoftver-fejlesztes-biztonsag', 'fa-solid fa-code', NULL);
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (3, NULL, 'MOBILITÁS & NAVIGÁCIÓ', 'mobilitas-navigacio', 'fa-solid fa-mobile-screen', NULL);
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (4, NULL, 'SZÓRAKOZTATÓ ELEKTRONIKA & FOTÓ', 'szorakoztato-elektronika-foto', 'fa-solid fa-tv', NULL);
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (5, NULL, 'IRODA & PERIFÉRIÁK', 'iroda-periferiak', 'fa-solid fa-print', NULL);
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (6, NULL, 'HÁLÓZAT & INTERNET', 'halozat-internet', 'fa-solid fa-network-wired', NULL);
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (7, NULL, 'HÁZTARTÁS & OTTHON', 'haztartas-otthon', 'fa-solid fa-house', NULL);
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (8, NULL, 'KÖZÖSSÉG & KIKAPCSOLÓDÁS', 'kozosseg-kikapcsolodas', 'fa-solid fa-comments', NULL);
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (21, 1, 'Processzorok (CPU)', 'processzorok-cpu', 'fa-solid fa-microchip', 'Intel, AMD, hűtés.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (22, 1, 'Videókártyák (GPU)', 'videokartyak-gpu', 'fa-solid fa-memory', 'NVIDIA, AMD, Intel, tuning.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (23, 1, 'Alaplapok & Memória', 'alaplapok-memoria', 'fa-solid fa-chess-board', 'BIOS, chipsetek, DDR4/DDR5.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (24, 1, 'Háttértár (SSD & HDD)', 'hattertar-ssd-hdd', 'fa-solid fa-hard-drive', 'Adatmentés, NVMe, SATA.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (25, 1, 'Számítógépházak & Tápegységek', 'szamitogephazak-tapegysegek', 'fa-solid fa-plug', 'Modding, kábelezés, teljesítmény.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (26, 2, 'Operációs Rendszerek', 'operacios-rendszerek', 'fab fa-windows', 'Windows, Linux, macOS telepítés és hibák.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (27, 2, 'Programozás & Fejlesztés', 'programozas-fejlesztes', 'fa-solid fa-code', 'Webfejlesztés, Python, C++, Java, Scriptek.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (28, 2, 'IT Biztonság & Vírusvédelem', 'it-biztonsag-virusvedelem', 'fa-solid fa-shield-halved', 'Tűzfalak, vírusirtók, adatvédelem, zsarolóvírusok.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (29, 2, 'Felhasználói Programok', 'felhasznaloi-programok', 'fa-solid fa-layer-group', 'Office, Adobe, segédprogramok, böngészők.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (30, 3, 'Okostelefonok', 'okostelefonok', 'fa-solid fa-mobile', 'Android, iOS, rootolás, kiegészítők.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (31, 3, 'Tabletek & E-book olvasók', 'tabletek-e-book-olvasok', 'fa-solid fa-tablet-screen-button', 'iPad, Android tab, Kindle.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (32, 3, 'Okosórák & Viselhető eszközök', 'okosorak-viselheto-eszkozok', 'fa-solid fa-stopwatch', 'Apple Watch, Garmin, fitness karkötők.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (33, 3, 'GPS & Navigáció', 'gps-navigacio', 'fa-solid fa-location-dot', 'Autós navigáció, térképfrissítések, túra GPS.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (34, 4, 'Televíziók & Házimozi', 'televiziok-hazimozi', 'fa-solid fa-tv', 'OLED, QLED, Smart TV, projektorok, hangrendszerek.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (35, 4, 'Konzolok & Játékgépek', 'konzolok-jatekgepek', 'fa-solid fa-gamepad', 'PlayStation, Xbox, Nintendo, kézikonzolok.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (36, 4, 'Fényképezőgépek & Videokamerák', 'fenykepezogepek-videokamerak', 'fa-solid fa-camera', 'DSLR, MILC, objektívek, drónok.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (37, 4, 'Audio & Hi-Fi', 'audio-hi-fi', 'fa-solid fa-headphones', 'Fejhallgatók, hangfalak, erősítők.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (38, 5, 'Monitorok & Kijelzők', 'monitorok-kijelzok', 'fa-solid fa-desktop', 'IPS, OLED, kalibrálás.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (39, 5, 'Billentyűzetek & Egerek', 'billentyuzetek-egerek', 'fa-solid fa-keyboard', 'Mechanikus, gamer, ergonómia.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (40, 5, 'Nyomtatók & Irodatechnika', 'nyomtatok-irodatechnika', 'fa-solid fa-print', 'Lézer, tintasugaras, szkennerek, fénymásolók.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (41, 6, 'Hálózati Eszközök', 'halozati-eszkozok', 'fa-solid fa-wifi', 'Routerek, Switch-ek, WiFi, Mesh rendszerek.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (42, 6, 'Internet Szolgáltatók', 'internet-szolgaltatok', 'fa-solid fa-globe', 'Digi, Telekom, Vodafone, optikai hálózatok.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (43, 6, 'Szerverek & NAS', 'szerverek-nas', 'fa-solid fa-server', 'Otthoni szerver, felhő tárhely, Docker.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (44, 7, 'Nagy Háztartási Gépek', 'nagy-haztartasi-gepek', 'fa-solid fa-plug', 'Mosógépek, hűtőszekrények, mosogatógépek.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (45, 7, 'Kis Háztartási Gépek', 'kis-haztartasi-gepek', 'fa-solid fa-mug-hot', 'Kávéfőzők, porszívók, konyhai eszközök.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (46, 7, 'Okosotthon (Smart Home)', 'okosotthon-smart-home', 'fa-solid fa-lightbulb', 'Okosvilágítás, automatizálás, biztonsági kamerák.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (47, 7, 'Klíma & Légtechnika', 'klima-legtechnika', 'fa-solid fa-wind', 'Légkondicionálók, párátlanítók, fűtés.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (48, 8, 'Csevegő & Off-topic', 'csevego-off-topic', 'fa-solid fa-coffee', 'Kötetlen beszélgetés bármiről.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (49, 8, 'Ismerkedés & Találkozók', 'ismerkedes-talalkozok', 'fa-solid fa-users', 'Társkeresés, közösségi programok.');
INSERT INTO qc_categories (id, parent_id, name, slug, icon_class, seo_description) VALUES (50, 8, 'Vélemények & Javaslatok', 'velemenyek-javaslatok', 'fa-solid fa-circle-question', 'Fórummal kapcsolatos ötletek.');


-- Dummy Questions
INSERT INTO qc_questions (user_id, category_id, title, slug, content, status, seo_description) VALUES
(2, 22, 'Milyen videókártyát vegyek 1440p gamingre?', 'milyen-videokartyat-vegyek-1440p-gamingre', 'Sziasztok! Építek egy új konfigot és elakadtam a videókártya választásnál. A célom a stabil 1440p felbontás és magas FPS.', 'solved', 'Videókártya választás 1440p felbontáshoz.'),
(3, 23, 'Hogyan lehet RAM-ot bővíteni laptopban?', 'hogyan-lehet-ram-ot-boviteni-laptopban', 'Segítséget szeretnék kérni. Van egy Lenovo Legion laptopom, és szeretnék bele még 8GB RAM-ot.', 'open', 'RAM bővítés laptopban.'),
(999, 26, 'Windows 11 telepítési hiba "TPM 2.0" hiány miatt', 'windows-11-telepitesi-hiba-tpm-2-0', 'Sziasztok! Próbálom telepíteni a Windows 11-et, de a telepítő megáll a TPM hiba miatt.', 'open', 'Windows 11 TPM 2.0 hiba elhárítása.');


-- Dummy Answers
INSERT INTO qc_answers (question_id, user_id, content, is_accepted, vote_score) VALUES
(1, 2, 'Szia! Ha a jövőállóság a cél, akkor az RX 7800 XT jó választás a 16GB VRAM miatt.', 1, 5),
(1, 3, 'Én az RTX 4070-et ajánlom a DLSS miatt.', 0, 2);


-- Settings
INSERT INTO qc_settings (setting_key, setting_value) VALUES ('use_pretty_urls', '0');

CREATE TABLE IF NOT EXISTS qc_question_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES qc_questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS qc_answer_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    answer_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (answer_id) REFERENCES qc_answers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES qc_users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS qc_answer_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    answer_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (answer_id) REFERENCES qc_answers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
