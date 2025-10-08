<?php
echo "Testing PHP database connection...\n";
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    echo "MySQL is available\n";
} catch(Exception $e) {
    echo "MySQL not available: " . $e->getMessage() . "\n";
}
?>
