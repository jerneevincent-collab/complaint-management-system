
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: splash.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $specialization = trim($_POST['specialization']);

    $stmt = $conn->prepare(
        "UPDATE users SET full_name=?, email=?, contact_number=?, specialization=? WHERE user_id=?"
    );

    $stmt->bind_param(
        "ssssi",
        $full_name,
        $email,
        $contact_number,
        $specialization,
        $user_id
    );

    $stmt->execute();
    $stmt->close();

    $_SESSION['full_name'] = $full_name;

    if (!empty($_POST['new_password'])) {
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        $pwStmt = $conn->prepare(
            "UPDATE users SET password=? WHERE user_id=?"
        );

        $pwStmt->bind_param("si", $hashed, $user_id);
        $pwStmt->execute();
        $pwStmt->close();
    }

    header("Location: manage_account.php?status=success");
    exit();
}

$stmt = $conn->prepare(
    "SELECT u.*, r.role_name, d.department_name
     FROM users u
     LEFT JOIN roles r ON u.role_id = r.role_id
     LEFT JOIN departments d ON u.department_id = d.department_id
     WHERE u.user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    die("Account not found.");
}

function safeValue($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="assets/style.css">

    <style>
        :root {
            --navy: #0a2540;
            --accent: #2f6fed;
            --page-bg: #f4f7fb;
            --border: #e2e8f0;
            --muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: var(--page-bg) !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }

        /* TOPBAR */

        .topbar {
            position: relative;
            overflow: hidden;
            background: var(--navy) !important;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 14px 30px;
            width: 100% !important;
            max-width: none !important;
            box-shadow: 0 3px 15px rgba(10, 37, 64, 0.10);
            animation: topbarReveal 0.5s ease both;
        }

        .topbar a {
            position: relative;
            color: #cbd5e1;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.25s ease, transform 0.25s ease;
        }

        .topbar a:hover {
            color: #fff;
            transform: translateY(-1px);
        }

        .topbar a:focus-visible {
            outline: 2px solid #93c5fd;
            outline-offset: 5px;
        }

        @keyframes topbarReveal {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* PAGE LAYOUT */

        .page-wrap {
            max-width: 520px !important;
            width: 90% !important;
            margin: 40px auto !important;
            padding: 0 !important;
            animation: pageReveal 0.6s ease both;
        }

        @keyframes pageReveal {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ACCOUNT CARD */

        .account-card {
            position: relative;
            overflow: hidden;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(10, 37, 64, 0.08);
            padding: 30px;
            width: 100% !important;
            max-width: 100% !important;
            opacity: 0;
            transform: translateY(20px) scale(0.985);
            animation: fadeUp 0.65s cubic-bezier(0.2, 0.7, 0.3, 1) 0.1s forwards;
            transition: box-shadow 0.3s ease;
        }

        .account-card:hover {
            box-shadow: 0 16px 38px rgba(10, 37, 64, 0.12);
        }

        /* TOP ACCENT LINE */

        .account-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(
                90deg,
                var(--navy),
                var(--accent),
                #75a5ff,
                var(--accent)
            );
            background-size: 200% 100%;
            animation: accentFlow 4s ease-in-out infinite;
        }

        @keyframes accentFlow {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }

        @keyframes fadeUp {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* ACCOUNT AVATAR */

        .avatar-wrap {
            position: relative;
            width: 88px;
            height: 88px;
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #edf4ff;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .avatar-wrap::before {
            content: "";
            position: absolute;
            inset: -5px;
            border: 1px solid rgba(47, 111, 237, 0.20);
            border-radius: 50%;
            transition: inset 0.3s ease, border-color 0.3s ease;
        }

        .avatar-wrap::after {
            content: "";
            position: absolute;
            width: 10px;
            height: 10px;
            right: 2px;
            bottom: 5px;
            background: #22c55e;
            border: 2px solid #fff;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.3);
        }

        .account-avatar {
            width: 68px !important;
            height: 68px !important;
            object-fit: contain;
            display: block;
            transition: transform 0.35s ease, filter 0.35s ease;
        }

        .avatar-wrap:hover {
            background: #e0ebff;
            transform: translateY(-3px);
        }

        .avatar-wrap:hover::before {
            inset: -8px;
            border-color: rgba(47, 111, 237, 0.55);
        }

        .avatar-wrap:hover .account-avatar {
            transform: scale(1.08);
            filter: drop-shadow(0 4px 7px rgba(47, 111, 237, 0.25));
        }

        /* PROFILE TEXT */

        .account-card h2 {
            color: var(--navy);
            text-align: center;
            margin: 0 0 5px;
            font-size: 22px;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .account-role {
            text-align: center;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
            margin: 0 0 25px;
        }

        /* SUCCESS MESSAGE */

        .success-msg {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 9px;
            color: #15803d !important;
            text-align: center;
            font-size: 13px;
            padding: 12px;
            margin: 0 0 18px;
            animation: successReveal 0.5s ease both;
        }

        .success-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 23px;
            height: 23px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #dcfce7;
            font-weight: 700;
            animation: checkPop 0.4s ease 0.2s both;
        }

        @keyframes successReveal {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes checkPop {
            from {
                transform: scale(0.5);
            }
            to {
                transform: scale(1);
            }
        }

        /* FORM */

        .account-card form {
            box-shadow: none !important;
            padding: 0 !important;
            max-width: 100% !important;
            margin: 0 !important;
            background: none !important;
        }

        .field-group {
            margin-bottom: 3px;
            animation: fieldReveal 0.45s ease both;
        }

        .field-group:nth-of-type(2) {
            animation-delay: 0.04s;
        }

        .field-group:nth-of-type(3) {
            animation-delay: 0.08s;
        }

        .field-group:nth-of-type(4) {
            animation-delay: 0.12s;
        }

        @keyframes fieldReveal {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .account-card label {
            font-size: 12px;
            font-weight: 600;
            color: var(--navy);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-top: 14px;
            margin-bottom: 6px;
            display: block;
        }

        .account-card input {
            display: block;
            background: #f4f7fb;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            color: var(--navy);
            padding: 11px 12px;
            width: 100%;
            min-height: 43px;
            transition:
                border-color 0.25s ease,
                box-shadow 0.25s ease,
                background 0.25s ease,
                transform 0.25s ease;
        }

        .account-card input:hover {
            border-color: #c5d5ee;
        }

        .account-card input:focus {
            outline: none;
            border-color: var(--accent);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(47, 111, 237, 0.12);
        }

        .account-card input::placeholder {
            color: #94a3b8;
            opacity: 1;
        }

        /* PASSWORD SECTION */

        .password-section {
            margin-top: 21px;
            padding-top: 17px;
            border-top: 1px solid var(--border);
        }

        .password-section p {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.6;
            margin: 0 0 8px;
        }

        .password-input-wrap {
            position: relative;
            width: 100%;
        }

        .password-input-wrap input {
            padding-right: 48px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            padding: 0;
            margin: 0;
            border: none;
            border-radius: 6px;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .toggle-password:hover {
            background: #e8efff;
            color: var(--accent);
        }

        .toggle-password:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 1px;
        }

        .toggle-password svg {
            width: 19px;
            height: 19px;
            display: block;
        }

        /* SAVE BUTTON */

        .account-card .save-button {
            position: relative;
            overflow: hidden;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            border: none;
            padding: 13px;
            border-radius: 8px;
            color: #fff;
            font-family: inherit;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition:
                background 0.25s ease,
                box-shadow 0.25s ease,
                transform 0.25s ease;
            margin-top: 23px;
        }

        .save-button::before {
            content: "";
            position: absolute;
            top: 0;
            left: -120%;
            width: 70%;
            height: 100%;
            background: linear-gradient(
                110deg,
                transparent,
                rgba(255, 255, 255, 0.22),
                transparent
            );
            transform: skewX(-20deg);
            transition: left 0.55s ease;
        }

        .save-button:hover {
            background: var(--navy);
            box-shadow: 0 7px 17px rgba(10, 37, 64, 0.18);
            transform: translateY(-2px);
        }

        .save-button:hover::before {
            left: 150%;
        }

        .save-button:active {
            transform: translateY(0) scale(0.99);
            box-shadow: none;
        }

        .save-button:focus-visible {
            outline: 3px solid rgba(47, 111, 237, 0.35);
            outline-offset: 3px;
        }

        .save-button svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
        }

        /* RESPONSIVE DESIGN */

        @media (max-width: 600px) {
            .topbar {
                padding: 14px 18px;
            }

            .page-wrap {
                width: 92% !important;
                margin: 25px auto !important;
            }

            .account-card {
                padding: 24px 20px;
                border-radius: 13px;
            }

            .account-card h2 {
                font-size: 20px;
            }

            .account-role {
                font-size: 12px;
            }
        }

        @media (max-width: 360px) {
            .topbar {
                padding: 13px 12px;
                font-size: 13px;
            }

            .account-card {
                padding: 22px 16px;
            }
        }

        /* ACCESSIBILITY: REDUCE MOTION */

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>

<body>

    <div class="topbar">
        <a href="index.php">&larr; Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">

        <div class="account-card">

            <div class="avatar-wrap">
                <img
                    src="assets/account.png"
                    alt="Account"
                    class="account-avatar"
                >
            </div>

            <h2><?php echo safeValue($user['full_name']); ?></h2>

            <p class="account-role">
                <?php echo safeValue($user['role_name']); ?>
                &middot;
                <?php echo safeValue($user['department_name']); ?>
            </p>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="success-msg" role="status" aria-live="polite">
                    <span class="success-icon" aria-hidden="true">&#10003;</span>
                    <span>Account updated successfully!</span>
                </div>
            <?php endif; ?>

            <form method="POST" id="accountForm">

                <div class="field-group">
                    <label for="full_name">Full Name</label>
                    <input
                        type="text"
                        id="full_name"
                        name="full_name"
                        value="<?php echo safeValue($user['full_name']); ?>"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="field-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo safeValue($user['email']); ?>"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="field-group">
                    <label for="contact_number">Contact Number</label>
                    <input
                        type="text"
                        id="contact_number"
                        name="contact_number"
                        value="<?php echo safeValue($user['contact_number']); ?>"
                        autocomplete="tel"
                    >
                </div>

                <div class="field-group">
                    <label for="specialization">Specialization</label>
                    <input
                        type="text"
                        id="specialization"
                        name="specialization"
                        value="<?php echo safeValue($user['specialization']); ?>"
                    >
                </div>

                <div class="password-section">

                    <p>
                        Leave blank to keep your current password.
                    </p>

                    <label for="new_password">New Password</label>

                    <div class="password-input-wrap">

                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            placeholder="Enter new password"
                            autocomplete="new-password"
                        >

                        <button
                            type="button"
                            class="toggle-password"
                            id="togglePassword"
                            aria-label="Show password"
                            aria-pressed="false"
                            title="Show password"
                        >
                            <svg
                                id="eyeIcon"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>

                    </div>
                </div>

                <button type="submit" class="save-button">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <path d="M17 21v-8H7v8"/>
                        <path d="M7 3v5h8"/>
                    </svg>

                    <span>Save Changes</span>
                </button>

            </form>

        </div>

    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('new_password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';

            passwordInput.type = isHidden ? 'text' : 'password';

            if (isHidden) {
                eyeIcon.innerHTML = `
                    <path d="M3 3l18 18"/>
                    <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/>
                    <path d="M9.9 5.2A10.7 10.7 0 0 1 12 5
                        c6.4 0 10 7 10 7a14.8 14.8 0 0 1-3.1 3.9"/>
                    <path d="M6.2 6.2C3.5 8 2 12 2 12
                        s3.6 7 10 7c1.2 0 2.3-.3 3.3-.7"/>
                `;

                togglePassword.setAttribute('aria-label', 'Hide password');
                togglePassword.setAttribute('title', 'Hide password');
                togglePassword.setAttribute('aria-pressed', 'true');
            } else {
                eyeIcon.innerHTML = `
                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                `;

                togglePassword.setAttribute('aria-label', 'Show password');
                togglePassword.setAttribute('title', 'Show password');
                togglePassword.setAttribute('aria-pressed', 'false');
            }
        });
    </script>

</body>
</html>
```