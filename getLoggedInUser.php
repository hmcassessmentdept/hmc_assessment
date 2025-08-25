<?php
// Start the session to access the user data
session_start();

header('Content-Type: application/json');

// Check if the user is logged in and the 'username' is in the session.
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['username'])) {
    // Fetch the username from the session variable.
    $user = [
        'name' => $_SESSION['username']
    ];
} else {
    // Fallback if the session variables aren't set.
    $user = [
        'name' => 'Guest'
    ];
}

// Encode the user array as JSON and send it.
echo json_encode($user);
?>
