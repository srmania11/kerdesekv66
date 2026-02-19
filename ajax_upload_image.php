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

// Check if file is uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Hiba a feltöltés során vagy nincs fájl kiválasztva.']);
    exit;
}

$file = $_FILES['file'];
$tmpPath = $file['tmp_name'];
$originalName = $file['name'];

// 1. Security Checks

// Check if it's an uploaded file
if (!is_uploaded_file($tmpPath)) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen feltöltés.']);
    exit;
}

// Check file size (e.g. max 10MB)
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'A fájl túl nagy (max 10MB).']);
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

// 2. Optimization and Conversion (Re-encode to strip malicious payload)

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

// Resize if necessary (Max width 2048px)
$maxWidth = 2048;
$width = imagesx($image);
$height = imagesy($image);

if ($width > $maxWidth) {
    $newWidth = $maxWidth;
    $newHeight = (int)($height * ($newWidth / $width));

    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG/WebP/GIF
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);

    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    imagedestroy($image);
    $image = $newImage;
}

// 3. Storage Logic

// Create directory structure uploads/images/YYYY/MM/DD/
$uploadBaseDir = __DIR__ . '/uploads/images/' . date('Y') . '/' . date('m') . '/' . date('d') . '/';
if (!is_dir($uploadBaseDir)) {
    if (!mkdir($uploadBaseDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Nem sikerült létrehozni a feltöltési könyvtárat.']);
        exit;
    }
}

// Generate unique filename
$newFileName = uniqid('img_', true) . '.webp';
$destinationPath = $uploadBaseDir . $newFileName;
$publicPath = 'uploads/images/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $newFileName;

// Save as WebP with 80% quality
if (imagewebp($image, $destinationPath, 80)) {
    $size = filesize($destinationPath);
    update_storage_usage($currentUser['user_id'], $size, $pdo);

    imagedestroy($image);

    // Store in session for ownership verification
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['uploaded_files'])) {
        $_SESSION['uploaded_files'] = [];
    }
    $_SESSION['uploaded_files'][] = $publicPath;

    echo json_encode([
        'success' => true,
        'path' => $publicPath,
        'original_name' => $originalName
    ]);
} else {
    imagedestroy($image);
    echo json_encode(['success' => false, 'message' => 'Hiba a kép mentése során.']);
}
?>
