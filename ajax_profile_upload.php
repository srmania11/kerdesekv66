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
    echo json_encode(['success' => false, 'message' => 'Jelentkezz be a feltöltéshez!']);
    exit;
}

$userId = $currentUser['user_id'];

// Check if file is uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Hiba a feltöltés során vagy nincs fájl kiválasztva.']);
    exit;
}

// Get upload type (avatar or cover)
$type = isset($_POST['type']) && $_POST['type'] === 'cover' ? 'cover' : 'avatar';

$file = $_FILES['file'];
$tmpPath = $file['tmp_name'];

// 1. Security Checks

// Check if it's an uploaded file
if (!is_uploaded_file($tmpPath)) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen feltöltés.']);
    exit;
}

// Check file size (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'A fájl túl nagy (max 5MB).']);
    exit;
}

// Check Storage Quota
if (!has_storage_space($currentUser, $file['size'])) {
    echo json_encode(['success' => false, 'message' => 'A tárhelyed megtelt!']);
    exit;
}

// Check MIME type using finfo
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($tmpPath);
$allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

if (!in_array($mime, $allowedMimes)) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen fájltípus. Csak képek engedélyezettek (JPEG, PNG, WEBP, GIF).']);
    exit;
}

// Check image dimensions and valid image data
$imageInfo = getimagesize($tmpPath);
if ($imageInfo === false) {
    echo json_encode(['success' => false, 'message' => 'A fájl nem érvényes kép.']);
    exit;
}

// 2. Optimization and Conversion

// Create image resource from file
switch ($imageInfo[2]) {
    case IMAGETYPE_JPEG:
        $image = imagecreatefromjpeg($tmpPath);
        break;
    case IMAGETYPE_PNG:
        $image = imagecreatefrompng($tmpPath);
        break;
    case IMAGETYPE_WEBP:
        $image = imagecreatefromwebp($tmpPath);
        break;
    case IMAGETYPE_GIF:
        $image = imagecreatefromgif($tmpPath);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Nem támogatott képformátum.']);
        exit;
}

if (!$image) {
    echo json_encode(['success' => false, 'message' => 'Hiba a kép feldolgozása során.']);
    exit;
}

// Dimensions Logic
$width = imagesx($image);
$height = imagesy($image);
$newImage = null;

if ($type === 'avatar') {
    // Square crop for avatar
    $size = min($width, $height);
    $x = ($width - $size) / 2;
    $y = ($height - $size) / 2;

    // Resize to max 512x512 for main image
    $targetSize = min($size, 512);

    $newImage = imagecreatetruecolor($targetSize, $targetSize);

    // Preserve transparency
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);

    // Crop and Resize
    imagecopyresampled($newImage, $image, 0, 0, $x, $y, $targetSize, $targetSize, $size, $size);

} else {
    // Cover image logic (Max width 1920)
    $maxWidth = 1920;

    if ($width > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = (int)($height * ($newWidth / $width));
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);

    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
}

imagedestroy($image);
$image = $newImage;


// 3. Storage Logic

// Calculate chunk directory: floor(user_id / 200) for max ~800 files per folder (assuming 4 files per user)
// Requirement: "maximum 1000 images / folder" - This logic satisfies the requirement (200 * 4 = 800 files max)
$chunk = floor($userId / 200);
$typeDir = ($type === 'avatar') ? 'avatars' : 'covers';
$uploadBaseDir = __DIR__ . "/uploads/$typeDir/$chunk/";

if (!is_dir($uploadBaseDir)) {
    if (!mkdir($uploadBaseDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Nem sikerült létrehozni a feltöltési könyvtárat.']);
        exit;
    }
}

// Filename Logic
if ($type === 'avatar') {
    // Delete existing avatars for this user to prevent accumulation
    $existingFiles = glob($uploadBaseDir . "{$userId}*.webp");
    if ($existingFiles) {
        foreach ($existingFiles as $f) {
            if (is_file($f)) {
                // Ensure we only delete files starting with "userId_" or "userId." to be safe
                $basename = basename($f);
                if (preg_match('/^' . $userId . '(_|\.)/', $basename)) {
                    $size = filesize($f);
                    if (unlink($f)) {
                        update_storage_usage($userId, -$size, $pdo);
                    }
                }
            }
        }
    }

    // Generate random suffix for new file
    $rand = rand(10000, 99999);
    $fileName = "{$userId}_{$rand}.webp";
} else {
    // For covers, we keep the original overwriting behavior
    $fileName = "{$userId}.webp";
    $rand = null; // No random suffix for covers
}

$destinationPath = $uploadBaseDir . $fileName;

// Save as WebP with 85% quality
if (imagewebp($image, $destinationPath, 85)) {
    $size = filesize($destinationPath);
    update_storage_usage($userId, $size, $pdo);

    $dbPath = "uploads/$typeDir/$chunk/$fileName"; // Default DB path (for cover or main avatar)

    // Generate additional sizes for avatars
    if ($type === 'avatar') {
        $sizes = [150, 36, 24];
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        foreach ($sizes as $size) {
            $thumb = imagecreatetruecolor($size, $size);

            // Preserve transparency
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);

            // Resample
            imagecopyresampled($thumb, $image, 0, 0, 0, 0, $size, $size, $originalWidth, $originalHeight);

            // Save thumb
            // Use same random number for consistency
            $thumbName = "{$userId}_{$rand}_{$size}x{$size}.webp";
            if (imagewebp($thumb, $uploadBaseDir . $thumbName, 85)) {
                $tSize = filesize($uploadBaseDir . $thumbName);
                update_storage_usage($userId, $tSize, $pdo);
            }
            imagedestroy($thumb);

            // If size is 150, use this as the DB path
            if ($size === 150) {
                $dbPath = "uploads/$typeDir/$chunk/$thumbName";
            }
        }
    }

    imagedestroy($image);

    // Update DB
    $dbColumn = ($type === 'avatar') ? 'avatar' : 'cover_image';

    // Add version query string to public path for cache busting
    $publicUrl = $dbPath . '?v=' . time();

    $stmt = $pdo->prepare("UPDATE qc_users SET `$dbColumn` = ? WHERE user_id = ?");
    if ($stmt->execute([$publicUrl, $userId])) {
        echo json_encode([
            'success' => true,
            'url' => $publicUrl
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Adatbázis hiba.']);
    }

} else {
    imagedestroy($image);
    echo json_encode(['success' => false, 'message' => 'Hiba a kép mentése során.']);
}
?>
