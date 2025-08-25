<?php
session_start();
include 'db_connect.php'; // Include your database connection file

// Check if the user is logged in, redirect to login if not
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit();
}

$certificate = null;
$message = '';
$message_type = '';

// Get the certificate ID from the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $certificateId = intval($_GET['id']);

    // Prepare and execute a query to fetch the certificate details
    $sql = "SELECT * FROM generated_certificates WHERE certificate_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $certificateId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $certificate = $result->fetch_assoc();
        } else {
            $message = "Certificate not found.";
            $message_type = "danger";
        }
        $stmt->close();
    } else {
        $message = "Database query preparation failed: " . $conn->error;
        $message_type = "danger";
        error_log("Error preparing statement for print_certificate: " . $conn->error);
    }
} else {
    $message = "No certificate ID provided.";
    $message_type = "danger";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mutation Certificate - Howrah Municipal Corporation</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Define your color palette */
        :root {
            --primary-blue: #0A3D62; /* Deeper, richer blue for main accents */
            --light-blue-bg: #e0f2f7; /* Soft sky blue for backgrounds */
            --accent-blue: #4A90E2; /* A vibrant blue for buttons/hover states */
            --text-color-dark: #333; /* Dark text for readability */
            --text-color-light: #555; /* Lighter text for subtle info */
            --border-color-light: #d0d9e2; /* Softer border color */
            --white: #ffffff;
            --success-btn: #28a745;
            --success-btn-hover: #218838;
            --gray-btn: #6c757d;
            --gray-btn-hover: #5a6268;
        }

        /* Basic styles for print and screen */
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: var(--light-blue-bg); /* Use the new light blue for the body background */
            color: var(--text-color-dark);
            line-height: 1.6;
            font-size: 14px; /* Default for screen, adjusted for print */
        }

        .certificate-container {
            width: 210mm; /* A4 width */
            min-height: 297mm; /* A4 height */
            margin: 20px auto;
            background-color: var(--white);
            border: 1px solid var(--primary-blue); /* Stronger blue border for official look */
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.15); /* Slightly more pronounced shadow */
            padding: 20mm 25mm; /* Top/bottom 20mm, left/right 25mm for A4 margins */
            box-sizing: border-box; /* Include padding in width/height */
            position: relative;
            border-radius: 8px; /* Slightly rounded corners for a softer feel */
        }

        .header-section {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px; /* More padding */
            border-bottom: 3px solid var(--accent-blue); /* Vibrant accent blue line */
        }

        .header-section img {
            height: 90px; /* Adjusted logo size */
            margin-bottom: 8px; /* More space below logo */
        }

        .header-section h1 {
            font-family: 'Open Sans', sans-serif;
            font-size: 2.3em; /* Slightly larger heading */
            margin: 5px 0 0;
            color: var(--primary-blue); /* Primary blue for main heading */
            font-weight: 700;
        }

        .header-section p {
            font-size: 0.95em; /* Slightly larger paragraph text */
            margin: 0;
            color: var(--text-color-light);
        }
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
            color: var(--white);
            font-weight: 700;
            text-align: center;
        }

        #shared-header-placeholder img {
            filter: none;
            height: 140px;
            margin-bottom: 5px;
        }

        #shared-header-placeholder h1 {
            font-size: 2.8em;
            margin: 0;
            line-height: 1.2;
            color: var(--dark-blue);
        }

        #shared-header-placeholder p {
            font-size: 1.2em;
            margin: 0;
            color: var(--light-text-color);
            font-weight: 400;
        }

        .container {
            background-color: var(--white);
            padding: 35px;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            max-width: 800px;
            margin: 20px auto;
            animation: fadeIn 0.8s ease-in-out;
        }
        .certificate-title {
            text-align: center;
            font-size: 1.8em; /* More prominent title */
            font-weight: 700;
            margin: 25px 0 35px; /* More spacing */
            color: var(--primary-blue);
            text-decoration: underline;
            text-decoration-color: var(--accent-blue); /* Accent blue underline */
        }

        .info-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px; /* More space between info lines */
            font-size: 1.05em;
        }

        .info-line span {
            flex-grow: 1;
            border-bottom: 1px dotted var(--text-color-light); /* Dotted line for fields */
            padding-bottom: 2px;
            margin-left: 8px; /* More space after label */
            color: var(--text-color-dark);
        }

        .info-line strong {
            white-space: nowrap;
            font-weight: 600;
            color: var(--primary-blue); /* Strong labels in primary blue */
        }

        .from-to-section {
            display: flex;
            flex-direction: column;
            margin-top: 20px;
            margin-bottom: 25px;
        }

        .from-section, .to-section {
            margin-bottom: 15px; /* Spacing between From and To blocks */
        }

        .from-section p, .to-section p {
            margin: 5px 0;
            font-size: 1.05em;
        }

        .address-label {
            display: inline-block;
            min-width: 50px;
            font-weight: 600;
            color: var(--primary-blue); /* Labels in primary blue */
        }

        .address-line-field {
            border-bottom: 1px dotted var(--text-color-light);
            padding-bottom: 2px;
            display: inline-block;
            min-width: 380px; /* Ensure enough space for content */
            text-align: left;
            color: var(--text-color-dark);
        }

        .intro-paragraph {
            margin-top: 30px; /* More space above intro paragraph */
            margin-bottom: 35px; /* More space below intro paragraph */
            font-size: 1.05em;
            text-align: justify;
            line-height: 1.8; /* Increased line height for readability */
            color: var(--text-color-dark);
        }

        .intro-paragraph span.field {
            border-bottom: 1px dotted var(--text-color-light);
            padding-bottom: 2px;
            display: inline-block;
            text-align: left;
            min-width: 100px;
            color: var(--text-color-dark);
        }

        .property-details-block {
            margin-top: 30px;
            margin-bottom: 35px;
            font-size: 1.05em;
            background-color: #f7faff; /* Very light blue background for this block */
            border: 1px solid var(--border-color-light);
            border-radius: 6px;
            padding: 15px 20px;
        }

        .property-details-block p {
            margin: 10px 0; /* More spacing for property details */
        }

        .property-details-block p strong {
            display: inline-block;
            width: 160px; /* Slightly wider to align labels */
            font-weight: 600;
            color: var(--primary-blue);
        }
        .property-details-block p span {
            border-bottom: 1px dotted var(--text-color-light);
            padding-bottom: 2px;
            display: inline-block;
            min-width: calc(100% - 170px); /* Adjust width based on label width */
            text-align: left;
            color: var(--text-color-dark);
        }

        .footer-signatures {
            margin-top: 70px; /* More space above signatures */
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: 0.95em;
        }

        .footer-signatures div {
            flex: 1;
        }

        .ward-clerk {
            text-align: left;
            padding-left: 10px; /* Small padding for visual balance */
        }

        .osd-signature {
            text-align: right;
            padding-right: 10px; /* Small padding for visual balance */
        }

        .signature-line {
            border-top: 1px solid var(--primary-blue); /* Stronger blue line for signature */
            margin-top: 60px; /* More space for actual signature */
            width: 80%;
            margin-left: auto;
            margin-right: auto; /* Center signature line within its div */
        }
        .ward-clerk .signature-line {
             margin-left: 0; /* Ensure ward clerk line is left aligned */
             margin-right: auto;
        }

        .signature-text {
            margin-top: 8px; /* More space below signature line */
            font-weight: 600;
            color: var(--primary-blue);
        }

        .user-timestamp {
            position: absolute;
            bottom: 25mm; /* Adjust based on margin */
            left: 25mm; /* Adjust based on margin */
            font-size: 0.8em;
            color: var(--text-color-light);
        }

        .note-text {
            position: absolute;
            bottom: 10mm; /* Adjust based on margin */
            left: 25mm;
            right: 25mm;
            font-size: 0.85em; /* Slightly larger note text */
            text-align: justify;
            line-height: 1.5;
            color: var(--text-color-dark);
            border-top: 1px dotted var(--border-color-light); /* Dotted line above note */
            padding-top: 5px;
        }

        .print-button-container {
            text-align: center;
            margin-top: 30px;
            padding-bottom: 20px;
            z-index: 100;
        }

        .print-button {
            padding: 12px 25px;
            background-color: var(--success-btn); /* Green for print button */
            color: var(--white);
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin: 0 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .print-button:hover {
            background-color: var(--success-btn-hover);
            transform: translateY(-1px);
        }

        .print-button.back { /* Style for the "Back to Generate" button */
            background-color: var(--gray-btn);
        }

        .print-button.back:hover {
            background-color: var(--gray-btn-hover);
        }

        .alert {
            padding: 15px;
            margin: 20px auto;
            border: 1px solid transparent;
            border-radius: 4px;
            font-weight: bold;
            text-align: center;
            max-width: 800px;
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
            z-index: 100;
            position: relative;
        }

        /* Print-specific styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
                font-size: 11pt; /* Adjust font size for print if needed */
                -webkit-print-color-adjust: exact; /* Ensure background colors and images print */
                print-color-adjust: exact;
            }

            .certificate-container {
                margin: 0;
                border: none; /* Remove border for actual print */
                box-shadow: none;
                width: 100%;
                min-height: 100vh; /* Ensure it takes full height of print page */
                padding: 15mm 20mm; /* Adjust padding for print */
                border-radius: 0; /* No rounded corners on print */
            }

            .print-button-container,
            .alert {
                display: none; /* Hide print button and alerts when printing */
            }

            /* Ensure all colors and borders print */
            * {
                color: black !important;
                background-color: transparent !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }
            .header-section h1, .header-section p,
            .certificate-title, .info-line strong, .address-block p,
            .intro-paragraph, .property-details-block p,
            .footer-signatures p, .user-timestamp, .note-text {
                    color: #000 !important; /* Force black text */
            }
            .header-section img {
                filter: grayscale(100%) brightness(0%); /* Make logo black and white for print */
            }
            .info-line span, .address-line-field, .intro-paragraph span.field, .property-details-block p span {
                border-bottom: 1px dotted #000 !important; /* Ensure dotted lines print black */
            }
            .header-section {
                border-bottom-color: #000 !important;
            }
            .signature-line {
                border-top-color: #000 !important;
            }
        }
    </style>
</head>
<body>
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($certificate): ?>
        <div id="shared-header-placeholder">
        <img src="logo.jpg" alt="Howrah Municipal Corporation Logo">
        <h1>Howrah Municipal Corporation</h1>
        <p>Assessment Department</p>
        <p>4, Mahatma Gandhi Road, Howrah-711101</p>
        <p><strong> Website:www.myhmc.in</strong> </p>
    </div>

            <h2 class="certificate-title">Mutation Certificate</h2>

            <div class="info-line">
                <strong>Memo No. (certificate id):</strong> <span><?php echo htmlspecialchars($certificate['certificate_id']); ?></span>
                <strong>Date :</strong> <span><?php echo htmlspecialchars(date('d F, Y', strtotime($certificate['memo_date']))); ?></span>
            </div>

            <div class="from-to-section">
                <div class="from-section">
                    <p><strong class="address-label">From :</strong> <span class="address-line-field" style="min-width: 380px;">
                        <?php echo htmlspecialchars($certificate['Certificate_issued_by']); ?>, <?php echo htmlspecialchars($certificate['Certificate_issued_at']); ?>
                    </span></p>
                    <p style="margin-left: 55px;"><strong>Howrah Municipal Corporation</strong></p>
                </div>

                <div class="to-section">
                    <p><strong class="address-label">To :</strong> <span class="address-line-field" style="min-width: 380px;">
                        <?php echo htmlspecialchars($certificate['applicant_details']); ?>
                    </span></p>
                    <p style="margin-left: 55px;"><span class="address-line-field" style="min-width: 380px;">
                        <?php echo nl2br(htmlspecialchars($certificate['applicant_address'])); ?>
                    </span></p>
                </div>
            </div>

            <div class="info-line" style="margin-top: 20px;">
                <strong>Re: Premises No.</strong> <span style="min-width: 150px;"><?php echo htmlspecialchars($certificate['holding_no']); ?></span>
            </div>
            <div class="info-line">
                <strong>Street Name:</strong> <span style="min-width: 250px;"><?php echo htmlspecialchars($certificate['street_name']); ?></span>
            </div>
            <div class="info-line">
                <strong>Ward No.:</strong> <span><?php echo htmlspecialchars($certificate['ward_no']); ?></span>
            </div>

            <p class="intro-paragraph">
                With Reference to your application No <span class="field"><?php echo htmlspecialchars($certificate['application_number']); ?></span>
                Dated <span class="field"><?php echo htmlspecialchars(date('d F, Y', strtotime($certificate['application_date']))); ?></span>
                this is to inform you that assessment of the above mentioned holding/s has been <span class="field"><?php echo htmlspecialchars($certificate['mutation_type']); ?></span>
                with effect from <span class="field"><?php echo htmlspecialchars(date('d F, Y', strtotime($certificate['mutation_effect_date']))); ?></span>
                as per order of <span class="field"><?php echo htmlspecialchars($certificate['approved_by']); ?></span> dated <span class="field"><?php echo htmlspecialchars(date('d F, Y', strtotime($certificate['approval_date']))); ?></span> as follows:
            </p>

            <div class="property-details-block">
                <p><strong>Premises No.:</strong> <span><?php echo htmlspecialchars($certificate['holding_no']); ?></span></p>
                <p><strong>Street Name:</strong> <span><?php echo htmlspecialchars($certificate['street_name']); ?></span></p>
                <p><strong>Ward No.:</strong> <span><?php echo htmlspecialchars($certificate['ward_no']); ?></span></p>
                <p><strong>Final Assessee Name:</strong> <span><?php echo htmlspecialchars($certificate['final_assessee_name']); ?></span></p>
                <p><strong>Description:</strong> <span><?php echo nl2br(htmlspecialchars($certificate['description'])); ?></span></p>
                <p><strong>Annual Valuation:</strong> <span>₹ <?php echo number_format(htmlspecialchars($certificate['annual_value']), 2); ?>/-</span></p>
            </div>

            <div class="footer-signatures">
                <div class="ward-clerk">
                    <br><br>
                    <div class="signature-line"></div> <p class="signature-text" style="margin-left: 0;">Ward Clerk</p> </div>
                <div class="osd-signature">
                    <br><br>
                    <div class="signature-line"></div>
                    <p class="signature-text">
                        <?php echo htmlspecialchars($certificate['Certificate_issued_by']); ?><br> <?php echo htmlspecialchars($certificate['Certificate_issued_at']); ?><br>
                        Howrah Municipal Corporation
                    </p>
                </div>
            </div>

            <div class="user-timestamp">
                User Name: <?php echo htmlspecialchars($certificate['generated_by']); ?> and Datetime: <?php echo htmlspecialchars(date('Y-m-d H:i:s')); ?>
            </div>

            <p class="note-text">
                N. B. - Please note that the above sub-division/apportioned or amalgamation is granted subject to your liability
                to pay arrears of taxes the parent holding upto the date of sub division or apportionment or amalgamation
            </p>
        </div>

        <div class="print-button-container">
            <button onclick="window.print()" class="print-button">Print Certificate</button>
            <a href="generate_mutation.php" class="print-button back">Back to Generate</a>
        </div>
    <?php endif; ?>
</body>
</html>