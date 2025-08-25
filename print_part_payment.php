<?php
// print_part_payment.php
require "db_connect.php"; // provides $conn = new mysqli(...)

// Accept either ?id=, or ?applicationNumber=&assesseeNumber=
$id          = isset($_GET['id']) ? trim($_GET['id']) : '';
$appNo       = isset($_GET['application_Number']) ? trim($_GET['application_Number']) : '';
$assesseeNo  = isset($_GET['assessee_Number']) ? trim($_GET['assessee_Number']) : '';

$sql    = "";
$params = [];
$types  = "";

// Prefer ID if given, else build from application/assessee
if ($id !== '') {
    $sql = "SELECT * FROM proportionate_arrear_records WHERE id = ?";
    $params[] = $id;
    $types   .= "i";
} else {
    $sql = "SELECT * FROM proportionate_arrear_records WHERE 1=1";
    if ($appNo !== '') {
        $sql .= " AND application_number = ?";
        $params[] = $appNo;
        $types   .= "s";
    }
    if ($assesseeNo !== '') {
        $sql .= " AND assessee_number = ?";
        $params[] = $assesseeNo;
        $types   .= "i";
    }
}

// Safety: require at least one filter
if ($sql === "" || ($id === '' && $appNo === '' && $assesseeNo === '')) {
    die("Missing search parameter. Provide ?id= or ?application_Number=&assessee_Number=.");
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL prepare failed: " . $conn->error);
}
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();
$stmt->close();

if (!$record) {
    die("No matching record found.");
}

// helpers
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function inr($n) { return number_format((float)$n, 2); }
function dmy($ymd) {
    if (!$ymd) return "";
    $ts = strtotime($ymd);
    return $ts ? date("d-m-Y", $ts) : h($ymd);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Part Payment Arrear Letter</title>
<style>
    body { font-family: Arial, sans-serif; margin: 36px; font-size: 14px; line-height: 1.6; color:#222; }
    .letter-header { text-align: center; margin-bottom: 16px; }
    .letter-header img { max-width: 100%; height: auto; }
    .meta { margin-top: 10px; margin-bottom: 16px; }
    .meta div { margin: 2px 0; }
    .subject { margin: 18px 0; font-weight: bold; text-decoration: underline; text-align: justify; }
    .field { margin: 6px 0; }
    .signature { margin-top: 60px; text-align: right; }
    .muted { color:#666; font-size: 12px; }
    @media print {
        .no-print { display:none; }
        body { margin: 12mm; }
    }
    .bar { height: 2px; background:#333; margin: 10px 0 20px; }
</style>

</head>
<body onload="window.print()">

<div class="letter-header">
    <img src="header.jpg" alt="Letter Header">
</div>
<div class="bar"></div>

<div class="meta">
 <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
    <div><strong>No:</strong> <?= h($record['id']) ?></div>
    <div><strong>Date:</strong> <?= h(date("d-m-Y")) ?></div>
</div>
    
   
</div>

<p>To,<br>
<strong><?= h($record['applicant_name']) ?></strong><br>
Proposed Holding No: <?= h($record['proposed_holding_number']) ?><br>
Ward No: <?= h($record['ward_number']) ?><br>
Street: <?= h($record['street_name']) ?><br>
Howrah
</p>

<<div class="subject" >
    Subject: Demand for Proportionate Arrear Amount of Property Tax
</div>

<p>Dear Sir/Madam,</p>

<p>
This is to inform you that, in connection with your mutation application 
(Application No.: <?= h($record['application_number']) ?>, 
Assessee No.: <?= h($record['assessee_number']) ?>), 
the proportionate arrear amount of property tax has been assessed. 
The Mother Holding No. is <?= h($record['mother_holding_number']) ?>, 
with arrears calculated as on <?= dmy($record['arrear_date']) ?> 
and due up to <?= h($record['due_up_to_qtr']) ?>. 
The Mother Annual Valuation is ₹<?= inr($record['mother_annual_valuation']) ?>, 
while the Proposed Annual Valuation is ₹<?= inr($record['proposed_annual_valuation']) ?>. 
The outstanding due without rebate amounts to ₹<?= inr($record['outstanding_due']) ?>, 
and the calculated proportionate due stands at ₹<?= inr($record['calculated_due']) ?> 
(<?= h($record['calculated_due_words']) ?>).
</p>

<p>
You are hereby requested to remit the above-mentioned amount at the Collection Department, 
Howrah Municipal Corporation. Please note that failure to make payment within the stipulated period 
may result in the mutation process being withheld until the dues are cleared.
</p>



<div class="signature">
    <strong>OSD(Assessment Department)</strong><br>
    Howrah Municipal Corporation
</div>
<p class="muted">This is a computer-generated letter.</p>
<!-- Optional: visible only on screen -->
<div class="no-print" style="margin-top:24px;">
    <button onclick="window.print()">Print</button>
    <button onclick="history.back()">Back</button>
</div>

</body>
</html>
