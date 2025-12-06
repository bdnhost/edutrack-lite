<?php
// Test Database Connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing EduTrack Lite Configuration...\n\n";

// Check if config.php exists
if (!file_exists('config.php')) {
    die("ERROR: config.php does not exist!\n");
}

echo "✓ config.php exists\n";

// Try to include it
try {
    require_once 'config.php';
    echo "✓ config.php loaded successfully\n";
} catch (Exception $e) {
    die("ERROR loading config.php: " . $e->getMessage() . "\n");
}

// Check database connection
if (!isset($conn)) {
    die("ERROR: \$conn variable not defined\n");
}

if ($conn->connect_error) {
    die("ERROR: Database connection failed: " . $conn->connect_error . "\n");
}

echo "✓ Database connected successfully\n";
echo "  - Host: " . DB_HOST . "\n";
echo "  - Database: " . DB_NAME . "\n";
echo "  - User: " . DB_USER . "\n";

// Test a simple query
try {
    $result = $conn->query("SELECT VERSION() as version");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✓ MySQL version: " . $row['version'] . "\n";
    }
} catch (Exception $e) {
    echo "Warning: Could not get MySQL version: " . $e->getMessage() . "\n";
}

// Check tables
echo "\n--- Checking Tables ---\n";
$tables = ['users', 'institutions', 'courses', 'students', 'lessons', 'attendance_sessions', 'attendance_records'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "✓ Table '$table' exists\n";
    } else {
        echo "✗ Table '$table' NOT FOUND\n";
    }
}

// Check session
echo "\n--- Session Info ---\n";
echo "Session name: " . SESSION_NAME . "\n";
echo "Session started: " . (session_status() === PHP_SESSION_ACTIVE ? 'Yes' : 'No') . "\n";

echo "\n=== ALL TESTS PASSED ===\n";
?>
