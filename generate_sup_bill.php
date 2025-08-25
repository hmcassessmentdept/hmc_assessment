<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "0012";
$dbname = "hmc_assessment";

// Connect to MySQL
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log("DB Connection Error: " . $conn->connect_error);
    die("Database connection failed.");
}

// Helper functions
function fetch_all($conn, $query, $types = "", $params = []) {
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("SQL Error: " . $conn->error);
        return false;
    }
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function fetch_one($conn, $query, $types = "", $params = []) {
    $results = fetch_all($conn, $query, $types, $params);
    return $results[0] ?? null;
}

// Variables
$supply_id = null;
$assessment = null;
$search_error = '';
$assessment_list = [];

// Handle search
if (isset($_GET['search'])) {
    $search_id = $_GET['search_id'] ?? '';
    $search_holding = $_GET['search_holding'] ?? '';
    $search_ward = $_GET['search_ward'] ?? '';

    if (!empty($search_id)) {
        $assessment_list = fetch_all(
            $conn,
            "SELECT supply_id, new_assessee_id, holding_no, ward_no, new_start, new_end FROM supplementary_details WHERE new_assessee_id = ?",
            "i",
            [$search_id]
        );

        if (isset($_GET['supply_id'])) {
            $supply_id = intval($_GET['supply_id']);
        } elseif (count($assessment_list) === 1) {
            $supply_id = $assessment_list[0]['supply_id'];
        } elseif (count($assessment_list) === 0) {
            $search_error = "No records found for this Assessee ID.";
        }
    } else {
        $query = "SELECT supply_id FROM supplementary_details WHERE 1";
        $params = [];
        $types = '';

        if (!empty($search_holding)) {
            $query .= " AND holding_no = ?";
            $types .= "s";
            $params[] = $search_holding;
        }
        if (!empty($search_ward)) {
            $query .= " AND ward_no = ?";
            $types .= "i";
            $params[] = $search_ward;
        }

        if (!empty($params)) {
            $row = fetch_one($conn, $query, $types, $params);
            $supply_id = $row['supply_id'] ?? null;
            if (!$supply_id) $search_error = "No record found.";
        } else {
            $search_error = "Please enter at least one search field.";
        }
    }
}

// Fetch data
if ($supply_id) {
    $assessment = fetch_one($conn, "SELECT * FROM supplementary_details WHERE supply_id = ?", "i", [$supply_id]);
    $old_avs = fetch_all($conn, "SELECT * FROM supplementary_old_av_periods WHERE supply_id = ?", "i", [$supply_id]);
    $quarterlies = fetch_all($conn, "SELECT * FROM supplementary_quarterly_calculations WHERE supply_id = ?", "i", [$supply_id]);
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Supplementary Bill Search</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
/* Styling omitted here for brevity — paste your full CSS here from earlier response */

body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    background: #eef2f7;
    color: #333;
    line-height: 1.6;
    text-transform: uppercase; /* THIS MAKES ALL TEXT UPPERCASE */

}

.container {
    max-width: 1100px;
    margin: 40px auto;
    padding: 30px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}

h1, h2, h3 {
    text-align: center;
    margin-bottom: 20px;
    color: #2c3e50;
}

h1 { font-size: 2rem; }
h2 { font-size: 1.4rem; color: #6c757d; }

.search-form {
    text-align: center;
    margin-bottom: 30px;
}

.search-form input {
    padding: 12px 15px;
    margin: 6px;
    border: 1px solid #ccc;
    border-radius: 8px;
    width: 200px;
}

.search-form input:focus {
    border-color: #0d6efd;
    outline: none;
}

.search-form button {
    background: linear-gradient(to right, #0d6efd, #2563eb);
    color: #fff;
    padding: 12px 25px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
}

.search-form button:hover {
    background: linear-gradient(to right, #0b5ed7, #1d4ed8);
}

.error {
    color: #e63946;
    margin-top: 10px;
    font-weight: 500;
}

.card {
    background: #f8f9fa;
    padding: 15px 20px;
    margin: 10px 0;
    border-radius: 10px;
    border: 1px solid #ddd;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

.card:hover {
    background-color: #dbeafe;
    border-color: #60a5fa;
}

.card.active {
    background-color: #93c5fd !important;
    border: 2px solid #2563eb;
    font-weight: bold;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 25px;
    font-size: 15px;
    background: #fff;
}

th, td {
    padding: 14px;
    border: 1px solid #e0e0e0;
    text-align: center;
}

th {
    background-color: #f1f5f9;
    color: #2d3748;
    font-weight: 600;
}

tr:nth-child(even) {
    background-color: #f9fafb;
}

.box-section {
    border: 1px solid #dee2e6;
    border-radius: 12px;
    background-color: #fdfefe;
    box-shadow: 0 4px 8px rgba(0,0,0,0.03);
    padding: 20px;
    margin-top: 30px;
}

.print-btn {
    text-align: center;
    margin-top: 30px;
}

.print-btn button {
    background-color: #198754;
    color: white;
    padding: 12px 30px;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
}

.print-btn button:hover {
    background-color: #157347;
}

@media (max-width: 768px) {
    .search-form input, .search-form button {
        width: 90%;
        margin: 10px auto;
        display: block;
    }

    table, thead, tbody, th, td, tr {
        font-size: 13px;
    }

    .card {
        font-size: 14px;
    }
}
    </style>
</head>
<body>
<div class="container">
    <div style="text-align: center;">
        <img src="HEADER.JPG" alt="HMC Logo" style="height: 180px; width: auto;">
    </div>

    <h2>Supplementary Tax Bill Search Portal</h2>

    <div class="search-form">
        <form method="get">
            <input type="number" name="search_id" placeholder="Assessee ID" value="<?= htmlspecialchars($_GET['search_id'] ?? '') ?>">
            <input type="text" name="search_holding" placeholder="Holding No" value="<?= htmlspecialchars($_GET['search_holding'] ?? '') ?>">
            <input type="number" name="search_ward" placeholder="Ward No" value="<?= htmlspecialchars($_GET['search_ward'] ?? '') ?>">
            <button type="submit" name="search" value="1">Search</button>
        </form>
        <?php if ($search_error): ?><div class="error"><?= $search_error ?></div><?php endif; ?>
    </div>

    <?php if (!empty($assessment_list) && !$supply_id): ?>
        <h3>Multiple Supplementary Bills Found for Assessee ID <?= htmlspecialchars($_GET['search_id']) ?>:</h3>
        <?php foreach ($assessment_list as $a): 
            $isActive = ($a['supply_id'] == ($_GET['supply_id'] ?? null)); ?>
            <div class="card <?= $isActive ? 'active' : '' ?>"
                 onclick="window.location.href='?search=1&search_id=<?= $a['new_assessee_id'] ?>&supply_id=<?= $a['supply_id'] ?>'">
                <strong>Bill No:</strong> <?= $a['supply_id'] ?> |
                <strong>Holding:</strong> <?= $a['holding_no'] ?> |
                <strong>Ward:</strong> <?= $a['ward_no'] ?> |
                <strong>Period:</strong> <?= $a['new_start'] ?> to <?= $a['new_end'] ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($assessment): ?>
        <h3>Supplementary Bill Details</h3>
        <table>
            <tr>
                <th>Supplementary Bill No.</th><td><?= $assessment['supply_id'] ?></td>
                <th>Assessee ID</th><td><?= $assessment['new_assessee_id'] ?></td>
            </tr>
            <tr>
                <th>Bill Generation Date</th><td><?= date('d-m-Y') ?></td>
                <th colspan="2"></th>
            </tr>
            <tr>
                <th>Ward No</th><td><?= $assessment['ward_no'] ?></td>
                <th>Holding No</th><td><?= $assessment['holding_no'] ?></td>
            </tr>
            <tr>
                <th>Street Name</th><td colspan="3"><?= $assessment['street_name'] ?></td>
            </tr>
        </table>

        <h3>New AV Details</h3>
        <div class="box-section">
            <table>
                <tr>
                    <th>New AV</th><th>Effective Start</th><th>Effective End</th><th>Start Qtr</th>
                    <th>End Qtr</th><th>Ward Type</th><th>Property Type</th>
                    <th>Surcharge</th><th>SC Start</th><th>SC End</th>
                </tr>
                <tr>
                    <td><?= $assessment['new_av'] ?></td><td><?= $assessment['new_start'] ?></td><td><?= $assessment['new_end'] ?></td>
                    <td><?= $assessment['new_effective_quarter'] ?></td><td><?= $assessment['new_end_quarter'] ?></td>
                    <td><?= $assessment['current_ward_type'] ?></td><td><?= $assessment['current_property_type'] ?></td>
                    <td><?= $assessment['new_surcharge_annual'] ?></td><td><?= $assessment['new_surcharge_start'] ?></td><td><?= $assessment['new_surcharge_end'] ?></td>
                </tr>
            </table>
        </div>

        <h3>Old Assessed Value Periods</h3>
        <table>
            <tr><th>Old AV</th><th>Start</th><th>End</th><th>Ward Type</th><th>Property Type</th><th>Surcharge</th><th>SC Start</th><th>SC End</th></tr>
            <?php foreach ($old_avs as $row): ?>
                <tr>
                    <td><?= $row['old_av'] ?></td><td><?= $row['start_date'] ?></td><td><?= $row['end_date'] ?></td>
                    <td><?= $row['ward_type'] ?></td><td><?= $row['property_type'] ?></td>
                    <td><?= $row['annual_surcharge'] ?></td><td><?= $row['surcharge_start'] ?></td><td><?= $row['surcharge_end'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3>Quarterly Calculations</h3>
        <table>
            <tr>
                <th>FY</th><th>Qtr</th><th>Old AV</th><th>New AV</th><th>Old PT</th><th>HB Old</th><th>Old SC</th>
                <th>New PT</th><th>HB New</th><th>New SC</th><th>Diff PT</th><th>Diff HB</th><th>Diff SC</th><th>Total Diff</th>
            </tr>
            <?php $total = 0; foreach ($quarterlies as $q): $total += $q['total_diff']; ?>
                <tr>
                    <td><?= $q['fy'] ?></td><td><?= $q['qtr'] ?></td><td><?= $q['old_av'] ?></td><td><?= $q['new_av'] ?></td>
                    <td><?= $q['old_prop_tax'] ?></td><td><?= $q['hb_old'] ?></td><td><?= $q['old_sc_qtr'] ?></td>
                    <td><?= $q['new_prop_tax'] ?></td><td><?= $q['hb_new'] ?></td><td><?= $q['new_sc_qtr'] ?></td>
                    <td><?= $q['diff_pt'] ?></td><td><?= $q['diff_hb'] ?></td><td><?= $q['diff_sc'] ?></td><td><strong><?= $q['total_diff'] ?></strong></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <th colspan="13" style="text-align:right;">Grand Total</th>
                <th><strong>₹ <?= number_format($total, 2) ?></strong></th>
            </tr>
        </table>

        <div class="print-btn">
            <button onclick="window.print()">🖨️ Print This Bill</button>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
