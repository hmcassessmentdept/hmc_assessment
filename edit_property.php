<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
require_once 'db_connect.php'; // Ensure this file establishes $conn as a mysqli connection

$message = '';
$property_data = null;
$ward_nos = [];
$street_names = [];
$street_location_map = [];

function dbStatusToDisplay($status) {
    return ($status == 1) ? 'Y' : 'N';
}

// Fetch wards
$sql = "SELECT DISTINCT WARD_NO FROM final_emut_data ORDER BY WARD_NO ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $ward_nos[] = $row;
    }
}

// Fetch streets
$sql = "SELECT DISTINCT STREET_NAME, LocationIid FROM final_emut_data ORDER BY STREET_NAME ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $street_names[] = $row;
        $street_location_map[$row['STREET_NAME']] = $row['LocationIid'];
    }
}

// Handle GET request to load property data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['new_assessee_id'], $_GET['asmnt_no'])) {
    $new_assessee_id = $conn->real_escape_string($_GET['new_assessee_id']);
    $asmnt_no = $conn->real_escape_string($_GET['asmnt_no']);
    // Select the specific 'sq.ft.' column along with others
    $sql = "SELECT *, `sq.ft.`, `A.V.` FROM final_emut_data WHERE New_AssesseeId='$new_assessee_id' AND Asmnt_No='$asmnt_no'";
    $res = $conn->query($sql);
    if ($res && $res->num_rows > 0) {
        $property_data = $res->fetch_assoc();

        // --- IMPORTANT: Ensure 'sqft' and 'av' keys are set for the form ---
        // Accessing the DB column 'sq.ft.' and assigning it to 'sqft' for form consistency
        $property_data['sqft'] = $property_data['sq.ft.'] ?? '';
        // Accessing the DB column 'A.V.' and assigning it to 'av' for form consistency
        $property_data['av'] = $property_data['A.V.'] ?? '';
        $property_data['Active_Status'] = dbStatusToDisplay($property_data['Active_Status']);

    } else {
        $message = "<div class='message error'>Error: Property record not found.</div>";
        // Initialize property_data as an empty array if not found to prevent errors in HTML
        $property_data = [];
    }
}

// Handle POST request for updating/creating new version
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $original_new_assessee_id = $conn->real_escape_string($_POST['original_New_AssesseeId'] ?? '');
    $original_asmnt_no        = $conn->real_escape_string($_POST['original_Asmnt_No'] ?? '');

    // Clean input mapping - these come from the HTML form names
    $n                     = $conn->real_escape_string($_POST['N'] ?? '');
    $sl                    = $conn->real_escape_string($_POST['SL'] ?? '');
    $ward_no               = $conn->real_escape_string($_POST['WARD_NO'] ?? '');
    $street_name           = $conn->real_escape_string($_POST['STREET_NAME'] ?? '');
    $holding_no            = $conn->real_escape_string($_POST['Holding_No'] ?? '');
    $old_ulb_id            = $conn->real_escape_string($_POST['Old_ULB_ID'] ?? '');
    $final_assessee_name   = $conn->real_escape_string($_POST['Final_AssesseeName'] ?? '');
    $holding_type          = $conn->real_escape_string($_POST['HoldingType'] ?? '');
    $gr_flag               = $conn->real_escape_string($_POST['GRFlag'] ?? '');
    $av                    = $conn->real_escape_string($_POST['av'] ?? ''); // Input from form
    $sqft                  = $conn->real_escape_string($_POST['sqft'] ?? ''); // Input from form
    $effect_date           = $conn->real_escape_string($_POST['Effect_Date'] ?? '');
    $exemption             = $conn->real_escape_string($_POST['Exemption'] ?? '');
    $bigha                 = $conn->real_escape_string($_POST['BIGHA'] ?? '');
    $katha                 = $conn->real_escape_string($_POST['Katha'] ?? '');
    $chatak                = $conn->real_escape_string($_POST['Chatak'] ?? '');
    $surch_yrly            = $conn->real_escape_string($_POST['Surch_Yrly'] ?? '');
    $description           = $conn->real_escape_string($_POST['Description'] ?? '');
    $remarks               = $conn->real_escape_string($_POST['Remarks'] ?? '');
    $apartment_col         = $conn->real_escape_string($_POST['ApartmentCol'] ?? 'N');

    $location_id = $street_location_map[$street_name] ?? '';

    $required_fields = [
        'N' => $n, 'SL' => $sl, 'WARD_NO' => $ward_no,
        'STREET_NAME' => $street_name, 'Holding_No' => $holding_no,
        'Old_ULB_ID' => $old_ulb_id, 'Final_AssesseeName' => $final_assessee_name,
        'HoldingType' => $holding_type, 'GRFlag' => $gr_flag,
        'ApartmentCol' => $apartment_col, 'av' => $av, 'sqft' => $sqft,
        'Effect_Date' => $effect_date, 'Exemption' => $exemption,
        'BIGHA' => $bigha, 'Katha' => $katha, 'Chatak' => $chatak,
        'Surch_Yrly' => $surch_yrly, 'Description' => $description,
        'Remarks' => $remarks
    ];
    $numeric_fields = ['av', 'sqft', 'BIGHA', 'Katha', 'Chatak', 'Surch_Yrly'];
    $missing_fields = [];

    foreach ($required_fields as $field => $val) {
        if (trim((string)$val) === '') {
            $missing_fields[] = $field;
        } elseif (in_array($field, $numeric_fields) && (!is_numeric($val) || floatval($val) < 0)) {
            $missing_fields[] = $field;
        }
    }

    if (!empty($missing_fields)) {
        $message = "<div class='message error'>Missing/invalid: " . implode(', ', $missing_fields) . "</div>";
        $property_data = $_POST;
        $property_data['New_AssesseeId'] = $original_new_assessee_id;
        $property_data['Asmnt_No']         = $original_asmnt_no;
        $property_data['LocationIid']      = $location_id;
        // When re-populating on error, ensure both 'av' and 'sqft' are set
        $property_data['av']               = $av; // From POST
        $property_data['sqft']             = $sqft; // From POST
        $property_data['Active_Status']    = 'Y';
    } else {
        // Tax calculation logic (remains mostly the same)
        $av_n = (float)$av;
        $surch_n = (float)$surch_yrly;
        $is_apartment = ($apartment_col === 'Y');
        $calc_gr_status = ($gr_flag === 'New') ? 'GR' : 'Non-GR';
        $tax = 0;
        if ($is_apartment) {
            $tax = ceil($av_n * ($calc_gr_status === 'GR' ? 0.3 : 0.4));
        } else {
            if ($av_n <= 999) {
                $base = 10 + ($av_n / 100);
            } elseif ($av_n <= 17999) {
                $base = ($calc_gr_status === 'GR') ? 20 + ($av_n / 1000) : 22 + ($av_n / 1000);
            } else {
                $base = ($calc_gr_status === 'GR') ? 0.3 : 0.4;
                $tax = ceil($av_n * $base);
            }
            // This 'isset($tax)' condition can still be problematic if $av_n > 17999
            // It's safer to ensure $tax is always calculated or defaulted.
            // For now, keeping as is, but consider its logic.
            if (!isset($tax) || $tax == 0) { // Added $tax == 0 for robustness
                // This branch would only be hit if the above 'if/elseif/else' didn't set $tax
                // which might happen for certain edge cases based on your ranges.
                // Assuming this is a fallback for rates not setting $tax initially.
                $tax = ceil($av_n * ($base / 100)); // Ensure $base is correctly defined for this case
            }
        }
        $ptax_yrly = $tax;
        $ptax_qtrly = ceil($tax / 4);
        $hb_base = $av_n * 0.0025;
        $hbtax_qtrly = max(1, ceil($hb_base / 4));
        $hbtax_yrly = $hbtax_qtrly * 4;
        $surch_qtrly = ceil($surch_n / 4);

        if ($exemption === 'Y') {
            $ptax_yrly = $ptax_qtrly = $hbtax_yrly = $hbtax_qtrly = $surch_n = $surch_qtrly = 0;
        }

        $ptax_yrly_db = number_format($ptax_yrly, 2, '.', '');
        $hbtax_yrly_db = number_format($hbtax_yrly, 2, '.', '');
        $surch_yrly_db = number_format($surch_n, 2, '.', '');
        $ptax_qtrly_db = number_format($ptax_qtrly, 2, '.', '');
        $hbtax_qtrly_db = number_format($hbtax_qtrly, 2, '.', '');
        $surch_qtrly_db = number_format($surch_qtrly, 2, '.', '');

        $new_asmnt_no = str_pad((int)$original_asmnt_no + 1, 3, '0', STR_PAD_LEFT);
        $check = $conn->query(
            "SELECT COUNT(*) FROM final_emut_data WHERE New_AssesseeId='$original_new_assessee_id' AND Asmnt_No='$new_asmnt_no'"
        );
        $exists = $check->fetch_row()[0] ?? 0;

        if ($exists) {
            $message = "<div class='message error'>Version '$new_asmnt_no' already exists.</div>";
            $property_data = $_POST;
            $property_data['New_AssesseeId'] = $original_new_assessee_id;
            $property_data['Asmnt_No']         = $original_asmnt_no;
            $property_data['Active_Status']    = 'Y';
            $property_data['sqft']             = $sqft; // Ensure sqft is re-populated on error
            $property_data['av']               = $av; // Ensure av is re-populated on error
        } else {
            $conn->begin_transaction();
            try {
                $stmt0 = $conn->prepare(
                    "SELECT CreatedBy, CreatedAt FROM final_emut_data WHERE New_AssesseeId=? AND Asmnt_No=?"
                );
                $stmt0->bind_param("ss", $original_new_assessee_id, $original_asmnt_no);
                $stmt0->execute();
                $orig = $stmt0->get_result()->fetch_assoc();
                $stmt0->close();

                $created_by = $orig['CreatedBy'] ?? $_SESSION['username'];
                $created_at = $orig['CreatedAt'] ?? date('Y-m-d H:i:s');
                $user = $_SESSION['username'];
                date_default_timezone_set('Asia/Kolkata');
                $now = date('Y-m-d H:i:s');

                $stmt1 = $conn->prepare(
                    "UPDATE final_emut_data SET Active_Status= 'N', LastModifiedBy=?, LastModifiedAt=? WHERE New_AssesseeId=? AND Asmnt_No=?"
                );
                $stmt1->bind_param("ssss", $user, $now, $original_new_assessee_id, $original_asmnt_no);
                $stmt1->execute();
                $stmt1->close();

                $stmt2 = $conn->prepare(
                    "INSERT INTO final_emut_data (
                        N, SL, Active_Status, Asmnt_No, WARD_NO, LocationIid, STREET_NAME, Holding_No,
                        New_AssesseeId, Old_ULB_ID, Final_AssesseeName, HoldingType, GRFlag,
                        `A.V.`, BIGHA, Katha, Chatak, `sq.ft.`, Ptax_Yrly, Hbtax_Yrly, Surch_Yrly,
                        Ptax_qtrly, Hbtax_Qtrly, Surch_Qtrly, Description, Remarks, Apartment,
                        CreatedBy, CreatedAt, LastModifiedBy, LastModifiedAt, Effect_Date, Exemption
                    ) VALUES (?, ?, 'Y', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                // Corrected bind_param: 'd' for `sq.ft.` (double/float), 'd' for `A.V.`
                $stmt2->bind_param(
                    "ssssssssssssdddddddddddsssssssss",
                    $n, $sl, $new_asmnt_no, $ward_no, $location_id, $street_name, $holding_no,
                    $original_new_assessee_id, $old_ulb_id, $final_assessee_name, $holding_type,
                    $gr_flag, $av, $bigha, $katha, $chatak, $sqft, // $av maps to `A.V.`, $sqft maps to `sq.ft.`
                    $ptax_yrly_db, $hbtax_yrly_db, $surch_yrly_db,
                    $ptax_qtrly_db, $hbtax_qtrly_db, $surch_qtrly_db,
                    $description, $remarks, $apartment_col,
                    $created_by, $created_at, $user, $now, $effect_date, $exemption
                );

                $stmt2->execute();
                $stmt2->close();

                $conn->commit();
                $message = "<div class='message success'>New version created: Asmnt No $new_asmnt_no</div>";
                header("Location: edit_property.php?new_assessee_id="
                               . urlencode($original_new_assessee_id)
                               . "&asmnt_no=" . urlencode($new_asmnt_no)
                               . "&message=" . urlencode($message));
                exit;

            } catch (Exception $e) {
                $conn->rollback();
                $message = "<div class='message error'>Transaction failed: " . $e->getMessage() . "</div>";
                $property_data = $_POST;
                $property_data['New_AssesseeId'] = $original_new_assessee_id;
                $property_data['Asmnt_No']         = $original_asmnt_no;
                $property_data['Active_Status']    = 'Y';
                $property_data['sqft']             = $sqft; // Ensure sqft is re-populated on error
                $property_data['av']               = $av; // Ensure av is re-populated on error
            }
        }
    }
}

if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property Record</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
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

        /* Header Styles */
        header {
            background: linear-gradient(to right, var(--dark-blue), #003d82);
            color: var(--white);
            padding: 20px 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 2.5em;
        }

        .top-right-buttons {
            display: flex;
            gap: 15px;
        }

        @keyframes slideInFromTop {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
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
            color: var(--deep-navy);
            font-weight: 700;
            text-align: center;
        }

        #shared-header-placeholder img {
            filter: none;
            height: 140px;
            margin-bottom: 5px;
        }

        #shared-header-placeholder h1 {
            font-size: 1.8em;
            margin: 0;
            line-height: 1.2;
            color: var(--dark-blue);
        }

        #shared-header-placeholder p {
            font-size: 0.9em;
            margin: 0;
            color: var(--light-text-color);
            font-weight: 400;
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
            color: var(--white);
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 2.2em;
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
        .btn.purple {
            background: linear-gradient(135deg, var(--purple-btn), var(--purple-btn-hover));
        }
        .btn.purple:hover {
            background: linear-gradient(135deg, var(--purple-btn-hover), var(--purple-btn));
        }
        .btn.red { background: linear-gradient(135deg, #e63946, #b71c1c); }

        :root {
            --primary-color: #0056b3;
            --secondary-color: #007bff;
            --accent-color: #ff9800;
            --success-color: #28a745;
            --error-color: #dc3545;
            --text-color: #333;
            --light-text-color: #666;
            --border-color: #ddd;
            --bg-light: #f8f9fa;
            --bg-white: #ffffff;
            --form-bg-readonly: #e9ecef;
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
            color: primary-color: #0056b3;;
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
            opacity: 0;
            animation: fadeIn 0.5s forwards;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .message.success {
            background-color: #d4edda;
            color: var(--success-color);
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

        .info-panel {
            background-color: #e0f2f7;
            border: 1px solid #b3e0ed;
            color: #0056b3;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-weight: 600;
            line-height: 1.5;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .info-panel strong {
            color: var(--primary-color);
        }

        .info-panel .icon {
            font-size: 1.8em;
            color: var(--accent-color);
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

        form label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color);
            font-size: 0.95em;
        }

        form input[type="text"],
        form input[type="number"],
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
        form select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        form input[readonly], form input[disabled] {
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
            box-shadow: 0 4px 10px rgba(255, 152, 0, 0.2);
            margin-top: 15px;
        }

        input[type="submit"]:hover {
            background-color: #e68a00;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 152, 0, 0.3);
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
                grid-template-columns: 1fr;
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
    <h2>Assessment Data Management</h2>
    <div class="top-right-buttons">
        <a href="change_password.php" class="btn purple">Change password</a>
        <a href="logout.php" class="btn red">Log out</a>
    </div>
</header>
<div class="container">
    <h2><span style="color: var(--accent-color);">✍️</span> Edit Assessee Record </h2>
    <div class="message-container">
        <?php echo $message; // Display any success or error messages ?>
    </div>

    <?php if ($property_data && isset($property_data['New_AssesseeId'])): // Only show form if property data is available ?>
    <div class="info-panel">
        <span class="icon">ℹ️</span>
        <div>
            You are editing record: <strong>New Assessee ID:<?php echo htmlspecialchars($property_data['New_AssesseeId']); ?></strong>, Asmnt No:<strong><?php echo htmlspecialchars($property_data['Asmnt_No']); ?></strong>. Submitting this form will create an incremented Asmnt No, and the current record will be set to Inactive.
        </div>
    </div>

    <form action="edit_property.php" method="POST">
    <input type="hidden" name="original_New_AssesseeId" value="<?php echo htmlspecialchars($property_data['New_AssesseeId']); ?>">
    <input type="hidden" name="original_Asmnt_No" value="<?php echo htmlspecialchars($property_data['Asmnt_No']); ?>">

    <div class="grid-item">
        <label for="new_assessee_id">New Assessee ID:</label>
        <input type="text" id="new_assessee_id" name="New_AssesseeId" value="<?php echo htmlspecialchars($property_data['New_AssesseeId']); ?>" readonly>
        <small>This New_AssesseeId cannot be changed. It identifies the property across versions.</small>
    </div>
    <div class="grid-item">
        <label>Asmnt No:</label>
        <input type="text" value="Will be <?php echo htmlspecialchars((int)$property_data['Asmnt_No'] + 1); ?>" disabled>
        <small>The assessment number increments for each new version.</small>
    </div>

    <div class="grid-item">
        <label>Active Status:</label>
        <input type="text" value="Active (Y)" disabled>
        <small>The new record created will be set to Active.</small>
    </div>

    <div class="grid-item">
        <label for="ward_no">Ward No:<span class="required-star">*</span></label>
        <select id="ward_no" name="WARD_NO" required>
            <option value="">Select Ward No</option>
            <?php foreach ($ward_nos as $ward) : ?>
                <option value="<?php echo htmlspecialchars($ward['WARD_NO']); ?>"
                    <?php echo ((isset($property_data['WARD_NO']) && $property_data['WARD_NO'] == $ward['WARD_NO'])) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($ward['WARD_NO']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="grid-item">
        <label for="street_name">Street Name:<span class="required-star">*</span></label>
        <select id="street_name" name="STREET_NAME" required>
            <option value="">Select Street Name</option>
            <?php foreach ($street_names as $street) : ?>
                <option value="<?php echo htmlspecialchars($street['STREET_NAME']); ?>"
                    <?php echo ((isset($property_data['STREET_NAME']) && $property_data['STREET_NAME'] == $street['STREET_NAME'])) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($street['STREET_NAME']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="grid-item">
        <label for="location_id">Location Id:<span class="required-star">*</span></label>
        <input type="text" id="location_id" name="LocationId" value="<?php echo htmlspecialchars($property_data['LocationId'] ?? ''); ?>" readonly required>
        <small>(Auto-filled based on Street Name)</small>
    </div>

    <div class="grid-item">
        <label for="holding_no">Holding No:<span class="required-star">*</span></label>
        <input type="text" id="holding_no" name="Holding_No" value="<?php echo htmlspecialchars($property_data['Holding_No'] ?? ''); ?>" required>
    </div>

    <div class="grid-item">
        <label for="old_ulb_id">Old ULB ID:<span class="required-star">*</span></label>
        <input type="number" step="0.01" id="old_ulb_id" name="Old_ULB_ID" value="<?php echo htmlspecialchars($property_data['Old_ULB_ID'] ?? ''); ?>" required>
    </div>

    <div class="grid-item">
        <label for="final_assessee_name">Final Assessee Name:<span class="required-star">*</span></label>
        <input type="text" id="final_assessee_name" name="Final_AssesseeName" value="<?php echo htmlspecialchars($property_data['Final_AssesseeName'] ?? ''); ?>" required>
        <small>Please mention owner and occupier separately</small>
    </div>

    <div class="grid-item">
        <label for="holding_type">Holding Type:<span class="required-star">*</span></label>
        <select id="holding_type" name="HoldingType" required>
            <option value="">Select Holding Type</option>
            <option value="Residential" <?php echo ((isset($property_data['HoldingType']) && $property_data['HoldingType'] == 'Residential')) ? 'selected' : ''; ?>>Residential</option>
            <option value="Commercial" <?php echo ((isset($property_data['HoldingType']) && $property_data['HoldingType'] == 'Commercial')) ? 'selected' : ''; ?>>Commercial</option>
            <option value="Semi Commercial" <?php echo ((isset($property_data['HoldingType']) && $property_data['HoldingType'] == 'Semi Commercial')) ? 'selected' : ''; ?>>Semi Commercial</option>
            <option value="Residential + Commercial" <?php echo ((isset($property_data['HoldingType']) && $property_data['HoldingType'] == 'Residential + Commercial')) ? 'selected' : ''; ?>>Residential + Commercial</option>
            <option value="Educational" <?php echo ((isset($property_data['HoldingType']) && $property_data['HoldingType'] == 'Educational')) ? 'selected' : ''; ?>>Educational</option>
            <option value="Government Property" <?php echo ((isset($property_data['HoldingType']) && $property_data['HoldingType'] == 'Government Property')) ? 'selected' : ''; ?>>Government Property</option>
            <option value="Health Care" <?php echo ((isset($property_data['HoldingType']) && $property_data['HoldingType'] == 'Health Care')) ? 'selected' : ''; ?>>Health Care</option>
            <option value="Others" <?php echo ((isset($property_data['HoldingType']) && $property_data['HoldingType'] == 'Others')) ? 'selected' : ''; ?>>Others</option>
        </select>
    </div>

    <div class="grid-item">
        <label for="gr_flag">GR Flag:<span class="required-star">*</span></label>
        <select id="gr_flag" name="GRFlag" required>
            <option value="">Select GR Flag</option>
            <option value="Old" <?php echo ((isset($property_data['GRFlag']) && $property_data['GRFlag'] == 'Old')) ? 'selected' : ''; ?>>Old</option>
            <option value="New" <?php echo ((isset($property_data['GRFlag']) && $property_data['GRFlag'] == 'New')) ? 'selected' : ''; ?>>New</option>
        </select>
    </div>

    <div class="grid-item">
        <label for="apartment_col">Apartment (Y/N):<span class="required-star">*</span></label>
        <select id="apartment_col" name="ApartmentCol" required>
            <option value="N" <?php echo ((isset($property_data['ApartmentCol']) && $property_data['ApartmentCol'] == 'N')) ? 'selected' : ''; ?>>No (N)</option>
            <option value="Y" <?php echo ((isset($property_data['ApartmentCol']) && $property_data['ApartmentCol'] == 'Y')) ? 'selected' : ''; ?>>Yes (Y)</option>
        </select>
    </div>

    <div class="grid-item">
    <label for="av">A.V.:<span class="required-star">*</span></label>
    <input type="number" step="any" id="av" name="av" value="<?php echo htmlspecialchars($property_data['A.V.'] ?? ''); ?>" required>
</div>


    <div class="grid-item">
        <label for="effect_date">Effect Date <span class="required-star">*</span>:</label>
        <input type="date" id="effect_date" name="Effect_Date" value="<?php echo htmlspecialchars($property_data['Effect_Date'] ?? date('Y-m-d')); ?>" required>
    </div>

    <div class="grid-item">
        <label for="exemption">Exemption <span class="required-star">*</span>:</label>
        <select id="exemption" name="Exemption" required>
            <option value="N" <?php echo ((isset($property_data['Exemption']) && $property_data['Exemption'] == 'N') || !isset($property_data['Exemption'])) ? 'selected' : ''; ?>>No (N)</option>
            <option value="Y" <?php echo ((isset($property_data['Exemption']) && $property_data['Exemption'] == 'Y')) ? 'selected' : ''; ?>>Yes (Y)</option>
        </select>
    </div>

    <div class="grid-item">
        <label for="bigha">BIGHA:<span class="required-star">*</span></label>
        <input type="number" step="0.01" id="bigha" name="BIGHA" value="<?php echo htmlspecialchars($property_data['BIGHA'] ?? ''); ?>" required>
    </div>

    <div class="grid-item">
        <label for="katha">Katha:<span class="required-star">*</span></label>
        <input type="number" step="0.01" id="katha" name="Katha" value="<?php echo htmlspecialchars($property_data['Katha'] ?? ''); ?>" required>
    </div>

    <div class="grid-item">
       <label for="chatak">Chatak:</label>
<input type="number" step="0.01" id="chatak" name="Chatak" value="<?php echo htmlspecialchars($property_data['Chatak'] ?? ''); ?>" required>
    </div>

   <div class="grid-item">
    <label for="sqft">Sq.Ft.:<span class="required-star">*</span></label>
    <input type="number" step="any" id="sqft" name="sqft" value="<?php echo htmlspecialchars($property_data['sq.ft.'] ?? ''); ?>" required>
</div>

    <div class="grid-item">
        <label for="ptax_yrly">Ptax_Yrly:</label>
        <input type="number" id="ptax_yrly" name="Ptax_Yrly" value="<?php echo htmlspecialchars($property_data['Ptax_Yrly'] ?? ''); ?>" readonly>
    </div>

    <div class="grid-item">
        <label for="hbtax_yrly">Hbtax_Yrly:</label>
        <input type="number" id="hbtax_yrly" name="Hbtax_Yrly" value="<?php echo htmlspecialchars($property_data['Hbtax_Yrly'] ?? ''); ?>" readonly>
    </div>

    <div class="grid-item">
        <label for="surch_yrly">Surch_Yrly:<span class="required-star">*</span></label>
        <input type="number" step="0.01" id="surch_yrly" name="Surch_Yrly" value="<?php echo htmlspecialchars($property_data['Surch_Yrly'] ?? ''); ?>">
    </div>

    <div class="grid-item">
        <label for="ptax_qtrly">Ptax_Qtrly:</label>
        <input type="number" id="ptax_qtrly" name="Ptax_Qtrly" value="<?php echo htmlspecialchars($property_data['Ptax_Qtrly'] ?? ''); ?>" readonly>
    </div>

    <div class="grid-item">
        <label for="hbtax_qtrly">Hbtax_Qtrly:</label>
        <input type="number" id="hbtax_qtrly" name="Hbtax_Qtrly" value="<?php echo htmlspecialchars($property_data['Hbtax_Qtrly'] ?? ''); ?>" readonly>
    </div>

    <div class="grid-item">
        <label for="surch_qtrly">Surch_Qtrly:</label>
        <input type="number" id="surch_qtrly" name="Surch_Qtrly" value="<?php echo htmlspecialchars($property_data['Surch_Qtrly'] ?? ''); ?>" readonly>
    </div>
	<div class="grid-item">
                <label for="n">N:</label>
                <input type="text" id="n" name="N" value="<?php echo htmlspecialchars($property_data['N'] ?? ''); ?>">
                <div class="error-message" id="N-error"></div>
            </div>

            <div class="grid-item">
                <label for="sl">SL:</label>
                <input type="text" id="sl" name="SL" value="<?php echo htmlspecialchars($property_data['SL'] ?? ''); ?>">
                <div class="error-message" id="SL-error"></div>
            </div>
            <div class="grid-item">
            </div>
	<div class="grid-item">
        <label for="remarks">Description:</label>
        <input type="text" id="description" name="Description" value="<?php echo htmlspecialchars($property_data['Description'] ?? ''); ?>">
		</div>
    <div class="grid-item">
        <label for="remarks">Remarks:</label>
        <input type="text" id="remarks" name="Remarks" value="<?php echo htmlspecialchars($property_data['Remarks'] ?? ''); ?>">
    </div>

  
       <input type="submit" .btn value="Update Assessee">
    
</form>

    <?php else: ?>
        <p style="text-align: center; color: var(--text-color);">Please provide a valid New Assessee ID and Asmnt No in the URL to edit a property.</p>
    <?php endif; ?>
    <a href="index.php" class="back-link">← Back to Home page </a>
</div>

<script>

document.querySelectorAll('input[type=number]').forEach(input => {
  input.addEventListener('keypress', function(evt) {
    const charCode = evt.which || evt.keyCode;

    // Allow: digits 0-9 (48-57), dot (46), minus (45), backspace (8), tab (9)
    if (
      (charCode >= 48 && charCode <= 57) || 
      charCode === 46 || 
      charCode === 45 || 
      charCode === 8 || 
      charCode === 9
    ) {
      return true;
    }
    evt.preventDefault();
    return false;
  });
});



document.addEventListener('DOMContentLoaded', function () {
    const streetNameSelect = document.getElementById('street_name');
    const locationIdInput = document.getElementById('location_id');
    const numericInputs = document.querySelectorAll('input[type="number"]');

    const streetLocationMap = <?php echo json_encode($street_location_map); ?>;

    // ----------- Prevent Invalid Characters in Numeric Fields ----------
    numericInputs.forEach(input => {
        input.addEventListener('keydown', function (e) {
            if (["e", "E", "+", "-"].includes(e.key)) {
                e.preventDefault();
            }
        });

        input.addEventListener('paste', function (e) {
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            if (!/^\d*\.?\d*$/.test(paste)) {
                e.preventDefault();
                this.setCustomValidity("Only numeric values are allowed.");
                this.reportValidity();
            } else {
                this.setCustomValidity('');
            }
        });

        input.addEventListener('input', function () {
            if (this.value !== '' && isNaN(this.value)) {
                this.setCustomValidity("Only numeric values are allowed.");
                this.reportValidity();
            } else {
                this.setCustomValidity('');
            }
        });
    });

    // ---------- Location ID Auto-Fill ----------
    function updateLocationId() {
        const selectedStreet = streetNameSelect.value;
        locationIdInput.value = streetLocationMap[selectedStreet] || '';
    }

    streetNameSelect.addEventListener('change', updateLocationId);
    if (streetNameSelect.value) updateLocationId();

    // ---------- Tax Calculation ----------
    const avInput = document.getElementById('av');
    const grFlagSelect = document.getElementById('gr_flag');
    const apartmentColSelect = document.getElementById('apartment_col');
    const surchYrlyInput = document.getElementById('surch_yrly');
    const exemptionSelect = document.getElementById('exemption');

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
        const exemptionStatus = exemptionSelect.value;

        let annualPropertyTax = 0;
        const calcGrStatus = (grFlag === "New") ? "GR" : "Non-GR";

        if (isApartment) {
            annualPropertyTax = (calcGrStatus === "GR") ? Math.ceil(av * 0.3) : Math.ceil(av * 0.4);
        } else {
            if (calcGrStatus === "GR") {
                if (av <= 999) annualPropertyTax = Math.ceil(av * ((10 + av / 100) / 100));
                else if (av <= 9999) annualPropertyTax = Math.ceil(av * ((20 + av / 1000) / 100));
                else annualPropertyTax = Math.ceil(av * 0.3);
            } else {
                if (av <= 999) annualPropertyTax = Math.ceil(av * ((10 + av / 100) / 100));
                else if (av <= 17999) annualPropertyTax = Math.ceil(av * ((22 + av / 1000) / 100));
                else annualPropertyTax = Math.ceil(av * 0.4);
            }
        }

        let ptaxYrly = annualPropertyTax;
        let ptaxQtrly = Math.ceil(annualPropertyTax / 4);
        let hbtaxQtrly = Math.max(1, Math.ceil((av * 0.0025) / 4));
        let hbtaxYrly = hbtaxQtrly * 4;
        let calculatedSurchQtrly = Math.ceil(surchYrly / 4);

        if (exemptionStatus === 'Y') {
            ptaxYrly = ptaxQtrly = hbtaxYrly = hbtaxQtrly = calculatedSurchQtrly = 0;
        }

        ptaxYrlyOutput.value = ptaxYrly.toFixed(2);
        hbtaxYrlyOutput.value = hbtaxYrly.toFixed(2);
        ptaxQtrlyOutput.value = ptaxQtrly.toFixed(2);
        hbtaxQtrlyOutput.value = hbtaxQtrly.toFixed(2);
        surchQtrlyOutput.value = calculatedSurchQtrly.toFixed(2);
    }

    avInput.addEventListener('input', calculateTaxes);
    grFlagSelect.addEventListener('change', calculateTaxes);
    apartmentColSelect.addEventListener('change', calculateTaxes);
    surchYrlyInput.addEventListener('input', calculateTaxes);
    exemptionSelect.addEventListener('change', calculateTaxes);

    calculateTaxes();
});
</script>



</body>
</html>