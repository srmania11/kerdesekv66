<?php

require_once 'db_connect.php';
require_once 'gamification.php';

echo "Starting badge seeding...\n";

try {
    $definitions = getBadgeDefinitions();

    // Prepare statement
    $stmt = $pdo->prepare("SELECT id FROM qc_badges WHERE name = ?");
    $insertStmt = $pdo->prepare("INSERT INTO qc_badges (name, description, icon) VALUES (?, ?, ?)");
    $updateStmt = $pdo->prepare("UPDATE qc_badges SET description = ?, icon = ? WHERE id = ?");

    $count = 0;

    foreach ($definitions as $category => $badges) {
        foreach ($badges as $badge) {
            $name = $badge['name'];
            $icon = $badge['icon'];
            $desc = "Awarded for reaching " . $badge['threshold'] . " " . str_replace('_', ' ', $category) . ".";

            // Check if exists
            $stmt->execute([$name]);
            $existingId = $stmt->fetchColumn();

            if ($existingId) {
                // Update
                $updateStmt->execute([$desc, $icon, $existingId]);
                // echo "Updated badge: $name\n";
            } else {
                // Insert
                $insertStmt->execute([$name, $desc, $icon]);
                echo "Created badge: $name\n";
                $count++;
            }
        }
    }

    echo "Seeding completed. $count new badges created.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
