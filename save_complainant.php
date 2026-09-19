<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$full_name = trim($_POST['full_name']);
$contact_number = trim($_POST['contact_number']);
$email = trim($_POST['email']);
$address = trim($_POST['address']);
$organization_department = trim($_POST['organization_department']);
$preferred_contact_method = $_POST['preferred_contact_method'];

// Activity 4: Validate required fields
if (empty($full_name) || empty($contact_number) || empty($email) || empty($address) || empty($organization_department)) {
    header("Location: complainant_register.php?status=error");
    exit();
}

// Activity 3: Insert operation
$stmt = $conn->prepare(
    "INSERT INTO complainants (full_name, contact_number, email, address, organization_department, preferred_contact_method)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("ssssss", $full_name, $contact_number, $email, $address, $organization_department, $preferred_contact_method);

// Activity 5: Display success/error messages
if ($stmt->execute()) {
    $new_complainant_id = $stmt->insert_id;
    header("Location: complaint_register.php?new_complainant=" . $new_complainant_id);
} else {
    header("Location: complainant_register.php?status=error");
}

$stmt->close();
$conn->close();
?>