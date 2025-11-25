<?php
// Database configuratie
define('DB_HOST', 'localhost');
define('DB_NAME', 'badeendjes_shop');
define('DB_USER', 'badeendadmin'); // Pas aan naar jouw database gebruiker
define('DB_PASS', 'badeendpw'); // Pas aan naar jouw database wachtwoord
define('DB_CHARSET', 'utf8mb4');

// Database connectie met PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Database connectie mislukt: " . $e->getMessage());
}

// Sessie starten
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
