<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['role_id']) || !isset($_SESSION['role_name'])) {
    header("Location: login.php");
    exit();
}

include "db_connect.php"; // Your database connection file

$username = $_SESSION['username'];
$role_id = $_SESSION['role_id'];
$role_name = $_SESSION['role_name'];

// New logic: Fetch authorized service details directly from the database
$servicesToShow = [];

if ($role_id) {
    // Updated SQL query to fetch service names, links, and descriptions
    $sql = "SELECT s.service_name, s.links, s.description
            FROM role_permissions rp
            JOIN permissions p ON rp.permission_id = p.permission_id
            JOIN services s ON p.service_id = s.Service_id
            WHERE rp.role_id = ? ORDER BY s.service_name";

    $stmt = $conn->prepare($sql);
    
    // Check if the statement was successfully prepared
    if ($stmt) {
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $servicesToShow[] = $row; // Store the full service row
            }
        }
        $stmt->close();
    } else {
        // If prepare failed, show an error message.
        // This is where you would see a detailed error from the database.
        die("Error preparing statement: " . $conn->error);
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Howrah Municipal Corporation - Assessment Department Services</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
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
<body>


    <div id="shared-header-placeholder">
	
        <img src="logo.jpg" alt="Howrah Municipal Corporation Logo">
        <h1>Howrah Municipal Corporation</h1>
        <p>4, Mahatma Gandhi Road, Howrah-711101</p>
        <p><strong> Website:www.myhmc.in</strong> </p>
		<p><strong> ________________________</strong> </p>

        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>Your Role: <strong><?php echo htmlspecialchars($role_name); ?></strong></p>
        <div class="header-buttons">
            <?php if ($role_name === 'Admin'): ?>
                <a href="user_management.php" class="header-btn">User Registration</a>
            <?php endif; ?>
            <a href="change_password.php" class="header-btn">Change Password</a>
            <a href="logout.php" class="header-btn">Logout</a>
        </div>
    </div>

    <section class="hero-section">
        <div class="hero-content">
            <h1>Welcome to Assessment Department Service Portal</h1>
            <p>Your gateway to streamlined property tax services and assessment tools.</p>
            <a href="#services" class="scroll-down-button">Explore Services</a>
        </div>
    </section>

    <main class="main-content-area container" id="services">
        <div class="services-section">
            <h2>Available Services</h2>
            <p class="section-description">Based on your role, you can access the following services.</p>
            <div class="service-grid">
                <?php
                // Loop through the array of authorized services from the database
                foreach ($servicesToShow as $service) {
                    $link = $service['links'];
                    $desc = $service['description'];
                    $serviceTitle = $service['service_name'];
                    $target = (strpos($link, 'http') === 0) ? ' target="_blank" rel="noopener noreferrer"' : '';
                    echo "<a href=\"$link\" class=\"service-card\"$target>";
                    echo "<h3>" . htmlspecialchars($serviceTitle) . "</h3>";
                    echo "<p>" . htmlspecialchars($desc) . "</p>";
                    echo "</a>";
                }
                ?>
            </div>
        </div>
    </main>

    <footer id="shared-footer-placeholder">
        &copy; <?php echo date('Y'); ?> Howrah Municipal Corporation. All rights reserved.
    </footer>
</body>
</html>