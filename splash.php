<!DOCTYPE html>
<html>
<head>
    <title>Complaint Management System</title>
    <meta http-equiv="refresh" content="4;url=login.php">
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

        .splash-wrap { text-align: center; }

        .logo-ring {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 24px;
        }

        .logo-ring img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            animation: pulse 1.6s ease-in-out infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(47, 111, 237, 0.5); }
            70% { box-shadow: 0 0 0 22px rgba(47, 111, 237, 0); }
            100% { box-shadow: 0 0 0 0 rgba(47, 111, 237, 0); }
        }

        .spinner-ring {
            position: absolute;
            top: -8px;
            left: -8px;
            width: 146px;
            height: 146px;
            border: 3px solid transparent;
            border-top-color: #2f6fed;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .splash-title {
            color: #fff;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0.5px;
            opacity: 0;
            animation: fadeIn 0.6s ease 0.3s forwards;
        }

        .splash-subtitle {
            color: #94a3b8;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-top: 8px;
            opacity: 0;
            animation: fadeIn 0.6s ease 0.5s forwards;
        }

        @keyframes fadeIn { to { opacity: 1; } }
    </style>
</head>
<body>
    <div class="splash-wrap">
        <div class="logo-ring">
            <div class="spinner-ring"></div>
            <img src="assets/cms-logo.png" alt="CMS Logo">
        </div>
        <div class="splash-title">Complaint Management System</div>
        <div class="splash-subtitle">Saint Mary's University</div>
    </div>
</body>
</html>