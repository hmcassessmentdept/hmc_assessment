<?php
session_start();
include 'db_connect.php'; // Include your database connection file

// Check if the user is logged in, redirect to login if not
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Initialize variables for form fields to prevent undefined variable errors and for persistence
$newAssesseeId = '';
$asmntNo = '';
$premisesNo = ''; // Holding No.
$streetName = ''; // STREET_NAME
$wardNo = '';     // WARD_NO
$finalAssesseeName = ''; // Final_AssesseeName
$description = ''; // Description
$annualValue = ''; // AV (Annual Valuation)

// Manual entry fields (will not be pre-populated from DB search in this simplified setup)
$applicantDetails = '';
$applicantAddress = '';
$applicationNumber = '';
$applicationDate = '';
$approvedBy = '';
$approvalDate = '';
$mutationEffectDate = '';
$mutationType = '';
$memoDate = date('Y-m-d'); // Default to current date for Memo Date
$certificateIssuedBy = '';
$certificateIssuedAtLocation = '';

// New variables for certificate search
$searchCertId = '';
$searchFinalAssesseeId = ''; // Variable for searching by final_Assesseeid (maps to assessee_id in generated_certificates)
$certificateSearchResults = []; // Array to store results of certificate search

$message = ''; // For user feedback messages
$message_type = ''; // Type of message (success, danger, warning, info)

// Variable to hold data fetched from the database after a search
$mutationDataFromDB = null;

// Handle form submission (POST requests)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and get Assessee ID and Assessment No. from POST data for initial search
    $newAssesseeId = $conn->real_escape_string($_POST['new_assessee_id'] ?? '');
    $asmntNo = $conn->real_escape_string($_POST['asmnt_no'] ?? '');

    // Sanitize and get search criteria for existing certificates
    $searchCertId = $conn->real_escape_string($_POST['search_cert_id'] ?? '');
    $searchFinalAssesseeId = $conn->real_escape_string($_POST['search_final_assesseeid'] ?? ''); // Correctly getting the value

    // Determine which action (button) was triggered: 'search', 'generate', or 'search_certificate'
    $action = $_POST['action'] ?? '';

    if ($action === 'search') {
        // --- Logic for 'Search Data' button click (for new certificate generation) ---
        if (!empty($newAssesseeId) && !empty($asmntNo)) {
            // ONLY SELECTING THE SPECIFIED FIELDS FROM final_emut_data
            $sql = "SELECT Holding_No, STREET_NAME, WARD_NO, Final_AssesseeName, Description, `A.V.` FROM final_emut_data WHERE New_AssesseeId = ? AND Asmnt_No = ? LIMIT 1";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("ss", $newAssesseeId, $asmntNo);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $mutationDataFromDB = $result->fetch_assoc();
                    $message = "Assessee data loaded successfully. Please review and complete the form.";
                    $message_type = "success";
					
				

                    // Populate ONLY the specified form fields from the database
                    $premisesNo = htmlspecialchars($mutationDataFromDB['Holding_No'] ?? '');
                    $streetName = htmlspecialchars($mutationDataFromDB['STREET_NAME'] ?? '');
                    $wardNo = htmlspecialchars($mutationDataFromDB['WARD_NO'] ?? '');
                    $finalAssesseeName = htmlspecialchars($mutationDataFromDB['Final_AssesseeName'] ?? '');
                    $description = htmlspecialchars($mutationDataFromDB['Description'] ?? '');
                    $annualValue = htmlspecialchars($mutationDataFromDB['A.V.'] ?? '');

                } else {
                    $message = "No record found for the provided Assessee ID and Assessment No. Please enter all details manually.";
                    $message_type = "warning";
                    // Clear all fetched fields if no record is found
                    $premisesNo = ''; $streetName = ''; $wardNo = ''; $finalAssesseeName = '';
                    $description = ''; $annualValue = '';
                    // Manual fields remain as is or default
                    $applicantDetails = ''; $applicantAddress = ''; $applicationNumber = ''; $applicationDate = '';
                    $approvedBy = ''; $approvalDate = ''; $mutationEffectDate = ''; $mutationType = '';
                    $memoDate = date('Y-m-d'); $certificateIssuedBy = ''; $certificateIssuedAtLocation = '';
                }
                $stmt->close();
            } else {
                $message = "Database query preparation failed: " . $conn->error;
                $message_type = "danger";
                error_log("Error preparing statement: " . $conn->error);
            }
        } else {
            $message = "Please enter both Assessee ID and Assessment No. to search.";
            $message_type = "info";
        }
    } elseif ($action === 'generate') {
        // --- Logic for 'Generate Certificate' button click ---

        // Re-fetch only the required primary data from final_emut_data for insertion integrity
        $sql = "SELECT Holding_No, STREET_NAME, WARD_NO, Final_AssesseeName, Description, `A.V.`, New_AssesseeId, Asmnt_No FROM final_emut_data WHERE New_AssesseeId = ? AND Asmnt_No = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ss", $newAssesseeId, $asmntNo);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $mutationDataFromDB = $result->fetch_assoc();
            } else {
                $message = "Cannot generate certificate: Assessee primary data not found or invalid. Please search again.";
                $message_type = "danger";
                goto end_script;
            }
            $stmt->close();
        } else {
            $message = "Database error during re-fetch for generation: " . $conn->error;
            $message_type = "danger";
            error_log("Error during re-fetch for generation: " . $conn->error);
            goto end_script;
        }

        if ($mutationDataFromDB) {
            // Retrieve ALL form fields for insertion. These come from $_POST,
            // which includes both pre-filled (from search) and manually entered data.

            // Safely retrieve values from $_POST for manual fields
            $applicantDetails = $conn->real_escape_string($_POST['applicant_details'] ?? '');
            $applicantAddress = $conn->real_escape_string($_POST['applicant_address'] ?? '');
            $applicationNumber = $conn->real_escape_string($_POST['application_number'] ?? '');
            $applicationDate = $conn->real_escape_string($_POST['application_date'] ?? '');
            $approvedBy = $conn->real_escape_string($_POST['approved_by'] ?? '');
            $approvalDate = $conn->real_escape_string($_POST['approval_date'] ?? '');
            $mutationEffectDate = $conn->real_escape_string($_POST['mutation_effect_date'] ?? '');
            $mutationType = $conn->real_escape_string($_POST['mutation_type'] ?? '');
            $memoDate = $conn->real_escape_string($_POST['memo_date'] ?? date('Y-m-d'));
            $certificateIssuedBy = $conn->real_escape_string($_POST['certificate_issued_by'] ?? '');
            $certificateIssuedAtLocation = $conn->real_escape_string($_POST['certificate_issued_at_location'] ?? '');
            // For Annual Value and Description, use the value from the form,
            // as they were pre-filled but might have been edited.
            $annualValue = $conn->real_escape_string($_POST['annual_value'] ?? '');
            $description = $conn->real_escape_string($_POST['description'] ?? '');


            // Prepare all data for insertion into the 'generated_certificates' table
            // Primary data from database fetch:
            $insert_assessee_id = $mutationDataFromDB['New_AssesseeId'];
            $insert_assessment_no = $mutationDataFromDB['Asmnt_No'];
            $insert_holding_no = $mutationDataFromDB['Holding_No'] ?? null;
            $insert_street_name = $mutationDataFromDB['STREET_NAME'] ?? null;
            $insert_ward_no = $mutationDataFromDB['WARD_NO'] ?? null;
            $insert_final_assessee_name = $mutationDataFromDB['Final_AssesseeName'] ?? null;
            $insert_annual_value = $annualValue; // From $_POST (pre-filled or edited)
            $insert_description = $description;    // From $_POST (pre-filled or edited)

            // Manual entry data from $_POST:
            $insert_memo_date = $memoDate;
            $insert_applicant_details = $applicantDetails;
            $insert_applicant_address = $applicantAddress;
            $insert_application_number = $applicationNumber;
            $insert_application_date = $applicationDate;
            $insert_approved_by = $approvedBy;
            $insert_approval_date = $approvalDate;
            $insert_mutation_effect_date = $mutationEffectDate;
            $insert_mutation_type = $mutationType;
            $insert_certificate_issued_by = $certificateIssuedBy;
            $insert_certificate_issued_at = $certificateIssuedAtLocation;
            $insert_generated_by = $_SESSION['username'] ?? 'Unknown';

            // SQL query to insert into generated_certificates table
            $insertSql = "INSERT INTO generated_certificates (
                                    assessee_id, assessment_no, holding_no, street_name, ward_no,
                                    final_assessee_name, annual_value, description,
                                    memo_date, applicant_details, applicant_address,
                                    application_number, application_date, approved_by, approval_date,
                                    mutation_effect_date, mutation_type, Certificate_issued_by, Certificate_issued_at,
                                    generated_by
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $insertStmt = $conn->prepare($insertSql);

            if ($insertStmt) { // Checks if prepare was successful
                $insertStmt->bind_param(
                    "ssssssds" . str_repeat("s", 12), // This generates a 20-character string (6 's' + 'd' + 13 's')
                    $insert_assessee_id,
                    $insert_assessment_no,
                    $insert_holding_no,
                    $insert_street_name,
                    $insert_ward_no,
                    $insert_final_assessee_name,
                    $insert_annual_value, // 7th variable, corresponding to 'd'
                    $insert_description,  // 8th variable, corresponding to 's'
                    $insert_memo_date,
                    $insert_applicant_details,
                    $insert_applicant_address,
                    $insert_application_number,
                    $insert_application_date,
                    $insert_approved_by,
                    $insert_approval_date,
                    $insert_mutation_effect_date,
                    $insert_mutation_type,
                    $insert_certificate_issued_by,
                    $insert_certificate_issued_at,
                    $insert_generated_by
                );

                if ($insertStmt->execute()) { // Attempt to execute the prepared and bound statement
                    $newCertificateId = $insertStmt->insert_id;
                    header("Location: print_certificate.php?id=" . $newCertificateId);
                    exit();
                } else {
                    $message = "Error saving certificate record: " . $insertStmt->error;
                    $message_type = "danger";
                    error_log("Error inserting generated certificate: " . $insertStmt->error);
                }
                $insertStmt->close(); // Close statement if it was prepared
            } else { // Handle database insert statement preparation failure
                $message = "Database insert statement preparation failed: " . $conn->error;
                $message_type = "danger";
                error_log("Error preparing insert statement for generated certificates: " . $conn->error);
            }
        } // End of if ($mutationDataFromDB)
    } elseif ($action === 'search_certificate') {
        // --- Logic for 'Search Certificate' button click (for existing certificates) ---
        if (!empty($searchCertId) || !empty($searchFinalAssesseeId)) {
            $searchSql = "SELECT certificate_id, assessee_id, assessment_no, final_assessee_name, memo_date FROM generated_certificates WHERE 1=1";
            $params = [];
            $types = '';

            if (!empty($searchCertId)) {
                $searchSql .= " AND certificate_id = ?";
                $params[] = $searchCertId;
                $types .= 'i'; // Assuming certificate_id is an integer
            }
            if (!empty($searchFinalAssesseeId)) {
                $searchSql .= " AND assessee_id LIKE ?"; // Searching by assessee_id which is the equivalent of final_Assesseeid in generated_certificates
                $params[] = "%" . $searchFinalAssesseeId . "%";
                $types .= 's';
            }

            $searchStmt = $conn->prepare($searchSql);

            if ($searchStmt) {
                if (!empty($params)) {
                    $searchStmt->bind_param($types, ...$params);
                }
                $searchStmt->execute();
                $searchResult = $searchStmt->get_result();

                if ($searchResult->num_rows > 0) {
                    $certificateSearchResults = $searchResult->fetch_all(MYSQLI_ASSOC);
                    $message = "Found " . count($certificateSearchResults) . " certificate(s).";
                    $message_type = "success";
                } else {
                    $message = "No certificates found matching your criteria.";
                    $message_type = "warning";
                }
                $searchStmt->close();
            } else {
                $message = "Database search preparation failed: " . $conn->error;
                $message_type = "danger";
                error_log("Error preparing certificate search statement: " . $conn->error);
            }
        } else {
            $message = "Please enter a Certificate ID or Final Assessee ID to search for existing certificates.";
            $message_type = "info";
        }
    }
} else {
    // --- Initial page load (GET request) ---
    $message = "Enter Assessee ID and Assessment No. and click 'Search' to load existing data.";
    $message_type = "info";
}

// This block ensures that form fields retain their values after POST or on GET if data was fetched
if ($mutationDataFromDB) {
    $newAssesseeId = htmlspecialchars($newAssesseeId);
    $asmntNo = htmlspecialchars($asmntNo);

    $premisesNo = htmlspecialchars($mutationDataFromDB['Holding_No'] ?? $premisesNo);
    $streetName = htmlspecialchars($mutationDataFromDB['STREET_NAME'] ?? $streetName);
    $wardNo = htmlspecialchars($mutationDataFromDB['WARD_NO'] ?? $wardNo);
    $finalAssesseeName = htmlspecialchars($mutationDataFromDB['Final_AssesseeName'] ?? $finalAssesseeName);
    $description = htmlspecialchars($mutationDataFromDB['Description'] ?? $description);
    $annualValue = htmlspecialchars($mutationDataFromDB['A.V.'] ?? $annualValue);
} else {
    // Only reset these if no data was fetched for new certificate generation
    // IMPORTANT: Do NOT clear search input values here
    if ($action !== 'search_certificate' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $premisesNo = ''; $streetName = ''; $wardNo = ''; $finalAssesseeName = '';
        $description = ''; $annualValue = '';
        $applicantDetails = ''; $applicantAddress = ''; $applicationNumber = ''; $applicationDate = '';
        $approvedBy = ''; $approvalDate = ''; $mutationEffectDate = ''; $mutationType = '';
        $memoDate = date('Y-m-d'); $certificateIssuedBy = ''; $certificateIssuedAtLocation = '';
    }
}


end_script:
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate/Search Mutation Certificate</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Your existing CSS (kept same for consistency) */
        :root {
            --primary-blue: #0056b3;
            --light-blue: #007bff;
            --success-green: #28a745;
            --warning-orange: #ffc107;
            --danger-red: #dc3545;
            --info-cyan: #17a2b8;
            --dark-gray: #2c3e50;
            --medium-gray: #6c757d;
            --light-gray: #f4f6f9;
            --border-color: #e0e0e0;
            --text-color: #333;
            --header-color: #1a2938;
            --white: #ffffff;
            --shadow-light: 0 6px 18px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 10px 25px rgba(0, 0, 0, 0.15);
            --deep-navy: #001f3f;
            --dark-blue: #003366;
            --light-text-color: #555;
            --purple-btn: #8a2be2;
            --purple-btn-hover: #6a1aae;
        }

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #eef2f3, #d9eaf7);
            color: var(--text-color);
            line-height: 1.6;
        }

        header {
            background: linear-gradient(to right, var(--primary-blue), #003d82);
            color: var(--white);
            padding: 5px 70px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h2 {
            margin: 0;
            font-size: 2.5em;
            color: var(--white);
        }

        .top-right-buttons {
            display: flex;
            gap: 15px;
        }

        #shared-header-placeholder {
            background-color: var(--white);
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-light);
            min-height: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', sans-serif;
            color: var(--white);
            font-weight: 700;
            text-align: center;
        }

        #shared-header-placeholder img {
            filter: none;
            height: 140px;
            margin-bottom: 5px;
        }

        #shared-header-placeholder h1 {
            font-size: 2.8em;
            margin: 0;
            line-height: 1.2;
            color: var(--dark-blue);
        }

        #shared-header-placeholder p {
            font-size: 1.2em;
            margin: 0;
            color: var(--light-text-color);
            font-weight: 400;
        }

        .container {
            background-color: var(--white);
            padding: 35px;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            max-width: 800px;
            margin: 20px auto;
            animation: fadeIn 0.8s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2.page-title {
            color: var(--header-color);
            text-align: center;
            margin-bottom: 35px;
            font-weight: 600;
            font-size: 2.5em;
            padding-bottom: 12px;
            border-bottom: 3px solid var(--primary-blue);
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
        }

        .btn {
            padding: 12px 22px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            cursor: pointer;
            color: var(--white);
            display: inline-block;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, var(--primary-blue), #003d82);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-decoration: none; /* For anchor tags acting as buttons */
        }
        .btn:hover {
            transform: translateY(-2px) scale(1.03);
            box-shadow: var(--shadow-hover);
            opacity: 0.95;
        }
        .btn.purple {
            background: linear-gradient(135deg, var(--purple-btn), var(--purple-btn-hover));
        }
        .btn.purple:hover {
            background: linear-gradient(135deg, var(--purple-btn-hover), var(--purple-btn));
        }
        .btn.red { background: linear-gradient(135deg, #e63946, #b71c1c); }
        .btn.green { background: linear-gradient(135deg, #28a745, #1c7c31); }
        .btn.gray { background: linear-gradient(135deg, #6c757d, #495057); }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="date"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 6px rgba(0, 123, 255, 0.3);
            outline: none;
        }

        .form-actions {
            text-align: center;
            margin-top: 30px;
        }

        .form-actions .btn {
            margin: 0 10px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
        }
        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }
        .search-actions {
            text-align: right; /* Align search button to the right */
            margin-top: 10px;
            margin-bottom: 20px;
        }

        /* Styles for search results table */
        .search-results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .search-results-table th, .search-results-table td {
            border: 1px solid var(--border-color);
            padding: 10px;
            text-align: left;
        }
        .search-results-table th {
            background-color: var(--light-gray);
            font-weight: 600;
            color: var(--header-color);
        }
        .search-results-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .search-results-table tr:hover {
            background-color: #f1f1f1;
        }
        .search-results-table .action-cell {
            text-align: center;
            white-space: nowrap; /* Prevent button from wrapping */
        }
    </style>
</head>
<body>
    <div id="shared-header-placeholder">
        <img src="logo.jpg" alt="Howrah Municipal Corporation Logo">
        <h1>Howrah Municipal Corporation</h1>
        <p>Assessment Department</p>
        <p>4, Mahatma Gandhi Road, Howrah-711101</p>
        <p><strong> Website:www.myhmc.in</strong> </p>
    </div>

    <header>
        <h2>Assessment Data Management</h2>
        <div class="top-right-buttons">
            <a href="change_password.php" class="btn purple">Change Password</a>
            <a href="logout.php" class="btn red">Log Out</a>
        </div>
    </header>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <h2 class="page-title">Generate New Mutation Certificate</h2>

        <form method="POST" action="generate_mutation.php" class="form-section">
            <div class="data-section">
                <h4>Assessee/Assessment Details (for New Certificate)</h4>
                <div class="form-group">
                    <label for="new_assessee_id">Assessee ID: *</label>
                    <input type="text" id="new_assessee_id" name="new_assessee_id" value="<?php echo htmlspecialchars($newAssesseeId); ?>" required>
                </div>
                <div class="form-group">
                    <label for="asmnt_no">Assessment No: *</label>
                    <input type="text" id="asmnt_no" name="asmnt_no" value="<?php echo htmlspecialchars($asmntNo); ?>" required>
                </div>
                <div class="search-actions">
                    <button type="submit" name="action" value="search" class="btn">Search Data</button>
                </div>
            </div>

            <?php if ($mutationDataFromDB): ?>
                <hr>
                <h3>Fetched Property Details</h3>
                <div class="form-group">
                    <label for="premises_no">Premises No. (Holding No.):</label>
                    <input type="text" id="premises_no" value="<?php echo htmlspecialchars($premisesNo); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="street_name">Street Name:</label>
                    <input type="text" id="street_name" value="<?php echo htmlspecialchars($streetName); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="ward_no">Ward No.:</label>
                    <input type="text" id="ward_no" value="<?php echo htmlspecialchars($wardNo); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="final_assessee_name">Final Assessee Name:</label>
                    <input type="text" id="final_assessee_name" value="<?php echo htmlspecialchars($finalAssesseeName); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="annual_value_fetched">Annual Valuation (from DB):</label>
                    <input type="text" id="annual_value_fetched" value="<?php echo htmlspecialchars($annualValue); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="description_fetched">Description (from DB):</label>
                    <textarea id="description_fetched" rows="2" readonly><?php echo htmlspecialchars($description); ?></textarea>
                </div>
            <?php endif; ?>

            <hr>
            <h3>Certificate Details (Manual Entry/Override)</h3>
            <div class="form-group">
                <label for="memo_date">Memo Date:</label>
                <input type="date" id="memo_date" name="memo_date" value="<?php echo htmlspecialchars($memoDate); ?>">
            </div>
            <div class="form-group">
                <label for="applicant_details">Applicant/To (Shri/Smt.):</label>
                <textarea id="applicant_details" name="applicant_details" rows="2" placeholder="e.g., Biswanath Mondal / Tarunima Mondal"><?php echo htmlspecialchars($applicantDetails); ?></textarea>
            </div>
            <div class="form-group">
                <label for="applicant_address">Applicant Address:</label>
                <textarea id="applicant_address" name="applicant_address" rows="2" placeholder="e.g., 10/1/2, Dasrath Ghosh Lane"><?php echo htmlspecialchars($applicantAddress); ?></textarea>
            </div>
            <div class="form-group">
                <label for="application_number">Application Number (Reference Letter No.):</label>
                <input type="text" id="application_number" name="application_number" value="<?php echo htmlspecialchars($applicationNumber); ?>">
            </div>
            <div class="form-group">
                <label for="application_date">Application Date (Reference Letter Date):</label>
                <input type="date" id="application_date" name="application_date" value="<?php echo htmlspecialchars($applicationDate); ?>">
            </div>
            <div class="form-group">
                <label for="approved_by">Approved By:</label>
                <select id="approved_by" name="approved_by">
                    <option value="">Select Approver</option>
                    <option value="OSD (Assessment)" <?php echo ($approvedBy == 'OSD (Assessment)') ? 'selected' : ''; ?>>OSD (Assessment)</option>
                    <option value="OSD (Borough)" <?php echo ($approvedBy == 'OSD (Borough)') ? 'selected' : ''; ?>>OSD (Borough)</option>
                    <option value="Assistant Engineer" <?php echo ($approvedBy == 'Assistant Engineer') ? 'selected' : ''; ?>>Assistant Engineer</option>
                    <option value="Deputy Commissioner" <?php echo ($approvedBy == 'Deputy Commissioner') ? 'selected' : ''; ?>>Deputy Commissioner</option>
                    <option value="Commissioner" <?php echo ($approvedBy == 'Commissioner') ? 'selected' : ''; ?>>Commissioner</option>
                    <option value="Chairman, BoA" <?php echo ($approvedBy == 'Chairman, BoA') ? 'selected' : ''; ?>>Chairman, BoA</option>
                </select>
            </div>
            <div class="form-group">
                <label for="approval_date">Approval Date:</label>
                <input type="date" id="approval_date" name="approval_date" value="<?php echo htmlspecialchars($approvalDate); ?>">
            </div>
            <div class="form-group">
                <label for="mutation_effect_date">Mutation Effect Date (Quarter Start):</label>
                <input type="date" id="mutation_effect_date" name="mutation_effect_date" value="<?php echo htmlspecialchars($mutationEffectDate); ?>">
                <small>e.g., 01/04/2023 for Quarter 2023-2024</small>
            </div>
            <div class="form-group">
                <label for="mutation_type">Mutation Type:</label>
                <select id="mutation_type" name="mutation_type">
                    <option value="">Select Type</option>
                    <option value="Absolute" <?php echo ($mutationType == 'Absolute') ? 'selected' : ''; ?>>Absolute</option>
                    <option value="apportioned" <?php echo ($mutationType == 'apportioned') ? 'selected' : ''; ?>>Apportioned</option>
                    <option value="amalgamated" <?php echo ($mutationType == 'amalgamated') ? 'selected' : ''; ?>>Amalgamated</option>
                    <option value="separated" <?php echo ($mutationType == 'separated') ? 'selected' : ''; ?>>Separated</option>
                </select>
            </div>
            <div class="form-group">
                <label for="annual_value">Annual Valuation:</label>
                <input type="text" id="annual_value" name="annual_value" value="<?php echo htmlspecialchars($annualValue); ?>">
            </div>
            <div class="form-group">
                <label for="description">Description (editable):</label>
                <textarea id="description" name="description" rows="2" placeholder="Enter property description here..."><?php echo htmlspecialchars($description); ?></textarea>
            </div>
            <div class="form-group">
                <label for="certificate_issued_by">Certificate Issued By:</label>
                <select id="certificate_issued_by" name="certificate_issued_by">
                    <option value="">Select Issuer</option>
                    <option value="Assistant Engineer" <?php echo ($certificateIssuedBy == 'Assistant Engineer') ? 'selected' : ''; ?>>Assistant Engineer</option>
                    <option value="OSD" <?php echo ($certificateIssuedBy == 'OSD') ? 'selected' : ''; ?>>OSD</option>
                </select>
            </div>
            <div class="form-group">
                <label for="certificate_issued_at_location">Certificate Issued At:</label>
                <select id="certificate_issued_at_location" name="certificate_issued_at_location">
                    <option value="">Select Location</option>
                    <option value="Assessment Department" <?php echo ($certificateIssuedAtLocation == 'Assessment Department') ? 'selected' : ''; ?>>Assessment Department</option>
                    <option value="Borough Office-I" <?php echo ($certificateIssuedAtLocation == 'Borough Office-I') ? 'selected' : ''; ?>>Borough Office-I</option>
                    <option value="Borough Office-II" <?php echo ($certificateIssuedAtLocation == 'Borough Office-II') ? 'selected' : ''; ?>>Borough Office-II</option>
                    <option value="Borough Office-III" <?php echo ($certificateIssuedAtLocation == 'Borough Office-III') ? 'selected' : ''; ?>>Borough Office-III</option>
                    <option value="Borough Office-IV" <?php echo ($certificateIssuedAtLocation == 'Borough Office-IV') ? 'selected' : ''; ?>>Borough Office-IV</option>
                    <option value="Borough Office-V" <?php echo ($certificateIssuedAtLocation == 'Borough Office-V') ? 'selected' : ''; ?>>Borough Office-V</option>
                    <option value="Borough Office-VI" <?php echo ($certificateIssuedAtLocation == 'Borough Office-VI') ? 'selected' : ''; ?>>Borough Office-VI</option>
                </select>
            </div>

            <hr>

            <div class="form-actions">
                <button type="submit" name="action" value="generate" class="btn green">Generate Certificate</button>
                <button type="button" class="btn gray" onclick="window.location.href='generate_mutation.php'">Clear Form</button>
                <a href="index.php" class="btn green">Back to Home</a>
            </div>
        </form>

        <hr style="margin: 40px 0;">

        <h2 class="page-title">Search Existing Certificates</h2>
        <form method="POST" action="generate_mutation.php" class="form-section">
            <div class="data-section">
                <h4>Search by Certificate ID or Final Assessee ID</h4>
                <div class="form-group">
                    <label for="search_cert_id">Certificate ID:</label>
                    <input type="text" id="search_cert_id" name="search_cert_id" value="<?php echo htmlspecialchars($searchCertId); ?>">
                </div>
                <div class="form-group">
                    <label for="search_final_assesseeid">Final Assessee ID:</label>
                    <input type="text" id="search_final_assesseeid" name="search_final_assesseeid" value="<?php echo htmlspecialchars($searchFinalAssesseeId); ?>">
                </div>
                <div class="search-actions">
                    <button type="submit" name="action" value="search_certificate" class="btn">Search Certificate</button>
                </div>
            </div>
        </form>

        <?php if (!empty($certificateSearchResults)): ?>
            <hr>
            <h3>Search Results</h3>
            <table class="search-results-table">
                <thead>
                    <tr>
                        <th>Certificate ID</th>
                        <th>Assessee ID</th>
                        <th>Assessment No</th>
                        <th>Assessee Name</th>
                        <th>Memo Date</th>
                        <th class="action-cell">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certificateSearchResults as $cert): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cert['certificate_id']); ?></td>
                        <td><?php echo htmlspecialchars($cert['assessee_id']); ?></td>
                        <td><?php echo htmlspecialchars($cert['assessment_no']); ?></td>
                        <td><?php echo htmlspecialchars($cert['final_assessee_name']); ?></td>
                        <td><?php echo htmlspecialchars(date('d-M-Y', strtotime($cert['memo_date']))); ?></td>
                        <td class="action-cell">
                            <a href="print_certificate.php?id=<?php echo htmlspecialchars($cert['certificate_id']); ?>" class="btn green" target="_blank">Print</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>