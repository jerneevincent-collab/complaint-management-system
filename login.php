<?php
session_start();
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body.login-page {
            margin: 0;
            min-height: 100vh;
            background: #0a2540;
            font-family: 'Poppins', sans-serif;
        }

        .login-header {
            background: #08213a;
            padding: 18px 40px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 3px solid #d4af37;
            animation: slideDown 0.5s ease;
        }
        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }

        .login-header .header-logo {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #fff;
        }
        .login-header span {
            color: #fff;
            font-weight: 600;
            font-size: 16px;
            letter-spacing: 0.02em;
        }

        .login-body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
        }

        .login-card {
            background: #ffffff;
            padding: 44px 40px;
            border-radius: 10px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.35);
            border-top: 4px solid #d4af37;
            opacity: 0;
            transform: translateY(24px);
            animation: fadeSlideUp 0.6s ease 0.15s forwards;
        }
        @keyframes fadeSlideUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .card-logo {
            display: block;
            width: 70px;
            height: 70px;
            margin: 0 auto 12px;
            animation: popIn 0.5s ease 0.3s both;
        }
        @keyframes popIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        .login-card h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 4px;
            color: #0a2540;
        }
        .login-subtitle {
            text-align: center;
            color: #64748b;
            font-size: 13px;
            margin-bottom: 28px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .login-card form {
            box-shadow: none;
            padding: 0;
            max-width: 100%;
            margin-top: 0;
        }

        .login-card input {
            transition: border-color 0.25s ease, box-shadow 0.25s ease, transform 0.15s ease;
        }
        .login-card input:focus {
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
            transform: translateY(-1px);
        }

        .login-card button {
            width: 100%;
            background: #0a2540;
            transition: background 0.25s ease, transform 0.15s ease;
        }
        .login-card button:hover {
            background: #d4af37;
            color: #0a2540;
            transform: translateY(-2px);
        }
        .login-card button:active {
            transform: translateY(0);
        }

        .error-msg {
            animation: shake 0.4s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }

        .complainant-link {
            text-align: center;
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #e2e8f0;
            font-size: 13px;
            color: #64748b;
        }
        .complainant-link a { color: #0a2540; font-weight: 600; }
    </style>
</head>
<body class="login-page">
    <div class="login-header">
        <img src="assets/cms-logo.png" alt="CMS Logo" class="header-logo">
        <span>Compliant Management System</span>
    </div>

    <div class="login-body">
        <div class="login-card">
            <img src="assets/cms-logo.png" alt="CMS Logo" class="card-logo">
            <h2>Personnel Login</h2>
            <p class="login-subtitle">Authorized Access Only</p>

            <?php if (isset($_GET['error'])): ?>
                <p class="error-msg" style="color:red;">Invalid email or password.</p>
            <?php endif; ?>

            <form action="process_login.php" method="POST">
                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <button type="submit">Login</button>
            </form>

            <p class="complainant-link">Are you a complainant? <a href="complainant_register.php">File a complaint here</a> — no login required.</p>
        </div>
    </div>
</body>
</html>