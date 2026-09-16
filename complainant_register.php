<!DOCTYPE html>
<html>
<head>
    <title>Register Complainant</title>
</head>
<body>
    <h2>Complainant Registration</h2>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'success') echo "<p style='color:green;'>Complainant registered successfully!</p>"; ?>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'error') echo "<p style='color:red;'>All required fields must be filled out.</p>"; ?>

    <form action="save_complainant.php" method="POST">
        <label>Full Name:</label><br>
        <input type="text" name="full_name" required><br><br>

        <label>Contact Number:</label><br>
        <input type="text" name="contact_number" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Address:</label><br>
        <input type="text" name="address" required><br><br>

        <label>Organization/Department:</label><br>
        <input type="text" name="organization_department" required><br><br>

        <label>Preferred Contact Method:</label><br>
        <select name="preferred_contact_method">
            <option value="email">Email</option>
            <option value="phone">Phone</option>
            <option value="sms">SMS</option>
        </select><br><br>

        <button type="submit">Register</button>
    </form>
</body>
</html>