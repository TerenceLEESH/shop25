<?php
// Database credentials
$host = 'localhost';
$db   = 'peachmart';
$user = 'root';
$pass = 'Lxyu1121';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
session_start();
function generateNonce() {
    $nonce = bin2hex(random_bytes(32));
    $_SESSION['nonce'] = $nonce;
    return $nonce;
}

function validateNonce($nonce) {
    if (isset($_SESSION['nonce']) && $_SESSION['nonce'] === $nonce) {
        unset($_SESSION['nonce']); // Consume the nonce so it can't be used again
        return true;
    }
    return false;
}

?>