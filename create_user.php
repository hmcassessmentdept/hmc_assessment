<?php
session_start();

// Database connection details
// You'll need to fill these out with your actual database credentials
$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "hmc_assessment";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in and is an Admin
// You will need to have a login system that populates $_SESSION
if (!isset($_SESSION['username']) || $_SESSION['role_name'] !== 'Admin') {
    // For this example, we will assume an admin is logged in.
    // In a real application, you would redirect to a login page.
    // header("Location: login.php");
    // exit();

    // TEMPORARY: For demonstration, we will assume an admin role and ID
    $_SESSION['username'] = 'admin_user';
    $_SESSION['role_name'] = 'Admin';
    $_SESSION['role_id'] = 1; // Assuming 'Admin' has role_id 1
}

$message = "";

// --- Handle Form Submissions ---

// 1. Handle User Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $new_username = $_POST['new_username'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $selected_role_id = $_POST['new_user_role'];

    $sql = "INSERT INTO users (username, password, role_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $new_username, $new_password, $selected_role_id);

    if ($stmt->execute()) {
        $message = "User '{$new_username}' created successfully.";
    } else {
        $message = "Error creating user: " . $conn->error;
    }
    $stmt->close();
}

// 2. Handle Role Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_role'])) {
    $new_role_name = $_POST['new_role_name'];

    $sql = "INSERT INTO roles (role_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $new_role_name);

    if ($stmt->execute()) {
        $message = "Role '{$new_role_name}' created successfully.";
    } else {
        $message = "Error creating role: " . $conn->error;
    }
    $stmt->close();
}

// 3. Handle Service Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_service'])) {
    $service_name = $_POST['service_name'];
    $link = $_POST['link'];
    $description = $_POST['description'];

    // First, insert into services table
    $sql_service = "INSERT INTO services (service_name, link, description) VALUES (?, ?, ?)";
    $stmt_service = $conn->prepare($sql_service);
    $stmt_service->bind_param("sss", $service_name, $link, $description);

    if ($stmt_service->execute()) {
        $service_id = $conn->insert_id;
        $stmt_service->close();

        // Then, insert into permissions table with a default permission_name
        $permission_name = "access_" . str_replace(' ', '_', strtolower($service_name));
        $sql_permission = "INSERT INTO permissions (permission_name, service_id) VALUES (?, ?)";
        $stmt_permission = $conn->prepare($sql_permission);
        $stmt_permission->bind_param("si", $permission_name, $service_id);

        if ($stmt_permission->execute()) {
            $message = "Service '{$service_name}' and its permission created successfully.";
        } else {
            $message = "Error creating permission: " . $conn->error;
        }
        $stmt_permission->close();
    } else {
        $message = "Error creating service: " . $conn->error;
    }
}

// 4. Handle Permission Assignment to Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_permission'])) {
    $role_id_to_assign = $_POST['role_to_assign'];
    $permission_id_to_assign = $_POST['permission_to_assign'];

    $sql = "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $role_id_to_assign, $permission_id_to_assign);

    if ($stmt->execute()) {
        $message = "Permission assigned to role successfully.";
    } else {
        $message = "Error assigning permission: " . $conn->error;
    }
    $stmt->close();
}

// --- Fetch Data for Forms ---

// Fetch all roles for dropdowns
$roles_result = $conn->query("SELECT * FROM roles");
$roles = $roles_result->fetch_all(MYSQLI_ASSOC);

// Fetch all permissions for dropdowns
$permissions_result = $conn->query("SELECT p.permission_id, p.permission_name, s.service_name FROM permissions p JOIN services s ON p.service_id = s.service_id");
$permissions = $permissions_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - User and Service Management</title>
    <!-- Tailwind CSS CDN for easy styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <div class="container mx-auto p-4 md:p-8">
        <h1 class="text-4xl font-bold text-center text-blue-800 mb-6">Admin Dashboard</h1>

        <?php if (!empty($message)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?php echo htmlspecialchars($message); ?></p>
        </div>
        <?php endif; ?>

        <!-- Main Grid for Forms -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-8">
            
            <!-- User Management Card -->
            <div class="bg-white rounded-lg shadow-xl p-6">
                <h2 class="text-2xl font-semibold text-blue-700 mb-4">Create New User</h2>
                <form action="user_management.php" method="POST" class="space-y-4">
                    <div>
                        <label for="new_username" class="block text-gray-700 font-bold mb-2">Username:</label>
                        <input type="text" id="new_username" name="new_username" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div>
                        <label for="new_password" class="block text-gray-700 font-bold mb-2">Password:</label>
                        <input type="password" id="new_password" name="new_password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div>
                        <label for="new_user_role" class="block text-gray-700 font-bold mb-2">Assign Role:</label>
                        <select id="new_user_role" name="new_user_role" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role['role_id']); ?>">
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="create_user" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Create User</button>
                </form>
            </div>

            <!-- Role Management Card -->
            <div class="bg-white rounded-lg shadow-xl p-6">
                <h2 class="text-2xl font-semibold text-blue-700 mb-4">Create New Role</h2>
                <form action="user_management.php" method="POST" class="space-y-4">
                    <div>
                        <label for="new_role_name" class="block text-gray-700 font-bold mb-2">Role Name:</label>
                        <input type="text" id="new_role_name" name="new_role_name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <button type="submit" name="create_role" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Create Role</button>
                </form>
            </div>

            <!-- Service Management Card -->
            <div class="bg-white rounded-lg shadow-xl p-6">
                <h2 class="text-2xl font-semibold text-blue-700 mb-4">Create New Service</h2>
                <form action="user_management.php" method="POST" class="space-y-4">
                    <div>
                        <label for="service_name" class="block text-gray-700 font-bold mb-2">Service Name:</label>
                        <input type="text" id="service_name" name="service_name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div>
                        <label for="link" class="block text-gray-700 font-bold mb-2">Service Link:</label>
                        <input type="text" id="link" name="link" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div>
                        <label for="description" class="block text-gray-700 font-bold mb-2">Description:</label>
                        <textarea id="description" name="description" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>
                    <button type="submit" name="create_service" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Create Service</button>
                </form>
            </div>

            <!-- Permission Management Card -->
            <div class="bg-white rounded-lg shadow-xl p-6">
                <h2 class="text-2xl font-semibold text-blue-700 mb-4">Assign Permission to Role</h2>
                <form action="user_management.php" method="POST" class="space-y-4">
                    <div>
                        <label for="role_to_assign" class="block text-gray-700 font-bold mb-2">Select Role:</label>
                        <select id="role_to_assign" name="role_to_assign" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role['role_id']); ?>">
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="permission_to_assign" class="block text-gray-700 font-bold mb-2">Select Service (Permission):</label>
                        <select id="permission_to_assign" name="permission_to_assign" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <?php foreach ($permissions as $permission): ?>
                                <option value="<?php echo htmlspecialchars($permission['permission_id']); ?>">
                                    <?php echo htmlspecialchars($permission['service_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="assign_permission" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Assign Permission</button>
                </form>
            </div>
            
        </div>
    </div>
</body>
</html>
