<?php
header('Content-Type: application/json');

// Replace with your actual DB credentials
$host = 'localhost';
$db = 'hmc_assessment';
$user = 'root';
$pass = '0012';

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Get POST values
$refNo = $_POST['refNo'];
$appDate = $_POST['appDate'];
$appNo = $_POST['appNo'];
$applicantName = $_POST['applicantName'];
$proposedHoldingNo = $_POST['proposedHoldingNo'];
$motherAssesseeNo = $_POST['motherAssesseeNo'];
$streetName = $_POST['streetName'];
$wardNo = $_POST['wardNo'];
$motherHoldingNo = $_POST['motherHoldingNo'] ?? NULL;
$propertyDetails = $_POST['Property_Details'] ?? NULL;
$hearingDate = $_POST['hearingDate'];
$hearingTime = $_POST['hearingTime'];
$timestamp = $_POST['timestamp'] ?? date('Y-m-d H:i:s');

// Prepare and bind SQL statement
$stmt = $conn->prepare("
    INSERT INTO mutation_notices 
    (ref_no, app_date, app_no, applicant_name, proposed_holding_no, mother_assessee_no, street_name, ward_no, mother_holding_no, property_details, hearing_date, hearing_time, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssssssssss",
    $refNo,
    $appDate,
    $appNo,
    $applicantName,
    $proposedHoldingNo,
    $motherAssesseeNo,
    $streetName,
    $wardNo,
    $motherHoldingNo,
    $propertyDetails,
    $hearingDate,
    $hearingTime,
    $timestamp
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Notice saved successfully']);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
