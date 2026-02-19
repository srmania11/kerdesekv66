<?php
function getCurrentUser($pdo) {
    global $usr_id, $usr_username; // From global context

    if (defined('DEV_MODE') && DEV_MODE) {
        // Simulate login if globals are missing
        if (empty($usr_id)) {
            $usr_id = 999;
            $usr_username = 'TestUser';
        }
    }

    if (!empty($usr_id) && $usr_id > 0) {
        // Fetch extra data from qc_users
        $stmt = $pdo->prepare("SELECT * FROM qc_users WHERE user_id = ?");
        $stmt->execute([$usr_id]);
        $user = $stmt->fetch();

        if (!$user) {
            // Create user if not exists (auto-register bridge)
            $stmt = $pdo->prepare("INSERT INTO qc_users (user_id, username) VALUES (?, ?)");
            $stmt->execute([$usr_id, $usr_username]);
            $user = ['user_id' => $usr_id, 'username' => $usr_username, 'reputation_points' => 0, 'is_admin' => 0];
        }
        return $user;
    }
    return null; // Guest
}

function slugify($text) {
    // Replace Hungarian accents
    $search = ['á','é','í','ó','ö','ő','ú','ü','ű', 'Á','É','Í','Ó','Ö','Ő','Ú','Ü','Ű'];
    $replace = ['a','e','i','o','o','o','u','u','u', 'a','e','i','o','o','o','u','u','u'];
    $text = str_replace($search, $replace, $text);

    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) {
        return 'n-a';
    }
    return $text;
}

function generateUrl($type, $id, $slug = '') {
    global $use_pretty_urls;
    if (!isset($use_pretty_urls)) $use_pretty_urls = false;

    if ($use_pretty_urls) {
        if ($type === 'question') return "kerdes/{$id}/{$slug}";
        if ($type === 'category') return "kategoria/{$id}/{$slug}";
    } else {
        if ($type === 'question') return "question.php?id={$id}";
        if ($type === 'category') return "kategoria.php?id={$id}";
    }
    return "#";
}

function time_elapsed_string($datetime, $full = false) {
    // Use default timezone (configured in config.php) for comparison
    // This fixes timezone mismatch issues where DB stores local time but PHP forced UTC
    try {
        $now = new DateTime();
        $ago = new DateTime($datetime);
    } catch (Exception $e) {
        return 'érvénytelen dátum';
    }

    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'éve',
        'm' => 'hónapja',
        'w' => 'hete',
        'd' => 'napja',
        'h' => 'órája',
        'i' => 'perce',
        's' => 'másodperce',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v;
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ezelőtt' : 'épp most';
}

function getSeoData($pdo, $page_type, $id = 0) {
    $seo = [
        'title' => 'SilverPC Fórum',
        'description' => 'Kérdezz és válaszolj hardver, szoftver és egyéb témákban.',
        'og_type' => 'website'
    ];

    if ($page_type === 'question' && $id > 0) {
        $stmt = $pdo->prepare("SELECT title, content, seo_description FROM qc_questions WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if ($data) {
            $seo['title'] = $data['title'] . ' | SilverPC Fórum';
            $seo['og_type'] = 'article';
            if (!empty($data['seo_description'])) {
                $seo['description'] = $data['seo_description'];
            } else {
                $plain = strip_tags($data['content']);
                $seo['description'] = mb_substr($plain, 0, 150) . '...';
            }
        }
    } elseif ($page_type === 'category' && $id > 0) {
        $stmt = $pdo->prepare("SELECT name, seo_description FROM qc_categories WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        if ($data) {
            $seo['title'] = $data['name'] . ' | SilverPC Fórum';
            if (!empty($data['seo_description'])) {
                $seo['description'] = $data['seo_description'];
            }
        }
    }

    return $seo;
}

function renderSeoHead($seoData) {
    echo '<title>' . htmlspecialchars($seoData['title']) . '</title>' . PHP_EOL;
    echo '<meta name="description" content="' . htmlspecialchars($seoData['description']) . '">' . PHP_EOL;
    echo '<meta property="og:title" content="' . htmlspecialchars($seoData['title']) . '">' . PHP_EOL;
    echo '<meta property="og:description" content="' . htmlspecialchars($seoData['description']) . '">' . PHP_EOL;
    echo '<meta property="og:type" content="' . htmlspecialchars($seoData['og_type']) . '">' . PHP_EOL;
}

function clean_html($content) {
    if (empty($content)) return '';

    // 1. Initial filtered strip to remove unwanted tags structure
    // Note: We keep this to simplify the DOM structure we load
    $allowed_tags_str = '<b><i><u><ul><ol><li><br><p><img><iframe><blockquote><strong><em><div><code><pre><span><h1><h2><h3><h4><h5><h6><table><thead><tbody><tr><th><td>';
    $content = strip_tags($content, $allowed_tags_str);

    // 2. Load into DOMDocument for robust attribute cleaning
    $dom = new DOMDocument();
    // Suppress errors for malformed HTML
    libxml_use_internal_errors(true);
    // Force UTF-8 handling by wrapping in a meta tag or header (using mb_convert_encoding)
    // We wrap in a root div to handle multiple top-level elements
    $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $content . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('//*');

    $allowed_attributes = ['src', 'href', 'title', 'alt', 'width', 'height', 'allowfullscreen', 'frameborder', 'class', 'style']; // class/style might be needed for some formatting, but risky. Let's restrict.
    // Restricting style/class is safer. Let's keep it minimal as per original intent (rich text).
    // Original code allowed basic rich text.
    $allowed_attributes = ['src', 'href', 'title', 'alt', 'width', 'height', 'allowfullscreen', 'frameborder', 'class'];

    foreach ($nodes as $node) {
        // Remove nodes that shouldn't be here (extra safety, though strip_tags helps)
        // Check for scripts again just in case
        if (strtolower($node->nodeName) === 'script' || strtolower($node->nodeName) === 'object' || strtolower($node->nodeName) === 'embed') {
            $node->parentNode->removeChild($node);
            continue;
        }

        if ($node->hasAttributes()) {
            $attributes = iterator_to_array($node->attributes);
            foreach ($attributes as $attr) {
                $attrName = strtolower($attr->name);

                // Remove on* events (onclick, etc) and anything not allowed
                if (!in_array($attrName, $allowed_attributes) || strpos($attrName, 'on') === 0) {
                    $node->removeAttribute($attr->name);
                    continue;
                }

                // Validate URLs in src/href
                if (in_array($attrName, ['src', 'href'])) {
                    $value = strtolower(trim($attr->value));
                    // Block javascript: and data: (unless image data, but let's block for safety)
                    if (strpos($value, 'javascript:') !== false || strpos($value, 'data:') !== false || strpos($value, 'vbscript:') !== false) {
                        $node->removeAttribute($attr->name);
                        continue;
                    }

                    // Strict YouTube check for iframes
                    if (strtolower($node->nodeName) === 'iframe' && $attrName === 'src') {
                        if (!preg_match('#^https://(www\.)?youtube\.com/embed/#i', $value)) {
                            // Invalid iframe src, remove the iframe node entirely to be safe
                            $node->parentNode->removeChild($node);
                            // Break inner loop as node is gone
                            break;
                        }
                    }
                }
            }
        }
    }

    // 3. Export filtered HTML
    // Get the div content
    $div = $dom->getElementsByTagName('div')->item(0);
    $output = '';
    if ($div && $div->hasChildNodes()) {
        foreach ($div->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }
    }

    return $output;
}

function updateUserActivity($pdo) {
    global $usr_id;
    if (!empty($usr_id) && $usr_id > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE qc_users SET last_active = NOW() WHERE user_id = ?");
            $stmt->execute([$usr_id]);
        } catch (PDOException $e) {
            // Ignore error if column missing
        }
    }
}

function seconds_to_time_elapsed($seconds) {
    if ($seconds < 60) return 'épp most';
    $minutes = round($seconds / 60);
    if ($minutes < 60) return $minutes . ' perce';
    $hours = round($seconds / 3600);
    if ($hours < 24) return $hours . ' órája';
    $days = round($seconds / 86400);
    return $days . ' napja';
}

function process_content_for_display($content, $is_logged_in) {
    if (empty($content)) return '';

    // Patterns
    // 1. YouTube Video Embed (Priority)
    // Matches https://www.youtube.com/watch?v=VIDEO_ID or similar
    $youtube_pattern = '(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})';

    // 2. General URL
    $url_pattern = '(?:https?:\/\/|www\.)[^\s<"]+';

    // Complex Regex
    // 1. Anchors: <a ...>...</a>
    // 2. Iframes: <iframe ...>...</iframe> (don't break existing videos)
    // 3. Other Tags: <...>
    // 4. YouTube URLs
    // 6. Other URLs

    $regex = '
    ~
      (<a\s[^>]*>.*?</a>)          # 1. Anchors
    | (<iframe\s[^>]*>.*?</iframe>)# 2. Iframes
    | (<[^>]+>)                    # 3. Other Tags
    | (' . $youtube_pattern . ')   # 4. YouTube URL (Group 4 is full match, Group 5 is ID)
    | (' . $url_pattern . ')       # 6. Other URL
    ~ixs';

    return preg_replace_callback($regex, function ($matches) use ($is_logged_in) {
        // Group 1: Anchor
        if (!empty($matches[1])) return $matches[1];
        // Group 2: Iframe
        if (!empty($matches[2])) return $matches[2];
        // Group 3: Other Tag
        if (!empty($matches[3])) return $matches[3];

        // Group 4: YouTube
        if (!empty($matches[4])) {
            // Re-match to extract ID properly if it matched the big pattern
            if (isset($matches[5])) {
                $video_id = $matches[5];
                return '<div class="q_v_video_container"><iframe src="https://www.youtube.com/embed/' . $video_id . '" allowfullscreen></iframe></div>';
            }
        }

        // Group 6: Other URL
        if (!empty($matches[6])) {
            $url = $matches[6];
            // Fix protocol
            if (strpos($url, 'http') !== 0 && strpos($url, 'www.') === 0) {
                $full_url = 'http://' . $url;
            } else {
                $full_url = $url;
            }

            if ($is_logged_in) {
                // Stylish link for logged in
                // Encode URL for redirect parameter
                $redirect_url = 'redirect.php?url=' . urlencode($full_url);
                // Truncate display URL if too long
                $display_text = (strlen($url) > 40) ? substr($url, 0, 37) . '...' : $url;

                return '<a href="' . $redirect_url . '" target="_blank" rel="nofollow noopener" class="q_v_external_link" title="Külső hivatkozás: ' . htmlspecialchars($full_url) . '">'.
                       '<i class="fa-solid fa-link"></i> ' . htmlspecialchars($display_text) . ' <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:0.7em; opacity:0.7;"></i></a>';
            } else {
                // Stylish login requirement for guests
                return '<a href="login.php" class="q_v_login_link_placeholder" title="Jelentkezz be a megtekintéshez!"><i class="fa-solid fa-lock"></i> A link megtekintéséhez bejelentkezés szükséges</a>';
            }
        }

        return $matches[0];
    }, $content);
}

// --- Storage Management Functions ---

function calculate_user_storage_limit($user) {
    $base = 10 * 1024 * 1024; // 10 MB base (fixed from previous 20MB mock)

    $q = (int)($user['questions_count'] ?? 0);
    $a = (int)($user['answers_count'] ?? 0);
    $accepted = (int)($user['accepted_answers_count'] ?? 0);

    $tiers = [
        [1000, 4000, 2048], // 2GB
        [700,  2000, 1024], // 1GB
        [500,  1000, 800],  // 800MB
        [400,  800,  500],  // 500MB
        [200,  400,  100],  // 100MB
        [100,  200,  30],   // 30MB
        [30,   60,   20],   // 20MB
        [5,    10,   15]    // 15MB
    ];

    $tierLimit = $base;

    // Check tiers from highest to lowest
    foreach ($tiers as $tier) {
        if ($q >= $tier[0] || $a >= $tier[1]) {
            $tierLimit = $tier[2] * 1024 * 1024;
            break;
        }
    }

    // Bonus logic: +200MB per 100 accepted answers
    $bonus = floor($accepted / 100) * 200 * 1024 * 1024;

    return $tierLimit + $bonus;
}

function update_storage_usage($user_id, $bytes, $pdo) {
    if ($bytes == 0) return;

    // Ensure we don't go below 0 (GREATEST(0, ...))
    $stmt = $pdo->prepare("UPDATE qc_users SET storage_used = GREATEST(0, storage_used + ?) WHERE user_id = ?");
    $stmt->execute([$bytes, $user_id]);
}

function has_storage_space($user, $required_size = 0) {
    $limit = calculate_user_storage_limit($user);
    $used = $user['storage_used'] ?? 0;
    return ($used + $required_size) <= $limit;
}
?>
