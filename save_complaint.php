<?php


$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$complainant_id = $_POST['complainant_id'];
$category_id = $_POST['category_id'];
$subject = trim($_POST['subject']);
$description = trim($_POST['description']);
$date_filed = $_POST['date_filed'];
$location = trim($_POST['location']);
$priority = $_POST['priority'];

if (empty($complainant_id) || empty($category_id) || empty($subject) || empty($description) || empty($date_filed)) {
    header("Location: complaint_register.php?status=error&reason=required");
    exit();
}

if ($date_filed > date("Y-m-d")) {
    header("Location: complaint_register.php?status=error&reason=futuredate");
    exit();
}

if (isset($_FILES['supporting_document']) && $_FILES['supporting_document']['error'] == 0) {
    $allowedTypes = array('image/jpeg', 'image/png', 'application/pdf');
    $maxSize = 5242880;

    if (!in_array($_FILES['supporting_document']['type'], $allowedTypes)) {
        header("Location: complaint_register.php?status=error&msg=" . urlencode("Only JPG, PNG, or PDF files are allowed."));
        exit();
    }

    if ($_FILES['supporting_document']['size'] > $maxSize) {
        header("Location: complaint_register.php?status=error&msg=" . urlencode("File size must not exceed 5MB."));
        exit();
    }
}

$year = date("Y");
$result = $conn->query("SELECT COUNT(*) as total FROM complaints WHERE YEAR(created_at) = $year");
$count = $result->fetch_assoc()['total'] + 1;
$complaint_number = "CMS-" . $year . "-" . str_pad($count, 4, "0", STR_PAD_LEFT);

$stmt = $conn->prepare("INSERT INTO complaints (complaint_number, complainant_id, category_id, subject, description, date_filed, location, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Submitted')");
$stmt->bind_param("siisssss", $complaint_number, $complainant_id, $category_id, $subject, $description, $date_filed, $location, $priority);

if ($stmt->execute()) {
    $complaint_id = $stmt->insert_id;

    if (isset($_FILES['supporting_document']) && $_FILES['supporting_document']['error'] == 0) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = time() . "_" . basename($_FILES['supporting_document']['name']);
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['supporting_document']['tmp_name'], $target_path)) {
            $stmt2 = $conn->prepare("INSERT INTO attachments (complaint_id, file_name, file_path) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $complaint_id, $file_name, $target_path);
            $stmt2->execute();
        }
    }

    header("Location: complaint_register.php?status=success&complaint_number=" . urlencode($complaint_number));
} else {
    header("Location: complaint_register.php?status=error&msg=" . urlencode("Database error. Please try again."));
}

$stmt->close();
$conn->close();
?>