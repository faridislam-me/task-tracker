<?php
/**
 * Database connection (PDO + MySQL)
 * ---------------------------------------------------------------------------
 * EDIT THESE CREDENTIALS to match your local MySQL / XAMPP setup.
 * On a default XAMPP install the user is "root" with an empty password.
 */
$DB_HOST = 'localhost';      // Database host (usually "localhost")
$DB_NAME = 'task_tracker';   // Database name (must match schema.sql)
$DB_USER = 'root';           // Database username
$DB_PASS = '';               // Database password ('' for default XAMPP)
$DB_CHARSET = 'utf8mb4';     // Character set
// ---------------------------------------------------------------------------

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // throw on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // assoc arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                    // real prepared statements
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // Don't leak credentials/stack traces to the browser in production.
    http_response_code(500);
    die('Database connection failed. Please check config/db.php and that MySQL is running.');
}
