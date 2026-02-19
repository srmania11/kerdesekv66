<?php
require_once 'db_connect.php';

try {
    $tables = ['qc_users', 'qc_login_history', 'qc_badges', 'qc_user_badges'];

    foreach ($tables as $table) {
        echo "Table: $table\n";
        $stmt = $pdo->prepare("DESCRIBE $table");
        $stmt->execute();
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            // Check if column key exists (older PHP/PDO versions might use slightly different keys, but standard is Field, Type, Null, Key, Default, Extra)
            $field = $col['Field'] ?? $col['field'];
            $type = $col['Type'] ?? $col['type'];
            $null = $col['Null'] ?? $col['null'];
            $key = $col['Key'] ?? $col['key'];
            $default = $col['Default'] ?? $col['default'] ?? 'NULL'; // Default can be null
            $extra = $col['Extra'] ?? $col['extra'];

            echo "  " . str_pad($field, 25) . str_pad($type, 25) . str_pad($null, 5) . str_pad($key, 5) . str_pad($default, 15) . str_pad($extra, 15) . "\n";
        }
        echo "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
