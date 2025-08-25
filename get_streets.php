<?php
// get_streets.php
include 'db_connect.php';

header('Content-Type: application/json');

$ward_no = isset($_GET['ward_no']) ? intval($_GET['ward_no']) : 0;

$streets = [];

if ($ward_no > 0) {
    $stmt = $conn->prepare("SELECT DISTINCT Street_Name FROM street_list WHERE Ward_No = ? ORDER BY Street_Name ASC");
    $stmt->bind_param("i", $ward_no);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $streets[] = $row['Street_Name'];
    }
    $stmt->close();
}

echo json_encode($streets);
$conn->close();
?>
