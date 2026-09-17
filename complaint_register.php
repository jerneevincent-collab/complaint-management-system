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
</head>
<body>
    <h2>Complaint Registration</h2>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo "<p style='color:green;'>Complaint filed successfully! Complaint Number: " . htmlspecialchars($_GET['complaint_number']) . "</p>"; ?>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'error'):
        $reason = isset($_GET['reason']) ? $_GET['reason'] : '';
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';
        if ($reason == 'futuredate') {
            echo "<p style='color:red;'>Date filed cannot be a future date.</p>";
        } elseif (!empty($msg)) {
            echo "<p style='color:red;'>" . htmlspecialchars($msg) . "</p>";
        } else {
            echo "<p style='color:red;'>Please fill out all required fields.</p>";
        }
    endif; ?>

    <form id="complaintForm" action="save_complaint.php" method="POST" enctype="multipart/form-data">
        <label>Complainant:</label><br>
        <select name="complainant_id" required>
            <option value="">-- Select Complainant --</option>
            <?php while ($row = $complainants->fetch_assoc()): ?>
                <option value="<?php echo $row['complainant_id']; ?>"><?php echo htmlspecialchars($row['full_name']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Category:</label><br>
        <select name="category_id" required>
            <option value="">-- Select Category --</option>
            <?php while ($row = $categories->fetch_assoc()): ?>
                <option value="<?php echo $row['category_id']; ?>"><?php echo htmlspecialchars($row['category_name']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Subject:</label><br>
        <input type="text" name="subject" required><br><br>

        <label>Description:</label><br>
        <textarea name="description" rows="4" required></textarea><br><br>

        <label>Date Filed:</label><br>
        <input type="date" name="date_filed" id="dateFiled" required><br><br>

        <label>Location:</label><br>
        <input type="text" name="location"><br><br>

        <label>Priority:</label><br>
        <select name="priority">
            <option value="low">Low</option>
            <option value="medium" selected>Medium</option>
            <option value="high">High</option>
        </select><br><br>

        <label>Supporting Document:</label><br>
        <input type="file" name="supporting_document"><br><br>

        <button type="submit">Submit Complaint</button>
    </form>

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