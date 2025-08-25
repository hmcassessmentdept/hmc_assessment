<?php
session_start();
include 'db_connect.php'; // Database connection

// Redirect if not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$action = $_POST['action'] ?? '';
$searchCertId = trim($_POST['search_cert_id'] ?? '');
$searchFinalAssesseeId = trim($_POST['search_final_assesseeid'] ?? '');
$certificateSearchResults = [];
$message = '';
$message_type = '';

// Handle Certificate Search
if ($action === 'search_certificate') {
    if (!empty($searchCertId) || !empty($searchFinalAssesseeId)) {
        $sql = "SELECT certificate_id, assessee_id, assessment_no, final_assessee_name, memo_date 
                FROM generated_certificates WHERE 1=1";
        $params = [];
        $types = '';

        if (!empty($searchCertId)) {
            $sql .= " AND certificate_id = ?";
            $params[] = $searchCertId;
            $types .= 'i';
        }

        if (!empty($searchFinalAssesseeId)) {
            $sql .= " AND assessee_id LIKE ?";
            $params[] = "%" . $searchFinalAssesseeId . "%";
            $types .= 's';
        }

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $certificateSearchResults = $result->fetch_all(MYSQLI_ASSOC);
                $message = "Found " . count($certificateSearchResults) . " certificate(s).";
                $message_type = "success";
            } else {
                $message = "No certificates found matching your criteria.";
                $message_type = "warning";
            }

            $stmt->close();
        } else {
            $message = "Database error: " . $conn->error;
            $message_type = "danger";
        }
    } else {
        $message = "Please enter a Certificate ID or Final Assessee ID to search.";
        $message_type = "info";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mutation Certificate Search</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f0f4f8;
            margin: 0;
            padding: 0;
        }
        header, .container {
            max-width: 900px;
            margin: auto;
            padding: 20px;
        }
        header {
            background: #003366;
            color: #fff;
            text-align: center;
            border-radius: 6px;
        }
        h2 {
            margin-top: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }
        input[type=text] {
            width: 100%;
            padding: 8px;
            font-size: 1em;
        }
        .btn {
            background: #0056b3;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn:hover {
            background: #004494;
        }
        .alert {
            padding: 12px;
            margin: 20px 0;
            border-radius: 4px;
            font-weight: bold;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .alert-warning { background: #fff3cd; color: #856404; }
        .alert-info { background: #d1ecf1; color: #0c5460; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        th {
            background: #f0f0f0;
        }
        .action-cell {
            text-align: center;
        }
    </style>
</head>
<body>

<header>
    <h1>Howrah Municipal Corporation</h1>
    <p>Assessment Department – Mutation Certificate Search</p>
</header>

<div class="container">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <h2>Search Existing Certificates</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="search_cert_id">Certificate ID:</label>
            <input type="text" name="search_cert_id" id="search_cert_id" value="<?php echo htmlspecialchars($searchCertId); ?>">
        </div>
        <div class="form-group">
            <label for="search_final_assesseeid">Final Assessee ID:</label>
            <input type="text" name="search_final_assesseeid" id="search_final_assesseeid" value="<?php echo htmlspecialchars($searchFinalAssesseeId); ?>">
        </div>
        <button type="submit" name="action" value="search_certificate" class="btn">Search Certificate</button>
    </form>

    <?php if (!empty($certificateSearchResults)): ?>
        <h3>Search Results</h3>
        <table>
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
                        <td><?php echo date('d-M-Y', strtotime($cert['memo_date'])); ?></td>
                        <td class="action-cell">
                            <a href="print_certificate.php?id=<?php echo urlencode($cert['certificate_id']); ?>" target="_blank" class="btn">Print</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>
