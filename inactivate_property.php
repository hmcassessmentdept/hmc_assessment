<?php
session_start();
include 'db_connect.php'; // Ensure this sets up $conn (MySQLi)

// Redirect to login if user is not authenticated
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Match form field names here
    if (isset($_POST['new_assessee_id_to_inactivate'], $_POST['asmnt_no_to_inactivate'], $_POST['remarks_inactivate'])) {
        $new_assessee_id = trim($_POST['new_assessee_id_to_inactivate']);
        $asmnt_no = trim($_POST['asmnt_no_to_inactivate']);
        $remarks = trim($_POST['remarks_inactivate']);

        // Validate remarks
        if ($remarks === '') {
            $_SESSION['message'] = "Remarks cannot be empty.";
            $_SESSION['message_type'] = 'danger';
            header("Location: index.php");
            exit();
        }

        // Optional format validation
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $new_assessee_id) || !preg_match('/^[A-Za-z0-9_-]+$/', $asmnt_no)) {
            $_SESSION['message'] = "Invalid Assessee ID or Assessment No format.";
            $_SESSION['message_type'] = 'danger';
            header("Location: index.php");
            exit();
        }

        // Check current status
        $stmt_check = $conn->prepare("SELECT Active_Status FROM final_emut_data WHERE New_AssesseeId = ? AND Asmnt_No = ?");
        $stmt_check->bind_param("ss", $new_assessee_id, $asmnt_no);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $current_status = strtoupper(trim($row['Active_Status']));

            if ($current_status === 'N') {
                $_SESSION['message'] = "Property (New Assessee ID: " . htmlspecialchars($new_assessee_id) . ", Asmnt No: " . htmlspecialchars($asmnt_no) . ") is already inactive.";
                $_SESSION['message_type'] = 'warning';
            } else {
                // Perform update
                $stmt_update = $conn->prepare("UPDATE final_emut_data SET Active_Status = 'N', Remarks = ? WHERE New_AssesseeId = ? AND Asmnt_No = ?");
                $stmt_update->bind_param("sss", $remarks, $new_assessee_id, $asmnt_no);

                if ($stmt_update->execute() && $stmt_update->affected_rows > 0) {
                    $_SESSION['message'] = "Property (New Assessee ID: " . htmlspecialchars($new_assessee_id) . ", Asmnt No: " . htmlspecialchars($asmnt_no) . ") successfully inactivated.";
                    $_SESSION['message_type'] = 'success';
                } else {
                    $_SESSION['message'] = "Error: Could not inactivate property or no changes were made.";
                    $_SESSION['message_type'] = 'danger';
                }

                $stmt_update->close();
            }
        } else {
            $_SESSION['message'] = "Property not found in the database.";
            $_SESSION['message_type'] = 'danger';
        }

        $stmt_check->close();
    } else {
        $_SESSION['message'] = "Missing required fields.";
        $_SESSION['message_type'] = 'danger';
    }
} else {
    $_SESSION['message'] = "Invalid request method.";
    $_SESSION['message_type'] = 'danger';
}

$conn->close();
header("Location: index.php");
exit();
?>
