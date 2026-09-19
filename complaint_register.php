<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complainants = $conn->query("SELECT complainant_id, full_name FROM complainants ORDER BY full_name");
$categories = $conn->query("SELECT category_id, category_name FROM complaint_categories WHERE is_active = 1 ORDER BY category_name");
?>
<!DOCTYPE html>
<html>
<head>
    <title>File a Complaint</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        :root {
            --card-bg: #ffffff;
            --navy: #0a2540;
            --accent: #2f6fed;
            --text: #475569;
            --input-bg: #f4f7fb;
            --elastic: cubic-bezier(0.75, -0.5, 0.27, 1.55);
        }

        body.reg-page {
            background: linear-gradient(135deg, #eef2f9 0%, #dbe6f5 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            font-family: 'Poppins', sans-serif;
        }

        .reg-card {
            position: relative;
            width: 100%;
            max-width: 520px;
            background: var(--card-bg);
            border-radius: 26px;
            box-shadow: 0 30px 60px rgba(10, 37, 64, 0.15);
            border: 1px solid #e2e8f0;
            padding: 40px 36px;
            overflow: hidden;
            opacity: 0;
            transform: translateY(60px) scale(0.95);
            animation: revealCard 0.9s var(--elastic) 0.1s forwards;
        }
        @keyframes revealCard {
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .glow {
            position: absolute;
            top: -40px;
            right: -40px;
            width: 180px;
            height: 180px;
            background: var(--accent);
            filter: blur(90px);
            opacity: 0.18;
            pointer-events: none;
        }

        .reg-card .logo {
            display: block;
            width: 64px;
            height: 64px;
            margin: 0 auto 10px;
            border-radius: 50%;
            position: relative;
            z-index: 2;
        }

        .reg-card h2 {
            color: var(--navy);
            text-align: center;
            margin: 0 0 4px;
            font-size: 24px;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 2;
        }
        .reg-card .subtitle {
            color: var(--text);
            text-align: center;
            font-size: 13px;
            margin-bottom: 24px;
            position: relative;
            z-index: 2;
        }
        .reg-card .subtitle a { color: var(--accent); font-weight: 600; }

        .reg-card form {
            box-shadow: none;
            padding: 0;
            max-width: 100%;
            margin-top: 0;
            background: transparent;
            display: flex;
            flex-direction: column;
            gap: 16px;
            position: relative;
            z-index: 2;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
        }
        .input-group:nth-child(1) { animation-delay: 0.5s; }
        .input-group:nth-child(2) { animation-delay: 0.55s; }
        .input-group:nth-child(3) { animation-delay: 0.6s; }
        .input-group:nth-child(4) { animation-delay: 0.65s; }
        .input-group:nth-child(5) { animation-delay: 0.7s; }
        .input-group:nth-child(6) { animation-delay: 0.75s; }
        .input-group:nth-child(7) { animation-delay: 0.8s; }
        .input-group:nth-child(8) { animation-delay: 0.85s; }
        @keyframes fadeIn { to { opacity: 1; } }

        .input-group label {
            color: var(--navy);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 600;
        }
        .input-group input, .input-group select, .input-group textarea {
            background: var(--input-bg);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 13px 14px;
            color: var(--navy);
            outline: none;
            transition: 0.3s;
            font-family: 'Poppins', sans-serif;
            resize: vertical;
        }
        .input-group input:focus, .input-group select:focus, .input-group textarea:focus {
            border-color: var(--accent);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(47, 111, 237, 0.12);
        }

        .reg-card button {
            background: var(--navy);
            color: #fff;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 6px;
            transition: 0.3s;
            width: 100%;
            opacity: 0;
            animation: fadeIn 0.5s ease 0.9s forwards;
        }
        .reg-card button:hover {
            background: var(--accent);
            transform: scale(1.02);
        }

        .success-msg { color: #16a34a !important; text-align: center; font-size: 13px; position: relative; z-index: 2; }
        .error-msg { color: #dc2626 !important; text-align: center; font-size: 13px; position: relative; z-index: 2; }
    </style>
</head>
<body class="reg-page">
    <div class="reg-card">
        <div class="glow"></div>
        <img src="assets/cms-logo.png" alt="CMS Logo" class="logo">
        <h2>File a Complaint</h2>
        <p class="subtitle">New here? <a href="complainant_register.php">Register as a complainant first</a></p>

                <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div style="background: #ecfdf5; border: 2px dashed #16a34a; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 20px; position: relative; z-index: 2;">
                <p style="margin: 0 0 6px; color: #166534; font-size: 13px; text-transform: uppercase; letter-spacing: 0.05em;">Complaint Filed Successfully</p>
                <p style="margin: 0; font-size: 26px; font-weight: 700; color: #15803d; letter-spacing: 1px;"><?php echo htmlspecialchars($_GET['complaint_number']); ?></p>
                <p style="margin: 10px 0 0; font-size: 12px; color: #166534;">Save this number — you'll need it to track your complaint.</p>
            </div>
        <?php endif; ?>
               <?php if (isset($_GET['new_complainant'])) echo "<p class='success-msg'>Registered successfully! Now let's file your complaint.</p>"; ?>
       <?php if (isset($_GET['status']) && $_GET['status'] == 'error'):
            $reason = isset($_GET['reason']) ? $_GET['reason'] : '';
            $msg = isset($_GET['msg']) ? $_GET['msg'] : '';
            if ($reason == 'futuredate') {
                echo "<p class='error-msg'>Date filed cannot be a future date.</p>";
            } elseif (!empty($msg)) {
                echo "<p class='error-msg'>" . htmlspecialchars($msg) . "</p>";
            } else {
                echo "<p class='error-msg'>Please fill out all required fields.</p>";
            }
        endif; ?>

        <form id="complaintForm" action="save_complaint.php" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label>Complainant</label>
                                <select name="complainant_id" required>
                    <option value="">-- Select Complainant --</option>
                    <?php
                    $preselected = isset($_GET['new_complainant']) ? $_GET['new_complainant'] : null;
                    while ($row = $complainants->fetch_assoc()): ?>
                        <option value="<?php echo $row['complainant_id']; ?>" <?php if ($preselected == $row['complainant_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['full_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">-- Select Category --</option>
                    <?php while ($row = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $row['category_id']; ?>"><?php echo htmlspecialchars($row['category_name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Subject</label>
                <input type="text" name="subject" required>
            </div>

            <div class="input-group">
                <label>Description</label>
                <textarea name="description" rows="4" required></textarea>
            </div>

            <div class="input-group">
                <label>Date Filed</label>
                <input type="date" name="date_filed" id="dateFiled" required>
            </div>

            <div class="input-group">
                <label>Location</label>
                <input type="text" name="location">
            </div>

            <div class="input-group">
                <label>Priority</label>
                <select name="priority">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

            <div class="input-group">
                <label>Supporting Document</label>
                <input type="file" name="supporting_document">
            </div>

            <button type="submit">Submit Complaint</button>
        </form>
    </div>

    <script>
        document.getElementById('complaintForm').addEventListener('submit', function(e) {
            var dateFiled = document.getElementById('dateFiled').value;
            var today = new Date();
            var todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');

            if (dateFiled > todayStr) {
                alert('Date filed cannot be a future date.');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>