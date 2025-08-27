<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
 
// Temporary form to add a contact
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contact'])) {
    require_once 'db.php';
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        echo "<script>alert('Contact added successfully!'); window.location.reload();</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error adding contact: " . $e->getMessage() . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Clone - Chat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        body {
            background: #ECE5DD;
            display: flex;
            height: 100vh;
        }
        .container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .sidebar {
            width: 30%;
            background: #F0F2F5;
            border-right: 1px solid #ddd;
            overflow-y: auto;
        }
        .chat-list {
            padding: 10px;
        }
        .chat-item {
            display: flex;
            padding: 15px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }
        .chat-item:hover {
            background: #E8ECEF;
        }
        .chat-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .chat-item div {
            flex: 1;
        }
        .chat-item h4 {
            color: #111;
            font-size: 16px;
        }
        .chat-item p {
            color: #666;
            font-size: 14px;
        }
        .add-contact-form {
            padding: 10px;
            background: #E8ECEF;
        }
        .add-contact-form input {
            width: 70%;
            padding: 5px;
            margin-right: 5px;
        }
        .add-contact-form button {
            padding: 5px 10px;
            background: #25D366;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }
        .chat-area {
            width: 70%;
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            padding: 15px;
            background: #F0F2F5;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
        }
        .chat-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .chat-header h3 {
            color: #111;
            font-size: 18px;
        }
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png');
        }
        .message {
            margin: 10px 0;
            padding: 10px 15px;
            border-radius: 10px;
            max-width: 60%;
            font-size: 14px;
            position: relative;
        }
        .message.sent {
            background: #DCF8C6;
            margin-left: auto;
            text-align: right;
        }
        .message.received {
            background: #FFF;
            margin-right: auto;
        }
        .message .timestamp {
            font-size: 10px;
            color: #999;
            margin-top: 5px;
        }
        .message .status {
            font-size: 10px;
            color: #25D366;
        }
        .chat-input {
            display: flex;
            padding: 15px;
            background: #F0F2F5;
            border-top: 1px solid #ddd;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 20px;
            margin-right: 10px;
            font-size: 14px;
        }
        .chat-input button {
            padding: 10px;
            background: #25D366;
            border: none;
            border-radius: 20px;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        .chat-input button:hover {
            background: #1DA851;
        }
        @media (max-width: 800px) {
            .container {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                max-height: 40%;
            }
            .chat-area {
                width: 100%;
                height: 60%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="add-contact-form">
                <form method="POST">
                    <input type="text" name="username" placeholder="New contact username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" name="add_contact">Add Contact</button>
                </form>
            </div>
            <div class="chat-list" id="chat-list"></div>
        </div>
        <div class="chat-area">
            <div class="chat-header" id="chat-header">
                <img src="https://via.placeholder.com/40" alt="Profile">
                <h3 id="chat-username">Select a Contact</h3>
            </div>
            <div class="chat-messages" id="chat-messages"></div>
            <div class="chat-input">
                <input type="text" id="message-input" placeholder="Type a message...">
                <button onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>
    <script>
        let currentChatUser = null;
 
        function loadUsers() {
            fetch('chat_backend.php?action=get_users')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(users => {
                    const chatList = document.getElementById('chat-list');
                    chatList.innerHTML = '';
                    if (users.length === 0) {
                        chatList.innerHTML = '<p>No contacts available. Please sign up or add more users.</p>';
                    } else {
                        users.forEach(user => {
                            const div = document.createElement('div');
                            div.className = 'chat-item';
                            div.innerHTML = `
                                <img src="https://via.placeholder.com/40" alt="Profile">
                                <div>
                                    <h4>${user.username}</h4>
                                    <p>Last message...</p>
                                </div>
                            `;
                            div.onclick = () => selectChat(user.id, user.username);
                            chatList.appendChild(div);
                        });
                    }
                })
                .catch(error => {
                    const chatList = document.getElementById('chat-list');
                    chatList.innerHTML = `<p>Error loading contacts: ${error.message}. Check server or database.</p>`;
                });
        }
 
        function selectChat(userId, username) {
            currentChatUser = userId;
            document.getElementById('chat-username').textContent = username;
            loadMessages();
        }
 
        function loadMessages() {
            if (!currentChatUser) return;
            fetch(`chat_backend.php?action=get_messages&recipient_id=${currentChatUser}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(messages => {
                    const chatMessages = document.getElementById('chat-messages');
                    chatMessages.innerHTML = '';
                    if (messages.length === 0) {
                        chatMessages.innerHTML = '<p>No messages yet.</p>';
                    } else {
                        messages.forEach(msg => {
                            const div = document.createElement('div');
                            div.className = `message ${msg.sender_id == currentChatUser ? 'received' : 'sent'}`;
                            div.innerHTML = `
                                ${msg.message}
                                <div class="timestamp">${new Date(msg.timestamp).toLocaleTimeString()}</div>
                                <div class="status">${msg.status}</div>
                            `;
                            chatMessages.appendChild(div);
                        });
                    }
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                })
                .catch(error => {
                    document.getElementById('chat-messages').innerHTML = `<p>Error loading messages: ${error.message}</p>`;
                });
        }
 
        function sendMessage() {
            const message = document.getElementById('message-input').value;
            if (!currentChatUser || !message) {
                alert('Please select a contact and enter a message.');
                return;
            }
            fetch('chat_backend.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=send_message&recipient_id=${currentChatUser}&message=${encodeURIComponent(message)}`
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('message-input').value = '';
                    loadMessages();
                } else {
                    alert('Failed to send message: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => alert('Error sending message: ' + error.message));
        }
 
        // Polling for real-time updates
        setInterval(loadMessages, 2000);
        setInterval(loadUsers, 5000);
 
        // Initial load
        loadUsers();
    </script>
</body>
</html>
