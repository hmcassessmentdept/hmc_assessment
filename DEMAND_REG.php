<?php
// Database connection parameters
$servername = "localhost"; // Default for XAMPP
$username = "root";        // Default for XAMPP
$password = "1234";            // Default for XAMPP (empty password)
$dbname = "HMC_ASSESSMENT"; // *** IMPORTANT: Replace with your actual database name ***
$tablename = "final_EMUT_data";   // *** IMPORTANT: Replace with your actual table name ***
$status_column = "active_status"; // *** IMPORTANT: Replace with your actual status column name ***
$active_value = "Y"; // *** IMPORTANT: Replace with the actual value for "active" status ***

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to select data with active status 'Y'
// Ensure column names are correct and case-sensitive if applicable
$sql = "SELECT * FROM $tablename WHERE $status_column = '$active_value'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Status 'Y' Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        .no-data {
            text-align: center;
            color: #666;
            padding: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.8em;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Report for Active Status '<?php echo htmlspecialchars($active_value); ?>'</h1>

        <?php
        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<thead><tr>";
            // Output table headers
            while ($fieldinfo = $result->fetch_field()) {
                echo "<th>" . htmlspecialchars($fieldinfo->name) . "</th>";
            }
            echo "</tr></thead>";
            echo "<tbody>";

            // Output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p class='no-data'>No records found with active status '" . htmlspecialchars($active_value) . "'.</p>";
        }
        $conn->close();
        ?>
        <div class="footer">
            Report generated on <?php echo date("Y-m-d H:i:s"); ?>
        </div>
    </div>
</body>
</html>