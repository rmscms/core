<?php

require_once 'vendor/autoload.php';

use RMS\Core\Data\Database;
use RMS\Core\Data\Field;

// Create a simple test
$fields = [
    Field::make('id', 'id'),
    Field::make('name', 'name'),
];

$database = Database::make($fields, 'users');

echo "Testing Database class with simple pagination support:\n\n";

echo "Method 1 - Regular paginate:\n";
try {
    $result = $database->get(10, 1, false);
    echo "✅ Regular pagination works - returns: " . get_class($result) . "\n";
} catch (Exception $e) {
    echo "❌ Regular pagination error: " . $e->getMessage() . "\n";
}

echo "\nMethod 2 - Simple paginate:\n";
try {
    $result = $database->get(10, 1, true);
    echo "✅ Simple pagination works - returns: " . get_class($result) . "\n";
} catch (Exception $e) {
    echo "❌ Simple pagination error: " . $e->getMessage() . "\n";
}

echo "\nMethod 3 - getSimple method:\n";
try {
    $result = $database->getSimple(10, 1);
    echo "✅ getSimple method works - returns: " . get_class($result) . "\n";
} catch (Exception $e) {
    echo "❌ getSimple method error: " . $e->getMessage() . "\n";
}

echo "\nAll tests completed!\n";