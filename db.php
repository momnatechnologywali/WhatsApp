<?php
$host = 'localhost';
$dbname = 'dbuv8ho9pqawbm';
$username = 'uws1gwyttyg2r';
$password = 'k1tdlhq4qpsf';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
Syntax highlighting powered by GeSHi
Help Guide | License
