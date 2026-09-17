<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die("Access denied. Administrator access only.");
}

$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_personnel'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $contact_number = trim($_POST['contact_number']);
    $role_id = $_POST['role_id'];
    $department_id = $_POST['department_id'];
    $specialization = trim($_POST['specialization']);
    $password = password_hash("password123", PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "INSERT INTO users (full_name, email, contact_number, role_id, department_id, specialization, password, status)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'active')"
    );
    $stmt->bind_param("sssiiss", $full_name, $email, $contact_number, $role_id, $department_id, $specialization, $password);
    $stmt->execute();

    header("Location: personnel_manage.php");
    exit();
}

if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $conn->prepare("UPDATE users SET status = IF(status='active','inactive','active') WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: personnel_manage.php");
    exit();
}

$roles = $conn->query("SELECT role_id, role_name FROM roles ORDER BY role_name");
$departments = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name");

$result = $conn->query(
    "SELECT u.*, r.role_name, d.department_name
     FROM users u
     LEFT JOIN roles r ON u.role_id = r.role_id
     LEFT JOIN departments d ON u.department_id = d.department_id
     ORDER BY u.user_id DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Personnel Management</title>
</head>
<body>
    <h2>Complaint-Handling Personnel</h2>
    <p><a href="index.php">&larr; Back to Home</a></p>

    <h3>Add New Personnel</h3>
    <form method="POST">
        <label>Full Name:</label><br>
        <input type="text" name="full_name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Contact Number:</label><br>
        <input type="text" name="contact_number"><br><br>

        <label>Role:</label><br>
        <select name="role_id" required>
            <?php $roles->data_seek(0); while ($r = $roles->fetch_assoc()): ?>
                <option value="<?php echo $r['role_id']; ?>"><?php echo htmlspecialchars($r['role_name']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Department:</label><br>
        <select name="department_id" required>
            <?php $departments->data_seek(0); while ($d = $departments->fetch_assoc()): ?>
                <option value="<?php echo $d['department_id']; ?>"><?php echo htmlspecialchars($d['department_name']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Specialization:</label><br>
        <input type="text" name="specialization"><br><br>

        <button type="submit" name="add_personnel">Add Personnel</button>
    </form>

    <h3>Personnel List</h3>
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Role</th>
            <th>Department</th>
            <th>Email</th>
            <th>Specialization</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['user_id']; ?></td>
            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
            <td><?php echo htmlspecialchars($row['role_name']); ?></td>
            <td><?php echo htmlspecialchars($row['department_name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['specialization']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td><a href="?toggle=<?php echo $row['user_id']; ?>"><?php echo $row['status'] == 'active' ? 'Deactivate' : 'Activate'; ?></a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>