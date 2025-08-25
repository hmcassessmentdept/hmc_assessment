<?php
// Start output buffering to prevent "Headers already sent" errors.
ob_start();

// Secure session configuration
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

include "db_connect.php"; // DB connection

$errorMessage = "";
$roles = [];

// Fetch roles for dropdown BEFORE processing login form
$roleQuery = "SELECT role_id, role_name FROM roles ORDER BY role_name";
$roleResult = $conn->query($roleQuery);

if ($roleResult && $roleResult->num_rows > 0) {
    while ($row = $roleResult->fetch_assoc()) {
        $roles[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get posted values safely
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $role_id = trim($_POST["role_id"]);

    // The SQL query now fetches the user record including the user's ID.
    $sql = "SELECT u.user_id, u.username, u.password, u.role_id, r.role_name
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            WHERE u.username = ? AND u.role_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $username, $role_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Securely verify the password
            if (password_verify($password, $user['password'])) {
                // Login successful
                // CRITICAL: Set these two session variables.
                $_SESSION["loggedin"] = true;
                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role_id"] = $user["role_id"];
                $_SESSION["role_name"] = $user["role_name"];

                header("Location: hmc.php");
                exit;
            } else {
                $errorMessage = "Invalid username, password, or role.";
            }
        } else {
            $errorMessage = "Invalid username, password, or role.";
        }
        $stmt->close();
    } else {
        $errorMessage = "Database query preparation failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Assessment System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(rgba(0, 51, 102, 0.85), rgba(0, 51, 102, 0.45)),
                        url('https://www.myhmc.in/wp-content/uploads/2020/03/IMG-1411.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: #fff;
            padding: 35px 30px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 400px;
            text-align: center;
            animation: fadeIn 0.8s ease;
        }

        h1 {
            font-size: 26px;
            color: #003366;
            margin-bottom: 18px;
        }

        .form-group {
            margin-bottom: 18px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
        }

        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 12px;
            border: 1px solid #bbb;
            border-radius: 8px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input:focus, select:focus {
            border-color: #0056b3;
            box-shadow: 0 0 6px rgba(0,86,179,0.3);
        }

        button {
            width: 100%;
            padding: 12px;
            background: #0056b3;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 17px;
            cursor: pointer;
            font-weight: bold;
            letter-spacing: 0.5px;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background: #003d80;
            transform: translateY(-2px);
        }

        .error-message {
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }

        #shared-header-placeholder img {
            height: 90px;
            margin-bottom: 8px;
        }

        #shared-header-placeholder h1 {
            font-size: 20px;
            color: #003366;
            margin: 4px 0;
        }

        #shared-header-placeholder p {
            font-size: 13px;
            color: #555;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div id="shared-header-placeholder">
            <img src="logo.jpg" alt="Howrah Municipal Corporation Logo">
            <h1>Howrah Municipal Corporation</h1>
            <p>4, Mahatma Gandhi Road, Howrah-711101</p>
            <p><strong>Website: www.myhmc.in</strong></p>
        </div>

        <h1>🔐 Assessment Login</h1>
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
            </div>
            <div class="form-group">
                <label for="role_id">Role</label>
                <select id="role_id" name="role_id" required>
                    <option value="">Select Role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo htmlspecialchars($role['role_id']); ?>">
                            <?php echo htmlspecialchars($role['role_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Login</button>
            
        </form>
        <div class="footer">
            © <?php echo date("Y"); ?> Howrah Municipal Corporation
        </div>
    </div>
</body>
</html>
