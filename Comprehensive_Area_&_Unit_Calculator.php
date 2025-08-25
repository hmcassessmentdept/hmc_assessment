<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Comprehensive Area & Unit Calculator</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
<style>
    body {
    font-family: 'Roboto', Arial, sans-serif;
    margin: 0;
    background-color: #eef2f6;
    color: #333;
    padding: 30px 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 100vh;
    box-sizing: border-box;
}

.header-bar {
    background-image: linear-gradient(to right, #0056b3, #007bff);
    color: white;
    padding: 20px 20px;
    width: 100%;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.25);
    margin-bottom: 40px;
    box-sizing: border-box;
    border-bottom-left-radius: 15px;
    border-bottom-right-radius: 15px;
    animation: slideDown 0.8s ease-out; /* Enhanced animation */
}
.header-bar h1 {
    margin: 0;
    font-size: 2.2em;
    letter-spacing: 0.5px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}

.logo-container {
    margin-bottom: 50px;
    animation: fadeIn 1s ease-out;
}

.logo-container img {
    max-width: 550px;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 0px 0px rgba(0,0,0,0.15);
}

.container {
    max-width: 950px;
    width: 100%;
    margin: 0 auto;
    padding: 0 25px;
    box-sizing: border-box;
}

.calculator-section, .converter-container {
    background-color: white;
    border: 1px solid #d8e2ed;
    padding: 30px;
    margin-bottom: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    position: relative;
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    animation: slideInUp 0.6s ease-out; /* Added animation for sections */
}
.calculator-section:hover, .converter-container:hover {
    transform: translateY(-8px) scale(1.01); /* More pronounced lift and slight scale */
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

h2 {
    color: #0056b3;
    border-bottom: 2px solid #007bff;
    padding-bottom: 12px;
    margin-top: 0;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1.6em;
    font-weight: 700;
}
h2 .remove-btn {
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 8px 15px;
    font-size: 0.9em;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.1s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
h2 .remove-btn:hover {
    background-color: #c82333;
    transform: translateY(-2px); /* More pronounced lift */
}

.input-group {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    position: relative;
    animation: fadeIn 0.5s ease-in-out;
}
.input-group label {
    flex: 0 0 120px;
    margin-right: 20px;
    font-weight: 600;
    color: #444;
    text-align: right;
}
.input-group input[type="number"] {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #c0d0e0;
    border-radius: 6px;
    box-sizing: border-box;
    max-width: 180px;
    font-size: 1.05em;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.input-group input[type="number"]:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 4px rgba(0,123,255,0.3); /* Stronger focus glow */
    outline: none;
}
.input-group span {
    flex: 0 0 70px;
    text-align: left;
    font-weight: 600;
    color: #666;
    margin-left: 15px;
    font-size: 0.95em;
}

.result-output, .conversion-result {
    font-weight: 700;
    margin-top: 20px;
    padding: 18px;
    background-color: #e6f2ff;
    border: 1px solid #a0c3e8;
    border-radius: 8px;
    text-align: center;
    color: #004085;
    min-height: 50px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    word-break: break-word;
    font-size: 1.15em;
    line-height: 1.4;
    box-shadow: inset 0 1px 4px rgba(0,0,0,0.08);
    animation: popIn 0.4s ease-out; /* New animation for results */
}
.result-output span {
    display: block;
    margin: 4px 0;
}
.result-output strong, .conversion-result strong {
    color: #007bff;
    font-size: 1.1em;
}

.button-group {
    text-align: center;
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 30px;
}
button {
    padding: 12px 25px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1.05em;
    font-weight: 700;
    transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}
button:hover {
    background-color: #0056b3;
    transform: translateY(-4px); /* More pronounced lift */
    box-shadow: 0 7px 15px rgba(0,0,0,0.2);
}
button:active {
    background-color: #004085;
    transform: translateY(0);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
button.add-btn {
    background-color: #28a745;
}
button.add-btn:hover {
    background-color: #218838;
}
button.clear-all-btn {
    background-color: #6c757d;
}
button.clear-all-btn:hover {
    background-color: #5a6268;
}

.custom-calculation-section {
    background-color: white;
    border: 1px solid #d8e2ed;
    padding: 30px;
    margin-bottom: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.custom-calculation-item {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    gap: 15px;
    animation: fadeIn 0.6s ease-out;
}
.custom-calculation-item select {
    width: auto;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #c0d0e0;
    font-weight: 600;
    flex-shrink: 0;
    background-color: #f8fbfd;
    color: #333;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.custom-calculation-item select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.2);
    outline: none;
}
.custom-calculation-item .area-label {
    font-weight: 600;
    flex-grow: 1;
    color: #555;
    font-size: 1.1em;
}

.operation-results {
    margin-top: 35px;
    padding: 25px;
    background-color: #f0f7ff;
    border: 1px solid #b3d9ff;
    border-radius: 10px;
    box-shadow: inset 0 2px 6px rgba(0,0,0,0.08);
    display: none;
    animation: slideInUp 0.5s ease-out;
}
.operation-results h3 {
    color: #0056b3;
    margin-top: 0;
    margin-bottom: 20px;
    text-align: center;
    font-size: 1.8em;
    font-weight: 700;
}
.operation-results p {
    font-size: 1.2em;
    margin-bottom: 10px;
    color: #343a40;
    line-height: 1.5;
}
.operation-results p strong {
    color: #007bff;
    font-size: 1.1em;
}

.converter-container {
    max-width: 1550px;
    margin-top: 40px;
    border: 1px solid #cceeff;
    background-color: #f7fcff;
}
.converter-container .input-group {
    flex-direction: column;
    align-items: flex-start;
    margin-bottom: 15px;
}
.converter-container .input-group label {
    text-align: left;
    margin-bottom: 8px;
    width: auto;
    flex: none;
}
.converter-container .input-group input[type="number"] {
    max-width: 100%;
    width: 100%;
}
.conversion-result {
    font-size: 1.15em;
    margin-top: 25px;
    background-color: #e6f2ff;
    border: 1px solid #a0c3e8;
}

/* New Animations */
@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes popIn {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .header-bar h1 {
        font-size: 1.8em;
    }
    .container {
        padding: 0 15px;
    }
    .calculator-section, .converter-container {
        padding: 20px;
    }
    h2 {
        font-size: 1.4em;
        margin-bottom: 18px;
    }
    h2 .remove-btn {
        padding: 6px 12px;
        font-size: 0.8em;
    }
    .input-group {
        flex-direction: column;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    .input-group label {
        text-align: left;
        margin-bottom: 5px;
        flex: none;
        width: auto;
    }
    .input-group input[type="number"] {
        max-width: 100%;
        width: 100%;
    }
    .input-group span {
        margin-left: 0;
        margin-top: 5px;
        text-align: left;
    }
    .result-output, .conversion-result {
        font-size: 1em;
        padding: 15px;
    }
    .button-group {
        flex-direction: column;
        gap: 12px;
        margin-top: 20px;
        margin-bottom: 20px;
    }
    button {
        padding: 10px 20px;
        font-size: 1em;
        width: 100%;
        max-width: 300px;
    }
    .custom-calculation-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    .custom-calculation-item select, .custom-calculation-item .area-label {
        width: 100%;
    }
    .operation-results {
        padding: 18px;
    }
    .operation-results h3 {
        font-size: 1.5em;
    }
    .operation-results p {
        font-size: 1em;
    }
}
</style>
</head>
<body>

<div class="logo-container">
    <img src="logo1.png" alt="Howrah Municipal Corporation Logo">
</div>
<div class="header-bar">
    <h1>Comprehensive Area & Unit Calculator</h1>
</div>

<div class="container">

    <div id="measurementsContainer">
            </div>

    <div class="button-group">
        <button class="add-btn" onclick="addMeasurement()">Add New Area Measurement</button>
        <button class="clear-all-btn" onclick="clearAllMeasurements()">Clear All Areas</button>
    </div>

    <div class="calculator-section custom-calculation-section">
        <h2>Custom Area Arithmetic</h2>
        <div id="customCalculationInputs">
            <p style="text-align: center; color: #777;">Add areas above to start a custom calculation.</p>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <button onclick="calculateCustomExpression()">Calculate Custom Expression</button>
        </div>
    </div>

    <div class="calculator-section operation-results" id="customOperationResults">
        <h3>Custom Calculation Result</h3>
        <p>Total (Sq.Ft.): <strong id="customResultSqFt">0.00</strong></p>
        <p>In Traditional Units: <strong id="customResultTraditional">0 Bigha, 0 Katha, 0 Chattak, 0.00 Sq.Ft.</strong></p>
        <p>In Imperial Units: <strong id="customResultImperial">0 Acre, 0 Satak, 0.00 Decimal</strong></p>
        <p>In Hectares: <strong id="customResultHectare">0.00 Hectare</strong></p>
    </div>

    <div class="converter-container">
        <h2>Square Feet ↔ Square Meter Converter</h2>

        <div class="input-group">
            <label for="sqFeetConverterInput">Square Feet (sq ft):</label>
            <input type="number" id="sqFeetConverterInput" value="0" min="0" oninput="convertSimpleUnits('sqft')">
        </div>

        <div class="input-group">
            <label for="sqMeterConverterInput">Square Meter (sq m):</label>
            <input type="number" id="sqMeterConverterInput" value="0" min="0" oninput="convertSimpleUnits('sqm')">
        </div>

        <div class="conversion-result" id="simpleConverterResult">
            Enter a value above to see conversion.
        </div>
    </div>
    </div> 

<script>
  // Constants for conversion (specific to Bengal/Bihar and general land units)
  const SQFT_PER_CHATTAK = 45;
  const CHATTAK_PER_KATHA = 16;
  const KATHA_PER_BIGHA = 20;
  const SQFT_PER_KATHA = CHATTAK_PER_KATHA * SQFT_PER_CHATTAK; // 16 chattak * 45 sqft/chattak = 720 sqft
  const SQFT_PER_BIGHA = KATHA_PER_BIGHA * SQFT_PER_KATHA; // 20 katha * 720 sqft/katha = 14400 sqft


  // New constants for Acre, Satak, Decimal, Hectare
  const SQFT_PER_ACRE = 43560;
  const SQFT_PER_DECIMAL = 435.6; // 1 Decimal = 1/100 Acre = 435.6 Sq.Ft.
  const SQFT_PER_SATAK = 435.6; // Assuming 1 Satak = 1 Decimal = 435.6 Sq.Ft. (common in some regions)
  const SQFT_PER_HECTARE = 107639.104; // 1 Hectare = 10,000 sq meters = 10,000 * 10.7639 sq ft

  let measurementCount = 0;
  const measurementsData = {}; // Stores { id: totalSqFt } for each measurement
  const measurementOrder = []; // Stores order of IDs to maintain display order

  const measurementsContainer = document.getElementById('measurementsContainer');
  const customCalculationInputsContainer = document.getElementById('customCalculationInputs');
  const customOperationResultsDiv = document.getElementById('customOperationResults');

  // Function to convert all units to total Sq.Ft.
  function toSqFt(bigha, katha, chattak, sqft, acre, satak, decimal, hectare) {
    return (bigha * SQFT_PER_BIGHA) +
           (katha * SQFT_PER_KATHA) +
           (chattak * SQFT_PER_CHATTAK) +
           sqft +
           (acre * SQFT_PER_ACRE) +
           (satak * SQFT_PER_SATAK) +
           (decimal * SQFT_PER_DECIMAL) +
           (hectare * SQFT_PER_HECTARE);
  }

  // Function to convert total Sq.Ft. back to all units
  function toTraditionalUnits(totalSqFt) {
    let remainingSqFt = Math.abs(totalSqFt); // Use absolute value for conversion for breakdown

    // Traditional units (Bengal/Bihar)
    const bigha = Math.floor(remainingSqFt / SQFT_PER_BIGHA);
    remainingSqFt %= SQFT_PER_BIGHA;

    const katha = Math.floor(remainingSqFt / SQFT_PER_KATHA);
    remainingSqFt %= SQFT_PER_KATHA;

    const chattak = Math.floor(remainingSqFt / SQFT_PER_CHATTAK);
    remainingSqFt %= SQFT_PER_CHATTAK;

    const sqft = remainingSqFt; // Remaining is in Sq.Ft.

    // Imperial units (Acre, Satak, Decimal) - derived from totalSqFt
    const acre = (Math.abs(totalSqFt) / SQFT_PER_ACRE).toFixed(4); // Keep more precision for decimal units
    let tempSqFtForSatakDecimal = Math.abs(totalSqFt); // Use total for Satak/Decimal if they can be independently large
    const satak = (tempSqFtForSatakDecimal / SQFT_PER_SATAK).toFixed(2);
    const decimal = (tempSqFtForSatakDecimal / SQFT_PER_DECIMAL).toFixed(2);
    
    // Hectare conversion
    const hectare = (Math.abs(totalSqFt) / SQFT_PER_HECTARE).toFixed(4);

    return {
      bigha: bigha,
      katha: katha,
      chattak: chattak,
      sqft: sqft.toFixed(2),
      acre: acre,
      satak: satak,
      decimal: decimal,
      hectare: hectare
    };
  }

  // Add a new measurement input block
  function addMeasurement() {
    measurementCount++;
    const id = `Area${measurementCount}`;
    measurementOrder.push(id); // Add to order array

    const section = document.createElement('div');
    section.className = 'calculator-section';
    section.id = `measurement-${id}`;

    section.innerHTML = `
      <h2>${id}
        <button class="remove-btn" onclick="removeMeasurement('${id}')">Remove</button>
      </h2>
      <div class="input-group">
          <label for="${id}Bigha">Bigha:</label>
          <input type="number" id="${id}Bigha" value="0" min="0" oninput="calculateIndividualMeasurement('${id}')" />
          <span>Bigha</span>
      </div>
      <div class="input-group">
          <label for="${id}Katha">Katha:</label>
          <input type="number" id="${id}Katha" value="0" min="0" oninput="calculateIndividualMeasurement('${id}')" />
          <span>Katha</span>
      </div>
      <div class="input-group">
          <label for="${id}Chattak">Chattak:</label>
          <input type="number" id="${id}Chattak" value="0" min="0" oninput="calculateIndividualMeasurement('${id}')" />
          <span>Chattak</span>
      </div>
      <div class="input-group">
          <label for="${id}SqFt">Sq.Ft.:</label>
          <input type="number" id="${id}SqFt" value="0" min="0" oninput="calculateIndividualMeasurement('${id}')" />
          <span>Sq.Ft.</span>
      </div>
        <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
        <div class="input-group">
          <label for="${id}Acre">Acre:</label>
          <input type="number" id="${id}Acre" value="0" min="0" oninput="calculateIndividualMeasurement('${id}')" />
          <span>Acre</span>
      </div>
        <div class="input-group">
          <label for="${id}Satak">Satak:</label>
          <input type="number" id="${id}Satak" value="0" min="0" oninput="calculateIndividualMeasurement('${id}')" />
          <span>Satak</span>
      </div>
        <div class="input-group">
          <label for="${id}Decimal">Decimal:</label>
          <input type="number" id="${id}Decimal" value="0" min="0" oninput="calculateIndividualMeasurement('${id}')" />
          <span>Decimal</span>
      </div>
        <div class="input-group">
          <label for="${id}Hectare">Hectare:</label>
          <input type="number" id="${id}Hectare" value="0" min="0" oninput="calculateIndividualMeasurement('${id}')" />
          <span>Hectare</span>
      </div>
      <div class="result-output" id="${id}ResultSqFt"><span>Total ${id}: 0.00 Sq.Ft.</span></div>
      <div class="result-output" id="${id}ResultTraditional"><span>${id} in Traditional: 0 Bigha, 0 Katha, 0 Chattak, 0.00 Sq.Ft.</span></div>
      <div class="result-output" id="${id}ResultImperial"><span>${id} in Imperial: 0 Acre, 0 Satak, 0.00 Decimal</span></div>
      <div class="result-output" id="${id}ResultHectare"><span>${id} in Hectares: 0.00 Hectare</span></div>
    `;

    measurementsContainer.appendChild(section);
    calculateIndividualMeasurement(id); // Initialize its value
    updateCustomCalculationUI(); // Update custom calculation section
  }

  // Remove a measurement input block
  function removeMeasurement(idToRemove) {
    const sectionToRemove = document.getElementById(`measurement-${idToRemove}`);
    if (sectionToRemove) {
      sectionToRemove.remove();
      delete measurementsData[idToRemove]; // Remove from stored data
      const index = measurementOrder.indexOf(idToRemove);
      if (index > -1) {
        measurementOrder.splice(index, 1); // Remove from order array
      }
      updateCustomCalculationUI(); // Update custom calculation section
      calculateCustomExpression(); // Recalculate custom expression
    }
  }

  // Calculate and display total Sq.Ft. and all units for an individual measurement
  function calculateIndividualMeasurement(id) {
    const bigha = parseFloat(document.getElementById(`${id}Bigha`).value) || 0;
    const katha = parseFloat(document.getElementById(`${id}Katha`).value) || 0;
    const chattak = parseFloat(document.getElementById(`${id}Chattak`).value) || 0;
    const sqft = parseFloat(document.getElementById(`${id}SqFt`).value) || 0;
    const acre = parseFloat(document.getElementById(`${id}Acre`).value) || 0;
    const satak = parseFloat(document.getElementById(`${id}Satak`).value) || 0;
    const decimal = parseFloat(document.getElementById(`${id}Decimal`).value) || 0;
    const hectare = parseFloat(document.getElementById(`${id}Hectare`).value) || 0;


    let totalSqFt = toSqFt(bigha, katha, chattak, sqft, acre, satak, decimal, hectare);
    measurementsData[id] = totalSqFt; // Store the calculated value

    const convertedUnits = toTraditionalUnits(totalSqFt);

    document.getElementById(`${id}ResultSqFt`).innerHTML = `<span>Total ${id}: ${totalSqFt.toFixed(2)} Sq.Ft.</span>`;
    document.getElementById(`${id}ResultTraditional`).innerHTML = `<span>${id} in Traditional: ${convertedUnits.bigha} Bigha, ${convertedUnits.katha} Katha, ${convertedUnits.chattak} Chattak, ${convertedUnits.sqft} Sq.Ft.</span>`;
    document.getElementById(`${id}ResultImperial`).innerHTML = `<span>${id} in Imperial: ${convertedUnits.acre} Acre, ${convertedUnits.satak} Satak, ${convertedUnits.decimal} Decimal</span>`;
    document.getElementById(`${id}ResultHectare`).innerHTML = `<span>${id} in Hectares: ${convertedUnits.hectare} Hectare</span>`;


    calculateCustomExpression(); // Trigger recalculation of custom expression
  }

  // Update the UI for custom calculations (add/remove selects)
  function updateCustomCalculationUI() {
    customCalculationInputsContainer.innerHTML = '';
    if (measurementOrder.length === 0) {
      customCalculationInputsContainer.innerHTML = '<p style="text-align: center; color: #777;">Add areas above to start a custom calculation.</p>';
      customOperationResultsDiv.style.display = 'none';
      return;
    }

    measurementOrder.forEach((id, index) => {
      const itemDiv = document.createElement('div');
      itemDiv.className = 'custom-calculation-item';

      const select = document.createElement('select');
      select.id = `op-${id}`;
      // For the first item, default to '+', otherwise default to '+'
      select.innerHTML = `
        <option value="+">${index === 0 ? "" : "+"} Add</option>
        <option value="-">- Subtract</option>
      `;
      if (index === 0) { // For the first item, hide the '+' sign as it's implied
        select.querySelector('option[value="+"]').textContent = "Include";
      }

      const label = document.createElement('span');
      label.className = 'area-label';
      label.textContent = id;

      itemDiv.appendChild(select);
      itemDiv.appendChild(label);
      customCalculationInputsContainer.appendChild(itemDiv);
    });
  }

  // Calculate the custom expression based on selected operators
  function calculateCustomExpression() {
    let finalResultSqFt = 0;
    if (Object.keys(measurementsData).length === 0) {
      customOperationResultsDiv.style.display = 'none';
      return;
    }

    measurementOrder.forEach(id => {
      const operatorSelect = document.getElementById(`op-${id}`);
      if (operatorSelect) { // Check if the element exists (might be removed)
        const operator = operatorSelect.value;
        const value = measurementsData[id] || 0;

        if (operator === '+') {
          finalResultSqFt += value;
        } else if (operator === '-') {
          finalResultSqFt -= value;
        }
      }
    });

    const convertedResult = toTraditionalUnits(finalResultSqFt);

    document.getElementById('customResultSqFt').textContent = finalResultSqFt.toFixed(2);
    // Display negative sign if result is negative
    if (finalResultSqFt < 0) {
      document.getElementById('customResultSqFt').textContent = `- ${Math.abs(finalResultSqFt).toFixed(2)}`;
      document.getElementById('customResultTraditional').textContent = `(Negative) ${convertedResult.bigha} Bigha, ${convertedResult.katha} Katha, ${convertedResult.chattak} Chattak, ${convertedResult.sqft} Sq.Ft.`;
      document.getElementById('customResultImperial').textContent = `(Negative) ${convertedResult.acre} Acre, ${convertedResult.satak} Satak, ${convertedResult.decimal} Decimal`;
      document.getElementById('customResultHectare').textContent = `(Negative) ${convertedResult.hectare} Hectare`;

    } else {
      document.getElementById('customResultTraditional').textContent = `${convertedResult.bigha} Bigha, ${convertedResult.katha} Katha, ${convertedResult.chattak} Chattak, ${convertedResult.sqft} Sq.Ft.`;
      document.getElementById('customResultImperial').textContent = `${convertedResult.acre} Acre, ${convertedResult.satak} Satak, ${convertedResult.decimal} Decimal`;
      document.getElementById('customResultHectare').textContent = `${convertedResult.hectare} Hectare`;
    }

    customOperationResultsDiv.style.display = 'block';
  }

  // Clear all measurements and reset
  function clearAllMeasurements() {
    measurementsContainer.innerHTML = '';
    measurementCount = 0;
    for (const key in measurementsData) {
      delete measurementsData[key];
    }
    measurementOrder.length = 0; // Clear the order array
    updateCustomCalculationUI(); // Reset custom calculation UI
    calculateCustomExpression(); // Hide results
  }

    // --- New Simple Sq.Ft. to Sq.M. Converter Logic ---
    const SQFT_TO_SQM_FACTOR = 0.09290304; // 1 square foot = 0.09290304 square meters

    function convertSimpleUnits(sourceUnit) {
        const sqFeetInput = document.getElementById('sqFeetConverterInput');
        const sqMeterInput = document.getElementById('sqMeterConverterInput');
        const resultDisplay = document.getElementById('simpleConverterResult');

        let sqFeet = parseFloat(sqFeetInput.value);
        let sqMeters = parseFloat(sqMeterInput.value);

        if (isNaN(sqFeet) || sqFeet < 0) sqFeet = 0;
        if (isNaN(sqMeters) || sqMeters < 0) sqMeters = 0;

        if (sourceUnit === 'sqft') {
            // Convert sq feet to sq meters
            sqMeters = sqFeet * SQFT_TO_SQM_FACTOR;
            sqMeterInput.value = sqMeters.toFixed(4); // Display with 4 decimal places
        } else if (sourceUnit === 'sqm') {
            // Convert sq meters to sq feet
            sqFeet = sqMeters / SQFT_TO_SQM_FACTOR;
            sqFeetInput.value = sqFeet.toFixed(4); // Display with 4 decimal places
        }

        // Update the result display
        resultDisplay.textContent = `${sqFeet.toFixed(4)} sq ft = ${sqMeters.toFixed(4)} sq m`;
        if (sqFeet === 0 && sqMeters === 0) {
            resultDisplay.textContent = "Enter a value above to see conversion.";
        }
    }


  // Initialize with one measurement section on load for immediate use and initialize the simple converter
  document.addEventListener('DOMContentLoaded', () => {
    addMeasurement(); // Initialize the main area calculator
    convertSimpleUnits('sqft'); // Initialize the simple Sq.Ft. to Sq.M. converter
  });
</script>

</body>
</html>