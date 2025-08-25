<?php
// Include DB connection
require 'db_connect.php'; // contains $conn

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role_id = intval($_POST['role_id']);
    $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];

    // Delete existing permissions for this role
    $conn->query("DELETE FROM role_permissions WHERE role_id = $role_id");

    // Insert new permissions
    if (!empty($permissions)) {
        $stmt = $conn->prepare("INSERT INTO role_permissions (role_id, service_id) VALUES (?, ?)");
        foreach ($permissions as $service_id) {
            $service_id = intval($service_id);
            $stmt->bind_param("ii", $role_id, $service_id);
            $stmt->execute();
        }
        $stmt->close();
    }

    echo "<p style='color:green;'>Permissions updated successfully!</p>";
}

// Fetch roles
$roles = [];
$result = $conn->query("SELECT * FROM roles");
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}

// Fetch services
$services = [];
$result = $conn->query("SELECT * FROM services");
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

// Fetch current permissions for selected role (if any)
$current_permissions = [];
if (isset($_GET['role_id'])) {
    $role_id = intval($_GET['role_id']);
    $result = $conn->query("SELECT service_id FROM role_permissions WHERE role_id = $role_id");
    while ($row = $result->fetch_assoc()) {
        $current_permissions[] = $row['service_id'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Role Permissions</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        select, input[type=submit] { padding: 8px; margin-top: 10px; }
        .checkbox-list { margin-top: 15px; }
    </style>
</head>
<body>
    <h2>Manage Role Permissions</h2>

    <!-- Role selection form -->
    <form method="GET" action="">
        <label for="role_id">Select Role:</label>
        <select name="role_id" id="role_id" onchange="this.form.submit()">
            <option value="">-- Select Role --</option>
            <?php foreach ($roles as $role): ?>
                <option value="<?= $role['id'] ?>" <?= (isset($_GET['role_id']) && $_GET['role_id'] == $role['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($role['role_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if (isset($_GET['role_id']) && $_GET['role_id'] != ''): ?>
        <!-- Permissions form -->
        <form method="POST" action="">
            <input type="hidden" name="role_id" value="<?= intval($_GET['role_id']) ?>">
            <div class="checkbox-list">
                <?php foreach ($services as $service): ?>
                    <label>
                        <input type="checkbox" name="permissions[]" value="<?= $service['id'] ?>"
                            <?= in_array($service['id'], $current_permissions) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($service['service_name']) ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
            <input type="submit" value="Save Permissions">
        </form>
    <?php endif; ?>

</body>
</html>
