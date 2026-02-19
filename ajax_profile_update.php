<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check user login
$currentUser = getCurrentUser($pdo);
if (!$currentUser) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Jelentkezz be a módosításhoz!']);
    exit;
}

$userId = $currentUser['user_id'];

// Get JSON Input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    // Fallback for form-urlencoded if JSON fails
    $input = $_POST;
}

if (empty($input)) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen adatok.']);
    exit;
}

// Prepare Update Query Components
$fields = [];
$params = [];
$passwordChanged = false;

// 1. Full Name Validation and Add
if (isset($input['full_name'])) {
    $fullName = trim(strip_tags($input['full_name']));
    if (!empty($fullName)) {
        $titles = ['dr.', 'dr', 'prof.', 'prof', 'id.', 'id', 'ifj.', 'ifj', 'özv.', 'özv'];
        $words = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);
        $count = 0;
        foreach ($words as $word) {
            if (!in_array(mb_strtolower($word), $titles)) {
                $count++;
            }
        }
        if ($count > 3) {
            echo json_encode(['success' => false, 'message' => 'A teljes név maximum 3 szóból állhat (plusz titulusok).']);
            exit;
        }
        $fields[] = "full_name = ?";
        $params[] = $fullName;
    } else {
        $fields[] = "full_name = ?";
        $params[] = '';
    }
}

// 2. Email Validation and Add
if (isset($input['email'])) {
    $email = trim(strip_tags($input['email']));
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Érvénytelen e-mail cím formátum.']);
            exit;
        }
        // Check uniqueness
        $stmt = $pdo->prepare("SELECT user_id FROM qc_users WHERE email = ? AND user_id != ?");
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Ez az e-mail cím már foglalt.']);
            exit;
        }
        $fields[] = "email = ?";
        $params[] = $email;
    }
}

// 3. Basic Info (Bio, Location)
if (isset($input['bio'])) {
    $fields[] = "bio = ?";
    $params[] = trim(strip_tags($input['bio']));
}
if (isset($input['location'])) {
    $fields[] = "location = ?";
    $params[] = trim(strip_tags($input['location']));
}

// 4. Website
if (isset($input['website'])) {
    $website = trim(strip_tags($input['website']));
    if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'message' => 'Érvénytelen weboldal URL.']);
        exit;
    }
    $fields[] = "website = ?";
    $params[] = $website;
}

// 5. Social Links
if (isset($input['social_links'])) {
    $socialLinks = $input['social_links'];
    if (is_string($socialLinks)) {
        $socialLinks = json_decode($socialLinks, true);
    }

    if (is_array($socialLinks)) {
        $sanitizedSocials = [];
        foreach ($socialLinks as $key => $url) {
            $url = trim(strip_tags($url));
            if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
                $sanitizedSocials[$key] = $url;
            }
        }
        $fields[] = "social_links = ?";
        $params[] = json_encode($sanitizedSocials);
    }
}

// 6. Password Update
if (!empty($input['new_password'])) {
    $currentPass = $input['current_password'] ?? '';
    $currentPassConfirm = $input['current_password_confirm'] ?? '';
    $newPass = $input['new_password'];
    $newPassConfirm = $input['new_password_confirm'] ?? '';

    if (empty($currentPass)) {
        echo json_encode(['success' => false, 'message' => 'A jelszó módosításához add meg a jelenlegi jelszavad!']);
        exit;
    }
    if ($currentPass !== $currentPassConfirm) {
        echo json_encode(['success' => false, 'message' => 'A jelenlegi jelszó két megadása nem egyezik!']);
        exit;
    }
    if ($newPass !== $newPassConfirm) {
         echo json_encode(['success' => false, 'message' => 'Az új jelszavak nem egyeznek!']);
         exit;
    }

    // Strong password check: Min 8 chars, 1 Uppercase, 1 Special
    if (!preg_match('/^(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $newPass)) {
         echo json_encode(['success' => false, 'message' => 'Az új jelszó nem elég erős! (Min 8 karakter, 1 nagybetű, 1 speciális karakter)']);
         exit;
    }

    // Verify current password
    $storedHash = $currentUser['password_hash'] ?? '';

    if (empty($storedHash) || !password_verify($currentPass, $storedHash)) {
         echo json_encode(['success' => false, 'message' => 'Hibás jelenlegi jelszó!']);
         exit;
    }

    $newHash = password_hash($newPass, PASSWORD_DEFAULT);
    $fields[] = "password_hash = ?";
    $params[] = $newHash;
    $passwordChanged = true;
}

// Helper function to delete avatar files
function deleteCustomAvatarFiles($avatarPath, $userId) {
    if (empty($avatarPath)) return;

    // Remove query string
    $path = parse_url($avatarPath, PHP_URL_PATH);

    // Check if it's a custom upload (starts with uploads/)
    if (strpos($path, 'uploads/') === 0) {
        $fullPath = __DIR__ . '/' . $path;
        $dir = dirname($fullPath);

        // Files to delete (original + sizes)
        // Use glob to find all files matching the pattern for this user
        $files = glob($dir . "/{$userId}*.webp");
        if ($files) {
            foreach ($files as $file) {
                 // Ensure strict match for user ID prefix (start with ID_ or ID.)
                 $basename = basename($file);
                 if (preg_match('/^' . $userId . '(_|\.)/', $basename)) {
                     if (file_exists($file)) {
                        unlink($file);
                     }
                 }
            }
        }
    }
}

// 7. Avatar Update (Delete or Select Default)
if (!empty($input['delete_avatar'])) {
    // Delete physical files
    deleteCustomAvatarFiles($currentUser['avatar'], $userId);

    $fields[] = "avatar = ?";
    $params[] = null;
} elseif (!empty($input['selected_avatar'])) {
    // Validate path
    $path = $input['selected_avatar'];
    // Must start with assets/default_avatars/ and no .. traversal
    if (strpos($path, 'assets/default_avatars/') === 0 && strpos($path, '..') === false) {
         if (file_exists(__DIR__ . '/' . $path)) {
            // Delete old custom files if exist (cleanup)
            deleteCustomAvatarFiles($currentUser['avatar'], $userId);

            $fields[] = "avatar = ?";
            $params[] = $path;
         }
    }
}

// 8. Cover Update (Delete)
if (!empty($input['delete_cover'])) {
    $fields[] = "cover_image = ?";
    $params[] = null;
}

// If no fields to update
if (empty($fields)) {
    echo json_encode(['success' => true, 'message' => 'Nincs változás.']);
    exit;
}

// Add user_id to params
$params[] = $userId;

$sql = "UPDATE qc_users SET " . implode(', ', $fields) . " WHERE user_id = ?";

try {
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        // Log password change if happened
        if ($passwordChanged) {
            $logStmt = $pdo->prepare("INSERT INTO qc_login_history (user_id, ip_address, event, user_agent) VALUES (?, ?, 'password_change', ?)");
            $logStmt->execute([$userId, $_SERVER['REMOTE_ADDR'] ?? 'Unknown', $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown']);
        }
        echo json_encode(['success' => true, 'message' => 'Profil sikeresen frissítve!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Adatbázis hiba.']);
    }
} catch (PDOException $e) {
    error_log("Profile Update Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Hiba történt a mentés során.']);
}
?>
