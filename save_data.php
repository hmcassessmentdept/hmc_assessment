<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// ✅ Check if username is stored in session
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}
$generated_by = $_SESSION['username'];

// ✅ Fatal error handler
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error) {
        while (ob_get_level()) ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Fatal Error: ' . $error['message'],
            'file'    => $error['file'],
            'line'    => $error['line']
        ]);
        exit;
    }
});

ob_start();

// ✅ DB config
$servername = "localhost";
$username   = "root";
$password   = "0012";
$dbname     = "hmc_assessment";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// ✅ Read and decode input JSON
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid or empty JSON input.']);
    exit;
}

$assessment   = $data['assessment_details'] ?? null;
$old_avs      = $data['old_av_periods'] ?? [];
$quarterlies  = $data['quarterly_calculations'] ?? [];

if (!$assessment) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Missing assessment_details in input.']);
    exit;
}

try {
    $conn->begin_transaction();

    // ✅ Insert into supplementary_details
    $stmt = $conn->prepare("
        INSERT INTO supplementary_details (
            assessee_id, new_assessee_id, old_ulb_id, ward_no, holding_no, street_name,
            new_av, current_ward_type, current_property_type,
            new_start, new_end, new_effective_quarter, new_end_quarter,
            new_surcharge_annual, new_surcharge_start, new_surcharge_end,
            generated_by, generated_on
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    if (!$stmt) throw new Exception("Prepare failed (details): " . $conn->error);

    $stmt->bind_param(
        "iiiissdssssssssss",
        $assessment['new_assessee_id'],
        $assessment['new_assessee_id'],
        $assessment['old_ulb_id'],
        $assessment['ward_no'],
        $assessment['holding_no'],
        $assessment['street_name'],
        $assessment['new_av'],
        $assessment['current_ward_type'],
        $assessment['current_property_type'],
        $assessment['new_start'],
        $assessment['new_end'],
        $assessment['new_effective_quarter'],
        $assessment['new_end_quarter'],
        $assessment['new_surcharge_annual'],
        $assessment['new_surcharge_start'],
        $assessment['new_surcharge_end'],
        $generated_by
    );

    if (!$stmt->execute()) throw new Exception("Insert failed (details): " . $stmt->error);
    $supply_id = $stmt->insert_id;
    $stmt->close();

    // ✅ Insert old AV periods
    foreach ($old_avs as $period) {
        $stmt = $conn->prepare("
            INSERT INTO supplementary_old_av_periods (
                supply_id, assessee_id, old_av, start_date, end_date,
                effective_quarter, end_quarter,
                ward_type, property_type,
                annual_surcharge, surcharge_start, surcharge_end
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) throw new Exception("Prepare failed (old_av): " . $conn->error);

        $stmt->bind_param(
            "iidssssssdss",
            $supply_id,
            $assessment['new_assessee_id'],
            $period['av'],
            $period['start'],
            $period['end'],
            $period['effectiveQuarterOld'],
            $period['endQuarterOld'],
            $period['wardType'],
            $period['propertyType'],
            $period['annualSurchargeOld'],
            $period['surchargeStartOld'],
            $period['surchargeEndOld']
        );
        if (!$stmt->execute()) throw new Exception("Insert failed (old_av): " . $stmt->error);
        $stmt->close();
    }

    // ✅ Insert quarterly calculations
    foreach ($quarterlies as $q) {
        $stmt = $conn->prepare("
            INSERT INTO supplementary_quarterly_calculations (
                supply_id, assessee_id, fy, qtr, old_av, new_av,
                old_prop_tax, hb_old, old_sc_qtr,
                new_prop_tax, hb_new, new_sc_qtr,
                diff_pt, diff_hb, diff_sc, total_diff
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) throw new Exception("Prepare failed (quarterly): " . $conn->error);

        $stmt->bind_param(
            "iissdddddddddddd",
            $supply_id,
            $assessment['new_assessee_id'],
            $q['fy'],
            $q['qtr'],
            $q['old_av'],
            $q['new_av'],
            $q['old_prop_tax'],
            $q['hb_old'],
            $q['old_sc_qtr'],
            $q['new_prop_tax'],
            $q['hb_new'],
            $q['new_sc_qtr'],
            $q['diff_pt'],
            $q['diff_hb'],
            $q['diff_sc'],
            $q['total_diff']
        );
        if (!$stmt->execute()) throw new Exception("Insert failed (quarterly): " . $stmt->error);
        $stmt->close();
    }

    $conn->commit();
    while (ob_get_level()) ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Data saved successfully!']);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    while (ob_get_level()) ob_end_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

$conn->close();
