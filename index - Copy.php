<?php
session_start(); // Start the session

// Check if the user is not logged in. If not, redirect to the login page.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php"); // Redirect to login page
    exit(); // Stop further script execution
}

// Include your database connection file.
include 'db_connect.php';

// Initialize search variables
$newAssesseeIdSearch = '';
$oldUlbIdSearch = '';
$wardNoSearch = '';
$streetNameSearch = '';
$holdingNoSearch = '';
$activeStatusSearch = '';

// Flag to check if any search has been performed
$isSearchPerformed = false;
$showNoResultsToast = false; // Flag for displaying "no results" toast

// --- Handle Search Queries from distinct input fields ---
if (isset($_GET['new_assessee_id']) && $_GET['new_assessee_id'] !== '') {
    $newAssesseeIdSearch = $conn->real_escape_string($_GET['new_assessee_id']);
    $isSearchPerformed = true;
}
if (isset($_GET['old_ulb_id']) && $_GET['old_ulb_id'] !== '') {
    $oldUlbIdSearch = $conn->real_escape_string($_GET['old_ulb_id']);
    $isSearchPerformed = true;
}
if (isset($_GET['ward_no']) && $_GET['ward_no'] !== '') {
    $wardNoSearch = $conn->real_escape_string($_GET['ward_no']);
    $isSearchPerformed = true;
}
if (isset($_GET['street_name']) && $_GET['street_name'] !== '') {
    $streetNameSearch = $conn->real_escape_string($_GET['street_name']);
    $isSearchPerformed = true;
}
if (isset($_GET['holding_no']) && $_GET['holding_no'] !== '') {
    $holdingNoSearch = $conn->real_escape_string($_GET['holding_no']);
    $isSearchPerformed = true;
}
if (isset($_GET['active_status']) && $_GET['active_status'] !== '') {
    $activeStatusSearch = $conn->real_escape_string($_GET['active_status']);
    $isSearchPerformed = true;
}


// --- Fetch unique WARD_NO values for dropdown ---
$ward_nos_result = $conn->query("SELECT DISTINCT WARD_NO FROM final_emut_data WHERE WARD_NO IS NOT NULL AND WARD_NO != '' ORDER BY WARD_NO ASC");
if ($ward_nos_result === FALSE) {
    error_log("Error fetching WARD_NOs: " . $conn->error);
    $ward_nos = [];
} else {
    $ward_nos = $ward_nos_result->fetch_all(MYSQLI_ASSOC);
}

// --- NEW PHP LOGIC: Fetch WARD_NO to STREET_NAME mapping for dynamic dropdowns ---
$ward_street_map = [];
$map_sql = "SELECT DISTINCT WARD_NO, STREET_NAME
            FROM final_emut_data
            WHERE WARD_NO IS NOT NULL AND WARD_NO != ''
            AND STREET_NAME IS NOT NULL AND STREET_NAME != ''
            ORDER BY WARD_NO ASC, STREET_NAME ASC";
$map_result = $conn->query($map_sql);

if ($map_result) {
    while ($row = $map_result->fetch_assoc()) {
        $ward = htmlspecialchars($row['WARD_NO']);
        $street = htmlspecialchars($row['STREET_NAME']);
        if (!isset($ward_street_map[$ward])) {
            $ward_street_map[$ward] = [];
        }
        $ward_street_map[$ward][] = $street;
    }
} else {
    error_log("Error fetching ward-street map for dropdowns: " . $conn->error);
}
// --- END NEW PHP LOGIC ---


// Initialize $result to null. It will only be populated if a search is performed.
$result = null;

// Only perform the database query if a search has been initiated
if ($isSearchPerformed) {
    // Construct the base SQL query to select from final_emut_data.
    $sql = "SELECT N, SL, Active_Status, Asmnt_No, WARD_NO, LocationIid, STREET_NAME, Holding_No, New_AssesseeId, Old_ULB_ID, Final_AssesseeName, HoldingType, GRFlag, `A.V.`, Effect_Date, Exemption, BIGHA, Katha, Chatak, `Sq.Ft.`, Ptax_Yrly, Hbtax_Yrly, Surch_Yrly, Ptax_qtrly, Hbtax_Qtrly, Surch_Qtrly, Description, Remarks, CreatedBy, CreatedAt, LastModifiedBy, LastModifiedAt, Apartment FROM final_emut_data";
    $conditions = []; // Array to hold WHERE clause conditions

    // --- Add individual search conditions ---
    if (!empty($newAssesseeIdSearch)) {
        $conditions[] = "New_AssesseeId LIKE '%$newAssesseeIdSearch%'";
    }
    if (!empty($oldUlbIdSearch)) {
        $conditions[] = "Old_ULB_ID LIKE '%$oldUlbIdSearch%'";
    }
    if (!empty($wardNoSearch)) {
        $conditions[] = "WARD_NO = '$wardNoSearch'"; // Exact match for dropdown
    }
    if (!empty($streetNameSearch)) {
        $conditions[] = "STREET_NAME = '$streetNameSearch'"; // Exact match for dropdown
    }
    if (!empty($holdingNoSearch)) {
        $conditions[] = "Holding_No LIKE '%$holdingNoSearch%'";
    }
    if ($activeStatusSearch !== '') {
        $conditions[] = "Active_Status = '" . $activeStatusSearch . "'";
    }


    // Combine all conditions with AND
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }
	
    // Order the results by the composite primary key.
    $sql .= " ORDER BY New_AssesseeId DESC, asmnt_No DESC";

    // Execute the SQL query.
    $result = $conn->query($sql);

    // --- START DEBUGGING CODE (VERY IMPORTANT TO SEE THE SQL ERROR) ---
    if ($result === FALSE) {
        error_log("MySQL Error in index.php query: " . $conn->error);
        echo "<p style='color: red; font-weight: bold;'>!!! DATABASE QUERY FAILED !!!</p>";
        echo "<p style='color: red;'><strong>MySQL Error:</strong> " . htmlspecialchars($conn->error) . "</p>";
        echo "<p style='color: red;'><strong>Failing SQL Query:</strong> <pre>" . htmlspecialchars($sql) . "</pre></p>";
        die("Please fix the error indicated above and check server logs.");
    } else {
        // NEW: Check for no rows and set toast flag
        if ($result->num_rows === 0) {
            $showNoResultsToast = true;
        }
    }
    // --- END DEBUGGING CODE ---
}

// Define the columns for the table, including their friendly names and corresponding DB column names
$tableColumns = [
    ['id' => 'col-N', 'header' => 'N', 'db_col' => 'N'],
    ['id' => 'col-SL', 'header' => 'SL', 'db_col' => 'SL'],
    ['id' => 'col-ActiveStatus', 'header' => 'Active Status', 'db_col' => 'Active_Status'],
    ['id' => 'col-AsmntNo', 'header' => 'Asmnt No', 'db_col' => 'Asmnt_No'],
    ['id' => 'col-WardNo', 'header' => 'Ward No', 'db_col' => 'WARD_NO'],
    ['id' => 'col-LocationIid', 'header' => 'Location Id', 'db_col' => 'LocationIid'],
    ['id' => 'col-StreetName', 'header' => 'Street Name', 'db_col' => 'STREET_NAME'],
    ['id' => 'col-HoldingNo', 'header' => 'Holding No', 'db_col' => 'Holding_No'],
    ['id' => 'col-NewAssesseeId', 'header' => 'New Assessee Id', 'db_col' => 'New_AssesseeId'],
    ['id' => 'col-OldULBID', 'header' => 'Old ULB ID', 'db_col' => 'Old_ULB_ID'],
    ['id' => 'col-FinalAssesseeName', 'header' => 'Final Assessee Name', 'db_col' => 'Final_AssesseeName'],
    ['id' => 'col-HoldingType', 'header' => 'HoldingType', 'db_col' => 'HoldingType'],
    ['id' => 'col-GRFlag', 'header' => 'GRFlag', 'db_col' => 'GRFlag'],
    ['id' => 'col-AV', 'header' => 'A.V.', 'db_col' => 'A.V.'],
    ['id' => 'col-EffectDate', 'header' => 'Effect Date', 'db_col' => 'Effect_Date'],
    ['id' => 'col-Exemption', 'header' => 'Exemption', 'db_col' => 'Exemption'],
    ['id' => 'col-Apartment', 'header' => 'Apartment', 'db_col' => 'Apartment'],
    ['id' => 'col-BIGHA', 'header' => 'BIGHA', 'db_col' => 'BIGHA'],
    ['id' => 'col-Katha', 'header' => 'Katha', 'db_col' => 'Katha'],
    ['id' => 'col-Chatak', 'header' => 'Chatak', 'db_col' => 'Chatak'],
    ['id' => 'col-SqFt', 'header' => 'Sq.Ft.', 'db_col' => 'Sq.Ft.'],
    ['id' => 'col-PtaxYrly', 'header' => 'Ptax Yrly', 'db_col' => 'Ptax_Yrly'],
    ['id' => 'col-HbtaxYrly', 'header' => 'Hbtax Yrly', 'db_col' => 'Hbtax_Yrly'],
    ['id' => 'col-SurchYrly', 'header' => 'Surch Yrly', 'db_col' => 'Surch_Yrly'],
    ['id' => 'col-PtaxQtrly', 'header' => 'Ptax Qtrly', 'db_col' => 'Ptax_qtrly'],
    ['id' => 'col-HbtaxQtrly', 'header' => 'Hbtax Qtrly', 'db_col' => 'Hbtax_Qtrly'],
    ['id' => 'col-SurchQtrly', 'header' => 'Surch Qtrly', 'db_col' => 'Surch_Qtrly'],
    ['id' => 'col-Description', 'header' => 'Description', 'db_col' => 'Description'],
    ['id' => 'col-Remarks', 'header' => 'Remarks', 'db_col' => 'Remarks'],
    ['id' => 'col-CreatedBy', 'header' => 'Created By', 'db_col' => 'CreatedBy'],
    ['id' => 'col-CreatedAt', 'header' => 'Created At', 'db_col' => 'CreatedAt'],
    ['id' => 'col-LastModifiedBy', 'header' => 'Last Modified By', 'db_col' => 'LastModifiedBy'],
    ['id' => 'col-LastModifiedAt', 'header' => 'Last Modified At', 'db_col' => 'LastModifiedAt'],
    ['id' => 'col-Actions', 'header' => 'Actions', 'db_col' => null]
];
$totalColumns = count($tableColumns); // Total columns for colspan

/**
 * Helper function to convert DB status values to display status ('Y' or 'N').
 * @param mixed $dbStatus The value retrieved from the Active_Status database column.
 * @return string 'Y' for active, 'N' for inactive.
 */
function dbStatusToDisplay($dbStatus) {
    // Explicitly check for 'Y' (case-insensitive)
    if (strtolower($dbStatus) === 'y') {
        return 'Y';
    }
    // Explicitly check for 'N' (case-insensitive)
    else if (strtolower($dbStatus) === 'n') {
        return 'N';
    }
    // Fallback for any other unexpected values, treat as inactive by default for safety
    return 'N';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Howrah Municipal Corporation - Assessment Database Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<style>
    /* Corporate King (Enhanced Stylish Theme) */
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
        --deep-navy: #001f3f; /* Added for header H1 color */
        --dark-blue: #003366; /* Added for H1 color */
        --light-text-color: #555; /* Added for address text */
        --purple-btn: #8a2be2; /* New variable for purple button */
        --purple-btn-hover: #6a1aae; /* New variable for purple button hover */
    }

    body {
        font-family: 'Roboto', sans-serif;
        margin: 0;
        padding: 0;
        background: linear-gradient(to right, #eef2f3, #d9eaf7); /* Gradient background */
        color: var(--text-color);
        line-height: 1.6;
    }

    /* Header Styles */
    header {
        background: linear-gradient(to right, var(--primary-blue), #003d82);
        color: var(--white);
        padding: 5px 70px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        margin-bottom: 20px;
        /* --- Key Flexbox Properties for Right Corner Alignment --- */
        display: flex; /* Makes the header a flex container */
        justify-content: space-between; /* Pushes items to opposite ends (h1 left, buttons right) */
        align-items: center; /* Vertically centers items within the header */
    }

    header h1 {
        margin: 0; /* Remove default margin from h1 inside header */
        font-size: 2.5em; /* Adjust as needed */
    }

    .top-right-buttons {
        display: flex; /* Makes the buttons inside side-by-side */
        gap: 15px;      /* Adds space between the buttons */
    }

    @keyframes slideInFromTop {
        from { opacity: 0; transform: translateY(-50px); }
        to { opacity: 1; transform: translateY(0); }
    }

    #shared-header-placeholder {
        background-color: var(--white); /* Changed to white */
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color); /* Light blue border */
        box-shadow: var(--shadow-light);
        min-height: 80px;
        display: flex;
        flex-direction: column; /* Stack items vertically */
        align-items: center;    /* Center items horizontally */
        justify-content: center; /* Center items vertically if there's extra space */
        font-family: 'Roboto', sans-serif;
        color: var(--white); /* Text color changed to deep navy */
        font-weight: 700;
        text-align: center; /* Ensures text inside is centered even if not flex-centered */
    }

    #shared-header-placeholder img {
        filter: none; /* Removed filter, so original logo color is visible */
        height: 140px; /* Adjust logo size as needed */
        margin-bottom: 5px; /* Space between logo and H1 */
    }

    #shared-header-placeholder h1 {
        font-size: 2.8em; /* Size for "Howrah Municipal Corporation" */
        margin: 0; /* Remove default margins */
        line-height: 1.2;
        color: var(--dark-blue); /* Changed H1 color to dark blue */
    }

    #shared-header-placeholder p {
        font-size: 1.2em; /* Size for address */
        margin: 0; /* Remove default margins */
        color: var(--light-text-color); /* Kept light text color */
        font-weight: 400; /* Lighter weight for address */
    }
    .scroll-down-button {
        background-color: var(--warning-orange);
        color: var(--dark-gray);
        padding: 15px 30px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1em;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .scroll-down-button:hover {
        background-color: #e0a800;
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
    }
    /* --- END NEW: Hero Section Styles --- */


    .container {
        background-color: var(--dark-gray);
        padding: 35px;
        border-radius: 15px;
        box-shadow: var(--shadow-light);
        max-width: 1100px;
        margin: 0 auto;
        overflow-x: auto;
        animation: fadeIn 0.8s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    h1 {
        color: var(--header-color);
        text-align: center;
        margin-bottom: 35px;
        font-weight: 600;
        font-size: 2.5em;
        padding-bottom: 12px;
        border-bottom: 3px solid var(--primary-blue);
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
    }
   h2 {
        color: var(--white);
        text-align: center;
        margin-bottom: 35px;
        font-weight: 600;
        font-size: 2.5em;
        padding-bottom: 10px;
        border-bottom: 3px solid var(--primary-blue);
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
		 }
    /* Stylish Buttons */
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
    }
    .btn:hover {
        transform: translateY(-2px) scale(1.03);
        box-shadow: var(--shadow-hover);
        opacity: 0.95;
    }

    /* Specific button colors */
    .btn.purple { /* New style for purple button */
        background: linear-gradient(135deg, var(--purple-btn), var(--purple-btn-hover));
    }
    .btn.purple:hover {
        background: linear-gradient(135deg, var(--purple-btn-hover), var(--purple-btn));
    }
    .btn.red { background: linear-gradient(135deg, #e63946, #b71c1c); }
    .btn.green { background: linear-gradient(135deg, #28a745, #1c7c31); }
    .btn.cyan { background: linear-gradient(135deg, #17a2b8, #11606b); }
    .btn.gray { background: linear-gradient(135deg, #6c757d, #495057); }
    .add-new { margin-top: 20px; text-decoration: none; }


    /* Search Box */
    .search-form-container {
        margin-top: 25px;
        margin-bottom: 35px;
        padding: 25px;
        background: #f1f8ff;
        border: 1px solid #cce5ff;
        border-radius: 12px;
        box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);
    }

    .search-inputs {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    .search-inputs input[type="text"],
    .search-inputs select {
        padding: 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 1em;
        transition: all 0.3s ease;
    }
    .search-inputs input[type="text"]:focus,
    .search-inputs select:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 6px rgba(0, 123, 255, 0.3);
        outline: none;
    }

    /* Table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
        font-size: 0.95em;
        background-color: var(--white);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-light);
    }
    th {
        background: linear-gradient(135deg, var(--primary-blue), #004494);
        color: var(--white);
        font-weight: 600;
        padding: 14px;
        text-transform: uppercase;
        font-size: 0.9em;
    }
    td {
        border-top: 1px solid var(--border-color);
        padding: 12px;
    }
    tbody tr:nth-child(even) { background-color: #f9fcff; }
    tbody tr:hover { background-color: #e8f1ff; transition: background 0.3s ease; }

    /* Table Actions */
    .actions {
        display: flex;
        gap: 8px;
    }
    .action-btn {
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 0.8em;
        color: var(--white);
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none; /* Ensure links look like buttons */
    }

    .action-btn.edit { background: linear-gradient(135deg, #007bff, #0056b3); }
    .action-btn.edit:hover { transform: scale(1.05); }
    .action-btn.inactivate { background: linear-gradient(135deg, #ffc107, #e0a800); color: #333; }
    .action-btn.inactivate:hover { transform: scale(1.05); }

    /* New style for disabled buttons (both Edit and Inactivate) */
    .action-btn.disabled {
        background: linear-gradient(135deg, #ccc, #aaa); /* Greyed out appearance */
        color: #666; /* Darker text */
        cursor: not-allowed; /* No-go cursor */
        pointer-events: none; /* Disables click events */
        opacity: 0.7; /* Slightly faded */
    }

    /* Column Visibility Panel */
    .column-visibility-controls {
        margin-bottom: 25px;
        padding: 20px;
        background: #e9f5fd;
        border: 1px solid #cce5ff;
        border-radius: 8px;
        display: none;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px;
    }
    .column-visibility-controls label {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.9em;
    }
    .column-visibility-controls input[type="checkbox"] {
        transform: scale(1.1);
    }
    .column-visibility-controls .control-buttons {
        grid-column: 1 / -1; /* Span all columns */
        display: flex;
        justify-content: flex-end;
        margin-top: 10px;
    }
    .column-visibility-controls .control-buttons button {
        padding: 8px 15px;
        border: 1px solid var(--primary-blue);
        border-radius: 5px;
        background-color: var(--primary-blue);
        color: var(--white);
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    .column-visibility-controls .control-buttons button:hover {
        background-color: #004494;
    }

    /* Animation Styles for the Table */
    .table-container-animated {
        opacity: 0; /* Start invisible */
        transform: translateY(20px); /* Start slightly below its final position */
        transition: opacity 0.6s ease-out, transform 0.6s ease-out; /* Smooth transition for both */
    }

    .table-container-animated.show {
        opacity: 1; /* Fully visible */
        transform: translateY(0); /* Move to its original position */
    }

    /* NEW: Styles for the "No Results" Toast Message */
    #noResultsToast {
        position: fixed; /* Or 'absolute' if you prefer it relative to a parent */
        top: 20px; /* Adjust vertical position */
        left: 50%;
        transform: translateX(-50%);
        background-color: var(--warning-orange); /* Or choose a color like #f44336 for error */
        color: var(--primary-blue);
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        z-index: 1000; /* Ensure it's on top of other content */
        opacity: 0; /* Start hidden */
        transition: opacity 0.5s ease-in-out, visibility 0.5s ease-in-out;
        visibility: hidden; /* Hide element completely when not visible */
        font-weight: bold;
        text-align: center;
        min-width: 250px;
    }

    #noResultsToast.show {
        opacity: 1;
        visibility: visible;
    }
    /* END NEW */

    /* Global Alert Messages */
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

    /* Modal Styles */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1001; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgba(0,0,0,0.6); /* Black w/ opacity */
        justify-content: center; /* Center horizontally */
        align-items: center; /* Center vertically */
        padding-top: 50px; /* Space from top */
    }

    .modal-content {
        background-color: #fefefe;
        margin: auto; /* For browsers that don't support flex centering */
        padding: 30px;
        border: 1px solid #888;
        width: 80%; /* Could be responsive */
        max-width: 500px; /* Max width */
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        position: relative;
        animation: fadeInModal 0.3s ease-out;
    }

    @keyframes fadeInModal {
        from { opacity: 0; transform: translateY(-30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .modal-content h2 {
        color: var(--primary-blue);
        margin-bottom: 15px;
        text-align: center;
    }

    .modal-content p {
        text-align: center;
        margin-bottom: 20px;
        color: var(--medium-gray);
    }

    .close-button {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        position: absolute;
        top: 10px;
        right: 15px;
        cursor: pointer;
    }

    .close-button:hover,
    .close-button:focus {
        color: #333;
        text-decoration: none;
        cursor: pointer;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-color);
    }

    .form-group textarea {
        width: calc(100% - 20px); /* Adjust for padding */
        padding: 10px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 1em;
        resize: vertical; /* Allow vertical resizing */
        min-height: 80px;
    }

    .modal .btn { /* Apply general btn styles to modal buttons */
        margin-top: 10px;
        margin-right: 10px;
    }

    .modal .cancel-button {
        background: linear-gradient(135deg, #6c757d, #495057); /* Gray color for cancel */
    }
    .modal .cancel-button:hover {
        background: linear-gradient(135deg, #495057, #6c757d);
    }


    @media (max-width: 768px) {
        h1 { font-size: 20 em; }
        table { font-size: 0.85em; }
        th, td { padding: 10px; }
        .actions { flex-direction: column; gap: 6px; }
        #noResultsToast {
            width: 90%; /* Make it wider on smaller screens */
            box-sizing: border-box; /* Include padding in width */
            left: 5%;
            transform: translateX(0);
        }
    }

</style>

</head>
<body>
    <div id="shared-header-placeholder">
        <img src="https://www.myhmc.in/wp-content/uploads/2020/01/Final-Logo.png" alt="Howrah Municipal Corporation Logo">
        <h1>Howrah Municipal Corporation</h1>
		 <p>Assessment Department</p>
        <p>4, Mahatma Gandhi Road, Howrah-711101</p>
        <p><strong> Website:www.myhmc.in</strong> </p>
    </div>

    <header>
        <H2>Assessment Data Management</H2>
       <div class="top-right-buttons">
        
        <a href="change_password.php" class="btn purple">Change password</a>
        <a href="logout.php" class="btn red">Log out</a>
    </div>
    </header>

    <div class="container">
        <?php
        // Display session messages (from inactivation attempts or other operations)
        if (isset($_SESSION['message'])) {
            $message_class = '';
            switch ($_SESSION['message_type']) {
                case 'success': $message_class = 'alert-success'; break;
                case 'danger':  $message_class = 'alert-danger';  break;
                case 'warning': $message_class = 'alert-warning'; break;
                case 'info':    $message_class = 'alert-info';    break;
                default:        $message_class = 'alert-info';    break;
            }
            echo '<div class="alert ' . $message_class . '">' . htmlspecialchars($_SESSION['message']) . '</div>';
            unset($_SESSION['message']); // Clear the message after displaying
            unset($_SESSION['message_type']); // Clear the type after displaying
        }
        ?>
        <div class="search-form-container">
            <form method="GET" action="index.php">
                <div class="search-inputs">
                    <input type="text" name="new_assessee_id" placeholder="New Assessee ID" value="<?php echo htmlspecialchars($newAssesseeIdSearch); ?>">
                    <input type="text" name="old_ulb_id" placeholder="Old ULB ID" value="<?php echo htmlspecialchars($oldUlbIdSearch); ?>">

                    <select name="ward_no" id="ward_no_search"> <option value="">Select Ward No</option>
                        <?php foreach ($ward_nos as $ward) : ?>
                            <option value="<?php echo htmlspecialchars($ward['WARD_NO']); ?>"
                                <?php echo ($ward['WARD_NO'] == $wardNoSearch) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ward['WARD_NO']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="street_name" id="street_name_search"> <option value="">Select Street Name</option>
                        <?php
                        // Pre-populate streets if a ward was already selected in the search form
                        if (!empty($wardNoSearch) && isset($ward_street_map[$wardNoSearch])) {
                            foreach ($ward_street_map[$wardNoSearch] as $street) {
                                echo '<option value="' . htmlspecialchars($street) . '"';
                                if ($street == $streetNameSearch) {
                                    echo ' selected';
                                }
                                echo '>' . htmlspecialchars($street) . '</option>';
                            }
                        }
                        ?>
                    </select>

                    <input type="text" name="holding_no" placeholder="Holding No" value="<?php echo htmlspecialchars($holdingNoSearch); ?>">

                    <select name="active_status" id="active_status">
                        <option value="">Select Active Status</option>
                        <option value="Y" <?php echo ($activeStatusSearch === 'Y') ? 'selected' : ''; ?>>Active (Y)</option>
                        <option value="N" <?php echo ($activeStatusSearch === 'N') ? 'selected' : ''; ?>>Inactive (N)</option>
                    </select>

                </div>
                <div class="search-buttons-group">
                    <button type="submit" class="btn green">Search</button>
                    <?php
                    if ($isSearchPerformed):
                    ?>
                        <button type="button" class="btn gray" onclick="window.location.href='index.php'">Clear Search</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <button class="btn cyan toggle-column-options-btn" id="toggleColumnOptionsBtn">Show/Hide Column Options</button>

        <div class="column-visibility-controls" id="columnVisibilityControls">
            <strong>Show/Hide Columns:</strong>
            <?php foreach ($tableColumns as $col): ?>
                <?php if ($col['id'] !== 'col-Actions'): ?>
                    <label>
                        <input type="checkbox" class="column-toggle" data-column-id="<?php echo $col['id']; ?>" checked>
                        <?php echo $col['header']; ?>
                    </label>
                <?php endif; ?>
            <?php endforeach; ?>
            <div class="control-buttons">
                <button onclick="resetColumnVisibility()">Reset</button>
            </div>
        </div>

        <div id="noResultsToast" class="no-results-toast">Data Not Found</div>
        <div id="animatedTableWrapper" class="table-container-animated">
            <table>
                <thead>
                    <tr>
                        <?php foreach ($tableColumns as $col): ?>
                            <th id="th-<?php echo $col['id']; ?>"><?php echo $col['header']; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Display results only if a search was performed and there are results
                    if ($isSearchPerformed && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            foreach ($tableColumns as $col) {
                                if ($col['id'] === 'col-Actions') {
                                    $isActive = (dbStatusToDisplay($row['Active_Status']) === 'Y');
                                    $editLinkClass = 'action-btn edit btn' . ($isActive ? '' : ' disabled');
                                    $editLinkHref = $isActive ? "edit_property.php?new_assessee_id=" . htmlspecialchars($row["New_AssesseeId"] ?? '') . "&asmnt_no=" . htmlspecialchars($row["Asmnt_No"] ?? '') : '#';
                                    
                                    echo "<td id=\"td-{$col['id']}-{$row['New_AssesseeId']}-{$row['Asmnt_No']}\" class='actions'>";
                                    
                                    // Edit button logic
                                    echo "<a href='" . $editLinkHref . "' class='" . $editLinkClass . "' " . ($isActive ? '' : 'title="Cannot edit inactive property."') . ">Edit</a>";
                                    
                                    // Inactivate button logic (re-using existing logic)
                                    if ($isActive) { // Only show clickable Inactivate button if status is 'Y'
                                        echo "<button type='button' class='action-btn inactivate btn open-inactivate-modal'
                                                data-new-assessee-id='" . htmlspecialchars($row["New_AssesseeId"] ?? '') . "'
                                                data-asmnt-no='" . htmlspecialchars($row["Asmnt_No"] ?? '') . "'>Inactivate</button>";
                                    } else {
                                        // Show a disabled button/text if status is 'N'
                                        echo "<span class='action-btn inactivate btn disabled' title='Property is already inactive.'>Inactivate</span>";
                                    }
                                    
                                    echo "</td>";
                                } elseif ($col['db_col'] === 'Active_Status') {
                                    echo "<td id=\"td-{$col['id']}-{$row['New_AssesseeId']}-{$row['Asmnt_No']}\">" . dbStatusToDisplay($row[$col['db_col']] ?? '') . "</td>";
                                } else {
                                    echo "<td id=\"td-{$col['id']}-{$row['New_AssesseeId']}-{$row['Asmnt_No']}\">" . htmlspecialchars($row[$col['db_col']] ?? '') . "</td>";
                                }
                            }
                            echo "</tr>";
                        }
                    } else if (!$isSearchPerformed) {
                        // Message displayed when the page is first loaded without any search
                        echo "<tr><td colspan='" . $totalColumns . "'>Please enter search criteria above and click 'Search'.</td></tr>";
                    }
                    // If a search was performed but no results were found, the toast message will handle it,
                    // so we don't need a table row message here.
                    ?>
                </tbody>
            </table>
        </div>

        <a href="add_property.php" class="add-new btn green">➕ Add New Assessee</a>
    </div>

    <div id="inactivateRemarkModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h2>Add Inactivation Remarks</h2>
            <p>Please enter remarks for inactivating this property.</p>
            <form id="inactivateRemarkForm" action="inactivate_property.php" method="GET">
                <input type="hidden" name="new_assessee_id" id="modal_new_assessee_id">
                <input type="hidden" name="asmnt_no" id="modal_asmnt_no">
                <div class="form-group">
                    <label for="remarks_input">Remarks:</label>
                    <textarea id="remarks_input" name="remarks" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn green">Confirm Inactivation</button>
                <button type="button" class="btn red cancel-button">Cancel</button>
            </form>
        </div>
    </div>

</body>
</html>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Column Visibility Logic
            const columnToggles = document.querySelectorAll('.column-toggle');
            const columnVisibilityControls = document.getElementById('columnVisibilityControls');
            const toggleColumnOptionsBtn = document.getElementById('toggleColumnOptionsBtn');
            const TABLE_COLUMNS_LS_KEY = 'tableColumnVisibility';
            const CONTROLS_VISIBILITY_LS_KEY = 'columnControlsVisible';

            function applyColumnVisibility() {
                let savedVisibility = JSON.parse(localStorage.getItem(TABLE_COLUMNS_LS_KEY));
                columnToggles.forEach(toggle => {
                    const columnId = toggle.dataset.columnId;
                    const th = document.getElementById(`th-${columnId}`);
                    const tds = document.querySelectorAll(`td[id^="td-${columnId}-"]`);
                    let isVisible = true;
                    if (savedVisibility && savedVisibility.hasOwnProperty(columnId)) {
                        isVisible = savedVisibility[columnId];
                    }
                    toggle.checked = isVisible;
                    if (th) {
                        th.style.display = isVisible ? '' : 'none';
                    }
                    tds.forEach(td => {
                        td.style.display = isVisible ? '' : 'none';
                    });
                });
            }

            function saveColumnVisibility() {
                const currentVisibility = {};
                columnToggles.forEach(toggle => {
                    currentVisibility[toggle.dataset.columnId] = toggle.checked;
                });
                localStorage.setItem(TABLE_COLUMNS_LS_KEY, JSON.stringify(currentVisibility));
            }

            function toggleControlsVisibility() {
                if (columnVisibilityControls.style.display === 'none' || columnVisibilityControls.style.display === '') {
                    columnVisibilityControls.style.display = 'grid';
                    localStorage.setItem(CONTROLS_VISIBILITY_LS_KEY, 'true');
                } else {
                    columnVisibilityControls.style.display = 'none';
                    localStorage.setItem(CONTROLS_VISIBILITY_LS_KEY, 'false');
                }
            }

            columnToggles.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const columnId = this.dataset.columnId;
                    const isChecked = this.checked;
                    const th = document.getElementById(`th-${columnId}`);
                    const tds = document.querySelectorAll(`td[id^="td-${columnId}-"]`);
                    if (th) {
                        th.style.display = isChecked ? '' : 'none';
                    }
                    tds.forEach(td => {
                        td.style.display = isChecked ? '' : 'none';
                    });
                    saveColumnVisibility();
                });
            });

            toggleColumnOptionsBtn.addEventListener('click', toggleControlsVisibility);

            window.resetColumnVisibility = function() {
                localStorage.removeItem(TABLE_COLUMNS_LS_KEY);
                applyColumnVisibility();
            };

            // Apply visibility on initial load
            applyColumnVisibility();

            // Restore column control panel visibility state
            const controlsWereVisible = localStorage.getItem(CONTROLS_VISIBILITY_LS_KEY);
            if (controlsWereVisible === 'true') {
                columnVisibilityControls.style.display = 'grid';
            } else {
                columnVisibilityControls.style.display = 'none';
            }

            // --- ANIMATION JAVASCRIPT for Table ---
            const animatedTableWrapper = document.getElementById('animatedTableWrapper');
            const tbody = animatedTableWrapper.querySelector('tbody');

            // Trigger table animation only if a search was performed AND actual data rows are present
            if (<?php echo json_encode($isSearchPerformed); ?> && tbody) {
                // Check if the first row has more than 1 cell (i.e., not just the "Please enter search criteria..." message)
                if (tbody.children.length > 0 && tbody.firstElementChild.cells.length > 1) {
                    animatedTableWrapper.classList.add('show');
                }
            }
            // --- END ANIMATION JAVASCRIPT ---

            // --- "No Results" Toast Logic ---
            const noResultsToast = document.getElementById('noResultsToast');
            if (<?php echo json_encode($showNoResultsToast); ?>) {
                noResultsToast.classList.add('show');
                setTimeout(() => {
                    noResultsToast.classList.remove('show');
                }, 3000); // Hide after 3 seconds
            }
            // --- END NEW ---

            // --- NEW: Dynamic Ward and Street Dropdowns for Search Form ---
            const wardNoSearchSelect = document.getElementById('ward_no_search');
            const streetNameSearchSelect = document.getElementById('street_name_search');

            // Pass the PHP-generated map to JavaScript
            const wardStreetMap = <?php echo json_encode($ward_street_map); ?>;

            function populateStreetSearchDropdown() {
                const selectedWard = wardNoSearchSelect.value;
                const prevSelectedStreet = '<?php echo htmlspecialchars($streetNameSearch); ?>'; // Retain previous selection if exists

                // Clear current options
                streetNameSearchSelect.innerHTML = '<option value="">Select Street Name</option>';

                if (selectedWard && wardStreetMap[selectedWard]) {
                    // Populate with streets related to the selected ward
                    wardStreetMap[selectedWard].forEach(street => {
                        const option = document.createElement('option');
                        option.value = street;
                        option.textContent = street;
                        if (street === prevSelectedStreet) {
                            option.selected = true; // Re-select if it was the previously searched street
                        }
                        streetNameSearchSelect.appendChild(option);
                    });
                }
            }

            // Add event listener for ward selection change
            wardNoSearchSelect.addEventListener('change', populateStreetSearchDropdown);

            // Call on page load to initialize based on any pre-selected ward from a previous search
            populateStreetSearchDropdown();
            // --- END NEW DYNAMIC DROPDOWN LOGIC ---

            // Scroll to the main content when "Explore Services" is clicked
            const exploreServicesBtn = document.getElementById('exploreServicesBtn');
            if (exploreServicesBtn) {
                exploreServicesBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId); // This needs an ID for the main content area, e.g., <div id="services" class="container">
                    if (targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 20, // Adjust offset as needed
                            behavior: 'smooth'
                        });
                    }
                });
            }

            // Modal Elements for Inactivation Remarks
            const modal = document.getElementById("inactivateRemarkModal");
            const closeButton = modal.querySelector(".close-button");
            const cancelButton = modal.querySelector(".cancel-button");
            const remarkForm = document.getElementById("inactivateRemarkForm");
            const modalNewAssesseeId = document.getElementById("modal_new_assessee_id");
            const modalAsmntNo = document.getElementById("modal_asmnt_no");
            const remarksInput = document.getElementById("remarks_input");

            // Function to open the modal
            function openModal(newAssesseeId, asmntNo) {
                modal.style.display = "flex"; // Use flex to center the modal content
                modalNewAssesseeId.value = newAssesseeId;
                modalAsmntNo.value = asmntNo;
                remarksInput.value = ''; // Clear previous remarks
                remarksInput.focus(); // Focus on the remarks input
            }

            // Function to close the modal
            function closeModal() {
                modal.style.display = "none";
            }

            // Event listener for opening the modal (delegation for dynamically added buttons)
            document.querySelector('table tbody').addEventListener('click', function(event) {
                // Check if the clicked element is an 'inactivate' button and not disabled
                if (event.target.classList.contains('open-inactivate-modal') && !event.target.classList.contains('disabled')) {
                    const newAssesseeId = event.target.dataset.newAssesseeId;
                    const asmntNo = event.target.dataset.asmntNo;
                    openModal(newAssesseeId, asmntNo);
                } else if (event.target.classList.contains('action-btn') && event.target.classList.contains('edit') && event.target.classList.contains('disabled')) {
                    // Prevent default action (navigation) for disabled edit link
                    event.preventDefault();
                }
            });

            // Event listeners for closing the modal
            closeButton.addEventListener('click', closeModal);
            cancelButton.addEventListener('click', closeModal);

            // Close modal if click outside the content
            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    closeModal();
                }
            });

            // Prevent form submission if remarks are empty (though 'required' helps)
            remarkForm.addEventListener('submit', function(event) {
                if (remarksInput.value.trim() === '') {
                    alert('Remarks are required for inactivation.');
                    event.preventDefault(); // Stop form submission
                }
            });
        });
    </script>
<?php
// Close the database connection at the very end of the script
$conn->close();
?>