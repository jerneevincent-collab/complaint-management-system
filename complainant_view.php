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
    <title>Complainant Details</title>
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

        .page-wrap { max-width: 700px; margin: 0 auto; padding: 30px 20px; }

        .detail-card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 10px 30px rgba(10,37,64,0.08); padding: 30px;
            opacity: 0; transform: translateY(20px);
            animation: fadeUp 0.5s ease forwards;
        }
        @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

        .detail-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;
        }
        .detail-header h2 { margin: 0; color: var(--navy); font-size: 22px; }

        .status-badge {
            display: inline-block; padding: 6px 14px; border-radius: 20px;
            font-size: 12px; font-weight: 700; text-transform: uppercase;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-inactive { background: #fee2e2; color: #991b1b; }

        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .detail-item label {
            display: block; font-size: 11px; text-transform: uppercase;
            letter-spacing: 0.05em; color: #94a3b8; font-weight: 600; margin-bottom: 4px;
        }
        .detail-item .value { color: var(--navy); font-size: 15px; }
        .detail-item.full { grid-column: 1 / -1; }
    </style>
</head>
<body>
    <div class="topbar">
        <a href="complainant_list.php">&larr; Complainant List</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="page-wrap">
        <div class="detail-card">
            <div class="detail-header">
                <h2><?php echo htmlspecialchars($complainant['full_name']); ?></h2>
                <span class="status-badge status-<?php echo $complainant['status']; ?>"><?php echo htmlspecialchars($complainant['status']); ?></span>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <label>Contact Number</label>
                    <div class="value"><?php echo htmlspecialchars($complainant['contact_number']); ?></div>
                </div>
                <div class="detail-item">
                    <label>Email</label>
                    <div class="value"><?php echo htmlspecialchars($complainant['email']); ?></div>
                </div>
                <div class="detail-item full">
                    <label>Address</label>
                    <div class="value"><?php echo htmlspecialchars($complainant['address']); ?></div>
                </div>
                <div class="detail-item">
                    <label>Organization/Department</label>
                    <div class="value"><?php echo htmlspecialchars($complainant['organization_department']); ?></div>
                </div>
                <div class="detail-item">
                    <label>Preferred Contact Method</label>
                    <div class="value"><?php echo htmlspecialchars($complainant['preferred_contact_method']); ?></div>
                </div>
                <div class="detail-item">
                    <label>Date Registered</label>
                    <div class="value"><?php echo $complainant['date_registered']; ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>