<?php
session_start();

// Check if the user is not logged in.
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // If not, redirect them to the login page.
    header("Location: login.php");
    exit;
	
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>HMC Property Valuation Calculator</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
<style>
    /* Global Styles & Body */
    :root {
        --primary-blue: #007bff;
        --dark-blue: #0056b3;
        --light-blue: #e0f2ff;
        --text-color: #333;
        --light-grey-bg: #f8f9fa;
        --medium-grey-border: #ced4da;
        --dark-grey-text: #495057;
        --success-green: #28a745;
        --success-light: #d4edda;
        --discount-red: #dc3545;
        --discount-light: #f8d7da;
        --shadow-light: rgba(0,0,0,0.08);
        --shadow-medium: rgba(0,0,0,0.12);
        --surcharge-yellow: #fff3cd;
        --surcharge-orange: #ffc107;
        --surcharge-dark: #856404;
    }

    body {
        font-family: 'Roboto', sans-serif;
        margin: 0;
        background-color: var(--light-grey-bg);
        color: var(--text-color);
        line-height: 1.6;
        padding-bottom: 3rem;
    }

    /* Header Bar */
    .header-bar {
        background-image: linear-gradient(to right, #0056b3, var(--primary-blue));
        color: white;
        padding: 1.5rem 1rem;
        text-align: center;
        box-shadow: 0 0.25rem 0.5rem var(--shadow-medium);
        margin-bottom: 2rem;
    }
    .header-bar h1 {
        margin: 0;
        font-size: 2em;
        font-weight: 500;
        letter-spacing: 0.05em;
    }

    /* Logo Container */
    .logo-container {
        text-align: center;
        padding: 1.5rem 0;
        background-color: white;
        border-bottom: 1px solid #e9ecef;
        box-shadow: 0 1px 3px var(--shadow-light);
    }
    .logo-container img {
        max-width: 70%;
        height: auto;
        width: clamp(300px, 60vw, 450px);
    }

    /* Main Content Wrapper */
    .container {
        max-width: 960px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    h2 {
        margin-top: 2.5rem;
        color: var(--dark-blue);
        border-bottom: 3px solid var(--primary-blue);
        padding-bottom: 0.8rem;
        margin-bottom: 1.5rem;
        font-size: 1.6em;
        font-weight: 500;
        text-align: center;
    }

    /* Table Styles */
    table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 2rem;
        background-color: white;
        box-shadow: 0 0.25rem 0.75rem var(--shadow-light);
        border-radius: 0.5rem;
        overflow-x: auto;
        white-space: nowrap;
    }
    th, td {
        border: 1px solid #e9ecef;
        padding: 0.8rem 0.5rem;
        text-align: center;
        vertical-align: middle;
    }
    th {
        background-color: var(--light-blue);
        font-weight: 600;
        color: var(--dark-blue);
        white-space: nowrap;
    }
    td {
        background-color: white;
    }
    #floorsTable {
        min-width: 600px;
    }
    #floorsTable th:first-child, #floorsTable td:first-child {
        text-align: left;
        background-color: var(--light-blue);
        color: var(--dark-blue);
        font-weight: 600;
        position: sticky;
        left: 0;
        z-index: 2;
    }
    #floorsTable tbody th {
        background-color: #f1f7fe;
        white-space: normal;
        min-width: 150px;
    }
    #floorsTable thead th {
        background-color: var(--light-blue);
        position: sticky;
        top: 0;
        z-index: 1;
    }


    /* Form Elements */
    select, input[type=number], input[type=text], textarea, input[type=date] {
        width: 100%;
        padding: 0.75rem 0.6rem;
        border: 1px solid var(--medium-grey-border);
        border-radius: 0.3rem;
        box-sizing: border-box;
        margin-top: 0.3rem;
        margin-bottom: 0.3rem;
        font-size: 1em;
        color: var(--text-color);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    select:focus, input[type=number]:focus, input[type=text]:focus, textarea:focus, input[type=date]:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: none;
    }
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }
    textarea {
        resize: vertical;
        min-height: 80px;
    }

    /* Buttons */
    button {
        padding: 0.75rem 1.5rem;
        margin-top: 1rem;
        background-color: var(--primary-blue);
        color: white;
        border: none;
        border-radius: 0.3rem;
        cursor: pointer;
        font-size: 1em;
        font-weight: 500;
        transition: background-color 0.2s ease, transform 0.1s ease, box-shadow 0.2s ease;
        display: inline-block;
        margin-right: 0.5rem;
    }
    button:last-of-type {
        margin-right: 0;
    }
    button:hover {
        background-color: var(--dark-blue);
        box-shadow: 0 0.15rem 0.4rem rgba(0,0,0,0.2);
    }
    button:active {
        background-color: #004085;
        transform: translateY(1px);
        box-shadow: none;
    }

    /* Result Section */
    #result, #finalOutput {
        margin-top: 2rem;
        padding: 1.5rem;
        background-color: var(--light-grey-bg);
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        box-shadow: 0 0.25rem 0.75rem var(--shadow-light);
    }
    #result h2, #finalOutput h2 {
        border-bottom: 2px solid var(--primary-blue);
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        font-size: 1.4em;
        text-align: left;
    }

    /* Highlighting for higher value */
    .highlight {
        background-color: var(--success-light) !important;
        border-color: var(--success-green) !important;
        color: var(--success-green) !important;
        font-weight: 700;
    }

    /* New: Highlight for Discounted Value */
    .discount-final {
        background-color: var(--discount-light) !important;
        border-color: var(--discount-red) !important;
        color: var(--discount-red) !important;
        font-weight: 700;
        font-size: 1.2em;
        padding: 0.8rem 1rem;
    }


    .section-header td {
        background-color: var(--light-blue) !important;
        font-weight: 600 !important;
        text-align: left !important;
        padding-top: 1.2rem !important;
        padding-bottom: 0.5rem !important;
        color: var(--dark-blue) !important;
        font-size: 1.1em;
        border-top: 2px solid var(--primary-blue);
    }

    /* Calculator Sections */
    .calculator-section {
        background-color: white;
        border: 1px solid #e0e0e0;
        padding: 1.5rem;
        margin-top: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 0.25rem 0.75rem var(--shadow-light);
    }
    .input-group {
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
        gap: 0.5rem;
    }
    .input-group label {
        flex: 1;
        text-align: right;
        margin-right: 1rem;
        font-weight: 500;
        color: var(--dark-grey-text);
        min-width: 80px;
    }
    .input-group input, .input-group textarea, .input-group select {
        flex: 2;
        max-width: 150px;
        margin-right: 0;
    }
    .input-group span {
        flex: 0 0 70px;
        text-align: left;
        font-weight: 500;
        color: var(--dark-grey-text);
    }
    .output-field {
        font-weight: 700;
        margin-top: 1.5rem;
        padding: 0.8rem 1rem;
        border: 1px solid var(--primary-blue);
        background-color: var(--light-blue);
        text-align: center;
        border-radius: 0.3rem;
        color: var(--dark-blue);
        font-size: 1.1em;
    }
    .explanation-text {
        font-size: 0.95em;
        color: var(--dark-grey-text);
        margin-bottom: 1.5rem;
        line-height: 1.6;
        background-color: var(--light-grey-bg);
        padding: 1rem;
        border-left: 5px solid var(--primary-blue);
        border-radius: 0.3rem;
    }
    #rateChartContainer {
        margin-top: 2.5rem;
        padding: 1.5rem;
        background-color: #f0f8ff;
        border: 1px solid #cce7ff;
        border-radius: 0.5rem;
        box-shadow: 0 0.25rem 0.75rem var(--shadow-light);
    }
    #rateChartContainer h2 {
        border-bottom: 2px solid var(--primary-blue);
        font-size: 1.4em;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        text-align: left;
    }
    #rateChartContainer h3 {
        margin-top: 1.5rem;
        margin-bottom: 0.8rem;
        color: var(--dark-blue);
        font-size: 1.2em;
        font-weight: 500;
        border-bottom: 1px dashed var(--primary-blue);
        padding-bottom: 0.3rem;
    }
    #rateChartContainer table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 1rem;
        box-shadow: none;
        border-radius: 0;
    }
    #rateChartContainer th, #rateChartContainer td {
        border: 1px solid #b3d9ff;
        padding: 0.6rem;
        text-align: left;
    }
    #rateChartContainer th {
        background-color: #cce7ff;
        font-weight: 600;
        color: var(--dark-blue);
    }

    /* Specific styles for the land area input */
    #landArea {
        width: 180px;
        margin-left: 0.5rem;
        background-color: #e9ecef;
        cursor: not-allowed;
    }
    .land-input-group {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .land-input-group label {
        font-weight: 500;
        color: var(--dark-grey-text);
        margin-right: 0.5rem;
    }

    /* Horizontal Rule */
    hr {
        border: none;
        border-top: 2px dashed #d0d0d0;
        margin: 2.5rem 0;
    }

    /* Discount Input Group */
    .discount-group, .surcharge-group {
        display: flex;
        align-items: center;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        gap: 0.5rem;
        padding: 0.8rem;
        background-color: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 0.3rem;
    }
    .discount-group label, .surcharge-group label {
        font-weight: 600;
        color: #856404;
        flex: 1;
        text-align: right;
        margin-right: 1rem;
        min-width: 120px;
    }
    .discount-group input, .surcharge-group input {
        flex: 2;
        max-width: 100px;
        margin-right: 0;
    }
    .discount-group span, .surcharge-group span {
        font-weight: 600;
        color: #856404;
        flex: 0 0 30px;
        text-align: left;
    }
    .surcharge-info {
        font-size: 0.85em;
        color: var(--dark-grey-text);
        text-align: center;
        margin-top: -0.5rem;
        margin-bottom: 1rem;
    }
    .input-group.full-width {
        flex-direction: column;
        align-items: flex-start;
    }
    .input-group.full-width label {
        text-align: left;
        width: 100%;
        margin-bottom: 0.5rem;
    }
    .input-group.full-width input, .input-group.full-width textarea, .input-group.full-width select {
        max-width: 100%;
    }


    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .header-bar h1 {
            font-size: 1.6em;
        }
        .container {
            padding: 0 1rem;
        }
        h2 {
            font-size: 1.4em;
        }
        th, td {
            padding: 0.6rem 0.3rem;
            font-size: 0.9em;
        }
        .input-group, .discount-group, .surcharge-group {
            flex-direction: column;
            align-items: flex-start;
        }
        .input-group label, .discount-group label, .surcharge-group label {
            text-align: left;
            margin-right: 0;
            margin-bottom: 0.3rem;
            width: 100%;
        }
        .input-group input, .discount-group input, .surcharge-group input {
            max-width: 100%;
        }
        .input-group span, .discount-group span, .surcharge-group span {
            width: 100%;
            text-align: left;
            margin-top: 0.3rem;
        }
        button {
            display: block;
            width: 100%;
            margin-right: 0;
            margin-bottom: 0.8rem;
        }
        .land-input-group {
            flex-direction: column;
            align-items: flex-start;
        }
        .land-input-group label {
            width: 100%;
            margin-right: 0;
            margin-bottom: 0.3rem;
        }
        #landArea {
            width: 100%;
            margin-left: 0;
        }
    }

    /* Print-specific styles */
    @media print {
        body {
            background-color: #fff;
            padding: 0;
            margin: 0;
            font-size: 12pt;
        }
        .container {
            max-width: 100%;
            padding: 0;
        }
        /* Hide elements not needed for printing */
        #calculatorForm, #rateChartContainer, #result, .button-group {
            display: none !important;
        }
        /* Make the final output section visible */
        #finalOutput {
            display: block !important;
            box-shadow: none !important;
            padding: 0;
            margin-top: 0;
        }
        #finalOutput table {
            box-shadow: none;
            border-radius: 0;
            width: 100%;
        }
        /* Adjust table for printing */
        #finalOutput table th, #finalOutput table td {
          white-space: normal;
          padding: 0.5rem;
          text-align: left;
        }
        .highlight, .discount-final {
            -webkit-print-color-adjust: exact;
            color: #000 !important;
            background-color: #f0f0f0 !important;
            border: 1px solid #000 !important;
        }
        .section-header td {
            background-color: #ccc !important;
        }
    }

    /* New Compact Styles */
    .calculator-section .input-group {
        margin-bottom: 0.5rem;
        gap: 0.25rem;
    }
    .calculator-section h2 {
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
    }
    .calculator-section {
        padding: 1rem;
        margin-top: 1rem;
    }
    hr {
        margin: 1.5rem 0;
    }
    .surcharge-info, .explanation-text {
        padding: 0.75rem;
        margin-bottom: 1rem;
        font-size: 0.9em;
    }
    .input-group label, .discount-group label, .surcharge-group label {
        font-size: 0.95em;
    }
    .input-group input, .input-group textarea, .input-group select,
    .discount-group input, .surcharge-group input {
        padding: 0.5rem;
        font-size: 0.9em;
    }
    #floorsTable th, #floorsTable td {
        padding: 0.5rem 0.3rem;
    }
    button {
        padding: 0.6rem 1.2rem;
        font-size: 0.95em;
        margin-top: 0.5rem;
    }
    #result, #finalOutput {
        padding: 1rem;
        margin-top: 1rem;
    }
    #result h2, #finalOutput h2 {
        font-size: 1.2em;
        margin-bottom: 1rem;
    }
    .output-field {
        padding: 0.6rem 0.8rem;
        font-size: 1em;
        margin-top: 1rem;
    }
    .discount-final {
        font-size: 1.1em;
        padding: 0.6rem 0.8rem;
    }
    @media (max-width: 768px) {
        button {
            width: auto; /* Revert to auto width on mobile for better side-by-side button placement if needed */
            margin-right: 0.5rem;
            margin-bottom: 0;
        }
        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }
    }
</style>
</head>
<body>

<div class="logo-container">
    <img src="logo1.png" alt="Howrah Municipal Corporation Logo">
</div>
<div
<h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
    <!-- The rest of your calculator's HTML goes here -->
   
    </div>
<div class="header-bar">
    <h1>Property Annual Valuation Calculator</h1>
	
</div>

<div class="container">
  
  <div id="calculatorForm">

    <div class="calculator-section">
      <h2>Application Details</h2>
      <div class="input-group full-width">
        <label for="applicationNumber">Application No.:</label>
        <input type="text" id="applicationNumber" placeholder="Enter application number" onchange="calculate()" />
      </div>
      <div class="input-group full-width">
        <label for="applicationDate">Date of Application:</label>
        <input type="date" id="applicationDate" onchange="calculate()" />
      </div>
      <div class="input-group full-width">
        <label for="filingStatus">Filing Status:</label>
        <select id="filingStatus" onchange="calculate()">
          <option value="new">New Assessment</option>
          <option value="reassessment">Re-assessment</option>
          <option value="mutation">Mutation</option>
        </select>
      </div>
      <input type="hidden" id="valuationDoneBy" value="" />
    </div>

    <div class="calculator-section">
      <h2>Additional Property Information</h2>
      <div class="input-group full-width">
        <label for="assesseeName">Assessee Name:</label>
        <input type="text" id="assesseeName" placeholder="Enter assessee name" onchange="calculate()" />
      </div>
      <div class="input-group full-width">
        <label for="premisesDetails">Premises Details:</label>
        <input type="text" id="premisesDetails" placeholder="Enter premises details" onchange="calculate()" />
      </div>
      <div class="input-group full-width">
        <label for="description">Description:</label>
        <textarea id="description" placeholder="Enter property description" onchange="calculate()"></textarea>
      </div>
    </div>

    <hr>
    <h2>Construction & floor related Information of Covered area</h2>
    <div class="surcharge-info">
      Please enter the total area and the commercial portion of it. The remaining area will be considered residential.
    </div>
    <table id="floorsTable">
      <thead>
        <tr>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th>Construction Type</th>
        </tr>
        <tr>
          <th>Floor Level</th>
        </tr>
        <tr>
          <th>Total Covered Area (sqft)</th>
        </tr>
        <tr>
          <th>Commercial Area (sqft)</th>
        </tr>
        <tr>
          <th>Residential Area (sqft)</th>
        </tr>
        <tr>
          <th>Cost Rate (₹/sqft)</th>
        </tr>
        <tr>
          <th>Cost Total (₹)</th>
        </tr>
        <tr>
          <th>Rental Rate (₹/sqft)</th>
        </tr>
        <tr>
          <th>Rental Total (₹)</th>
        </tr>
        <tr>
          <th>Remove</th>
        </tr>
      </tbody>
    </table>
    <button onclick="addFloor()">Add New Floor</button>

    <hr>

    <div class="calculator-section">
        <h2>Land Details</h2>
        <p class="explanation-text">
            Use this tool to precisely calculate the total land area in square feet by adding or subtracting different land units.
            <br>
            • Enter **positive** values for areas you wish to **add** (e.g., `1` for 1 Bigha).
            <br>
            • Enter **negative** values for areas you wish to **subtract** (e.g., enter `-1` in the 'Katha' field to subtract 1 Katha).
            <br>
            The calculated total in Square Feet will automatically update the "Land Area (sqft)" field below, ensuring it's ready for valuation.
        </p>

        <div class="input-group">
            <label for="arithmeticBigha">Bigha:</label>
            <input type="number" id="arithmeticBigha" value="0" oninput="calculateArithmeticArea()" />
            <span>Bigha</span>
        </div>

        <div class="input-group">
            <label for="arithmeticKatha">Katha:</label>
            <input type="number" id="arithmeticKatha" value="0" oninput="calculateArithmeticArea()" />
            <span>Katha</span>
        </div>

        <div class="input-group">
            <label for="arithmeticChattak">Chattak:</label>
            <input type="number" id="arithmeticChattak" value="0" oninput="calculateArithmeticArea()" />
            <span>Chattak</span>
        </div>

        <div class="input-group">
            <label for="arithmeticSqft">Square Feet:</label>
            <input type="number" id="arithmeticSqft" value="0" oninput="calculateArithmeticArea()" />
            <span>Sq.Ft.</span>
        </div>

        <div id="arithmeticResult" class="output-field">Calculated Total Area: 0.00 Sq.Ft.</div>
    </div>

    <hr>
    <h2>Property Type Selection</h2>
    <div class="input-group">
      <label for="propertyType">Property Type:</label>
      <select id="propertyType" onchange="calculate()">
        <option value="apartment">Apartment</option>
        <option value="non-apartment">Non-Apartment</option>
      </select>
    </div>
    <h2>Additional Factors</h2>
    <div class="land-input-group">
      <label for="landArea">Land Area (sqft):</label>
      <input type="number" id="landArea" value="0" min="0" readonly />
    </div>
    <br />
    <label>
      Ward No.:
      <select id="wardNo" onchange="calculate()"></select>
    </label>
    <br /><br />
    <label>
      Passage Width:
      <select id="passageWidth" onchange="calculate()">
        <option value="3.5">Less than 3.5m (~11.5ft)</option>
        <option value="3.5-7">3.5 to 7.0m (~11.5ft - 23ft)</option>
        <option value="7.01-10">7.01 to 10m (~23ft - 32.8ft)</option>
        <option value="10.01-15">10.01 to 15m (~32.8ft - 49.2ft)</option>
        <option value="15.01+">Above 15.01m (~49.2ft+)</option>
      </select>
    </label>


    <div class="surcharge-group">
        <label for="surchargePercentage">Surcharge (%):</label>
        <input type="number" id="surchargePercentage" value="0" min="0" max="1000" oninput="this.value = Math.max(0, Math.min(1000, this.value || 0)); calculate();" />
        <span>%</span>
    </div>

    <div class="discount-group">
        <label for="discountPercentage">Discount (%):</label>
        <input type="number" id="discountPercentage" value="0" min="0" max="100" oninput="this.value = Math.max(0, Math.min(100, this.value || 0)); calculate();" />
        <span>%</span>
    </div>

    <br />
    
      <button onclick="calculate()">Calculate Valuation</button>
      <button onclick="toggleRateChart()">View Full Rate Chart</button>
      <button onclick="preparePrintOutput()" id="printButton" style="display: none;">Print/Save as PDF</button>

    </div>

    <div id="result"></div>
  </div>

  <div id="finalOutput" style="display:none;"></div>


    <hr>

    <div id="rateChartContainer" style="display: none;">
        <h2>Full Rate Chart</h2>

        <h3>Structure Construction Rates (₹/sqft)</h3>
        <table id="structureRatesTable">
            <thead>
                <tr>
                    <th>Construction Type</th>
                    <th>Ground Floor Cost</th>
                    <th>1st & Above Cost</th>
                    <th>Rental Rate</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <h3>Land Rates (₹/Katha)</h3>
        <table id="landRatesTable">
            <thead>
                <tr>
                    <th>Ward Group</th>
                    <th>Passage &lt;3.5m</th>
                    <th>Passage 3.5-7m</th>
                    <th>Passage 7.01-10m</th>
                    <th>Passage 10.01-15m</th>
                    <th>Passage &gt;15.01m</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
		
    </div>

</div> <script>
    // Constants for conversion (specific to Bengal/Bihar Bigha, Katha, Chattak)
    const SQFT_PER_CHATTAK = 45;
    const CHATTAK_PER_KATHA = 16;
    const KATHA_PER_BIGHA = 20;
    const SQFT_PER_KATHA = CHATTAK_PER_KATHA * SQFT_PER_CHATTAK; // 16 chattak * 45 sqft/chattak = 720 sqft
    const SQFT_PER_BIGHA = KATHA_PER_BIGHA * SQFT_PER_KATHA; // 20 katha * 720 sqft/katha = 14400 sqft
    const METERS_TO_FEET = 3.28084; // Conversion factor for meters to feet

    const structureRates = {
      cost: {
        "RCC with Marble": { ground: 50, upper: 40 },
        "RCC with Mojaik": { ground: 35, upper: 25 },
        "RCC with IPS": { ground: 20, upper: 15 },
        "ASB/RTS/CIS with IPS": { ground: 6, upper: 6 }
      },
      rental: {
        "RCC with Marble": 1.5,
        "RCC with Mojaik": 1.0,
        "RCC with IPS": 0.6,
        "ASB/RTS/CIS with IPS": 0.4
      }
    };

    const landRates = {
      "1-44": {
        "less3.5": 6000,
        "3.5-7": 14000,
        "7.01-10": 20000,
        "10.01-15": 24000,
        "15.01+": 30000
      },
      "45-50": {
        "less3.5": 3000,
        "3.5-7": 7000,
        "7.01-10": 10000,
        "10.01-15": 12000,
        "15.01+": 15000
      }
    };

    const wardSelect = document.getElementById('wardNo');
    for(let i=1; i<=50; i++) {
      const opt = document.createElement('option');
      opt.value = i;
      opt.textContent = i;
      wardSelect.appendChild(opt);
    }
    
    /**
     * Fetches the logged-in user's name from a PHP backend.
     */
    function getLoggedInUser() {
        // This makes a request to the PHP script on your server
        fetch('getLoggedInUser.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(user => {
                // If the response is successful, set the value of the hidden input
                document.getElementById('valuationDoneBy').value = user.name;
            })
            .catch(error => {
                console.error('Error fetching user data:', error);
                document.getElementById('valuationDoneBy').value = 'Guest'; // Fallback value
            });
    }

    function getPassageKey(val) {
      switch(val) {
        case "3.5": return "less3.5";
        case "3.5-7": return "3.5-7";
        case "7.01-10": return "7.01-10";
        case "10.01-15": return "10.01-15";
        case "15.01+": return "15.01+";
        default: return "less3.5";
      }
    }

    const floorsTable = document.getElementById('floorsTable');
    const floorsTableHeader = floorsTable.querySelector('thead tr');
    const floorsTableBody = floorsTable.querySelector('tbody');
    let floorCount = 0;

    function addFloor() {
        floorCount++;
        // Add a new header cell for the floor number
        const floorHeader = document.createElement('th');
        floorHeader.textContent = `Floor ${floorCount}`;
        floorsTableHeader.appendChild(floorHeader);

        // Add cells for each detail row
        const constructionTypeRow = floorsTableBody.querySelector('tr:nth-child(1)');
        const floorLevelRow = floorsTableBody.querySelector('tr:nth-child(2)');
        const totalAreaRow = floorsTableBody.querySelector('tr:nth-child(3)');
        const commercialAreaRow = floorsTableBody.querySelector('tr:nth-child(4)');
        const residentialAreaRow = floorsTableBody.querySelector('tr:nth-child(5)');
        const costRateRow = floorsTableBody.querySelector('tr:nth-child(6)');
        const costTotalRow = floorsTableBody.querySelector('tr:nth-child(7)');
        const rentalRateRow = floorsTableBody.querySelector('tr:nth-child(8)');
        const rentalTotalRow = floorsTableBody.querySelector('tr:nth-child(9)');
        const removeButtonRow = floorsTableBody.querySelector('tr:nth-child(10)');

        // Append new cells to each row
        const newConstructionCell = document.createElement('td');
        newConstructionCell.innerHTML = `<select class="constructionType" onchange="updateRatesAndTotals(${floorCount})"><option value="">Select</option><option value="RCC with Marble">RCC with Marble</option><option value="RCC with Mojaik">RCC with Mojaik</option><option value="RCC with IPS">RCC with IPS</option><option value="ASB/RTS/CIS with IPS">ASB/RTS/CIS with IPS</option></select>`;
        constructionTypeRow.appendChild(newConstructionCell);

        const newFloorLevelCell = document.createElement('td');
        newFloorLevelCell.innerHTML = `<select class="floorLevel" onchange="updateRatesAndTotals(${floorCount})"><option value="">Select</option><option value="ground">Ground Floor</option><option value="upper">1st & Above</option></select>`;
        floorLevelRow.appendChild(newFloorLevelCell);

        const newTotalAreaCell = document.createElement('td');
        newTotalAreaCell.innerHTML = `<input type="number" class="totalArea" min="0" value="0" oninput="updateRatesAndTotals(${floorCount})" />`;
        totalAreaRow.appendChild(newTotalAreaCell);

        const newCommercialAreaCell = document.createElement('td');
        newCommercialAreaCell.innerHTML = `<input type="number" class="commercialArea" min="0" value="0" oninput="updateRatesAndTotals(${floorCount})" />`;
        commercialAreaRow.appendChild(newCommercialAreaCell);

        const newResidentialAreaCell = document.createElement('td');
        newResidentialAreaCell.textContent = '0';
        newResidentialAreaCell.classList.add('residentialArea');
        residentialAreaRow.appendChild(newResidentialAreaCell);

        const newCostRateCell = document.createElement('td');
        newCostRateCell.textContent = '-';
        newCostRateCell.classList.add('costRate');
        costRateRow.appendChild(newCostRateCell);

        const newCostTotalCell = document.createElement('td');
        newCostTotalCell.textContent = '0';
        newCostTotalCell.classList.add('costTotal');
        costTotalRow.appendChild(newCostTotalCell);

        const newRentalRateCell = document.createElement('td');
        newRentalRateCell.textContent = '-';
        newRentalRateCell.classList.add('rentalRate');
        rentalRateRow.appendChild(newRentalRateCell);

        const newRentalTotalCell = document.createElement('td');
        newRentalTotalCell.textContent = '0';
        newRentalTotalCell.classList.add('rentalTotal');
        rentalTotalRow.appendChild(newRentalTotalCell);

        const newRemoveCell = document.createElement('td');
        newRemoveCell.innerHTML = `<button onclick="removeFloor(this, ${floorCount})">Remove</button>`;
        removeButtonRow.appendChild(newRemoveCell);
    }

    function removeFloor(button, floorNumber) {
        // Find the index of the column to remove
        const removeColumnIndex = Array.from(button.closest('tr').children).indexOf(button.closest('td'));

        // Remove the header cell
        floorsTableHeader.deleteCell(removeColumnIndex);

        // Remove the cells from each body row
        floorsTableBody.querySelectorAll('tr').forEach(row => {
            row.deleteCell(removeColumnIndex);
        });
        
        recalcFloorNumbers();
    }
    
    function recalcFloorNumbers() {
        floorCount = 0;
        const floorHeaders = floorsTableHeader.querySelectorAll('th:not(:first-child)');
        floorHeaders.forEach((th, index) => {
            floorCount++;
            th.textContent = `Floor ${floorCount}`;
        });
    }


    function updateRatesAndTotals(floorIndex) {
      const allRows = floorsTableBody.querySelectorAll('tr');
      const constructionTypeCellIndex = floorIndex; // 1-based index to 1-based index
      
      const constructionType = allRows[0].cells[constructionTypeCellIndex].querySelector('.constructionType').value;
      const floorLevel = allRows[1].cells[constructionTypeCellIndex].querySelector('.floorLevel').value;
      const totalArea = Number(allRows[2].cells[constructionTypeCellIndex].querySelector('.totalArea').value) || 0;
      const commercialArea = Number(allRows[3].cells[constructionTypeCellIndex].querySelector('.commercialArea').value) || 0;
      
      const residentialArea = Math.max(0, totalArea - commercialArea);
      allRows[4].cells[constructionTypeCellIndex].textContent = residentialArea.toFixed(2);

      const costRateCell = allRows[5].cells[constructionTypeCellIndex];
      const rentalRateCell = allRows[7].cells[constructionTypeCellIndex];
      const costTotalCell = allRows[6].cells[constructionTypeCellIndex];
      const rentalTotalCell = allRows[8].cells[constructionTypeCellIndex];

      // Update cost rate
      let costRate = 0;
      if(constructionType && floorLevel) {
        if(constructionType === "ASB/RTS/CIS with IPS"){
          costRate = structureRates.cost[constructionType].ground;
        } else {
          costRate = structureRates.cost[constructionType][floorLevel] || 0;
        }
      }
      costRateCell.textContent = costRate ? costRate.toFixed(2) : '-';

      // Update rental rate
      let rentalRate = 0;
      if(constructionType) {
        rentalRate = structureRates.rental[constructionType] || 0;
      }
      rentalRateCell.textContent = rentalRate ? rentalRate.toFixed(2) : '-';

      // Update totals
      costTotalCell.textContent = (totalArea * costRate).toFixed(2);
      rentalTotalCell.textContent = (totalArea * rentalRate).toFixed(2);
      
      calculate();
    }

    function calculate() {
        const floorColumns = floorsTableHeader.querySelectorAll('th:not(:first-child)');
        const allRows = floorsTableBody.querySelectorAll('tr');
        const applicationNumber = document.getElementById('applicationNumber').value || 'N/A';
        const applicationDate = document.getElementById('applicationDate').value || 'N/A';
        const filingStatus = document.getElementById('filingStatus').value || 'N/A';
        const assesseeName = document.getElementById('assesseeName').value || 'N/A';
        const premisesDetails = document.getElementById('premisesDetails').value || 'N/A';
        const description = document.getElementById('description').value || 'N/A';
        const propertyType = document.getElementById('propertyType').value;
        const valuationDoneBy = document.getElementById('valuationDoneBy').value || 'N/A';
        const valuationTimestamp = new Date().toLocaleString('en-IN', { timeZone: 'Asia/Kolkata', dateStyle: 'medium', timeStyle: 'short' });

        let totalBuiltUpCost = 0;
        let totalBuiltUpRental = 0;
        let totalCommercialArea = 0;
        let totalResidentialArea = 0;
        let totalBuiltUpArea = 0;

        let appDetailsHtml = `
            <h2>Application & Assessee Details</h2>
            <table style="width:100%;">
                <tr><th>Application No.</th><td>${applicationNumber}</td></tr>
                <tr><th>Date of Application</th><td>${applicationDate}</td></tr>
                <tr><th>Filing Status</th><td>${filingStatus}</td></tr>
                <tr><th>Assessee Name</th><td>${assesseeName}</td></tr>
                <tr><th>Premises Details</th><td>${premisesDetails}</td></tr>
                <tr><th>Description</th><td>${description}</td></tr>
                <tr><th>Property Type</th><td>${propertyType}</td></tr>
                <tr><th>Valuation Done By</th><td>${valuationDoneBy}</td></tr>
                <tr><th>Date/Time Stamp</th><td>${valuationTimestamp}</td></tr>
            </table>
        `;

        let floorDetailsHtml = `
            <h2>Detailed Covered Area Calculations</h2>
            <table>
                <thead>
                    <tr>
                        <th>Floor No.</th>
                        <th>Construction Type</th>
                        <th>Floor Level</th>
                        <th>Total Area (sqft)</th>
                        <th>Commercial Area (sqft)</th>
                        <th>Residential Area (sqft)</th>
                        <th>Cost Total (₹)</th>
                        <th>Rental Total (₹)</th>
                    </tr>
                </thead>
                <tbody>
        `;

        const constructionTypeRow = allRows[0];
        const floorLevelRow = allRows[1];
        const totalAreaRow = allRows[2];
        const commercialAreaRow = allRows[3];
        const residentialAreaRow = allRows[4];
        const costTotalRow = allRows[6];
        const rentalTotalRow = allRows[8];

        for(let i = 1; i < floorColumns.length + 1; i++) {
            const floorNo = i;
            const constructionType = constructionTypeRow.cells[i].querySelector('.constructionType').value || '-';
            const floorLevel = floorLevelRow.cells[i].querySelector('.floorLevel').value || '-';
            const floorLevelText = floorLevel === 'ground' ? 'Ground Floor' : (floorLevel === 'upper' ? '1st & Above' : '-');
            const totalArea = Number(totalAreaRow.cells[i].querySelector('.totalArea').value) || 0;
            const commercialArea = Number(commercialAreaRow.cells[i].querySelector('.commercialArea').value) || 0;
            const residentialArea = Number(residentialAreaRow.cells[i].textContent) || 0;
            const costTotal = parseFloat(costTotalRow.cells[i].textContent) || 0;
            const rentalTotal = parseFloat(rentalTotalRow.cells[i].textContent) || 0;

            totalBuiltUpCost += costTotal;
            totalBuiltUpRental += rentalTotal;
            totalCommercialArea += commercialArea;
            totalResidentialArea += residentialArea;
            totalBuiltUpArea += totalArea;

            floorDetailsHtml += `
                <tr>
                    <td>${floorNo}</td>
                    <td>${constructionType}</td>
                    <td>${floorLevelText}</td>
                    <td>${totalArea.toFixed(2)}</td>
                    <td>${commercialArea.toFixed(2)}</td>
                    <td>${residentialArea.toFixed(2)}</td>
                    <td>${costTotal.toFixed(2)}</td>
                    <td>${rentalTotal.toFixed(2)}</td>
                </tr>
            `;
        }

        floorDetailsHtml += '</tbody></table>';

      // Land value (cost basis only)
      const landArea = Number(document.getElementById('landArea').value) || 0;
      const ward = Number(document.getElementById('wardNo').value);
      const passage = document.getElementById('passageWidth').value;
      const passageKey = getPassageKey(passage);
      const wardGroup = (ward >= 1 && ward <= 44) ? "1-44" : "45-50";

      const ratePerKatha = landRates[wardGroup][passageKey] || 0;
      const kathas = landArea / SQFT_PER_KATHA;

      const landValue = kathas * ratePerKatha;
      
      const surchargePercentage = parseFloat(document.getElementById('surchargePercentage').value) || 0;
      const discountPercentage = parseFloat(document.getElementById('discountPercentage').value) || 0;
      const discountFactor = (100 - discountPercentage) / 100;

      // --- Valuation for Cost Basis Method ---
      let residentialCostValue = 0;
      let commercialCostValue = 0;

      if (totalBuiltUpArea > 0) {
        residentialCostValue = (totalBuiltUpCost * (totalResidentialArea / totalBuiltUpArea));
        commercialCostValue = (totalBuiltUpCost * (totalCommercialArea / totalBuiltUpArea));
      }
      const totalCostValue = residentialCostValue + commercialCostValue + landValue;
      
      const initialAnnualCostValue = totalCostValue * 0.05;
      const surchargeCostAmount = (commercialCostValue * (surchargePercentage / 100)) * 0.05;

      const finalCostAnnualValue = initialAnnualCostValue * discountFactor;
      const finalCostSurchargeValue = surchargeCostAmount * discountFactor;

      // --- Valuation for Rental Basis Method ---
      let residentialRentalValue = 0;
      let commercialRentalValue = 0;

      if (totalBuiltUpArea > 0) {
        residentialRentalValue = (totalBuiltUpRental * (totalResidentialArea / totalBuiltUpArea));
        commercialRentalValue = (totalBuiltUpRental * (totalCommercialArea / totalBuiltUpArea));
      }
      const totalRentalValue = residentialRentalValue + commercialRentalValue;
      
      const initialAnnualRentalValue = totalRentalValue * 12;
      const surchargeRentalAmount = (commercialRentalValue * (surchargePercentage / 100)) * 12;

      const finalRentalAnnualValue = initialAnnualRentalValue * discountFactor;
      const finalRentalSurchargeValue = surchargeRentalAmount * discountFactor;

      let passageWidthDisplay = "";
      if (passage === "3.5") {
          passageWidthDisplay = "Less than 3.5m (~11.5ft)";
      } else if (passage === "3.5-7") {
          passageWidthDisplay = "3.5 to 7.0m (~11.5ft - 23ft)";
      } else if (passage === "7.01-10") {
          passageWidthDisplay = "7.01 to 10m (~23ft - 32.8ft)";
      } else if (passage === "10.01-15") {
          passageWidthDisplay = "10.01 to 15m (~32.8ft - 49.2ft)";
      } else if (passage === "15.01+") {
          passageWidthDisplay = "Above 15.01m (~49.2ft+)";
      }

      const landDetailsHtml = `
        <h2>Land Valuation Details </h2>
        <table>
          <tr><th>Land Area (sqft)</th><td>${landArea.toFixed(2)}</td></tr>
          <tr><th>Ward No.</th><td>${ward}</td></tr>
          <tr><th>Passage Width</th><td>${passageWidthDisplay}</td></tr>
          <tr><th>Rate per Katha (₹)</th><td>${ratePerKatha.toFixed(2)}</td></tr>
          <tr><th>Total Land Value (₹)</th><td>${landValue.toFixed(2)}</td></tr>
        </table>
      `;
      
      let finalAnnualValue = 0;
      let finalSurchargeValue = 0;
      let highlightCost = '';
      let highlightRental = '';
      let finalMethodText = '';

      if (propertyType === 'apartment') {
        finalAnnualValue = finalRentalAnnualValue;
        finalSurchargeValue = finalRentalSurchargeValue;
        highlightRental = ' class="highlight"';
        finalMethodText = 'Rental Basis';
      } else { // Non-apartment, based on user request, ONLY Cost Basis
        finalAnnualValue = finalCostAnnualValue;
        finalSurchargeValue = finalCostSurchargeValue;
        highlightCost = ' class="highlight"';
        finalMethodText = 'Cost Basis';
      }

      const costBasisDiscountAmount = initialAnnualCostValue * (discountPercentage / 100);
      const rentalBasisDiscountAmount = initialAnnualRentalValue * (discountPercentage / 100);

      let summaryHtml = `
        <h2>Summary of Valuation Methods</h2>
        <table>
          <thead>
            <tr>
              <th>Method</th>
              <th colspan="4">Details and Calculation</th>
            </tr>
          </thead>
          <tbody>
            <tr class="section-header"><td colspan="5">Cost Basis Method</td></tr>
            <tr>
              <td rowspan="6">Cost Basis</td>
              <td style="text-align: left;">Residential Built-Up Value</td>
              <td style="text-align: right;">₹ ${residentialCostValue.toFixed(2)}</td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td style="text-align: left;">Commercial Built-Up Value</td>
              <td style="text-align: right;">₹ ${commercialCostValue.toFixed(2)}</td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td style="text-align: left;">Total Land Value</td>
              <td style="text-align: right;">₹ ${landValue.toFixed(2)}</td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td style="text-align: left; font-weight: bold;">Total Property Value</td>
              <td style="text-align: right; font-weight: bold;">₹ ${totalCostValue.toFixed(2)}</td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td style="text-align: left;">Initial Annual Value (× 5%)</td>
              <td style="text-align: right;">₹ ${(initialAnnualCostValue).toFixed(2)}</td>
              <td></td>
              <td rowspan="2"${highlightCost}>
                <span class="main-value">AV: ${finalCostAnnualValue.toFixed(2)}</span><br>
                <span class="surcharge-value">Surcharge: ${finalCostSurchargeValue.toFixed(2)}</span>
              </td>
            </tr>
            <tr>
              <td style="text-align: left;">**Less Discount (${discountPercentage.toFixed(2)}%)**</td>
              <td style="text-align: right;">(₹ ${costBasisDiscountAmount.toFixed(2)})</td>
              <td></td>
            </tr>

            <tr class="section-header"><td colspan="5">Rental Basis Method</td></tr>
            <tr>
              <td rowspan="4">Rental Basis</td>
              <td style="text-align: left;">Residential Monthly Rental Value</td>
              <td style="text-align: right;">₹ ${residentialRentalValue.toFixed(2)}</td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td style="text-align: left;">Commercial Monthly Rental Value</td>
              <td style="text-align: right;">₹ ${commercialRentalValue.toFixed(2)}</td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td style="text-align: left; font-weight: bold;">Total Monthly Rental Value</td>
              <td style="text-align: right; font-weight: bold;">₹ ${totalRentalValue.toFixed(2)}</td>
              <td></td>
              <td rowspan="2"${highlightRental}>
                <span class="main-value">AV: ${finalRentalAnnualValue.toFixed(2)}</span><br>
                <span class="surcharge-value">Surcharge: ${finalRentalSurchargeValue.toFixed(2)}</span>
              </td>
            </tr>
            <tr>
              <td style="text-align: left;">Initial Annual Value (× 12)</td>
              <td style="text-align: right;">₹ ${(initialAnnualRentalValue).toFixed(2)}</td>
              <td></td>
            </tr>

            <tr class="section-header"><td colspan="5" style="padding-top: 20px;">Final Annual Valuation</td></tr>
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold;">Final Annual Value (AV)</td>
                <td class="discount-final">₹ ${finalAnnualValue.toFixed(2)}</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold;">Surcharge (Final)</td>
                <td class="discount-final">₹ ${finalSurchargeValue.toFixed(2)}</td>
            </tr>
			
          </tbody>
        </table>
      `;

      document.getElementById('result').innerHTML = appDetailsHtml + floorDetailsHtml + landDetailsHtml + summaryHtml;
      document.getElementById('result').style.display = 'block';
      document.getElementById('printButton').style.display = 'inline-block';
    }
    
    function preparePrintOutput() {
        // This function will render the final output in the hidden div for printing
        const applicationNumber = document.getElementById('applicationNumber').value || 'N/A';
        const applicationDate = document.getElementById('applicationDate').value || 'N/A';
        const filingStatus = document.getElementById('filingStatus').value || 'N/A';
        const assesseeName = document.getElementById('assesseeName').value || 'N/A';
        const premisesDetails = document.getElementById('premisesDetails').value || 'N/A';
        const description = document.getElementById('description').value || 'N/A';
        const propertyType = document.getElementById('propertyType').value;
        const valuationDoneBy = document.getElementById('valuationDoneBy').value || 'N/A';
        const valuationTimestamp = new Date().toLocaleString('en-IN', { timeZone: 'Asia/Kolkata', dateStyle: 'medium', timeStyle: 'short' });

        // Recalculate everything to ensure fresh values
        const floorColumns = floorsTableHeader.querySelectorAll('th:not(:first-child)');
        const allRows = floorsTableBody.querySelectorAll('tr');

        let totalBuiltUpCost = 0;
        let totalBuiltUpRental = 0;
        let totalCommercialArea = 0;
        let totalResidentialArea = 0;
        let totalBuiltUpArea = 0;

        const constructionTypeRow = allRows[0];
        const floorLevelRow = allRows[1];
        const totalAreaRow = allRows[2];
        const commercialAreaRow = allRows[3];
        const residentialAreaRow = allRows[4];
        const costTotalRow = allRows[6];
        const rentalTotalRow = allRows[8];

        for(let i = 1; i < floorColumns.length + 1; i++) {
            const totalArea = Number(totalAreaRow.cells[i].querySelector('.totalArea').value) || 0;
            const commercialArea = Number(commercialAreaRow.cells[i].querySelector('.commercialArea').value) || 0;
            const costTotal = parseFloat(costTotalRow.cells[i].textContent) || 0;
            const rentalTotal = parseFloat(rentalTotalRow.cells[i].textContent) || 0;
            const residentialArea = totalArea - commercialArea;

            totalBuiltUpCost += costTotal;
            totalBuiltUpRental += rentalTotal;
            totalCommercialArea += commercialArea;
            totalResidentialArea += residentialArea;
            totalBuiltUpArea += totalArea;
        }

        // Land value (cost basis only)
        const landArea = Number(document.getElementById('landArea').value) || 0;
        const ward = Number(document.getElementById('wardNo').value);
        const passage = document.getElementById('passageWidth').value;
        const passageKey = getPassageKey(passage);
        const wardGroup = (ward >= 1 && ward <= 44) ? "1-44" : "45-50";

        const ratePerKatha = landRates[wardGroup][passageKey] || 0;
        const kathas = landArea / SQFT_PER_KATHA;
        const landValue = kathas * ratePerKatha;

        const surchargePercentage = parseFloat(document.getElementById('surchargePercentage').value) || 0;
        const discountPercentage = parseFloat(document.getElementById('discountPercentage').value) || 0;
        const discountFactor = (100 - discountPercentage) / 100;

        let residentialCostValue = 0;
        let commercialCostValue = 0;
        if (totalBuiltUpArea > 0) {
            residentialCostValue = (totalBuiltUpCost * (totalResidentialArea / totalBuiltUpArea));
            commercialCostValue = (totalBuiltUpCost * (totalCommercialArea / totalBuiltUpArea));
        }
        const totalCostValue = residentialCostValue + commercialCostValue + landValue;
        const initialAnnualCostValue = totalCostValue * 0.05;
        const surchargeCostAmount = (commercialCostValue * (surchargePercentage / 100)) * 0.05;
        const finalCostAnnualValue = initialAnnualCostValue * discountFactor;
        const finalCostSurchargeValue = surchargeCostAmount * discountFactor;

        let residentialRentalValue = 0;
        let commercialRentalValue = 0;
        if (totalBuiltUpArea > 0) {
            residentialRentalValue = (totalBuiltUpRental * (totalResidentialArea / totalBuiltUpArea));
            commercialRentalValue = (totalBuiltUpRental * (totalCommercialArea / totalBuiltUpArea));
        }
        const totalRentalValue = residentialRentalValue + commercialRentalValue;
        const initialAnnualRentalValue = totalRentalValue * 12;
        const surchargeRentalAmount = (commercialRentalValue * (surchargePercentage / 100)) * 12;
        const finalRentalAnnualValue = initialAnnualRentalValue * discountFactor;
        const finalRentalSurchargeValue = surchargeRentalAmount * discountFactor;

        let finalAnnualValue = 0;
        let finalSurchargeValue = 0;

        if (propertyType === 'apartment') {
            finalAnnualValue = finalRentalAnnualValue;
            finalSurchargeValue = finalRentalSurchargeValue;
        } else {
            finalAnnualValue = finalCostAnnualValue;
            finalSurchargeValue = finalCostSurchargeValue;
        }
        
        let reportDetailsHtml = `
            
            <div class="container">
                <h2>Application & Assessee Details</h2>
                <table style="width:100%;">
                    <tr>
                        <th style="width:30%;">Application No.</th>
                        <td>${applicationNumber}</td>
                    </tr>
                    <tr>
                        <th>Date of Application</th>
                        <td>${applicationDate}</td>
                    </tr>
                    <tr>
                        <th>Filing Status</th>
                        <td>${filingStatus}</td>
                    </tr>
                    <tr>
                        <th>Assessee Name</th>
                        <td>${assesseeName}</td>
                    </tr>
                    <tr>
                        <th>Premises Details</th>
                        <td>${premisesDetails}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>${description}</td>
                    </tr>
                    <tr>
                        <th>Property Type</th>
                        <td>${propertyType}</td>
                    </tr>
                    <tr>
                        <th>Valuation Done By</th>
                        <td>${valuationDoneBy}</td>
                    </tr>
                    <tr>
                        <th>Date/Time Stamp</th>
                        <td>${valuationTimestamp}</td>
                    </tr>
                </table>
        `;
        
        const floorHeaders = floorsTableHeader.querySelectorAll('th:not(:first-child)');
        const allFloorRows = floorsTableBody.querySelectorAll('tr');
        let floorDetailsTableHtml = `
            <h2>Detailed Covered Area Calculations</h2>
            <table style="width:100%;">
                <thead>
                    <tr>
                        <th>Floor No.</th>
                        <th>Construction Type</th>
                        <th>Floor Level</th>
                        <th>Total Area (sqft)</th>
                        <th>Comm. Area (sqft)</th>
                        <th>Resi. Area (sqft)</th>
                        <th>Cost Total (₹)</th>
                        <th>Rental Total (₹)</th>
                    </tr>
                </thead>
                <tbody>
        `;
        for(let i = 1; i < floorHeaders.length + 1; i++) {
            floorDetailsTableHtml += `
                <tr>
                    <td>${i}</td>
                    <td>${allFloorRows[0].cells[i].querySelector('.constructionType').value || '-'}</td>
                    <td>${allFloorRows[1].cells[i].querySelector('.floorLevel').value === 'ground' ? 'Ground Floor' : '1st & Above'}</td>
                    <td>${Number(allFloorRows[2].cells[i].querySelector('.totalArea').value).toFixed(2)}</td>
                    <td>${Number(allFloorRows[3].cells[i].querySelector('.commercialArea').value).toFixed(2)}</td>
                    <td>${Number(allFloorRows[4].cells[i].textContent).toFixed(2)}</td>
                    <td>${parseFloat(allFloorRows[6].cells[i].textContent).toFixed(2)}</td>
                    <td>${parseFloat(allFloorRows[8].cells[i].textContent).toFixed(2)}</td>
                </tr>
            `;
        }
        floorDetailsTableHtml += '</tbody></table>';

        let passageWidthDisplay = document.getElementById('passageWidth').options[document.getElementById('passageWidth').selectedIndex].text;
        const landDetailsPrintHtml = `
            <h2>Land Valuation Details </h2>
            <table style="width:100%;">
              <tr><th style="width:30%;">Land Area (sqft)</th><td>${landArea.toFixed(2)}</td></tr>
              <tr><th>Ward No.</th><td>${ward}</td></tr>
              <tr><th>Passage Width</th><td>${passageWidthDisplay}</td></tr>
              <tr><th>Rate per Katha (₹)</th><td>${ratePerKatha.toFixed(2)}</td></tr>
              <tr><th>Total Land Value (₹)</th><td>${landValue.toFixed(2)}</td></tr>
            </table>
        `;
        
        const costBasisDiscountAmount = initialAnnualCostValue * (discountPercentage / 100);
        const rentalBasisDiscountAmount = initialAnnualRentalValue * (discountPercentage / 100);

        let summaryPrintHtml = `
            <h2>Summary of Valuation Methods</h2>
            <table style="width:100%;">
              <thead>
                <tr>
                    <th>Method</th>
                    <th colspan="4">Details and Calculation</th>
                </tr>
              </thead>
              <tbody>
                <tr class="section-header"><td colspan="5">Cost Basis Method</td></tr>
                <tr>
                    <td rowspan="6">Cost Basis</td>
                    <td style="text-align: left;">Residential Built-Up Value</td>
                    <td style="text-align: right;">₹ ${residentialCostValue.toFixed(2)}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align: left;">Commercial Built-Up Value</td>
                    <td style="text-align: right;">₹ ${commercialCostValue.toFixed(2)}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align: left;">Total Land Value</td>
                    <td style="text-align: right;">₹ ${landValue.toFixed(2)}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align: left; font-weight: bold;">Total Property Value</td>
                    <td style="text-align: right; font-weight: bold;">₹ ${totalCostValue.toFixed(2)}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align: left;">Initial Annual Value (× 5%)</td>
                    <td style="text-align: right;">₹ ${(initialAnnualCostValue).toFixed(2)}</td>
                    <td></td>
                    <td rowspan="2" style="background-color: #f0f0f0;">
                        <span class="main-value">AV: ${finalCostAnnualValue.toFixed(2)}</span><br>
                        <span class="surcharge-value">Surcharge: ${finalCostSurchargeValue.toFixed(2)}</span>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left;">**Less Discount (${discountPercentage.toFixed(2)}%)**</td>
                    <td style="text-align: right;">(₹ ${costBasisDiscountAmount.toFixed(2)})</td>
                    <td></td>
                </tr>

                <tr class="section-header"><td colspan="5">Rental Basis Method</td></tr>
                <tr>
                    <td rowspan="4">Rental Basis</td>
                    <td style="text-align: left;">Residential Monthly Rental Value</td>
                    <td style="text-align: right;">₹ ${residentialRentalValue.toFixed(2)}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align: left;">Commercial Monthly Rental Value</td>
                    <td style="text-align: right;">₹ ${commercialRentalValue.toFixed(2)}</td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align: left; font-weight: bold;">Total Monthly Rental Value</td>
                    <td style="text-align: right; font-weight: bold;">₹ ${totalRentalValue.toFixed(2)}</td>
                    <td></td>
                    <td rowspan="2" style="background-color: #f0f0f0;">
                        <span class="main-value">AV: ${finalRentalAnnualValue.toFixed(2)}</span><br>
                        <span class="surcharge-value">Surcharge: ${finalRentalSurchargeValue.toFixed(2)}</span>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left;">Initial Annual Value (× 12)</td>
                    <td style="text-align: right;">₹ ${(initialAnnualRentalValue).toFixed(2)}</td>
                    <td></td>
                </tr>

                <tr class="section-header"><td colspan="5" style="padding-top: 20px;">Final Annual Valuation</td></tr>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">Final Annual Value (AV)</td>
                    <td class="discount-final">₹ ${finalAnnualValue.toFixed(2)}</td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">Surcharge (Final)</td>
                    <td class="discount-final">₹ ${finalSurchargeValue.toFixed(2)}</td>
                </tr>
			
              </tbody>
			
            </table>
			
            </div>
			<DIV>
			<P> <STRONG> PREPARED BY: <STRONG/><P/> 
			<P> ...................... <P/>
			 </div>
			<DIV>
			<P> <STRONG> CHECKED & VERIFIED BY: <STRONG/>
			<P/><P>  ................. <P/>
		 </div>
			<DIV> <P> <STRONG> APPROVED BY: <STRONG/>
			<P/> <P> ....................... <P/></div>
        `;
        
        document.getElementById('finalOutput').innerHTML = reportDetailsHtml + floorDetailsTableHtml + landDetailsPrintHtml + summaryPrintHtml;

        window.print();
    }


    // --- Area Arithmetic Functions ---
    function calculateArithmeticArea() {
      let bigha = parseFloat(document.getElementById('arithmeticBigha').value) || 0;
      let katha = parseFloat(document.getElementById('arithmeticKatha').value) || 0;
      let chattak = parseFloat(document.getElementById('arithmeticChattak').value) || 0;
      let sqft = parseFloat(document.getElementById('arithmeticSqft').value) || 0;

      // Convert all inputs to square feet and sum them
      let totalSqft =
          (bigha * SQFT_PER_BIGHA) +
          (katha * SQFT_PER_KATHA) +
          (chattak * SQFT_PER_CHATTAK) +
          sqft;

      document.getElementById('arithmeticResult').textContent = `Calculated Total Area: ${totalSqft.toFixed(2)} Sq.Ft.`;
      // Populate the Land Area (sqft) field with this result
      document.getElementById('landArea').value = totalSqft.toFixed(2);
      
      calculate();
    }
    
    // --- Rate Chart Display Functions ---
    function populateRateChart() {
      const structureTableBody = document.getElementById('structureRatesTable').querySelector('tbody');
      const landTableBody = document.getElementById('landRatesTable').querySelector('tbody');

      // Clear existing content
      structureTableBody.innerHTML = '';
      landTableBody.innerHTML = '';

      // Populate Structure Rates
      for (const type in structureRates.cost) {
        const row = structureTableBody.insertRow();
        row.insertCell().textContent = type;
        row.insertCell().textContent = structureRates.cost[type].ground.toFixed(2);
        const upperCost = (type === "ASB/RTS/CIS with IPS") ? structureRates.cost[type].ground : structureRates.cost[type].upper;
        row.insertCell().textContent = upperCost.toFixed(2);
        row.insertCell().textContent = structureRates.rental[type].toFixed(2);
      }

      // Populate Land Rates
      const passageKeys = ["less3.5", "3.5-7", "7.01-10", "10.01-15", "15.01+"];
      for (const wardGroup in landRates) {
        const row = landTableBody.insertRow();
        row.insertCell().textContent = `Ward ${wardGroup}`;
        passageKeys.forEach(key => {
          row.insertCell().textContent = landRates[wardGroup][key].toFixed(2);
        });
      }
    }
    
    function toggleRateChart() {
        const chartContainer = document.getElementById('rateChartContainer');
        if (chartContainer.style.display === 'none') {
            chartContainer.style.display = 'block';
            populateRateChart();
        } else {
            chartContainer.style.display = 'none';
        }
    }
    
    document.addEventListener('DOMContentLoaded', (event) => {
      populateRateChart();
      addFloor();
      getLoggedInUser();
	  document.addEventListener('DOMContentLoaded', function() {
            // Fetch the logged-in user name from the server
            fetch('getLoggedInUser.php')
                .then(response => response.json())
                .then(data => {
                    const userNameElement = document.getElementById('valuation-done-by-name');
                    if (data && data.name) {
                        userNameElement.textContent = data.name;
                    } else {
                        userNameElement.textContent = 'Guest';
                    }
                })
                .catch(error => {
                    console.error('Error fetching user data:', error);
                    document.getElementById('valuation-done-by-name').textContent = 'Error';
                });
        });
    });
</script>
</body>
</html>