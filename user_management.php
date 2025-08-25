<?php
session_start();


include "db_connect.php"; // DB connection

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['username']) || $_SESSION['role_name'] !== 'Admin') {
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
// NEW FIELDS
    $employee_name     = trim($_POST['employee_name']);
    $employee_code     = trim($_POST['employee_code']);
    $mobile_number     = trim($_POST['mobile_number']);

  $sql = "INSERT INTO users (username, password, role_id, employee_name, employee_code, mobile_number) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisss", $new_username, $new_password, $selected_role_id, $employee_name, $employee_code, $mobile_number);


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

// 5. NEW: Handle Permission Deletion from Role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_permission'])) {
    $role_id_to_delete = $_POST['role_to_delete'];
    $permission_id_to_delete = $_POST['permission_to_delete'];

    $sql = "DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $role_id_to_delete, $permission_id_to_delete);

    if ($stmt->execute()) {
        $message = "Permission removed from role successfully.";
    } else {
        $message = "Error removing permission: " . $conn->error;
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

// NEW: Fetch permissions for a specific role if selected
$selected_role_id_for_view = null;
$role_permissions_to_view = [];
if (isset($_GET['view_role_id'])) {
    $selected_role_id_for_view = $_GET['view_role_id'];
    $sql = "SELECT p.permission_id, p.permission_name, s.service_name, s.description
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.permission_id
            JOIN services s ON p.service_id = s.service_id
            WHERE rp.role_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_role_id_for_view);
    $stmt->execute();
    $result = $stmt->get_result();
    $role_permissions_to_view = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
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
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div id="shared-header-placeholder">
        <div class="header-buttons">
		 <a href="hmc.php" class="header-btn">Back to Dashboard</a>
              <a href="change_password.php" class="header-btn">Change Password</a>
    <a href="logout.php" class="header-btn">Logout</a>
        </div>
        <img src="logo.jpg" alt="Howrah Municipal Corporation Logo">
        <h1>Howrah Municipal Corporation</h1>
        <p>4, Mahatma Gandhi Road, Howrah-711101</p>
        <p><strong> Website:www.myhmc.in</strong> </p>
    </div>

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
        <!-- Employee Details -->
        <div>
            <label for="employee_name" class="block text-gray-700 font-bold mb-2">Employee Name:</label>  
            <input type="text" id="employee_name" name="employee_name" placeholder="Employee Name" required 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
        </div>

        <div>
            <label for="employee_code" class="block text-gray-700 font-bold mb-2">Employee Code:</label>  
            <input type="text" id="employee_code" name="employee_code" placeholder="Employee Code" required 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
        </div>

        <div>
            <label for="mobile_number" class="block text-gray-700 font-bold mb-2">Mobile Number:</label>  
            <input type="text" id="mobile_number" name="mobile_number" placeholder="Mobile Number" required 
                   pattern="\d{10}" title="Enter 10-digit mobile number"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
        </div>

        <!-- Username & Password -->
        <div>
            <label for="new_username" class="block text-gray-700 font-bold mb-2">Username:</label>
            <input type="text" id="new_username" name="new_username" required 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
        </div>

        <div>
            <label for="new_password" class="block text-gray-700 font-bold mb-2">Password:</label>
            <input type="password" id="new_password" name="new_password" required 
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
        </div>

        <!-- Role Selection -->
        <div>
            <label for="new_user_role" class="block text-gray-700 font-bold mb-2">Assign Role:</label>
            <select id="new_user_role" name="new_user_role" required 
                    class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo htmlspecialchars($role['role_id']); ?>">
                        <?php echo htmlspecialchars($role['role_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Submit Button -->
        <button type="submit" name="create_user" 
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Create User
        </button>
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
            
             <!-- NEW: View and Delete Permissions Card -->
             <div class="bg-white rounded-lg shadow-xl p-6 col-span-1 md:col-span-2">
                <h2 class="text-2xl font-semibold text-blue-700 mb-4">View and Delete Permissions</h2>
                
                <form action="user_management.php" method="GET" class="space-y-4">
                    <div>
                        <label for="view_role_id" class="block text-gray-700 font-bold mb-2">Select Role to View Permissions:</label>
                        <select id="view_role_id" name="view_role_id" required onchange="this.form.submit()" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">-- Select a Role --</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo htmlspecialchars($role['role_id']); ?>" <?php echo ($selected_role_id_for_view == $role['role_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['role_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>

                <?php if ($selected_role_id_for_view && !empty($role_permissions_to_view)): ?>
                    <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-4">Permissions for Role: <?php echo htmlspecialchars($roles[array_search($selected_role_id_for_view, array_column($roles, 'role_id'))]['role_name']); ?></h3>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($role_permissions_to_view as $permission): ?>
                            <li class="py-4 flex justify-between items-center">
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo htmlspecialchars($permission['service_name']); ?></p>
                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($permission['description']); ?></p>
                                </div>
                                <form action="user_management.php" method="POST">
                                    <input type="hidden" name="role_to_delete" value="<?php echo htmlspecialchars($selected_role_id_for_view); ?>">
                                    <input type="hidden" name="permission_to_delete" value="<?php echo htmlspecialchars($permission['permission_id']); ?>">
                                    <button type="submit" name="delete_permission" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded focus:outline-none focus:shadow-outline">
                                        Delete
                                    </button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php elseif ($selected_role_id_for_view): ?>
                     <div class="mt-6 p-4 bg-yellow-100 text-yellow-800 rounded-lg">No permissions found for this role.</div>
                <?php endif; ?>
            </div>
            
        </div>
		
    
            
        </div>
	</div>
</body>
</html>
