<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM complainants WHERE complainant_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$complainant = $stmt->get_result()->fetch_assoc();

if (!$complainant) {
    die("Complainant not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Complainant</title>
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

        .form-card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 10px 30px rgba(10,37,64,0.08); padding: 30px;
            opacity: 0; transform: translateY(20px);
            animation: fadeUp 0.5s ease forwards;
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .form-card h2 { color: var(--navy); margin-top: 0; }
        .form-card form { box-shadow: none; padding: 0; max-width: 100%; margin: 0; background: none; }

        .form-card label {
            font-size: 12px; font-weight: 600; color: var(--navy);
            text-transform: uppercase; letter-spacing: 0.03em;
        }
        .form-card input, .form-card select {
            background: #f4f7fb; border: 1px solid #e2e8f0; border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }
        .form-card input:focus, .form-card select:focus {
            outline: none; border-color: var(--accent); background: #fff;
        }
        .form-card button {
            background: var(--accent); width: 100%; border: none; padding: 13px;
            border-radius: 8px; color: #fff; font-weight: 700; cursor: pointer; transition: 0.2s;
        }
        .form-card button:hover { background: var(--navy); }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="complainant_list.php">&larr; Complainant List</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="form-card">
            <h2>Edit Complainant</h2>

            <form action="update_complainant.php" method="POST">
                <input type="hidden" name="complainant_id" value="<?php echo $complainant['complainant_id']; ?>">

                <label>Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($complainant['full_name']); ?>" required>

                <label>Contact Number</label>
                <input type="text" name="contact_number" value="<?php echo htmlspecialchars($complainant['contact_number']); ?>" required>

                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($complainant['email']); ?>" required>

                <label>Address</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($complainant['address']); ?>" required>

                <label>Organization/Department</label>
                <input type="text" name="organization_department" value="<?php echo htmlspecialchars($complainant['organization_department']); ?>" required>

                <label>Status</label>
                <select name="status">
                    <option value="active" <?php if ($complainant['status'] == 'active') echo 'selected'; ?>>Active</option>
                    <option value="inactive" <?php if ($complainant['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
                </select>

                <button type="submit">Update</button>
            </form>
        </div>
    </div>
</body>
</html>