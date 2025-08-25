<?php
// Show all errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB connection
$host = 'localhost';
$db = 'hmc_assessment';
$user = 'root';
$pass = '0012';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get and sanitize query
$query = $_GET['query'] ?? '';
$query = trim($query);

// Start HTML
echo "<!DOCTYPE html>
<html>
<head>
    <title>Search Mutation Notices</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h2 {
            color: #1d3557;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #1d3557;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f4f4f4;
        }
        .print-link {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .search-box input[type='text'] {
            padding: 8px;
            width: 300px;
        }
        .search-box input[type='submit'] {
            padding: 8px 16px;
        }
    </style>
</head>
<body>

<h2>Search Mutation Notices</h2>

<form class='search-box' method='get' action=''>
    <input type='text' name='query' value='" . htmlspecialchars($query) . "' placeholder='Search by Ref No / App No / Applicant / Assessee / ID' required />
    <input type='submit' value='Search' />
</form>
";

// If empty search
if (empty($query)) {
    echo "<p>Please enter a search term above.</p></body></html>";
    exit;
}

// Escape input
$queryEscaped = $conn->real_escape_string($query);

// Build search query across relevant fields
$sql = "
    SELECT * FROM mutation_notices
    WHERE
        CAST(id AS CHAR) LIKE '%$queryEscaped%' OR
        ref_no LIKE '%$queryEscaped%' OR
        app_no LIKE '%$queryEscaped%' OR
        applicant_name LIKE '%$queryEscaped%' OR
        mother_assessee_no LIKE '%$queryEscaped%'
    ORDER BY created_at DESC
";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

if ($result->num_rows > 0) {
    echo "<h3>Search results for: <em>" . htmlspecialchars($query) . "</em></h3>";
    echo "<table>";
    echo "<tr>
            <th>ID</th>
            <th>Ref No</th>
            <th>App Date</th>
            <th>App No</th>
            <th>Applicant</th>
            <th>Proposed Holding</th>
            <th>Mother Assessee No</th>
            <th>Ward</th>
            <th>Street</th>
            <th>Hearing Date</th>
            <th>Time</th>
            <th>Action</th>
          </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . htmlspecialchars($row['id']) . "</td>
                <td>" . htmlspecialchars($row['ref_no']) . "</td>
                <td>" . htmlspecialchars($row['app_date']) . "</td>
                <td>" . htmlspecialchars($row['app_no']) . "</td>
                <td>" . htmlspecialchars($row['applicant_name']) . "</td>
                <td>" . htmlspecialchars($row['proposed_holding_no']) . "</td>
                <td>" . htmlspecialchars($row['mother_assessee_no']) . "</td>
                <td>" . htmlspecialchars($row['ward_no']) . "</td>
                <td>" . htmlspecialchars($row['street_name']) . "</td>
                <td>" . htmlspecialchars($row['hearing_date']) . "</td>
                <td>" . htmlspecialchars($row['hearing_time']) . "</td>
                <td><a class='print-link' href='print_notice.php?id=" . urlencode($row['id']) . "' target='_blank'>🖨️ Print</a></td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "<p>No results found for <strong>" . htmlspecialchars($query) . "</strong></p>";
}

echo "</body></html>";

$conn->close();
?>
