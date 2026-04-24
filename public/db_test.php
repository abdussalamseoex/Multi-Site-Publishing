<?php
// db_test.php - Database and Environment Diagnostics Tool
header('Content-Type: text/plain');

echo "=== Laravel Environment & Database Diagnostics ===\n\n";

$envPath = __DIR__ . '/../.env';
$configCache = __DIR__ . '/../bootstrap/cache/config.php';

echo "[1] CHECKING CONFIG CACHE...\n";
if (file_exists($configCache)) {
    echo "WARNING: Config is cached! This is likely causing the issue.\n";
    echo "Please delete the file: bootstrap/cache/config.php\n";
} else {
    echo "OK: No config cache found.\n";
}

echo "\n[2] CHECKING .ENV FILE...\n";
if (!file_exists($envPath)) {
    echo "ERROR: .env file not found at " . $envPath . "\n";
} else {
    $envContent = file_get_contents($envPath);
    $lines = explode("\n", $envContent);
    $dbConfig = [];
    $sessionDriver = 'not found';
    $appInstalled = 'not found';

    echo "Found DB Configuration in .env:\n";
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'DB_') === 0 || strpos($line, '# DB_') === 0 || strpos($line, '#DB_') === 0) {
            // Mask password for security
            if (strpos($line, 'DB_PASSWORD') === 0) {
                echo "DB_PASSWORD=********\n";
            } else {
                echo $line . "\n";
            }
            if (strpos($line, 'DB_') === 0) {
                list($key, $val) = explode('=', $line, 2);
                $dbConfig[$key] = trim($val, '"\' ');
            }
        }
        if (strpos($line, 'SESSION_DRIVER') !== false) {
            $sessionDriver = $line;
        }
        if (strpos($line, 'APP_INSTALLED') !== false) {
            $appInstalled = $line;
        }
    }
    
    echo "\nSession Config: " . $sessionDriver . "\n";
    echo "App Installed : " . $appInstalled . "\n";

    echo "\n[3] TESTING DATABASE CONNECTION...\n";
    $host = $dbConfig['DB_HOST'] ?? '127.0.0.1';
    $port = $dbConfig['DB_PORT'] ?? '3306';
    $db   = $dbConfig['DB_DATABASE'] ?? '';
    $user = $dbConfig['DB_USERNAME'] ?? '';
    $pass = $dbConfig['DB_PASSWORD'] ?? '';

    if (empty($db) || empty($user)) {
        echo "ERROR: DB_DATABASE or DB_USERNAME is missing or commented out in .env.\n";
    } else {
        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "SUCCESS: Database connection to '$db' was successful!\n";
            
            // Check if tables exist
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "Tables found in database: " . count($tables) . "\n";
            if (count($tables) > 0) {
                echo implode(", ", $tables) . "\n";
            }
        } catch (PDOException $e) {
            echo "ERROR: Database connection failed.\n";
            echo "MySQL Error: " . $e->getMessage() . "\n";
            echo "Please check if the password in .env matches the CloudPanel database password exactly.\n";
        }
    }
}

echo "\n============================================\n";
echo "Please copy ALL of this text and send it to me.";
