<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: chat.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Clone - Sign Up</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #25D366, #128C7E);
        }
        .signup-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .signup-container h2 {
            color: #128C7E;
            margin-bottom: 1.5rem;
            font-size: 24px;
        }
        .signup-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        .signup-container button {
            width: 100%;
            padding: 12px;
            background: #25D366;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .signup-container button:hover {
            background: #1DA851;
        }
        .login-link {
            margin-top: 1rem;
            color: #128C7E;
            cursor: pointer;
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .signup-container {
                margin: 20px;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>WhatsApp Clone - Sign Up</h2>
        <input type="text" id="username" placeholder="Username" required>
        <input type="password" id="password" placeholder="Password" required>
        <button onclick="signup()">Sign Up</button>
        <div class="login-link" onclick="redirectToLogin()">Already have an account? Login</div>
    </div>
    <script>
        function signup() {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            fetch('auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=signup&username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'chat.php';
                } else {
                    alert(data.message);
                }
            });
        }
        function redirectToLogin() {
            window.location.href = 'index.php';
        }
    </script>
</body>
</html>
