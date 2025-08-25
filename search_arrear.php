<?php
require "db_connect.php"; // contains $conn = new mysqli(...)

$appNo = isset($_GET['application_number']) ? trim($_GET['application_number']) : '';
$assesseeNo = isset($_GET['assessee_number']) ? trim($_GET['assessee_number']) : '';

$sql = "SELECT * FROM proportionate_arrear_records WHERE 1=1";
$params = [];
$types = "";

if ($appNo !== '') {
    $sql .= " AND application_number = ?";
    $params[] = $appNo;
    $types .= "s";
}
if ($assesseeNo !== '') {
    $sql .= " AND assessee_number = ?";
    $params[] = $assesseeNo;
    $types .= "s"; // string for safety
}

$stmt = $conn->prepare($sql);
if (!$stmt) die("SQL Prepare failed: " . $conn->error);

if (!empty($params)) $stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Search Arrear Records</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background: #f4f4f4; }
.no-results { margin-top: 20px; color: red; font-weight: bold; }
.print-btn {
    padding: 6px 10px;
    background: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    text-decoration: none;
}
.print-btn:hover { background: #45a049; }
form { margin-bottom: 20px; }
input[type="text"] { padding: 6px; margin-right: 10px; width: 200px; }
input[type="submit"] { padding: 6px 12px; }
</style>
</head>
<body>

<h2>Search Arrear Records</h2>

<form method="get" action="">
    <label>Application Number:
        <input type="text" name="application_number" value="<?= htmlspecialchars($appNo) ?>">
    </label>
    <label>Assessee Number:
        <input type="text" name="assessee_number" value="<?= htmlspecialchars($assesseeNo) ?>">
    </label>
    <input type="submit" value="Search">
</form>

<?php if ($result->num_rows > 0): ?>
<table>
    <tr>
        <th>ID</th>
        <th>Application Number</th>
        <th>Applicant Name</th>
        <th>Proposed Holding Number</th>
        <th>Assessee Number</th>
        <th>Mother Holding Number</th>
        <th>Ward Number</th>
        <th>Street Name</th>
        <th>Arrear Date</th>
        <th>Mother AV</th>
        <th>Proposed AV</th>
        <th>Outstanding Due</th>
        <th>Due Up To Qtr</th>
        <th>Calculated Due</th>
        <th>Calculated Due (Words)</th>
        <th>Created At</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td><?= htmlspecialchars($row['application_number']) ?></td>
        <td><?= htmlspecialchars($row['applicant_name']) ?></td>
        <td><?= htmlspecialchars($row['proposed_holding_number']) ?></td>
        <td><?= htmlspecialchars($row['assessee_number']) ?></td>
        <td><?= htmlspecialchars($row['mother_holding_number']) ?></td>
        <td><?= htmlspecialchars($row['ward_number']) ?></td>
        <td><?= htmlspecialchars($row['street_name']) ?></td>
        <td><?= htmlspecialchars($row['arrear_date']) ?></td>
        <td><?= htmlspecialchars($row['mother_annual_valuation']) ?></td>
        <td><?= htmlspecialchars($row['proposed_annual_valuation']) ?></td>
        <td><?= htmlspecialchars($row['outstanding_due']) ?></td>
        <td><?= htmlspecialchars($row['due_up_to_qtr']) ?></td>
        <td><?= htmlspecialchars($row['calculated_due']) ?></td>
        <td><?= htmlspecialchars($row['calculated_due_words']) ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td>
            <a class="print-btn" href="print_part_payment.php?id=<?= urlencode($row['id']) ?>" target="_blank">Print</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
<div class="no-results">
    No records found<?php if ($appNo !== '' || $assesseeNo !== '') {
        echo " for Application No.: ".htmlspecialchars($appNo)." and Assessee No.: ".htmlspecialchars($assesseeNo);
    } ?>.
</div>
<?php endif; ?>

</body>
</html>
