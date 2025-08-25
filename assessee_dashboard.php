<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db_connect.php';

$sql = "SELECT New_AssesseeId, Asmnt_No, Final_AssesseeName, WARD_NO, `A.V.`, Active_Status FROM FINAL_Emut_data WHERE Active_Status = 'Active' ORDER BY New_AssesseeId DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>📋 Assessee Dashboard</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; }
        th { background-color: #eee; }
    </style>
</head>
<body>
    <h2>📋 Assessee Data Dashboard</h2>
    <p>👤 Logged in as: <?php echo $_SESSION['username']; ?> | <a href='logout.php'>Logout</a></p>
    <p><a href="add_assessee.php">➕ নতুন Assessee যুক্ত করুন</a></p>

    <table>
        <tr>
            <th>New Assessee ID</th>
            <th>ASMNT_NO</th>
            <th>Name</th>
            <th>Ward No</th>
            <th>AV</th>
            <th>Status</th>
            <th>✏️ Edit</th>
            <th>🚫 Inactivate</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['New_AssesseeId'] ?></td>
            <td><?= $row['Asmnt_No'] ?></td>
            <td><?= $row['Final_AssesseeName'] ?></td>
            <td><?= $row['WARD_NO'] ?></td>
            <td><?= $row['A.V.'] ?></td>
            <td><?= $row['Active_Status'] ?></td>
            <td><a href="edit_assessee.php?New_AssesseeId=<?= $row['New_AssesseeId'] ?>&Asmnt_No=<?= $row['Asmnt_No'] ?>">Edit</a></td>
            <td><a href="delete_assessee.php?New_AssesseeId=<?= $row['New_AssesseeId'] ?>&Asmnt_No=<?= $row['Asmnt_No'] ?>" onclick="return confirm('Inactive করবেন?')">Inactivate</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>