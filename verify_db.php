<?php
require_once 'db_connect.php';
$stmt = $pdo->query("SELECT id, title FROM qc_questions");
while ($row = $stmt->fetch()) {
    echo $row['id'] . ": " . $row['title'] . "\n";
}
?>
