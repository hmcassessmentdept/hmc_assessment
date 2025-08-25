<?php
// Turn on error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db_connect.php';

if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "DB connection error", "error" => $conn->connect_error]);
    exit();
}

// GET: Fetch records
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 100;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    $sql = "SELECT * FROM mutation_records ORDER BY application_date ASC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);

    if ($result) {
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        echo json_encode($records);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Query failed", "error" => $conn->error]);
    }
}

// POST: Insert new record
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid JSON"]);
        exit();
    }

    $refNo = $data['refNo'] ?? '';
    $applicationDate = $data['application_date'] ?? '';
    $applicationNo = $data['application_no'] ?? '';
    $applicantName = $data['applicant_name'] ?? '';
    $proposedHoldingNo = $data['proposed_holding_no'] ?? '';
    $motherAssesseeNo = $data['mother_assessee_no'] ?? '';
    $streetName = $data['street_name'] ?? '';
    $wardNo = $data['ward_no'] ?? '';
    $motherHoldingNo = $data['mother_holding_no'] ?? null;
    $propertyDetails = $data['Property_Details'] ?? '';
    $hearingDate = $data['hearing_date'] ?? '';
    $hearingTime = $data['hearing_time'] ?? '';

    // Simple validation
    if (!$refNo || !$applicationDate || !$applicationNo || !$applicantName || !$hearingDate || !$hearingTime) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"]);
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO mutation_records 
        (ref_no, application_date, application_no, applicant_name, proposed_holding_no,
         mother_assessee_no, street_name, ward_no, mother_holding_no, Property_Details,
         hearing_date, hearing_time)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
