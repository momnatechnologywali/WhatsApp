<?php
session_start();
require_once 'db.php';
 
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
 
$action = $_GET['action'] ?? $_POST['action'] ?? '';
 
try {
    if ($action === 'get_users') {
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id != ?");
        $stmt->execute([$_SESSION['user_id']]);
        $users = $stmt->fetchAll();
        if ($stmt->rowCount() === 0) {
            error_log("No users found for user_id: " . $_SESSION['user_id']);
        }
        echo json_encode($users);
    }
 
    if ($action === 'get_messages') {
        $recipient_id = $_GET['recipient_id'] ?? '';
        if (!$recipient_id) {
            echo json_encode([]);
            exit;
        }
        $stmt = $pdo->prepare("
            SELECT m.*, u.username 
            FROM messages m 
            JOIN users u ON u.id = m.sender_id 
            WHERE (m.sender_id = ? AND m.recipient_id = ?) OR (m.sender_id = ? AND m.recipient_id = ?)
            ORDER BY m.timestamp
        ");
        $stmt->execute([$_SESSION['user_id'], $recipient_id, $recipient_id, $_SESSION['user_id']]);
        $messages = $stmt->fetchAll();
 
        // Update message status to 'read'
        $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE sender_id = ? AND recipient_id = ? AND status != 'read'");
        $stmt->execute([$recipient_id, $_SESSION['user_id']]);
 
        echo json_encode($messages);
    }
 
    if ($action === 'send_message') {
        $recipient_id = $_POST['recipient_id'] ?? '';
        $message = $_POST['message'] ?? '';
        if (!$recipient_id || !$message) {
            echo json_encode(['success' => false, 'message' => 'Missing recipient or message']);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, recipient_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $recipient_id, $message]);
        echo json_encode(['success' => true]);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
