<?php
// get_wards.php
include 'db_connect.php';

header('Content-Type: application/json');

$sql = "SELECT DISTINCT Ward_No FROM street_list ORDER BY Ward_No ASC";
$result = $conn->query($sql);

$wards = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $wards[] = $row['Ward_No'];
    }
}

echo json_encode($wards);
$conn->close();
?>
