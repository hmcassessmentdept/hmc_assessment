<?php
require "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("
        INSERT INTO proportionate_arrear_records 
        (application_number, applicant_name, proposed_holding_number, assessee_number, mother_holding_number, ward_number, street_name, arrear_date, mother_annual_valuation, proposed_annual_valuation, outstanding_due, due_up_to_qtr, calculated_due, calculated_due_words) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sssissssdddsds",
        $_POST['applicationNumber'],
        $_POST['applicantName'],
        $_POST['proposedHoldingNumber'],
        $_POST['assesseeNumber'],
        $_POST['motherHoldingNumber'],
        $_POST['wardNumber'],
        $_POST['streetName'],
        $_POST['arrearDate'],
        $_POST['motherAnnualValuation'],
        $_POST['proposedAnnualValuation'],
        $_POST['totalOutstandingDue'],
        $_POST['dueUpToQtr'],
        $_POST['calculatedDue'],
        $_POST['calculatedDueWords']
    );

    if ($stmt->execute()) {
        echo "Data saved successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
