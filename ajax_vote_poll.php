<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

$currentUser = getCurrentUser($pdo);

if (!$currentUser) {
    echo json_encode(['success' => false, 'message' => 'Kérjük jelentkezz be a szavazáshoz!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen kérés.']);
    exit;
}

$poll_id = isset($_POST['poll_id']) ? (int)$_POST['poll_id'] : 0;
$option_id = isset($_POST['option_id']) ? (int)$_POST['option_id'] : 0;

if ($poll_id <= 0 || $option_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Hibás adatok.']);
    exit;
}

// Check poll status
$stmt = $pdo->prepare("SELECT is_closed FROM qc_polls WHERE id = ?");
$stmt->execute([$poll_id]);
$poll_data = $stmt->fetch();

if (!$poll_data) {
    echo json_encode(['success' => false, 'message' => 'Szavazás nem található.']);
    exit;
}

if ($poll_data['is_closed']) {
    echo json_encode(['success' => false, 'message' => 'A szavazás lezárult.']);
    exit;
}

// Check if already voted
$stmt = $pdo->prepare("SELECT id FROM qc_poll_votes WHERE poll_id = ? AND user_id = ?");
$stmt->execute([$poll_id, $currentUser['user_id']]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Már szavaztál ebben a kérdésben!']);
    exit;
}

// Verify option belongs to poll
$stmt = $pdo->prepare("SELECT id FROM qc_poll_options WHERE id = ? AND poll_id = ?");
$stmt->execute([$option_id, $poll_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Érvénytelen válaszlehetőség.']);
    exit;
}

// Record vote
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO qc_poll_votes (poll_id, option_id, user_id) VALUES (?, ?, ?)");
    $stmt->execute([$poll_id, $option_id, $currentUser['user_id']]);

    $stmt = $pdo->prepare("UPDATE qc_poll_options SET vote_count = vote_count + 1 WHERE id = ?");
    $stmt->execute([$option_id]);

    $pdo->commit();

    // Fetch updated stats
    $stmt = $pdo->prepare("SELECT id, vote_count FROM qc_poll_options WHERE poll_id = ?");
    $stmt->execute([$poll_id]);
    $options = $stmt->fetchAll();

    $total_votes = 0;
    foreach ($options as $opt) {
        $total_votes += $opt['vote_count'];
    }

    $stats = [];
    foreach ($options as $opt) {
        $percent = ($total_votes > 0) ? round(($opt['vote_count'] / $total_votes) * 100, 1) : 0;
        $stats[] = [
            'id' => $opt['id'],
            'count' => $opt['vote_count'],
            'percent' => $percent
        ];
    }

    echo json_encode([
        'success' => true,
        'message' => 'Köszönjük a szavazatodat!',
        'stats' => $stats,
        'total_votes' => $total_votes
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Hiba történt a szavazás során: ' . $e->getMessage()]);
}
?>
