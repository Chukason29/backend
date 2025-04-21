<?php
$config = require __DIR__ . '/config.php';
// Database config
$host = $config['db']['host'];
$port = $config['db']['port'];
$dbname = $config['db']['database'];
$schema = $config['db']['schema'];
$user = $config['db']['username'];
$password = $config['db']['password'];
$charset = $config['db']['charset'];


// DSN
$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";


try {
    // Connect with PDO
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Error mode
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

} catch (PDOException $e) {
    echo "❌ Connection or query failed: " . $e->getMessage();
    exit;
}
?>
