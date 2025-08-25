<?php
session_start();
// Include your database connection file
include 'db_connect.php';

// Check if the user is not logged in. If not, redirect to the login page.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// --- Retrieve parameters ---
// New Assessee ID is now the minimum requirement
$newAssesseeId = $_GET['new_assessee_id'] ?? '';
// Asmnt No is now OPTIONAL for report generation
$asmntNo = $_GET['asmnt_no'] ?? '';

// --- Validation: Only require New Assessee ID ---
if (empty($newAssesseeId)) {
    $_SESSION['message'] = "Error: New Assessee ID is required to generate a report.";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php"); // Redirect back to index page with error
    exit();
}

// --- Fetch Data from Database ---
// Construct the SQL query. It always filters by New_AssesseeId.
// It conditionally adds Asmnt_No as a further filter if it's provided.
$sql = "SELECT N, SL, Active_Status, Asmnt_No, WARD_NO, LocationIid, STREET_NAME, Holding_No, New_AssesseeId, Old_ULB_ID, Final_AssesseeName, HoldingType, GRFlag, `A.V.`, Effect_Date, Exemption, BIGHA, Katha, Chatak, `Sq.Ft.`, Ptax_Yrly, Hbtax_Yrly, Surch_Yrly, Ptax_qtrly, Hbtax_Qtrly, Surch_Qtrly, Description, Remarks, CreatedBy, CreatedAt, LastModifiedBy, LastModifiedAt, Apartment FROM final_emut_data WHERE New_AssesseeId = ?";
$params = [$newAssesseeId];
$types = "s"; // 's' for string (for New_AssesseeId)

if (!empty($asmntNo)) {
    $sql .= " AND Asmnt_No = ?";
    $params[] = $asmntNo;
    $types .= "s"; // Add 's' for Asmnt_No if it's included
}

// Prepare and execute the statement
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc(); // Fetch the single row of data
        // --- Report Generation HTML ---
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Assessment Report for <?php echo htmlspecialchars($data['New_AssesseeId'] ?? 'N/A'); ?></title>
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
            <style>
                body {
                    font-family: 'Roboto', Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                    background-color: #f4f6f9;
                }
                .report-container {
                    max-width: 900px;
                    margin: 20px auto;
                    padding: 30px;
                    border: 1px solid #ddd;
                    box-shadow: 0 0 15px rgba(0,0,0,0.1);
                    background-color: #fff;
                    border-radius: 8px;
                }
                .header-section {
                    text-align: center;
                    margin-bottom: 30px;
                    border-bottom: 2px solid #0056b3;
                    padding-bottom: 15px;
                }
                .header-section img {
                    height: 100px; /* Adjust logo size */
                    margin-bottom: 10px;
                }
                .header-section h1 {
                    color: #003366;
                    font-size: 2.2em;
                    margin: 0;
                }
                .header-section p {
                    font-size: 0.9em;
                    color: #555;
                    margin: 2px 0;
                }
                h2 {
                    color: #0056b3;
                    font-size: 1.5em;
                    margin-top: 25px;
                    margin-bottom: 15px;
                    border-bottom: 1px solid #eee;
                    padding-bottom: 5px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 15px;
                }
                th, td {
                    border: 1px solid #eee;
                    padding: 10px;
                    text-align: left;
                    font-size: 0.95em;
                }
                th {
                    background-color: #e9f5fd;
                    color: #003366;
                    font-weight: 500;
                    width: 35%; /* Adjust width for labels */
                }
                td {
                    background-color: #fff;
                    color: #333;
                }
                .print-button {
                    display: block;
                    width: fit-content;
                    margin: 30px auto 0;
                    padding: 12px 25px;
                    background-color: #007bff;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-size: 16px;
                    transition: background-color 0.3s ease;
                }
                .print-button:hover {
                    background-color: #0056b3;
                }
                @media print {
                    .print-button { display: none; }
                    body { background-color: #fff; margin: 0; padding: 0; }
                    .report-container {
                        box-shadow: none;
                        border: none;
                        margin: 0;
                        max-width: none;
                        padding: 0;
                    }
                }
            </style>
        </head>
        <body>
            <div class="report-container">
                <div class="header-section">
                    <img src="logo.jpg" alt="Howrah Municipal Corporation Logo">
                    <h1>Howrah Municipal Corporation</h1>
                    <p>Assessment Department</p>
                    <p>4, Mahatma Gandhi Road, Howrah-711101</p>
                    <p><strong>Website: www.myhmc.in</strong></p>
                    <hr style="border: 0; height: 1px; background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0)); margin-top: 20px;">
                    <h2 style="color: #0056b3; margin-top: 20px; font-size: 1.8em;">Assessment Data Report</h2>
                    <p style="font-size: 0.9em; color: #666;">Report Generated On: <?php echo date("d-M-Y H:i:s"); ?></p>
                </div>

                <h2>Basic Details</h2>
                <table>
                    <tr><th>New Assessee ID:</th><td><?php echo htmlspecialchars($data['New_AssesseeId'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Old ULB ID:</th><td><?php echo htmlspecialchars($data['Old_ULB_ID'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Asmnt No:</th><td><?php echo htmlspecialchars($data['Asmnt_No'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Final Assessee Name:</th><td><?php echo htmlspecialchars($data['Final_AssesseeName'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Active Status:</th><td><?php echo htmlspecialchars($data['Active_Status'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Holding Type:</th><td><?php echo htmlspecialchars($data['HoldingType'] ?? 'N/A'); ?></td></tr>
                    <tr><th>GR Flag:</th><td><?php echo htmlspecialchars($data['GRFlag'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Apartment:</th><td><?php echo htmlspecialchars($data['Apartment'] ?? 'N/A'); ?></td></tr>
                </table>

                <h2>Location Details</h2>
                <table>
                    <tr><th>Ward No:</th><td><?php echo htmlspecialchars($data['WARD_NO'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Street Name:</th><td><?php echo htmlspecialchars($data['STREET_NAME'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Holding No:</th><td><?php echo htmlspecialchars($data['Holding_No'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Location Id:</th><td><?php echo htmlspecialchars($data['LocationIid'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Description:</th><td><?php echo htmlspecialchars($data['Description'] ?? 'N/A'); ?></td></tr>
                </table>

                <h2>Assessment & Property Area Details</h2>
                <table>
                    <tr><th>A.V.:</th><td><?php echo htmlspecialchars($data['A.V.'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Effect Date:</th><td><?php echo htmlspecialchars($data['Effect_Date'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Exemption:</th><td><?php echo htmlspecialchars($data['Exemption'] ?? 'N/A'); ?></td></tr>
                    <tr><th>BIGHA:</th><td><?php echo htmlspecialchars($data['BIGHA'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Katha:</th><td><?php echo htmlspecialchars($data['Katha'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Chatak:</th><td><?php echo htmlspecialchars($data['Chatak'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Sq.Ft.:</th><td><?php echo htmlspecialchars($data['Sq.Ft.'] ?? 'N/A'); ?></td></tr>
                </table>

                <h2>Tax Details (Yearly)</h2>
                <table>
                    <tr><th>Ptax Yrly:</th><td><?php echo htmlspecialchars($data['Ptax_Yrly'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Hbtax Yrly:</th><td><?php echo htmlspecialchars($data['Hbtax_Yrly'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Surch Yrly:</th><td><?php echo htmlspecialchars($data['Surch_Yrly'] ?? 'N/A'); ?></td></tr>
                </table>

                <h2>Tax Details (Quarterly)</h2>
                <table>
                    <tr><th>Ptax Qtrly:</th><td><?php echo htmlspecialchars($data['Ptax_qtrly'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Hbtax Qtrly:</th><td><?php echo htmlspecialchars($data['Hbtax_Qtrly'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Surch Qtrly:</th><td><?php htmlspecialchars($data['Surch_Qtrly'] ?? 'N/A'); ?></td></tr>
                </table>

                <h2>Admin & Remarks</h2>
                <table>
                    <tr><th>Remarks:</th><td><?php echo htmlspecialchars($data['Remarks'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Created By:</th><td><?php echo htmlspecialchars($data['CreatedBy'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Created At:</th><td><?php echo htmlspecialchars($data['CreatedAt'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Last Modified By:</th><td><?php echo htmlspecialchars($data['LastModifiedBy'] ?? 'N/A'); ?></td></tr>
                    <tr><th>Last Modified At:</th><td><?php echo htmlspecialchars($data['LastModifiedAt'] ?? 'N/A'); ?></td></tr>
                </table>

                <button class="print-button" onclick="window.print()">Print Report</button>
            </div>
        </body>
        </html>
        <?php
    } else {
        // No data found for the provided IDs
        $_SESSION['message'] = "No record found for New Assessee ID: " . htmlspecialchars($newAssesseeId) . (!empty($asmntNo) ? " and Asmnt No: " . htmlspecialchars($asmntNo) : "") . ". Please check your input.";
        $_SESSION['message_type'] = "warning";
        header("Location: index.php");
        exit();
    }
    $stmt->close();
} else {
    // Error in preparing the statement
    $_SESSION['message'] = "Database error: Could not prepare statement for report generation. " . $conn->error;
    $_SESSION['message_type'] = "danger";
    error_log("Report generation SQL prepare error: " . $conn->error); // Log the error for debugging
    header("Location: index.php");
    exit();
}

$conn->close();
?>