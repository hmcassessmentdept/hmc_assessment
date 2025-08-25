<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = 'localhost';
$db = 'hmc_assessment';
$user = 'root';
$pass = '0012';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get ID from query
$id = $_GET['id'] ?? null;
if (!$id) {
    die("No record ID provided.");
}

// Fetch record by ID
$sql = "SELECT * FROM mutation_notices WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No record found.");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mutation Hearing Notice</title>
    <style>
        body {
            font-family: Georgia, serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 40px;
            color: #000;
        }

        .letter-content {
            background: #fff;
            padding: 40px;
            max-width: 850px;
            margin: auto;
            border: 1px solid #ccc;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        .letter-letterhead {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #444;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .letter-letterhead img {
            width: 1000px;
            height: 150px;
        }

        .letter-letterhead div {
            margin-left: 20px;
        }

        .letter-letterhead h2 {
            margin: 0;
            font-size: 26px;
            color: #1d3557;
        }

        .letter-letterhead p {
            margin: 2px 0;
            font-size: 14px;
            color: #333;
        }

        .letter-ref-date {
            display: flex;
            justify-content: space-between;
            font-size: 15px;
            margin-bottom: 30px;
        }

        .output-fields-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            row-gap: 12px;
            column-gap: 50px;
            margin-bottom: 30px;
            font-size: 15px;
        }

        .output-group {
            display: flex;
        }

        .output-label {
            font-weight: bold;
            min-width: 180px;
            color: #000;
        }

        .letter-subject {
            margin: 25px 0 15px;
            font-weight: bold;
            font-size: 16px;
            text-decoration: underline;
        }

        .letter-body-text {
            font-size: 15px;
            line-height: 1.7;
            text-align: justify;
        }

        .letter-signature {
            margin-top: 50px;
            font-size: 15px;
            line-height: 1.6;
        }

        .letter-footer {
            margin-top: 60px;
            font-style: italic;
            font-size: 13px;
            text-align: center;
            color: #666;
        }

        .print-button-container {
            margin-top: 30px;
            text-align: center;
        }

        .print-button {
            background-color: #1d3557;
            color: white;
            border: none;
            padding: 10px 25px;
            font-size: 15px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }

        .print-button:hover {
            background-color: #0d253f;
        }

        @media print {
            .print-button-container {
                display: none;
            }
            body {
                background: white;
                padding: 0;
            }
            .letter-content {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
<div id="printableOutput">
    <div class="letter-content">
        <div class="letter-letterhead">
            <img src="HEADER.jpg" alt="HMC Logo">
            
        </div>

        <div class="letter-ref-date">
            <p>Ref No: <?= htmlspecialchars($row['ref_no']) ?></p>
            <p>Date: <?= isset($row['created_at']) ? date('d-m-Y', strtotime($row['created_at'])) : '' ?></p>
        </div>

        <div class="output-fields-grid">
            <div class="output-group"><span class="output-label">Application No:</span><span><?= htmlspecialchars($row['app_no']) ?></span></div>
            <div class="output-group"><span class="output-label">Applicant Name:</span><span><?= htmlspecialchars($row['applicant_name']) ?></span></div>
            <div class="output-group"><span class="output-label">Proposed Holding Number:</span><span><?= htmlspecialchars($row['proposed_holding_no']) ?></span></div>
            <div class="output-group"><span class="output-label">Mother Assessee Number:</span><span><?= htmlspecialchars($row['mother_assessee_no']) ?></span></div>
            <div class="output-group"><span class="output-label">Street Name:</span><span><?= htmlspecialchars($row['street_name']) ?></span></div>
            <div class="output-group"><span class="output-label">Ward Number:</span><span><?= htmlspecialchars($row['ward_no']) ?></span></div>
            <div class="output-group"><span class="output-label">Mother Holding Number (if any):</span><span><?= htmlspecialchars($row['mother_holding_no']) ?></span></div>
            <div class="output-group"><span class="output-label">Property Details:</span><span><?= htmlspecialchars($row['property_details']) ?></span></div>
        </div>

        <p class="letter-subject">Subject: Hearing Notice for Mutation Case</p>

        <div class="letter-body-text">
            Sir/Madam,<br><br>
            This is to inform you that your application for mutation of the property situated at the above-mentioned address under Ward No. <?= htmlspecialchars($row['ward_no']) ?> has been received and is under process.<br><br>
            You are hereby requested to attend a hearing regarding this matter at the Assessment Department, Howrah Municipal Corporation on <strong><?= htmlspecialchars($row['hearing_date']) ?></strong> at <strong><?= htmlspecialchars($row['hearing_time']) ?></strong> hours.<br><br>
            Please carry all relevant supporting documents such as <strong>deed papers, tax receipts, identity proof, and any other papers necessary</strong> for the mutation process.<br><br>
            In case of failure to attend the hearing on the scheduled date, your application may be processed on the basis of available documents or may be kept in abeyance.<br><br>
            Thank you for your attention to this matter.
        </div>

        <div class="letter-signature">
            Yours faithfully,<br><br><br>
            <strong>O.S.D., Assessment Department</strong><br>
            Howrah Municipal Corporation
        </div>

        <div class="letter-footer">
            This is a system-generated notice and does not require a physical signature.
        </div>

        <div class="print-button-container">
            <button class="print-button" onclick="window.print()">Print This Notice</button>
            <button class="print-button" onclick="downloadRecordsAsCsv()">Download All Records (CSV)</button>
        </div>
    </div>
</div>

<script>
    function downloadRecordsAsCsv() {
        window.location.href = 'download_csv.php';
    }
</script>
</body>
</html>