<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Loading...</title>
    <meta http-equiv="refresh" content="2;url=index.php">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: #0a2540;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
        }

        .loading-wrap {
            text-align: center;
        }

        .logo-ring {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 24px;
        }

        .logo-ring img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            animation: pulse 1.6s ease-in-out infinite;
            box-shadow: 0 0 0 0 rgba(47, 111, 237, 0.5);
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(47, 111, 237, 0.5); }
            70% { box-shadow: 0 0 0 20px rgba(47, 111, 237, 0); }
            100% { box-shadow: 0 0 0 0 rgba(47, 111, 237, 0); }
        }

        .spinner-ring {
            position: absolute;
            top: -8px;
            left: -8px;
            width: 136px;
            height: 136px;
            border: 3px solid transparent;
            border-top-color: #2f6fed;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            color: #fff;
            font-size: 16px;
            font-weight: 500;
            opacity: 0;
            animation: fadeIn 0.6s ease 0.3s forwards;
        }

        .loading-dots span {
            display: inline-block;
            animation: dotBounce 1.4s infinite;
        }
        .loading-dots span:nth-child(2) { animation-delay: 0.2s; }
        .loading-dots span:nth-child(3) { animation-delay: 0.4s; }

        @keyframes dotBounce {
            0%, 80%, 100% { opacity: 0.3; }
            40% { opacity: 1; }
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="loading-wrap">
        <div class="logo-ring">
            <div class="spinner-ring"></div>
            <img src="assets/cms-logo.png" alt="CMS Logo">
        </div>
        <p class="loading-text">Loading your dashboard<span class="loading-dots"><span>.</span><span>.</span><span>.</span></span></p>
    </div>
</body>
</html>