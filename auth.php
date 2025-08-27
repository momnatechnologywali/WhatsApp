<?php
session_start();
require_once 'db.php';
 
$action = $_POST['action'] ?? '';
 
if ($action === 'signup') {
    $username = $_POST['username'];
    $password = $_POST['password'];
 
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        exit;
    }
 
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashedPassword]);
 
    $_SESSION['user_id'] = $pdo->lastInsertId();
    echo json_encode(['success' => true]);
}
 
if ($action === 'login') {
    $username = $_POST['username'];
    $password = $_POST['password'];
 
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
 
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
}
?>
