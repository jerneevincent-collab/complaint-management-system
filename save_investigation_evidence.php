<?php
$conn = new mysqli("localhost", "root", "", "complaint_management_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$investigation_id = $_POST['investigation_id'];

if (isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] == 0) {
    $upload_dir = "uploads/evidence/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $file_name = time() . "_" . basename($_FILES['evidence_file']['name']);
    $target_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['evidence_file']['tmp_name'], $target_path)) {
        $stmt = $conn->prepare(
            "INSERT INTO investigation_evidence (investigation_id, file_name, file_path) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iss", $investigation_id, $file_name, $target_path);
        $stmt->execute();
    }
}

header("Location: investigation_evidence_form.php?investigation_id=" . $investigation_id . "&status=success");
exit();
?>
