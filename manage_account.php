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

    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, contact_number=?, specialization=? WHERE user_id=?");
    $stmt->bind_param("ssssi", $full_name, $email, $contact_number, $specialization, $user_id);
    $stmt->execute();

    $_SESSION['full_name'] = $full_name;

    if (!empty($_POST['new_password'])) {
        $hashed = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $pwStmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $pwStmt->bind_param("si", $hashed, $user_id);
        $pwStmt->execute();
    }

    header("Location: manage_account.php?status=success");
    exit();
}

$stmt = $conn->prepare("SELECT u.*, r.role_name, d.department_name FROM users u LEFT JOIN roles r ON u.role_id = r.role_id LEFT JOIN departments d ON u.department_id = d.department_id WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Account</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root { --navy: #0a2540; --accent: #2f6fed; }
        body { background: #f4f7fb; }

        .topbar {
            background: var(--navy); color: #fff; display: flex;
            align-items: center; justify-content: space-between; padding: 14px 30px;
        }
        .topbar a { color: #cbd5e1; font-weight: 500; }
        .topbar a:hover { color: #fff; }

        .page-wrap { max-width: 560px; margin: 0 auto; padding: 30px 20px; }

        .account-card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 10px 30px rgba(10,37,64,0.08); padding: 30px;
            opacity: 0; transform: translateY(20px);
            animation: fadeUp 0.5s ease forwards;
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .account-avatar {
            width: 72px; height: 72px; margin: 0 auto 12px; display: block;
        }
        .account-card h2 { color: var(--navy); text-align: center; margin: 0 0 4px; }
        .account-role {
            text-align: center; color: #64748b; font-size: 13px; margin-bottom: 24px;
        }

        .account-card form { box-shadow: none; padding: 0; max-width: 100%; margin: 0; background: none; }
        .account-card label {
            font-size: 12px; font-weight: 600; color: var(--navy);
            text-transform: uppercase; letter-spacing: 0.03em;
        }
        .account-card input {
            background: #f4f7fb; border: 1px solid #e2e8f0; border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }
        .account-card input:focus { outline: none; border-color: var(--accent); background: #fff; }

        .password-section {
            margin-top: 10px; padding-top: 16px; border-top: 1px solid #e2e8f0;
        }
        .password-section p { font-size: 12px; color: #94a3b8; margin: 0 0 4px; }

        .account-card button {
            background: var(--accent); width: 100%; border: none; padding: 13px;
            border-radius: 8px; color: #fff; font-weight: 700; cursor: pointer; transition: 0.2s;
        }
        .account-card button:hover { background: var(--navy); }

        .success-msg { color: #16a34a !important; text-align: center; }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="index.php">&larr; Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="account-card">
            <img src="assets/account.png" alt="Account" class="account-avatar">
            <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p class="account-role"><?php echo htmlspecialchars($user['role_name']); ?> &middot; <?php echo htmlspecialchars($user['department_name']); ?></p>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo "<p class='success-msg'>Account updated successfully!</p>"; ?>

            <form method="POST">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>

                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <label>Contact Number</label>
                <input type="text" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number']); ?>">

                <label>Specialization</label>
                <input type="text" name="specialization" value="<?php echo htmlspecialchars($user['specialization']); ?>">

                <div class="password-section">
                    <p>Leave blank to keep your current password</p>
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="••••••••">
                </div>

                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>