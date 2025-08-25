<?php
// Enable error reporting for debugging. REMOVE OR COMMENT OUT IN PRODUCTION!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db_connect.php'; // Ensure this file correctly connects to your database

// Set database connection charset to UTF-8 for consistency
if ($conn) {
    $conn->set_charset("utf8mb4"); // Use utf8mb4 if your MySQL supports it, otherwise 'utf8'
}


// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$message = ''; // To display success or error messages
$current_user = $_SESSION['username'] ?? 'Unknown'; // Get logged-in username
echo date_default_timezone_get();
date_default_timezone_set('Asia/Kolkata'); 
$current_timestamp = date('Y-m-d H:i:s');

// Initialize $_POST values for form repopulation, if not set
// This ensures the form doesn't show "undefined index" errors on first load
$default_post_values = [
    'N' => '', 'SL' => '', 'Asmnt_No' => '1', 'WARD_NO' => '', 'LocationIid' => '',
    'STREET_NAME' => '', 'Holding_No' => '', 'New_AssesseeId' => '', 'Old_ULB_ID' => '',
    'Final_AssesseeName' => '', 'HoldingType' => '', 'Apartment' => 'N', 'GRFlag' => '',
    'A_V' => '', 'Effect_Date' => date('Y-m-d'), 'Exemption' => 'N',
    'BIGHA' => '', 'Katha' => '', 'Chatak' => '', 'Sq_Ft' => '',
    'Surch_Yrly' => '', 'Description' => '', 'Remarks' => ''
];

foreach ($default_post_values as $key => $defaultValue) {
    if (!isset($_POST[$key])) {
        $_POST[$key] = $defaultValue;
    }
}

// --- Define Validation Parameters ---
// Each field can have multiple rules:
// 'required': true/false
// 'type': 'string', 'numeric', 'date', 'enum'
// 'min': minimum value for numeric types
// 'max': maximum value for numeric types
// 'options': array of allowed values for 'enum' type
// 'pattern': regex pattern for string types
$validation_rules = [
    'N' => ['type' => 'string', 'label' => 'N'],
    'SL' => ['type' => 'string', 'label' => 'SL'],
    'Asmnt_No' => ['required' => true, 'type' => 'numeric', 'min' => 0, 'label' => 'Assessment No'], // Added 'label' for better error messages
    'WARD_NO' => ['required' => true,'type' => 'numeric', 'min' => 0, 'label' => 'Ward No'],
    'LocationIid' => ['required' => true,'type' => 'numeric', 'min' => 0,'label' => 'Location ID'],
    'STREET_NAME' => ['required' => true, 'type' => 'string', 'label' => 'Street Name'],
    'Holding_No' => ['required' => true, 'type' => 'string', 'label' => 'Holding No'],
    'New_AssesseeId' => ['required' => true,'type' => 'numeric', 'min' => 0, 'label' => 'New Assessee ID'],
    'Old_ULB_ID' => ['required' => true, 'type' => 'numeric', 'min' => 0, 'label' => 'Old ULB ID'],
    'Final_AssesseeName' => ['required' => true, 'type' => 'string', 'label' => 'Final Assessee Name'],
    'HoldingType' => ['required' => true, 'type' => 'enum', 'options' => ['Residential', 'Commercial', 'Semi Commercial', 'Residential + Commercial', 'Educational', 'Government Property', 'Health Care', 'Others'], 'label' => 'Holding Type'], // Example options, adjust as needed
    'Apartment' => ['required' => true, 'type' => 'enum', 'options' => ['Y', 'N'], 'label' => 'Apartment'],
    'GRFlag' => ['required' => true, 'type' => 'enum', 'options' => ['Old', 'New'], 'label' => 'GR Flag'],
    'A_V' => ['required' => true, 'type' => 'numeric', 'min' => 0, 'label' => 'Annual Value (A.V.)'],
    'Effect_Date' => ['required' => true, 'type' => 'date', 'label' => 'Effect Date'],
    'Exemption' => ['required' => true, 'type' => 'enum', 'options' => ['Y', 'N'], 'label' => 'Exemption'],
    'BIGHA' => ['required' => true, 'type' => 'numeric', 'min' => 0, 'label' => 'Bigha'],
    'Katha' => ['required' => true, 'type' => 'numeric', 'min' => 0, 'label' => 'Katha'],
    'Chatak' => ['required' => true, 'type' => 'numeric', 'min' => 0, 'label' => 'Chatak'],
    'Sq_Ft' => ['required' => true, 'type' => 'numeric', 'min' => 0, 'label' => 'Sq. Ft.'],
    'Surch_Yrly' => ['required' => true, 'type' => 'numeric', 'min' => 0, 'label' => 'Surcharge Yearly'],
    'Description' => ['required' => true, 'type' => 'string', 'label' => 'Description'],
    'Remarks' => ['required' => true, 'type' => 'string', 'label' => 'Remarks']
];
// --- END Validation Parameters ---
// Initialize $_POST values for form repopulation, if not set
// This ensures the form doesn't show "undefined index" errors on first load
$default_post_values = [
    'N' => '', 'SL' => '', 'Asmnt_No' => '1', 'WARD_NO' => '', 'LocationIid' => '',
    'STREET_NAME' => '', 'Holding_No' => '', 'New_AssesseeId' => '', 'Old_ULB_ID' => '',
    'Final_AssesseeName' => '', 'HoldingType' => '', 'Apartment' => 'N', 'GRFlag' => '',
    'A_V' => '', 'Effect_Date' => date('Y-m-d'), 'Exemption' => 'N',
    'BIGHA' => '', 'Katha' => '', 'Chatak' => '', 'Sq_Ft' => '',
    'Surch_Yrly' => '', 'Description' => '', 'Remarks' => ''
];

foreach ($default_post_values as $key => $defaultValue) {
    if (!isset($_POST[$key])) {
        $_POST[$key] = $defaultValue;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form data
    $active_status_db = 'Y'; // Hardcode Active_Status to 'Y'

    // Using null coalescing operator (??) for robustness
    $n = $conn->real_escape_string($_POST['N']);
    $sl = $conn->real_escape_string($_POST['SL']);
    $asmnt_no = $conn->real_escape_string($_POST['Asmnt_No']);
    $ward_no = $conn->real_escape_string($_POST['WARD_NO']);
    $location_id = $conn->real_escape_string($_POST['LocationIid']);
    $street_name = $conn->real_escape_string($_POST['STREET_NAME']);
    $holding_no = $conn->real_escape_string($_POST['Holding_No']);
    $new_assessee_id = $conn->real_escape_string($_POST['New_AssesseeId']);
    $old_ulb_id = $conn->real_escape_string($_POST['Old_ULB_ID']);
    $final_assessee_name = $conn->real_escape_string($_POST['Final_AssesseeName']);
    $holding_type = $conn->real_escape_string($_POST['HoldingType']); // This field remains for its own purpose

    // NEW: Apartment field
    $apartment_flag = $conn->real_escape_string($_POST['Apartment']); // 'Y' or 'N'

    // GRFlag will be 'Old' or 'New' from the form
    $gr_flag = $conn->real_escape_string($_POST['GRFlag']);

    $av = floatval($_POST['A_V']);
    $effect_date = $conn->real_escape_string($_POST['Effect_Date']);
    $exemption = $conn->real_escape_string($_POST['Exemption']);
    $bigha = floatval($_POST['BIGHA']);
    $katha = floatval($_POST['Katha']);
    $chatak = floatval($_POST['Chatak']);
    $sq_ft = floatval($_POST['Sq_Ft']);
    $surch_yrly = floatval($_POST['Surch_Yrly']);

    $Description = $conn->real_escape_string($_POST['Description']);
    $Remarks = $conn->real_escape_string($_POST['Remarks']);

    // --- Expanded Validation for Mandatory Fields ---
    $required_fields = [
        'Asmnt_No' => $asmnt_no,
        'WARD_NO' => $ward_no,
        'LocationIid' => $location_id,
        'STREET_NAME' => $street_name,
        'Holding_No' => $holding_no,
        'New_AssesseeId' => $new_assessee_id,
        'Old_ULB_ID' => $old_ulb_id,
        'Final_AssesseeName' => $final_assessee_name,
        'HoldingType' => $holding_type,
        'GRFlag' => $gr_flag,
        'Apartment' => $apartment_flag,
        'A_V' => $av,
        'Effect_Date' => $effect_date,
        'Exemption' => $exemption,
        'BIGHA' => $bigha,
        'Katha' => $katha,
        'Chatak' => $chatak,
        'Sq_Ft' => $sq_ft,
        'Surch_Yrly' => $surch_yrly,
        'Description' => $Description,
        'Remarks' => $Remarks
    ];

    $missing_fields = [];
    foreach ($required_fields as $fieldName => $value) {
        if (is_string($value) && trim($value) === '') {
            $missing_fields[] = str_replace(['_', 'Iid'], [' ', ' ID'], $fieldName); // More readable field names
        } elseif (is_numeric($value) && $value < 0 && in_array($fieldName, ['A_V', 'BIGHA', 'Katha', 'Chatak', 'Sq_Ft', 'Surch_Yrly'])) {
             $missing_fields[] = str_replace(['_', 'Iid'], [' ', ' ID'], $fieldName); // For negative numeric values
        }
    }


    if (!empty($missing_fields)) {
        $message = "<div class='message error'>❌ The following fields are required or invalid: " . implode(', ', $missing_fields) . "</div>";
    } else {
        // --- START Tax Calculation Logic (using new Apartment column) ---
        $annualPropertyTax = 0;
        $annualHB_base = 0; // Base for HB tax calculation

        // Map GRFlag ('Old'/'New') to the 'GR' string used in the calculation logic
        $calc_gr_status = ($gr_flag == "New") ? "GR" : "Non-GR";

        // Use the new $apartment_flag ('Y' or 'N')
        $is_apartment = (strtoupper($apartment_flag) == "Y");

        // Apply exemption: If Exemption is 'Y', set taxes to 0
        if ($exemption == 'Y') {
            $annualPropertyTax = 0;
            $annualHB_base = 0;
        } else {
            if ($is_apartment) { // If 'Apartment' column is 'Y'
                $annualPropertyTax = ($calc_gr_status == "GR") ? ceil($av * 0.3) : ceil($av * 0.4);
            } else { // If 'Apartment' column is 'N' (Non-Apartment)
                if ($calc_gr_status == "GR") {
                    if ($av <= 999) $annualPropertyTax = ceil($av * ((10 + $av / 100) / 100));
                    else if ($av <= 9999) $annualPropertyTax = ceil($av * ((20 + $av / 1000) / 100));
                    else $annualPropertyTax = ceil($av * 0.3);
                } else { // Non-GR
                    if ($av <= 999) $annualPropertyTax = ceil($av * ((10 + $av / 100) / 100));
                    else if ($av <= 17999) $annualPropertyTax = ceil($av * ((22 + $av / 1000) / 100));
                    else $annualPropertyTax = ceil($av * 0.4);
                }
            }
            $annualHB_base = $av * 0.0025; // This calculation applies only if not exempted
        }

        $ptax_yrly = $annualPropertyTax;
        $ptax_qtrly = ceil($annualPropertyTax / 4);

        // NEW LOGIC: Calculate hbtax_qtrly first, then hbtax_yrly
        $hbtax_qtrly = max(1, ceil($annualHB_base / 4)); // Still ensure minimum 1 and ceil
        $hbtax_yrly = $hbtax_qtrly * 4; // Yearly is now exactly 4 times quarterly

        $surch_qtrly = ceil($surch_yrly / 4);

        // --- END Tax Calculation Logic ---

        // --- Start of Primary Duplicate Check (Holding_No, Asmnt_No, WARD_NO, STREET_NAME, Active_Status) ---
        $check_holding_duplicate_sql = "SELECT COUNT(*) FROM final_emut_data WHERE Holding_No = ? AND Asmnt_No = ? AND WARD_NO = ? AND STREET_NAME = ? AND Active_Status = ?";
        $holding_check_stmt = $conn->prepare($check_holding_duplicate_sql);
        if ($holding_check_stmt === false) {
            $message = "<div class='message error'>❌ SQL prepare failed for holding number check: " . $conn->error . "</div>";
        } else {
            $holding_check_stmt->bind_param("sssss", $holding_no, $asmnt_no, $ward_no, $street_name, $active_status_db);
            $holding_check_stmt->execute();
            $holding_check_result = $holding_check_stmt->get_result();
            $holding_row = $holding_check_result->fetch_row();
            $holding_check_stmt->close();

            if ($holding_row[0] > 0) {
                $message = "<div class='message error'>Error: An ACTIVE property with this Holding Number, Asmnt No, Ward No, and Street Name already exists.</div>";
            } else {
                // --- Secondary Duplicate Check (New_AssesseeId and Asmnt_No) ---
                // This check is good to keep to prevent properties with the same main identifiers.
                $check_assessee_asmnt_stmt = $conn->prepare("SELECT COUNT(*) FROM final_emut_data WHERE New_AssesseeId = ? AND Asmnt_No = ?");
                if ($check_assessee_asmnt_stmt === false) {
                    $message = "<div class='message error'>❌ SQL prepare failed for assessee-asmnt check: " . $conn->error . "</div>";
                } else {
                    $check_assessee_asmnt_stmt->bind_param("ss", $new_assessee_id, $asmnt_no);
                    $check_assessee_asmnt_stmt->execute();
                    $check_assessee_asmnt_result = $check_assessee_asmnt_stmt->get_result();
                    $assessee_asmnt_row = $check_assessee_asmnt_result->fetch_row();
                    $check_assessee_asmnt_stmt->close();

                    if ($assessee_asmnt_row[0] > 0) {
                        $message = "<div class='message error'>Error: A property with this New Assessee ID and Asmnt No already exists.</div>";
                    } else {
                        // If all checks pass, proceed with INSERT
                        // IMPORTANT: Ensure your `final_emut_data` table has a column named `Apartment`
                        $insert_sql = "INSERT INTO final_emut_data (
                            N, SL, Active_Status, Asmnt_No, WARD_NO, LocationIid, STREET_NAME, Holding_No,
                            New_AssesseeId, Old_ULB_ID, Final_AssesseeName, HoldingType, GRFlag, Apartment,
                            `A.V.`, Effect_Date, Exemption, BIGHA, Katha, Chatak, `Sq.Ft.`, Ptax_Yrly, Hbtax_Yrly, Surch_Yrly,
                            Ptax_qtrly, Hbtax_Qtrly, Surch_Qtrly, Description, Remarks,
                            CreatedBy, CreatedAt, LastModifiedBy, LastModifiedAt
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                        $stmt = $conn->prepare($insert_sql);

                        if ($stmt === false) {
                            $message = "<div class='message error'>❌ SQL prepare failed: " . $conn->error . "</div>";
                        } else {
                            // Bind parameters
                            // Add apartment_flag to the bind_param string and list
                            $stmt->bind_param(
                                "ssssssssssssssdssdssssdddddssssss", // Add one 's' for apartment_flag
                                $n, $sl, $active_status_db, $asmnt_no, $ward_no, $location_id, $street_name, $holding_no,
                                $new_assessee_id, $old_ulb_id, $final_assessee_name, $holding_type, $gr_flag, $apartment_flag, // NEW: added apartment_flag
                                $av, $effect_date, $exemption, $bigha, $katha, $chatak, $sq_ft,
                                $ptax_yrly, $hbtax_yrly, $surch_yrly,
                                $ptax_qtrly, $hbtax_qtrly, $surch_qtrly,
                                $Description, $Remarks,
                                $current_user, $current_timestamp, $current_user, $current_timestamp
                            );

                            if ($stmt->execute()) {
                                $message = "<div class='message success'>✔️ New property added successfully!</div>";
                                // Clear form fields on successful submission for a fresh form
                                $_POST = array();
                                // Re-initialize default values for the next rendering
                                foreach ($default_post_values as $key => $defaultValue) {
                                    $_POST[$key] = $defaultValue;
                                }
                            } else {
                                $message = "<div class='message error'>❌ Error adding property: " . $stmt->error . "</div>";
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
    }
}

// Fetch unique WARD_NO values for the primary dropdown
$ward_nos_result = $conn->query("SELECT DISTINCT WARD_NO FROM final_emut_data WHERE WARD_NO IS NOT NULL AND WARD_NO != '' ORDER BY WARD_NO ASC");
$ward_nos = $ward_nos_result ? $ward_nos_result->fetch_all(MYSQLI_ASSOC) : [];

// --- NEW PHP LOGIC: Fetch WARD_NO to STREET_NAME and LocationIid mapping ---
$ward_street_location_map = [];
$map_sql = "SELECT WARD_NO, STREET_NAME, MIN(LocationIid) as LocationIid
            FROM final_emut_data
            WHERE WARD_NO IS NOT NULL AND WARD_NO != ''
            AND STREET_NAME IS NOT NULL AND STREET_NAME != ''
            AND LocationIid IS NOT NULL AND LocationIid != ''
            GROUP BY WARD_NO, STREET_NAME
            ORDER BY WARD_NO ASC, STREET_NAME ASC";
$map_result = $conn->query($map_sql);

if ($map_result) {
    while ($row = $map_result->fetch_assoc()) {
        $ward = htmlspecialchars($row['WARD_NO']);
        $street = htmlspecialchars($row['STREET_NAME']);
        $location = htmlspecialchars($row['LocationIid']);

        if (!isset($ward_street_location_map[$ward])) {
            $ward_street_location_map[$ward] = [];
        }
        $ward_street_location_map[$ward][$street] = $location;
    }
} else {
    error_log("Error fetching ward-street-location map: " . $conn->error);
}
// --- END NEW PHP LOGIC ---


$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Assessee Record</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
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
        background: linear-gradient(to right, var(--dark-blue), #003d82);
        color: var(--white);
        padding: 20px 40px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        margin-bottom: 30px;
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
        color: var(--deep-navy); /* Text color changed to deep navy */
        font-weight: 700;
        text-align: center; /* Ensures text inside is centered even if not flex-centered */
    }

    #shared-header-placeholder img {
        filter: none; /* Removed filter, so original logo color is visible */
        height: 140px; /* Adjust logo size as needed */
        margin-bottom: 5px; /* Space between logo and H1 */
    }

    #shared-header-placeholder h1 {
        font-size: 1.8em; /* Size for "Howrah Municipal Corporation" */
        margin: 0; /* Remove default margins */
        line-height: 1.2;
        color: var(--dark-blue); /* Changed H1 color to dark blue */
    }

    #shared-header-placeholder p {
        font-size: 0.9em; /* Size for address */
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
        background-color: var(--white);
        padding: 35px;
        border-radius: 15px;
        box-shadow: var(--shadow-light);
        max-width: 1300px;
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
        /* ... (CSS remains the same as previous versions) ... */
        :root {
            --primary-color: #0056b3; /* Dark Blue */
            --secondary-color: #007bff; /* Lighter Blue */
            --accent-color: #28a745; /* Green for success/buttons */
            --error-color: #dc3545; /* Red for errors */
            --text-color: #333;
            --light-text-color: #666;
            --border-color: #ddd;
            --bg-light: #f8f9fa;
            --bg-white: #ffffff;
            --form-bg-readonly: #e9ecef; /* Light gray for readonly fields */
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: var(--bg-light);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            background-color: var(--bg-white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            max-width: 900px;
            margin: 30px auto;
            border-top: 5px solid var(--primary-color);
        }

        h1 {
            color: var(--white);
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2.2em;
        } 
		
		h2 {
            color: var(--primary-color); /* Corrected variable usage */
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2.2em;
        }

        .message {
            padding: 12px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            opacity: 0; /* Start hidden for animation */
            animation: fadeIn 0.5s forwards;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .message.success {
            background-color: #d4edda;
            color: var(--accent-color);
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: var(--error-color);
            border: 1px solid #f5c6cb;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .grid-item {
            display: flex;
            flex-direction: column;
        }
        
        /* Error message styling */
        .error-message {
            color: var(--error-color);
            font-size: 0.8em;
            margin-top: 5px;
            display: none; /* Hidden by default */
        }

        /* Input invalid state */
        input.invalid, select.invalid {
            border-color: var(--error-color) !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }


        form label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color);
            font-size: 0.95em;
        }

        form input[type="text"],
        form input[type="number"],
        form input[type="date"], /* Added for Effect Date */
        form select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1em;
            color: var(--text-color);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background-color: var(--bg-white);
        }

        form input[type="text"]:focus,
        form input[type="number"]:focus,
        form input[type="date"]:focus, /* Added for Effect Date */
        form select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        form input[readonly] {
            background-color: var(--form-bg-readonly);
            cursor: not-allowed;
        }

        small {
            color: var(--light-text-color);
            font-size: 0.85em;
            margin-top: 5px;
        }

        .required-star {
            color: var(--error-color);
            margin-left: 4px;
        }

        input[type="submit"] {
            grid-column: 1 / 3;
            padding: 12px 25px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.2);
            margin-top: 15px;
        }

        input[type="submit"]:hover {
            background-color: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.3);
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 30px;
            padding: 10px 15px;
            text-decoration: none;
            color: var(--secondary-color);
            font-weight: 600;
            border: 1px solid var(--secondary-color);
            border-radius: 6px;
            transition: all 0.3s ease;
            max-width: 250px;
            margin-left: auto;
            margin-right: auto;
        }

        .back-link:hover {
            background-color: var(--secondary-color);
            color: white;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.2);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            form {
                grid-template-columns: 1fr; /* Stack columns on smaller screens */
            }
            input[type="submit"], .full-width {
                grid-column: 1 / 2;
            }
            .container {
                margin: 20px;
                padding: 20px;
            }
            h2 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div id="shared-header-placeholder">
        <img src="logo.jpg" alt="Howrah Municipal Corporation Logo">
        <h1>Howrah Municipal Corporation</h1>
        <p>4, Mahatma Gandhi Road, Howrah-711101</p>
        <p><strong> Website:www.myhmc.in</strong> </p>
    </div>

    <header>
        <h1>Assessment Data Management</h1>
        <div class="top-right-buttons">
            <a href="change_password.php" class="btn purple">Change password</a>
            <a href="logout.php" class="btn red">Log out</a>
        </div>
    </header>
    <div class="container">
        <h2><span style="color: var(--accent-color);">➕</span> Add New Assessee</h2>
        <div class="message-container">
            <?php echo $message; ?>
        </div>
        <form action="add_property.php" method="POST" id="addPropertyForm">
            <div class="grid-item">
                <label for="new_assessee_id">New Assessee ID <span class="required-star">*</span>:</label>
                <input type="text" id="new_assessee_id" name="New_AssesseeId" value="<?php echo htmlspecialchars($_POST['New_AssesseeId'] ?? ''); ?>" required>
                <div class="error-message" id="New_AssesseeId-error"></div>
            </div>
            <div class="grid-item">
                <label for="asmnt_no">Asmnt No <span class="required-star">*</span>:</label>
                <input type="text" id="asmnt_no" name="Asmnt_No" value="<?php echo htmlspecialchars($_POST['Asmnt_No'] ?? '1'); ?>" readonly required>
                <div class="error-message" id="Asmnt_No-error"></div>
            </div>

            <div class="grid-item">
                <label for="active_status_display">Active Status:</label>
                <input type="text" id="active_status_display" name="Active_Status_Display" value="Active (Y)" readonly>
                <small>This record will be added as Active.</small>
                <input type="hidden" name="Active_Status" value="Y">
            </div>
            <div class="grid-item">
                <label for="ward_no">Ward No <span class="required-star">*</span>:</label>
                <select id="ward_no" name="WARD_NO" required>
                    <option value="">Select Ward No</option>
                    <?php foreach ($ward_nos as $ward) : ?>
                        <option value="<?php echo htmlspecialchars($ward['WARD_NO']); ?>"
                            <?php echo ((isset($_POST['WARD_NO']) && $_POST['WARD_NO'] == $ward['WARD_NO'])) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ward['WARD_NO']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="error-message" id="WARD_NO-error"></div>
            </div>

            <div class="grid-item">
                <label for="street_name">Street Name <span class="required-star">*</span>:</label>
                <select id="street_name" name="STREET_NAME" required>
                    <option value="">Select Street Name</option>
                    <?php
                    // If a ward was previously selected and street data is available for it,
                    // pre-populate the street names for the chosen ward.
                    if (isset($_POST['WARD_NO']) && !empty($_POST['WARD_NO']) && isset($ward_street_location_map[$_POST['WARD_NO']])) {
                        foreach ($ward_street_location_map[$_POST['WARD_NO']] as $street => $loc_id) {
                            echo '<option value="' . $street . '"';
                            if (isset($_POST['STREET_NAME']) && $_POST['STREET_NAME'] == $street) {
                                echo ' selected';
                            }
                            echo '>' . $street . '</option>';
                        }
                    }
                    ?>
                </select>
                <div class="error-message" id="STREET_NAME-error"></div>
            </div>
            <div class="grid-item">
                <label for="location_id">Location Id <span class="required-star">*</span>:</label>
                <input type="text" id="location_id" name="LocationIid" value="<?php echo htmlspecialchars($_POST['LocationIid'] ?? ''); ?>" readonly required>
                <small>(Auto-filled based on Street Name)</small>
                <div class="error-message" id="LocationIid-error"></div>
            </div>

            <div class="grid-item">
                <label for="holding_no">Holding No <span class="required-star">*</span>:</label>
                <input type="text" id="holding_no" name="Holding_No" value="<?php echo htmlspecialchars($_POST['Holding_No'] ?? ''); ?>" required>
                <div class="error-message" id="Holding_No-error"></div>
            </div>

            <div class="grid-item">
                <label for="old_ulb_id">Old ULB ID <span class="required-star">*</span>:</label>
                <input type="text" id="old_ulb_id" name="Old_ULB_ID" value="<?php echo htmlspecialchars($_POST['Old_ULB_ID'] ?? ''); ?>" required>
                <div class="error-message" id="Old_ULB_ID-error"></div>
            </div>
            <div class="grid-item">
                <label for="final_assessee_name">Final Assessee Name <span class="required-star">*</span>:</label>
                <input type="text" id="final_assessee_name" name="Final_AssesseeName" value="<?php echo htmlspecialchars($_POST['Final_AssesseeName'] ?? ''); ?>" required>
                <div class="error-message" id="Final_AssesseeName-error"></div>
            </div>

            <div class="grid-item">
                <label for="holding_type">Holding Type <span class="required-star">*</span>:</label>
                <select id="holding_type" name="HoldingType" required>
                    <option value="">Select Holding Type</option>
                    <option value="Residential" <?php echo ((isset($_POST['HoldingType']) && $_POST['HoldingType'] == 'Residential')) ? 'selected' : ''; ?>>Residential</option>
                    <option value="Commercial" <?php echo ((isset($_POST['HoldingType']) && $_POST['HoldingType'] == 'Commercial')) ? 'selected' : ''; ?>>Commercial</option>
                    <option value="Semi Commercial" <?php echo ((isset($_POST['HoldingType']) && $_POST['HoldingType'] == 'Semi Commercial')) ? 'selected' : ''; ?>>Semi Commercial</option>
                    <option value="Residential + Commercial" <?php echo ((isset($_POST['HoldingType']) && $_POST['HoldingType'] == 'Residential + Commercial')) ? 'selected' : ''; ?>>Residential + Commercial</option>
                    <option value="Educational" <?php echo ((isset($_POST['HoldingType']) && $_POST['HoldingType'] == 'Educational')) ? 'selected' : ''; ?>>Educational</option>
                    <option value="Government Property" <?php echo ((isset($_POST['HoldingType']) && $_POST['HoldingType'] == 'Government Property')) ? 'selected' : ''; ?>>Government Property</option>
                    <option value="Health Care" <?php echo ((isset($_POST['HoldingType']) && $_POST['HoldingType'] == 'Health Care')) ? 'selected' : ''; ?>>Health Care</option>
                    <option value="Others" <?php echo ((isset($_POST['HoldingType']) && $_POST['HoldingType'] == 'Others')) ? 'selected' : ''; ?>>Others</option>
                </select>
                <div class="error-message" id="HoldingType-error"></div>
            </div>
            <div class="grid-item">
                <label for="gr_flag">GR Flag <span class="required-star">*</span>:</label>
                <select id="gr_flag" name="GRFlag" required>
                    <option value="">Select GR Flag</option>
                    <option value="Old" <?php echo ((isset($_POST['GRFlag']) && $_POST['GRFlag'] == 'Old')) ? 'selected' : ''; ?>>Old (Non-GR)</option>
                    <option value="New" <?php echo (isset($_POST['GRFlag']) && $_POST['GRFlag'] == 'New') ? 'selected' : ''; ?>>New (GR)</option>
                </select>
                <div class="error-message" id="GRFlag-error"></div>
            </div>

            <div class="grid-item">
                <label for="apartment_col">Apartment <span class="required-star">*</span>:</label>
                <select id="apartment_col" name="Apartment" required>
                    <option value="">Select if Apartment</option>
                    <option value="Y" <?php echo ((isset($_POST['Apartment']) && $_POST['Apartment'] == 'Y')) ? 'selected' : ''; ?>>Yes (Y)</option>
                    <option value="N" <?php echo (isset($_POST['Apartment']) && $_POST['Apartment'] == 'N') ? 'selected' : ''; ?>>No (N)</option>
                </select>
                <div class="error-message" id="Apartment-error"></div>
            </div>
            <div class="grid-item"></div>

            <div class="grid-item">
                <label for="av">A.V. <span class="required-star">*</span>:</label>
                <input type="number" step="0.01" id="av" name="A_V" value="<?php echo htmlspecialchars($_POST['A_V'] ?? ''); ?>" required>
                <div class="error-message" id="A_V-error"></div>
            </div>
            <div class="grid-item">
                <label for="effect_date">Effect Date <span class="required-star">*</span>:</label>
                <input type="date" id="effect_date" name="Effect_Date" value="<?php echo htmlspecialchars($_POST['Effect_Date'] ?? date('Y-m-d')); ?>" required>
                <div class="error-message" id="Effect_Date-error"></div>
            </div>

            <div class="grid-item">
                <label for="exemption">Exemption <span class="required-star">*</span>:</label>
                <select id="exemption" name="Exemption" required>
                    <option value="N" <?php echo ((isset($_POST['Exemption']) && $_POST['Exemption'] == 'N') || !isset($_POST['Exemption'])) ? 'selected' : ''; ?>>No (N)</option>
                    <option value="Y" <?php echo (isset($_POST['Exemption']) && $_POST['Exemption'] == 'Y') ? 'selected' : ''; ?>>Yes (Y)</option>
                </select>
                <div class="error-message" id="Exemption-error"></div>
            </div>
            <div class="grid-item">
                <label for="bigha">BIGHA <span class="required-star">*</span>:</label>
                <input type="number" step="0.01" id="bigha" name="BIGHA" value="<?php echo htmlspecialchars($_POST['BIGHA'] ?? ''); ?>" required>
                <div class="error-message" id="BIGHA-error"></div>
            </div>

            <div class="grid-item">
                <label for="katha">Katha <span class="required-star">*</span>:</label>
                <input type="number" step="0.01" id="katha" name="Katha" value="<?php echo htmlspecialchars($_POST['Katha'] ?? ''); ?>" required>
                <div class="error-message" id="Katha-error"></div>
            </div>
            <div class="grid-item">
                <label for="chatak">Chatak <span class="required-star">*</span>:</label>
                <input type="number" step="0.01" id="chatak" name="Chatak" value="<?php echo htmlspecialchars($_POST['Chatak'] ?? ''); ?>" required>
                <div class="error-message" id="Chatak-error"></div>
            </div>

            <div class="grid-item">
                <label for="sq_ft">Sq.Ft. <span class="required-star">*</span>:</label>
                <input type="number" step="0.01" id="sq_ft" name="Sq_Ft" value="<?php echo htmlspecialchars($_POST['Sq_Ft'] ?? ''); ?>" required>
                <div class="error-message" id="Sq_Ft-error"></div>
            </div>

            <div class="grid-item">
                <label for="ptax_yrly">Ptax Yrly <span class="required-star">*</span>:</label>
                <input type="number" step="0.01" id="ptax_yrly" name="Ptax_Yrly" value="<?php echo htmlspecialchars($_POST['Ptax_Yrly'] ?? ''); ?>" readonly required>
                <small>Calculated automatically.</small>
            </div>

            <div class="grid-item">
                <label for="hbtax_yrly">Hbtax Yrly <span class="required-star">*</span>:</label>
                <input type="number" step="0.01" id="hbtax_yrly" name="Hbtax_Yrly" value="<?php echo htmlspecialchars($_POST['Hbtax_Yrly'] ?? ''); ?>" readonly required>
                <small>Calculated automatically (4 * Hbtax Qtrly).</small>
            </div>
            <div class="grid-item">
                <label for="surch_yrly">Surch Yrly <span class="required-star">*</span>:</label>
                <input type="number" step="0.01" id="surch_yrly" name="Surch_Yrly" value="<?php echo htmlspecialchars($_POST['Surch_Yrly'] ?? ''); ?>" required>
                <div class="error-message" id="Surch_Yrly-error"></div>
                <small>Input from user.</small>
            </div>

            <div class="grid-item">
                <label for="ptax_qtrly">Ptax Qtrly <span class="required-star">*</span>:</label>
                <input type="number" step="0.01" id="ptax_qtrly" name="Ptax_qtrly" value="<?php echo htmlspecialchars($_POST['Ptax_qtrly'] ?? ''); ?>" readonly required>
                <small>Calculated automatically.</small>
            </div>
            <div class="grid-item">
                <label for="hbtax_qtrly">Hbtax Qtrly <span class="required-star">*</span>:</label>
                <input type="number" step="0.01" id="hbtax_qtrly" name="Hbtax_Qtrly" value="<?php echo htmlspecialchars($_POST['Hbtax_Qtrly'] ?? ''); ?>" readonly required>
                <small>Calculated automatically.</small>
            </div>

            <div class="grid-item">
                <label for="surch_qtrly">Surch Qtrly <span class="required-star">*</span>:</label>
                <input type="number" step="0.01" id="surch_qtrly" name="Surch_Qtrly" value="<?php echo htmlspecialchars($_POST['Surch_Qtrly'] ?? ''); ?>" readonly required>
                <small>Calculated automatically.</small>
            </div>
            <div class="grid-item">
                <label for="n">N:</label>
                <input type="text" id="n" name="N" value="<?php echo htmlspecialchars($_POST['N'] ?? ''); ?>">
                <div class="error-message" id="N-error"></div>
            </div>

            <div class="grid-item">
                <label for="sl">SL:</label>
                <input type="text" id="sl" name="SL" value="<?php echo htmlspecialchars($_POST['SL'] ?? ''); ?>">
                <div class="error-message" id="SL-error"></div>
            </div>
            <div class="grid-item">
            </div>

            <div class="grid-item full-width">
                <label for="Description">Description <span class="required-star">*</span>:</label>
                <input type="text" id="Description" name="Description" value="<?php echo htmlspecialchars($_POST['Description'] ?? ''); ?>" required>
                <div class="error-message" id="Description-error"></div>
            </div>

            <div class="grid-item full-width">
                <label for="Remarks">Remarks <span class="required-star">*</span>:</label>
                <input type="text" id="Remarks" name="Remarks" value="<?php echo htmlspecialchars($_POST['Remarks'] ?? ''); ?>" required>
                <div class="error-message" id="Remarks-error"></div>
            </div>

            <input type="submit" value="Add Assessee">
        </form>
        <a href="index.php" class="back-link">← Back to Home Page</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wardNoSelect = document.getElementById('ward_no');
            const streetNameSelect = document.getElementById('street_name');
            const locationIdInput = document.getElementById('location_id');
            const addPropertyForm = document.getElementById('addPropertyForm');

            // NEW: Use the comprehensive map for ward, street, and location
            const wardStreetLocationMap = <?php echo json_encode($ward_street_location_map); ?>;
            // NEW: Pass validation rules from PHP to JavaScript
            const validationRules = <?php echo json_encode($validation_rules); ?>;

            function populateStreetsAndLocation() {
                const selectedWard = wardNoSelect.value;
                // Preserve the previously selected street and location if the ward hasn't changed,
                // or if it's the initial load with an existing selection.
                const prevSelectedStreet = '<?php echo isset($_POST['STREET_NAME']) ? htmlspecialchars($_POST['STREET_NAME']) : ''; ?>';
                const prevSelectedLocation = '<?php echo isset($_POST['LocationIid']) ? htmlspecialchars($_POST['LocationIid']) : ''; ?>';


                // Clear existing street options
                streetNameSelect.innerHTML = '<option value="">Select Street Name</option>';
                locationIdInput.value = ''; // Clear location ID when ward changes or no selection


                if (selectedWard && wardStreetLocationMap[selectedWard]) {
                    const streetsForWard = wardStreetLocationMap[selectedWard];
                    let streetFoundInMap = false;

                    for (const street in streetsForWard) {
                        const option = document.createElement('option');
                        option.value = street;
                        option.textContent = street;
                        // Attempt to re-select the previously chosen street if it exists for the new ward
                        if (street === prevSelectedStreet) {
                            option.selected = true;
                            streetFoundInMap = true;
                        }
                        streetNameSelect.appendChild(option);
                    }

                    // If a previous street was selected but not found in the new ward's map,
                    // ensure the streetNameSelect doesn't implicitly select the first item.
                    if (prevSelectedStreet && !streetFoundInMap) {
                        streetNameSelect.value = ""; // Force "Select Street Name"
                    }
                }
                // After populating streets, also call the street change handler to set location_id
                updateLocationId();
            }

            function updateLocationId() {
                const selectedWard = wardNoSelect.value;
                const selectedStreet = streetNameSelect.value;
                locationIdInput.value = ''; // Clear first

                if (selectedWard && selectedStreet && wardStreetLocationMap[selectedWard] && wardStreetLocationMap[selectedWard][selectedStreet]) {
                    locationIdInput.value = wardStreetLocationMap[selectedWard][selectedStreet];
                }
                // Validate LocationIid after update
                validateField(locationIdInput);
            }


            // Attach event listeners
            wardNoSelect.addEventListener('change', populateStreetsAndLocation);
            streetNameSelect.addEventListener('change', updateLocationId);


            // Initial population on page load
            // Call this to populate streets based on any pre-selected ward (e.g., after form submission with errors)
            // and set the correct location ID if a street was previously selected.
            populateStreetsAndLocation();

            // Ensure the Effect Date is pre-filled if not already.
            const effectDateInput = document.getElementById('effect_date');
            if (!effectDateInput.value) {
                const today = new Date();
                const year = today.getFullYear();
                const month = String(today.getMonth() + 1).padStart(2, '0');
                const day = String(today.getDate()).padStart(2, '0');
                effectDateInput.value = `${year}-${month}-${day}`;
            }

            // --- Client-Side (JavaScript) Tax Calculation for live display ---
            const avInput = document.getElementById('av');
            const grFlagSelect = document.getElementById('gr_flag');
            const apartmentColSelect = document.getElementById('apartment_col');
            const surchYrlyInput = document.getElementById('surch_yrly');
            const exemptionSelect = document.getElementById('exemption'); // Re-added

            const ptaxYrlyOutput = document.getElementById('ptax_yrly');
            const hbtaxYrlyOutput = document.getElementById('hbtax_yrly');
            const surchQtrlyOutput = document.getElementById('surch_qtrly');
            const ptaxQtrlyOutput = document.getElementById('ptax_qtrly');
            const hbtaxQtrlyOutput = document.getElementById('hbtax_qtrly');


            function calculateTaxes() {
                const av = parseFloat(avInput.value) || 0;
                const grFlag = grFlagSelect.value;
                const isApartment = apartmentColSelect.value === 'Y';
                const surchYrly = parseFloat(surchYrlyInput.value) || 0;
                const isExempt = exemptionSelect.value === 'Y';

                let annualPropertyTax = 0;
                let annualHB_base = 0; // Base for HB tax calculation
                const calcGrStatus = (grFlag === "New") ? "GR" : "Non-GR";

                if (isExempt) {
                    annualPropertyTax = 0;
                    annualHB_base = 0;
                } else {
                    if (isApartment) {
                        annualPropertyTax = (calcGrStatus === "GR") ? Math.ceil(av * 0.3) : Math.ceil(av * 0.4);
                    } else {
                        if (calcGrStatus === "GR") {
                            if (av <= 999) annualPropertyTax = Math.ceil(av * ((10 + av / 100) / 100));
                            else if (av <= 9999) annualPropertyTax = Math.ceil(av * ((20 + av / 1000) / 100));
                            else annualPropertyTax = Math.ceil(av * 0.3);
                        } else { // Non-GR
                            if (av <= 999) annualPropertyTax = Math.ceil(av * ((10 + av / 100) / 100));
                            else if (av <= 17999) annualPropertyTax = Math.ceil(av * ((22 + av / 1000) / 100));
                            else annualPropertyTax = Math.ceil(av * 0.4);
                        }
                    }
                    annualHB_base = av * 0.0025; // Base HB calculation
                }


                const ptaxYrly = annualPropertyTax;
                const ptaxQtrly = Math.ceil(annualPropertyTax / 4);

                // NEW LOGIC: Calculate hbtaxQtrly first, then hbtaxYrly
                const hbtaxQtrly = Math.max(1, Math.ceil(annualHB_base / 4)); // Still ensure minimum 1 and ceil
                const hbtaxYrly = hbtaxQtrly * 4; // Yearly is now exactly 4 times quarterly

                const surchQtrly = Math.ceil(surchYrly / 4);

                ptaxYrlyOutput.value = ptaxYrly.toFixed(2);
                hbtaxYrlyOutput.value = hbtaxYrly.toFixed(2);
                ptaxQtrlyOutput.value = ptaxQtrly.toFixed(2);
                hbtaxQtrlyOutput.value = hbtaxQtrly.toFixed(2);
                surchQtrlyOutput.value = surchQtrly.toFixed(2);
            }

            // Attach event listeners to relevant input fields for live calculation
            avInput.addEventListener('input', calculateTaxes);
            grFlagSelect.addEventListener('change', calculateTaxes);
            apartmentColSelect.addEventListener('change', calculateTaxes);
            surchYrlyInput.addEventListener('input', calculateTaxes);
            exemptionSelect.addEventListener('change', calculateTaxes);

            // Run calculation on page load in case values are pre-filled (e.g., after a failed submission)
            calculateTaxes();


            // --- Client-Side Validation Logic ---
            const formFields = addPropertyForm.querySelectorAll('input[name], select[name]');

            function showValidationError(inputElement, message) {
                inputElement.classList.add('invalid');
                const errorMessageElement = document.getElementById(inputElement.name + '-error');
                if (errorMessageElement) {
                    errorMessageElement.textContent = message;
                    errorMessageElement.style.display = 'block';
                }
            }

            function hideValidationError(inputElement) {
                inputElement.classList.remove('invalid');
                const errorMessageElement = document.getElementById(inputElement.name + '-error');
                if (errorMessageElement) {
                    errorMessageElement.textContent = '';
                    errorMessageElement.style.display = 'none';
                }
            }

            function validateField(inputElement) {
                const fieldName = inputElement.name;
                const rules = validationRules[fieldName];
                let isValid = true;
                let errorMessage = '';

                if (!rules) {
                    // No specific rules defined, assume valid for now or add a default check
                    hideValidationError(inputElement);
                    return true;
                }

                const value = inputElement.value.trim();

                // Required validation
                if (rules.required && value === '') {
                    isValid = false;
                    errorMessage = `${rules.label || fieldName} is required.`;
                } else if (rules.type === 'numeric') {
                    // Numeric validation
                    const numValue = parseFloat(value);
                    if (value !== '' && (isNaN(numValue) || (rules.min !== undefined && numValue < rules.min))) {
                        isValid = false;
                        errorMessage = `${rules.label || fieldName} must be a non-negative number.`;
                    }
                } else if (rules.type === 'enum') {
                    // Enum (select) validation
                    if (rules.required && !rules.options.includes(value)) {
                        isValid = false;
                        errorMessage = `Please select a valid ${rules.label || fieldName}.`;
                    }
                }
                // Add more validation types (date, pattern) here if needed based on rules

                if (!isValid) {
                    showValidationError(inputElement, errorMessage);
                } else {
                    hideValidationError(inputElement);
                }
                return isValid;
            }

            // Attach validation listeners to all relevant form fields
            formFields.forEach(input => {
                // Skip readonly fields for direct user input validation, but include them in form submit validation
                if (input.readOnly) {
                    return;
                }

                // For text/number inputs
                if (input.type === 'text' || input.type === 'number' || input.type === 'date') {
                    input.addEventListener('input', function() {
                        // For numeric fields, strip non-numeric characters in real-time
                        if (validationRules[this.name] && validationRules[this.name].type === 'numeric') {
                            this.value = this.value.replace(/[^0-9.]/g, ''); // Allow digits and one decimal point
                            if (this.value.indexOf('.') !== -1 && this.value.indexOf('.') !== this.value.lastIndexOf('.')) {
                                this.value = this.value.substring(0, this.value.lastIndexOf('.')); // Remove extra decimal points
                            }
                        }
                        validateField(this);
                        // Recalculate taxes if any of the tax-related inputs change
                        if (['A_V', 'Surch_Yrly'].includes(this.name)) {
                            calculateTaxes();
                        }
                    });
                    input.addEventListener('blur', function() {
                        validateField(this);
                    });
                }
                // For select elements
                else if (input.tagName === 'SELECT') {
                    input.addEventListener('change', function() {
                        validateField(this);
                        // Recalculate taxes if any of the tax-related selects change
                        if (['gr_flag', 'apartment_col', 'exemption'].includes(this.name)) {
                            calculateTaxes();
                        }
                    });
                    input.addEventListener('blur', function() {
                        validateField(this);
                    });
                }
            });

            // Form submission validation
            addPropertyForm.addEventListener('submit', function(event) {
                let formIsValid = true;
                formFields.forEach(input => {
                    // Validate all fields, including readonly ones for final check (though readonly fields should be valid by default)
                    if (!validateField(input)) {
                        formIsValid = false;
                    }
                });

                if (!formIsValid) {
                    event.preventDefault(); // Stop form submission
                    // Scroll to the first invalid field
                    const firstInvalidField = document.querySelector('.invalid');
                    if (firstInvalidField) {
                        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    // Display a general error message at the top
                    const messageContainer = document.querySelector('.message-container');
                    if (messageContainer) {
                        messageContainer.innerHTML = "<div class='message error'>❌ Please correct the errors in the form.</div>";
                    }
                }
            });

            // Initial validation on load for pre-filled values (e.g., after server-side validation failure)
            formFields.forEach(input => {
                validateField(input);
            });
        });
    </script>
</body>
</html>