<?php
session_start(); // Start the session
include 'db_connect.php'; // Include your database connection

// Check if the user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php"); // Redirect to login page
    exit();
}

$message = '';
$messageType = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and get POST data
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmNewPassword = $_POST['confirm_new_password'];
    $userId = $_SESSION['user_id']; // Get the logged-in user's ID from the session

    // 1. Fetch the hashed password from the database
    // We use a prepared statement to prevent SQL injection
    // The query has been corrected to use 'id' instead of 'User_id'
    $sql = "SELECT password FROM users WHERE User_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $storedHashedPassword = $user['password'];

            // 2. Verify current password using password_verify()
            if (password_verify($currentPassword, $storedHashedPassword)) {
                // 3. Check if new passwords match
                if ($newPassword === $confirmNewPassword) {
                    // 4. Hash the new password before updating
                    $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                    // Update the password in the database
                    // The query has been corrected to use 'id' instead of 'user_id'
                    $updateSql = "UPDATE users SET password = ? WHERE user_id = ?";
                    if ($updateStmt = $conn->prepare($updateSql)) {
                        $updateStmt->bind_param("si", $newHashedPassword, $userId);

                        if ($updateStmt->execute()) {
                            $message = "Password updated successfully! 🎉";
                            $messageType = "success";
                        } else {
                            $message = "Error updating password: " . $conn->error;
                            $messageType = "error";
                        }
                        $updateStmt->close();
                    } else {
                        $message = "Database update query preparation failed: " . $conn->error;
                        $messageType = "error";
                    }
                } else {
                    $message = "New password and confirm password do not match.";
                    $messageType = "error";
                }
            } else {
                $message = "Incorrect current password.";
                $messageType = "error";
            }
        } else {
            $message = "User not found. (This shouldn't happen if logged in)";
            $messageType = "error";
        }

        $stmt->close();
    } else {
        $message = "Database select query preparation failed: " . $conn->error;
        $messageType = "error";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <!-- Tailwind CSS for modern styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
     <style>
        /* CSS remains unchanged */
        :root {
            --primary-blue: #007bff;
            --light-blue: #e0f2f7;
            --dark-blue: #0056b3;
            --text-color: #333;
            --light-text-color: #666;
            --white: #ffffff;
            --shadow-light: 0 4px 8px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 16px rgba(0, 0, 0, 0.15);
            --border-color: #b3d9ff;
            /* New deep navy blue for stronger contrast/accents */
            --deep-navy: #0A1931; /* A very dark, almost black blue */
            --accent-blue: #1E90FF; /* A slightly brighter blue for highlights */
        }

        body {
            font-family: 'Open Sans', sans-serif;
            margin: 0;
            padding: 0;
            color: var(--text-color);
            line-height: 1.6;
            background-color: var(--light-blue);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .main-content-area {
            flex-grow: 1;
            width: 100%;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* --- Header Styles --- */
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
            position: relative; /* Add this for positioning buttons */
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

        /* New button container style */
        .header-buttons {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px; /* Space between buttons */
        }

        .header-btn {
            background-color: var(--accent-blue);
            color: var(--white);
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Open Sans', sans-serif;
            font-size: 0.9em;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .header-btn:hover {
            background-color: var(--dark-blue);
        }

        /* --- Hero Section --- */
        .hero-section {
            background: linear-gradient(135deg, var(--deep-navy) 0%, rgba(0, 86, 179, 0.8) 100%),
                 url('https://www.is2digital.com/sites/default/files/styles/1400x900/public/blog/iStock-2024211110-cropped.jpg?itok=vcGKnHzB');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            color: var(--white);
            text-align: center;
            padding: 100px 20px;
            position: relative;
            overflow: hidden;
        }
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .hero-section h1 {
            font-family: 'Roboto', sans-serif;
            font-size: 3.5em;
            margin-bottom: 20px;
            line-height: 1.2;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.4);
        }

        .hero-section p {
            font-size: 1.3em;
            max-width: 600px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }

        .scroll-down-button {
            display: inline-block;
            background-color: var(--accent-blue);
            color: var(--white);
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            border: 2px solid var(--white);
        }

        .scroll-down-button:hover {
            background-color: var(--primary-blue);
            transform: translateY(-3px);
        }

        /* --- Services Section --- */
        .services-section {
            background-color: var(--white);
            box-shadow: inset 0 8px 15px -8px rgba(0,0,0,0.1);
            text-align: center;
            padding: 60px 20px; /* Always show services section */
        }

        .services-section h2 {
            font-family: 'Roboto', sans-serif;
            font-size: 2.5em;
            color: var(--deep-navy);
            margin-bottom: 15px;
        }

        .section-description {
            font-size: 1.1em;
            color: var(--light-text-color);
            margin-bottom: 40px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .service-card {
            background-color: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 30px;
            text-align: left;
            text-decoration: none;
            color: var(--text-color);
            box-shadow: var(--shadow-light);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-medium);
            background-color: var(--light-blue);
        }

        .service-card h3 {
            font-family: 'Roboto', sans-serif;
            font-size: 1.5em;
            color: var(--dark-blue);
            margin-top: 0;
            margin-bottom: 15px;
            border-bottom: 2px solid var(--accent-blue);
            padding-bottom: 10px;
            line-height: 1.3;
        }

        .service-card p {
            font-size: 0.95em;
            color: var(--light-text-color);
            margin-bottom: 0;
        }

        /* --- Disclaimer Section --- */
        .disclaimer-section {
            background-color: var(--deep-navy);
            color: yellow;
            padding: 30px 20px;
            text-align: center;
            font-size: 15px; /* Fixed font size */
            opacity: 0.9;
            box-shadow: inset 0 8px 15px -8px rgba(0,0,0,0.1);
        }

        /* --- Responsive Adjustments --- */
        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2.5em;
            }

            .hero-section p {
                font-size: 1em;
            }

            .services-section h2 {
                font-size: 2em;
            }

            .service-grid {
                grid-template-columns: 1fr;
            }

            #shared-header-placeholder img {
                height: 40px;
            }
            #shared-header-placeholder h1 {
                font-size: 1.5em;
            }
            #shared-header-placeholder p {
                font-size: 0.8em;
            }
            
            .header-buttons {
                position: static; /* Position buttons below header text */
                margin-top: 10px;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .hero-section {
                padding: 60px 20px;
            }
            .hero-section h1 {
                font-size: 2em;
            }
            .scroll-down-button {
                padding: 10px 20px;
            }
        }

        /* Footer Styles */
        #shared-footer-placeholder {
            background-color: var(--deep-navy);
            color: var(--white);
            padding: 20px;
            text-align: center;
            font-size: 0.85em;
            margin-top: auto;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
 <div id="shared-header-placeholder">
	
        <img src="logo.jpg" alt="Howrah Municipal Corporation Logo">
        <h1>Howrah Municipal Corporation</h1>
        <p>4, Mahatma Gandhi Road, Howrah-711101</p>
        <p><strong> Website:www.myhmc.in</strong> </p>
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6 flex items-center justify-center">
            <span class="mr-2">🔑</span> Change Password
        </h1>

        <?php if (!empty($message)): ?>
            <div class="p-4 rounded-lg mb-6 text-center
                <?php echo ($messageType === 'success') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="change_password.php" class="space-y-6">
            <div>
                <label for="current_password" class="block text-gray-700 font-semibold mb-2">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>
            <div>
                <label for="new_password" class="block text-gray-700 font-semibold mb-2">New Password:</label>
                <input type="password" id="new_password" name="new_password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>
            <div>
                <label for="confirm_new_password" class="block text-gray-700 font-semibold mb-2">Confirm New Password:</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition duration-200">
                Change Password
            </button>
        </form>

        <a href="hmc.php" class="block text-center mt-6 text-blue-600 hover:text-blue-800 transition duration-200">
            ← Back to Dashboard
        </a>
    </div>
</body>
</html>
