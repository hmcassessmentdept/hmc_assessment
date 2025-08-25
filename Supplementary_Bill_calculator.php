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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplementary Calculator - Howrah Municipal Corporation - Assessment Department</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            color: #333;
        }
        header {
            background-color: #004f99;
            padding: 20px 0;
            text-align: center;
            color: white;
            font-size: 2.5em;
            font-weight: 700;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        header img {
            width: 150px;
            margin-bottom: 10px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2, h3 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 20px;
        }
        h3 {
            font-size: 1.4em;
        }
        label {
            font-weight: 500;
            margin-right: 10px;
            display: block;
            margin-bottom: 5px;
        }
        input[type="number"], input[type="date"], input[type="text"], select {
            padding: 8px;
            margin-bottom: 15px;
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group, .old-av-period, .new-av-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: flex-end;
        }
        .form-group > div, .new-av-group > div, .old-av-period > div {
            flex: 1 1 200px;
        }
        .form-group.old-av-group {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
        }
        .old-av-period {
            border: 1px solid #eee;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .old-av-period button {
            margin-top: 10px;
            background-color: #dc3545;
        }
        .old-av-period button:hover {
            background-color: #c82333;
        }
        .new-av-group {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background-color: #f9f9f9;
            flex-direction: row;
        }
        .new-av-group h3 {
            flex-basis: 100%;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }
        button:hover {
            background-color: #218838;
        }
        .print-button {
            background-color: #007bff;
            margin-top: 20px;
        }
        .print-button:hover {
            background-color: #0056b3;
        }
        .print-button, button {
            margin-right: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: #fff;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #004f99;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tfoot {
            font-weight: bold;
            background-color: #f1f1f1;
        }
        footer {
            text-align: center;
            padding: 20px;
            margin-top: 30px;
            background-color: #343a40;
            color: white;
            border-radius: 8px 8px 0 0;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="header-container" style="text-align: center;">
        <img src="logo1.png" alt="Howrah Municipal Corporation Logo" width="550">
    </div>
    <header>
        <span style="font-size: 1.2em; font-weight: 500;">Calculation of Supplementary Bill</span>
    </header>

    <div class="container">
        <h2>Enter Property Details</h2>

        <div class="form-group new-av-group">
            <div>
                <label for="New_AssesseeID">New Assessee ID:</label>
                <input type="number" id="New_AssesseeID" placeholder="Enter New Assessee ID" value="0" min="0">
            </div>
            <div>
                <label for="OLD_ULB_ID">Old ULB ID:</label>
                <input type="number" id="OLD_ULB_ID" placeholder="Enter Old ULB ID" value="0" min="0">
            </div>
            <div>
    <label for="Ward_No">Ward No.:</label>
    <select id="Ward_No" name="Ward_No" required>
        <option value="">Select Ward</option>
        <!-- Options will be populated by JS -->
    </select>
</div>
<div>
    <label for="Street_Name">Street Name:</label>
    <select id="Street_Name" name="Street_Name" required>
        <option value="">Select Street</option>
        <!-- Options will be populated by JS -->
    </select>
</div>
<div>
                <label for="Holding_No">Holding No.:</label>
                <input type="text" id="Holding_No" placeholder="Enter Holding No." required>
            </div>
            
            
            <h3 style="flex-basis: 100%; margin-top: 20px;">New Assessed Value Details</h3>
            
            <div>
                <label for="newAV">New AV (₹):</label>
                <input type="number" id="newAV" placeholder="Enter New Assessed Value" value="0" min="0" required>
            </div>
            <div>
                <label for="currentWardType">Ward Type:</label>
                <select id="currentWardType">
                    <option value="gr">GR</option>
                    <option value="nonGr">Non-GR</option>
                </select>
            </div>
            <div>
                <label for="currentPropertyType">Property Type:</label>
                <select id="currentPropertyType">
                    <option value="apartment">Apartment</option>
                    <option value="nonApartment">Non-Apartment</option>
                </select>
            </div>
            <div>
                <label for="newStart">Effective Start Date:</label>
                <input type="date" id="newStart" required>
            </div>
            <div>
                <label for="effectiveQuarter">Effective Quarter:</label>
                <input type="text" id="effectiveQuarter" readonly>
            </div>
            <div>
                <label for="newEnd">Effective End Date:</label>
                <input type="date" id="newEnd" required>
            </div>
            <div>
                <label for="endQuarter">End Quarter:</label>
                <input type="text" id="endQuarter" readonly>
            </div>
        </div>

        <div class="form-group" id="new-surcharge-group">
            <div>
                <label for="newAnnualSurcharge">New Annual Surcharge (₹):</label>
                <input type="number" id="newAnnualSurcharge" placeholder="Enter New Annual Surcharge" value="0" min="0">
            </div>
            <div>
                <label for="newSurchargeStart">New Surcharge Effective Start:</label>
                <input type="date" id="newSurchargeStart">
            </div>
            <div>
                <label for="effectiveSurchargeQuarter">Effective Surcharge Qtr:</label>
                <input type="text" id="effectiveSurchargeQuarter" readonly>
            </div>
            <div>
                <label for="newSurchargeEnd">New Surcharge Effective End:</label>
                <input type="date" id="newSurchargeEnd">
            </div>
            <div>
                <label for="endSurchargeQuarter">End Surcharge Qtr:</label>
                <input type="text" id="endSurchargeQuarter" readonly>
            </div>
        </div>

        <div class="form-group old-av-group">
            <h4>Old AV Periods:</h4>
            <div id="oldAVContainer"></div>
            <button type="button" onclick="addOldAVPeriod()">Add Old AV Period</button>
        </div>

        <button type="button" onclick="calculateAndSave()">Generate & Save Tax Table</button>
		<button type="button" onclick="window.location.href='generate_sup_bill.php'">Search Supplementary Bill</button>
        <button type="button" class="print-button" onclick="window.print()">Print Table</button>
        <button type="button" onclick="exportToExcel()">Export to Excel</button>
        <button type="button" onclick="exportToPDF()">Export to PDF</button>

        <div id="statusMessage" style="margin-top: 20px; font-weight: bold;"></div>

       <table id="resultTable">
            <thead>
                <tr>
                    <th>Assessee ID</th> <!-- ✅ Added Column -->
                    <th>FY</th>
                    <th>Qtr</th>
                    <th>Old AV</th>
                    <th>New AV</th>
                    <th>Old Prop Tax</th>
                    <th>HB Old</th>
                    <th>Old SC/Qtr</th>
                    <th>New Prop Tax</th>
                    <th>HB New</th>
                    <th>New SC/Qtr</th>
                    <th>Diff PT</th>
                    <th>Diff HB</th>
                    <th>Diff SC</th>
                    <th>Total Diff</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot id="totalRow"></tfoot>
        </table>

        <div style="margin-top: 30px; font-size: 0.9em; color: #777;">
            <p>
                <strong>Note:</strong> The calculated values in the table provide an estimate for the supplementary bill. Minor discrepancies might occur due to rounding during the official assessment process. For precise calculations and official billing, please refer to the Howrah Municipal Corporation - Assessment Department.
            </p>
            <p>
                For any queries or clarifications, please contact the Assessment Department at the Howrah Municipal Corporation.
            </p>
        </div>
        <footer class="footer">
            <p>&copy; This Supplementary Calculator has been developed for the <strong> Howrah Municipal Corporation </strong>Designed by <strong>DC</strong>. </p>
        </footer>
    </div>

    <script>
        const HBR_TAX_RATE = 0.0025;

        function getFinancialYear(date) {
            const year = date.getFullYear();
            const month = date.getMonth() + 1;
            if (month >= 4) {
                return `${year}-${year + 1}`;
            } else {
                return `${year - 1}-${year}`;
            }
        }

        function getQuarter(date) {
            const month = date.getMonth() + 1;
            if (month >= 4 && month <= 6) return 1;
            if (month >= 7 && month <= 9) return 2;
            if (month >= 10 && month <= 12) return 3;
            return 4;
        }
        
        function updateQuarterFields(dateInput, quarterField) {
            const dateString = dateInput.value;
            if (dateString) {
                const date = new Date(dateString);
                const fy = getFinancialYear(date);
                const qtr = getQuarter(date);
                quarterField.value = `${fy} Q${qtr}`;
            } else {
                quarterField.value = '';
            }
        }

        function addOldAVPeriod() {
            const container = document.getElementById('oldAVContainer');
            const div = document.createElement('div');
            div.classList.add('old-av-period');
            div.innerHTML = `
                <div style="flex-basis: 100%;"><hr style="border-top: 1px dashed #ccc; margin: 15px 0;"></div>
                <div>
                    <label for="oldAV">Old AV (₹):</label>
                    <input type="number" class="oldAV" placeholder="Enter Old Assessed Value" value="0" min="0">
                </div>
                <div>
                    <label for="oldPeriodStart">Period Start Date:</label>
                    <input type="date" class="oldStart">
                </div>
                <div>
                    <label for="effectiveQuarterOld">Effective Quarter:</label>
                    <input type="text" class="effectiveQuarterOld" readonly>
                </div>
                <div>
                    <label for="oldPeriodEnd">Period End Date:</label>
                    <input type="date" class="oldEnd">
                </div>
                <div>
                    <label for="endQuarterOld">End Quarter:</label>
                    <input type="text" class="endQuarterOld" readonly>
                </div>
                <div>
                    <label for="oldWardType">Ward Type (Old):</label>
                    <select class="oldWardType">
                        <option value="gr">GR</option>
                        <option value="nonGr">Non-GR</option>
                    </select>
                </div>
                <div>
                    <label for="oldPropertyType">Property Type (Old):</label>
                    <select class="oldPropertyType">
                        <option value="apartment">Apartment</option>
                        <option value="nonApartment">Non-Apartment</option>
                    </select>
                </div>
                <div style="flex-basis: 100%;">
                    <hr style="border-top: 1px dashed #ccc; margin: 15px 0;">
                </div>
                <div>
                    <label for="annualSurchargeOld">Annual Surcharge (Old) (₹):</label>
                    <input type="number" class="annualSurchargeOld" placeholder="Enter Old Annual Surcharge for this period" value="0" min="0">
                </div>
                <div>
                    <label for="surchargeStartOld">Surcharge Effective Start (Old):</label>
                    <input type="date" class="surchargeStartOld">
                </div>
                <div>
                    <label for="effectiveSurchargeQuarterOld">Effective Surcharge Qtr:</label>
                    <input type="text" class="effectiveSurchargeQuarterOld" readonly>
                </div>
                <div>
                    <label for="surchargeEndOld">Surcharge Effective End (Old):</label>
                    <input type="date" class="surchargeEndOld">
                </div>
                <div>
                    <label for="endSurchargeQuarterOld">End Surcharge Qtr:</label>
                    <input type="text" class="endSurchargeQuarterOld" readonly>
                </div>
                <div style="flex-basis: 100%; display: flex; justify-content: flex-end;">
                    <button type="button" onclick="removeOldAVPeriod(this)">Remove Period</button>
                </div>
            `;
            container.appendChild(div);
            
            const oldStart = div.querySelector('.oldStart');
            const effectiveQuarterOld = div.querySelector('.effectiveQuarterOld');
            oldStart.addEventListener('change', () => updateQuarterFields(oldStart, effectiveQuarterOld));

            const oldEnd = div.querySelector('.oldEnd');
            const endQuarterOld = div.querySelector('.endQuarterOld');
            oldEnd.addEventListener('change', () => updateQuarterFields(oldEnd, endQuarterOld));

            const surchargeStartOld = div.querySelector('.surchargeStartOld');
            const effectiveSurchargeQuarterOld = div.querySelector('.effectiveSurchargeQuarterOld');
            surchargeStartOld.addEventListener('change', () => updateQuarterFields(surchargeStartOld, effectiveSurchargeQuarterOld));

            const surchargeEndOld = div.querySelector('.surchargeEndOld');
            const endSurchargeQuarterOld = div.querySelector('.endSurchargeQuarterOld');
            surchargeEndOld.addEventListener('change', () => updateQuarterFields(surchargeEndOld, endSurchargeQuarterOld));
        }

        function removeOldAVPeriod(button) {
            const container = document.getElementById('oldAVContainer');
            container.removeChild(button.parentNode.parentNode);
        }

        function getLastDayOfQuarter(year, quarter) {
            if (quarter === 1) return new Date(year, 5, 30);
            if (quarter === 2) return new Date(year, 8, 30);
            if (quarter === 3) return new Date(year, 11, 31);
            return new Date(year, 2, 31);
        }

        async function calculateAndSave() {
            // New validation for main numeric fields
            const assesseeID = parseFloat(document.getElementById('New_AssesseeID').value);
            const oldUlbID = parseFloat(document.getElementById('OLD_ULB_ID').value);
            const wardNo = parseFloat(document.getElementById('Ward_No').value);
            const newAV = parseFloat(document.getElementById('newAV').value);
            
            if (isNaN(assesseeID) || assesseeID < 0) {
                alert('Please enter a valid, non-negative number for New Assessee ID.');
                return;
            }
            if (isNaN(oldUlbID) || oldUlbID < 0) {
                alert('Please enter a valid, non-negative number for Old ULB ID.');
                return;
            }
            if (isNaN(wardNo) || wardNo < 0) {
                alert('Please enter a valid, non-negative number for Ward No.');
                return;
            }

            const holdingNo = document.getElementById('Holding_No').value;
            const streetName = document.getElementById('Street_Name').value;
            const newStartInput = document.getElementById('newStart').value;
            const newEndInput = document.getElementById('newEnd').value;
            const newEffectiveQuarter = document.getElementById('effectiveQuarter').value;
            const newEndQuarter = document.getElementById('endQuarter').value;
            
            if (!holdingNo || !streetName || isNaN(newAV) || newAV < 0 || !newStartInput || !newEndInput) {
                alert('Please fill in all required fields for New AV Details: Holding No., Street Name, New AV, Effective Start Date, and Effective End Date.');
                return;
            }

            const newStart = new Date(newStartInput);
            const newEnd = new Date(newEndInput);

            if (newStart > newEnd) {
                alert('New AV Effective Start Date cannot be after the Effective End Date.');
                return;
            }

            const newSurchargeStartInput = document.getElementById('newSurchargeStart').value;
            const newSurchargeEndInput = document.getElementById('newSurchargeEnd').value;
            const newAnnualSurcharge = parseFloat(document.getElementById('newAnnualSurcharge').value);
            
            let newSurchargeStart = null, newSurchargeEnd = null;
            if (newSurchargeStartInput && newSurchargeEndInput) {
                newSurchargeStart = new Date(newSurchargeStartInput);
                newSurchargeEnd = new Date(newSurchargeEndInput);
                if (newSurchargeStart > newSurchargeEnd) {
                    alert('New Surcharge Effective Start Date cannot be after the Effective End Date.');
                    return;
                }
            } else if (newSurchargeStartInput || newSurchargeEndInput) {
                 alert('Please provide both New Surcharge Start and End dates or leave both blank.');
                 return;
            }
            if (!isNaN(newAnnualSurcharge) && newAnnualSurcharge < 0) {
                 alert('New Annual Surcharge must be a non-negative number.');
                 return;
            }

            const oldAVPeriods = [];
            const oldAVPeriodDivs = document.querySelectorAll('#oldAVContainer .old-av-period');

            for (let i = 0; i < oldAVPeriodDivs.length; i++) {
                const div = oldAVPeriodDivs[i];
                const avInput = div.querySelector('.oldAV');
                const startInput = div.querySelector('.oldStart');
                const endInput = div.querySelector('.oldEnd');
                const effectiveQuarterOld = div.querySelector('.effectiveQuarterOld').value;
                const endQuarterOld = div.querySelector('.endQuarterOld').value;
                const annualSurchargeOldInput = div.querySelector('.annualSurchargeOld');
                const surchargeStartOldInput = div.querySelector('.surchargeStartOld');
                const surchargeEndOldInput = div.querySelector('.surchargeEndOld');

                const av = parseFloat(avInput.value);
                const start = startInput.value ? new Date(startInput.value) : null;
                const end = endInput.value ? new Date(endInput.value) : null;
                const annualSurchargeOld = parseFloat(annualSurchargeOldInput.value);

                if (isNaN(av) || av < 0 || !start || !end) {
                    alert(`Please ensure all required fields for Old AV Period #${i + 1} are filled correctly (AV, Period Start Date, Period End Date).`);
                    return;
                }
                if (start > end) {
                    alert(`Old AV Period #${i + 1} Start Date cannot be after its End Date.`);
                    return;
                }

                let surchargeStartOld = null, surchargeEndOld = null;
                if (surchargeStartOldInput.value && surchargeEndOldInput.value) {
                    surchargeStartOld = new Date(surchargeStartOldInput.value);
                    surchargeEndOld = new Date(surchargeEndOldInput.value);
                    if (surchargeStartOld > surchargeEndOld) {
                        alert(`Old AV Period #${i + 1} Surcharge Start Date cannot be after its Surcharge End Date.`);
                        return;
                    }
                } else if (surchargeStartOldInput.value || surchargeEndOldInput.value) {
                     alert(`Please provide both Surcharge Start and End dates for Old AV Period #${i + 1} or leave both blank.`);
                     return;
                }
                 if (!isNaN(annualSurchargeOld) && annualSurchargeOld < 0) {
                     alert(`Old Annual Surcharge for Period #${i + 1} must be a non-negative number.`);
                     return;
                 }


                oldAVPeriods.push({
                    av: av,
                    start: startInput.value,
                    end: endInput.value,
                    effectiveQuarterOld: effectiveQuarterOld,
                    endQuarterOld: endQuarterOld,
                    wardType: div.querySelector('.oldWardType').value,
                    propertyType: div.querySelector('.oldPropertyType').value,
                    annualSurchargeOld: annualSurchargeOld,
                    surchargeStartOld: surchargeStartOldInput.value,
                    surchargeEndOld: surchargeEndOldInput.value
                });
            }

            // Calculations for the table
            const tbody = document.querySelector('#resultTable tbody');
            tbody.innerHTML = '';
            const tfoot = document.getElementById('totalRow');
            tfoot.innerHTML = '';

            const currentWardType = document.getElementById('currentWardType').value;
            const currentPropertyType = document.getElementById('currentPropertyType').value;

            function calculatePropertyTax(av, ward, type) {
                let annualTax = 0;
                if (type === "apartment") {
                    annualTax = ward === "gr" ? Math.ceil(av * 0.3) : Math.ceil(av * 0.4);
                } else {
                    if (ward === "gr") {
                        if (av <= 999) annualTax = Math.ceil(av * ((10 + av / 100) / 100));
                        else if (av <= 9999) annualTax = Math.ceil(av * ((20 + av / 1000) / 100));
                        else annualTax = Math.ceil(av * 0.3);
                    } else {
                        if (av <= 999) annualTax = Math.ceil(av * ((10 + av / 100) / 100));
                        else if (av <= 17999) annualTax = Math.ceil(av * ((22 + av / 1000) / 100));
                        else annualTax = Math.ceil(av * 0.4);
                    }
                }
                return annualTax / 4;
            }

            function calculateQuarterlySurcharge(annualSurcharge) {
                return annualSurcharge / 4;
            }
            
            const quarterlyCalculations = [];

            let currentDate = new Date(newStart);
            currentDate.setDate(1);

            const initialQuarter = getQuarter(newStart);
            if (initialQuarter === 1) currentDate.setMonth(3);
            else if (initialQuarter === 2) currentDate.setMonth(6);
            else if (initialQuarter === 3) currentDate.setMonth(9);
            else if (initialQuarter === 4) currentDate.setMonth(0);

            let totalOldPropTax = 0;
            let totalHBOld = 0;
            let totalOldSC = 0;
            let totalNewPropTax = 0;
            let totalHBNew = 0;
            let totalNewSC = 0;
            let totalDiffPT = 0;
            let totalDiffHB = 0;
            let totalDiffSC = 0;
            let totalDiffTotal = 0;

            while (currentDate <= newEnd) {
                const fy = getFinancialYear(currentDate);
                const qtr = getQuarter(currentDate);
                
                let firstDayOfQuarter;
                let lastDayOfQuarter;

                const currentYearForQuarter = currentDate.getFullYear();
                if (qtr === 1) {
                    firstDayOfQuarter = new Date(currentYearForQuarter, 3, 1);
                    lastDayOfQuarter = new Date(currentYearForQuarter, 5, 30);
                } else if (qtr === 2) {
                    firstDayOfQuarter = new Date(currentYearForQuarter, 6, 1);
                    lastDayOfQuarter = new Date(currentYearForQuarter, 8, 30);
                } else if (qtr === 3) {
                    firstDayOfQuarter = new Date(currentYearForQuarter, 9, 1);
                    lastDayOfQuarter = new Date(currentYearForQuarter, 11, 31);
                } else {
                    firstDayOfQuarter = new Date(currentYearForQuarter, 0, 1);
                    lastDayOfQuarter = new Date(currentYearForQuarter, 2, 31);
                }

                if (firstDayOfQuarter > newEnd || lastDayOfQuarter < newStart) {
                    currentDate.setMonth(currentDate.getMonth() + 3);
                    continue;
                }

                let applicableOldAV = 0;
                let applicableOldWardType = currentWardType;
                let applicableOldPropertyType = currentPropertyType;
                let applicableOldAnnualSurcharge = 0;
                let applicableOldSurchargeStart = null;
                let applicableOldSurchargeEnd = null;

                for (const period of oldAVPeriods) {
                    const periodStart = new Date(period.start);
                    const periodEnd = new Date(period.end);

                    if (firstDayOfQuarter <= periodEnd && lastDayOfQuarter >= periodStart) {
                        applicableOldAV = period.av;
                        applicableOldWardType = period.wardType;
                        applicableOldPropertyType = period.propertyType;
                        applicableOldAnnualSurcharge = period.annualSurchargeOld;
                        applicableOldSurchargeStart = period.surchargeStartOld ? new Date(period.surchargeStartOld) : null;
                        applicableOldSurchargeEnd = period.surchargeEndOld ? new Date(period.surchargeEndOld) : null;
                        break;
                    }
                }

                const oldPropTax = Math.ceil(calculatePropertyTax(applicableOldAV, applicableOldWardType, applicableOldPropertyType));
                const newPropTax = Math.ceil(calculatePropertyTax(newAV, currentWardType, currentPropertyType));

                const annualHBOld = applicableOldAV * HBR_TAX_RATE;
                const hbOld = Math.max(1, Math.ceil(annualHBOld / 4));

                const annualHBNew = newAV * HBR_TAX_RATE;
                const hbNew = Math.max(1, Math.ceil(annualHBNew / 4));

                let currentOldSCQuarter = 0;
                if (!isNaN(applicableOldAnnualSurcharge) && applicableOldAnnualSurcharge > 0 && applicableOldSurchargeStart && applicableOldSurchargeEnd) {
                    if (firstDayOfQuarter <= applicableOldSurchargeEnd && lastDayOfQuarter >= applicableOldSurchargeStart) {
                        currentOldSCQuarter = Math.ceil(calculateQuarterlySurcharge(applicableOldAnnualSurcharge));
                    }
                }
                
                let currentNewSCQuarter = 0;
                if (!isNaN(newAnnualSurcharge) && newAnnualSurcharge > 0 && newSurchargeStart && newSurchargeEnd) {
                    if (firstDayOfQuarter <= newSurchargeEnd && lastDayOfQuarter >= newSurchargeStart) {
                        currentNewSCQuarter = Math.ceil(calculateQuarterlySurcharge(newAnnualSurcharge));
                    }
                }

                const diffPT = newPropTax - oldPropTax;
                const diffHB = hbNew - hbOld;
                const diffSC = currentNewSCQuarter - currentOldSCQuarter;
                const totalDiff = diffPT + diffHB + diffSC;

                const row = tbody.insertRow();
				row.insertCell().textContent = assesseeID; // <-- Add this line for Assessee ID
                row.insertCell().textContent = fy;
                row.insertCell().textContent = `Q${qtr}`;
                row.insertCell().textContent = Math.ceil(applicableOldAV).toFixed(0);
                row.insertCell().textContent = Math.ceil(newAV).toFixed(0);
                row.insertCell().textContent = oldPropTax.toFixed(0);
                row.insertCell().textContent = hbOld.toFixed(0);
                row.insertCell().textContent = currentOldSCQuarter.toFixed(0);
                row.insertCell().textContent = newPropTax.toFixed(0);
                row.insertCell().textContent = hbNew.toFixed(0);
                row.insertCell().textContent = currentNewSCQuarter.toFixed(0);
                row.insertCell().textContent = diffPT.toFixed(0);
                row.insertCell().textContent = diffHB.toFixed(0);
                row.insertCell().textContent = diffSC.toFixed(0);
                row.insertCell().textContent = Math.ceil(totalDiff).toFixed(0);
                
                totalOldPropTax += oldPropTax;
                totalHBOld += hbOld;
                totalOldSC += currentOldSCQuarter;
                totalNewPropTax += newPropTax;
                totalHBNew += hbNew;
                totalNewSC += currentNewSCQuarter;
                totalDiffPT += diffPT;
                totalDiffHB += diffHB;
                totalDiffSC += diffSC;
                totalDiffTotal += totalDiff;

                quarterlyCalculations.push({
                    assessee_id: assesseeID,  // <-- Add this line
					fy: fy,
                    qtr: qtr,
                    old_av: applicableOldAV,
                    new_av: newAV,
                    old_prop_tax: oldPropTax,
                    hb_old: hbOld,
                    old_sc_qtr: currentOldSCQuarter,
                    new_prop_tax: newPropTax,
                    hb_new: hbNew,
                    new_sc_qtr: currentNewSCQuarter,
                    diff_pt: diffPT,
                    diff_hb: diffHB,
                    diff_sc: diffSC,
                    total_diff: totalDiff
                });
                
                currentDate.setMonth(currentDate.getMonth() + 3);
            }

            const footerRow = tfoot.insertRow();
            footerRow.insertCell().textContent = '';
            footerRow.insertCell();
            footerRow.insertCell();
            footerRow.insertCell();
			 footerRow.insertCell().textContent = 'Total:';
            footerRow.insertCell().textContent = totalOldPropTax.toFixed(0);
            footerRow.insertCell().textContent = totalHBOld.toFixed(0);
            footerRow.insertCell().textContent = totalOldSC.toFixed(0);
            footerRow.insertCell().textContent = totalNewPropTax.toFixed(0);
            footerRow.insertCell().textContent = totalHBNew.toFixed(0);
            footerRow.insertCell().textContent = totalNewSC.toFixed(0);
            footerRow.insertCell().textContent = totalDiffPT.toFixed(0);
            footerRow.insertCell().textContent = totalDiffHB.toFixed(0);
            footerRow.insertCell().textContent = totalDiffSC.toFixed(0);
            footerRow.insertCell().textContent = Math.ceil(totalDiffTotal).toFixed(0);

            // Package all data into a single object for sending
            const dataToSave = {
                assessment_details: {
                    new_assessee_id: assesseeID,
                    old_ulb_id: oldUlbID,
                    ward_no: wardNo,
                    holding_no: holdingNo,
                    street_name: streetName,
                    new_av: newAV,
                    current_ward_type: currentWardType,
                    current_property_type: currentPropertyType,
                    new_start: newStartInput,
                    new_end: newEndInput,
                    new_effective_quarter: newEffectiveQuarter,
                    new_end_quarter: newEndQuarter,
                    new_surcharge_annual: newAnnualSurcharge,
                    new_surcharge_start: newSurchargeStartInput,
                    new_surcharge_end: newSurchargeEndInput
                },
                old_av_periods: oldAVPeriods,
                quarterly_calculations: quarterlyCalculations,
                totals: {
                    total_old_prop_tax: totalOldPropTax,
                    total_hb_old: totalHBOld,
                    total_old_sc: totalOldSC,
                    total_new_prop_tax: totalNewPropTax,
                    total_hb_new: totalHBNew,
                    total_new_sc: totalNewSC,
                    total_diff_pt: totalDiffPT,
                    total_diff_hb: totalDiffHB,
                    total_diff_sc: totalDiffSC,
                    total_diff_total: totalDiffTotal
                }
            };

            // Send data to PHP script
            const statusMessage = document.getElementById('statusMessage');
            statusMessage.textContent = 'Saving data...';
            statusMessage.style.color = '#333';

            try {
                const response = await fetch('save_data.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dataToSave)
                });

                const result = await response.json();
                if (result.success) {
                    statusMessage.textContent = 'Data saved successfully!';
                    statusMessage.style.color = 'green';
                } else {
                    statusMessage.textContent = `Error: ${result.message}`;
                    statusMessage.style.color = 'red';
                }
            } catch (error) {
                statusMessage.textContent = `Network error: ${error.message}`;
                statusMessage.style.color = 'red';
            }
        }

        function exportToExcel() {
            alert('Excel export functionality is under development.');
        }

        function exportToPDF() {
            alert('PDF export functionality is under development.');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const newAVContainer = document.querySelector('.new-av-group');
            const newSurchargeContainer = document.getElementById('new-surcharge-group');

            const newStart = newAVContainer.querySelector('#newStart');
            const newEnd = newAVContainer.querySelector('#newEnd');
            const effectiveQuarter = newAVContainer.querySelector('#effectiveQuarter');
            const endQuarter = newAVContainer.querySelector('#endQuarter');
            
            if (newStart && effectiveQuarter) newStart.addEventListener('change', () => updateQuarterFields(newStart, effectiveQuarter));
            if (newEnd && endQuarter) newEnd.addEventListener('change', () => updateQuarterFields(newEnd, endQuarter));

            const newSurchargeStart = newSurchargeContainer.querySelector('#newSurchargeStart');
            const newSurchargeEnd = newSurchargeContainer.querySelector('#newSurchargeEnd');
            const effectiveSurchargeQuarter = newSurchargeContainer.querySelector('#effectiveSurchargeQuarter');
            const endSurchargeQuarter = newSurchargeContainer.querySelector('#endSurchargeQuarter');

            if (newSurchargeStart && effectiveSurchargeQuarter) newSurchargeStart.addEventListener('change', () => updateQuarterFields(newSurchargeStart, effectiveSurchargeQuarter));
            if (newSurchargeEnd && endSurchargeQuarter) newSurchargeEnd.addEventListener('change', () => updateQuarterFields(newSurchargeEnd, endSurchargeQuarter));
            
            addOldAVPeriod();
        });
		
document.addEventListener('DOMContentLoaded', function() {
    const wardSelect = document.getElementById('Ward_No');
    const streetSelect = document.getElementById('Street_Name');

    // Fetch wards from server and populate Ward dropdown
    fetch('get_wards.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(ward => {
                const option = document.createElement('option');
                option.value = ward;
                option.textContent = ward;
                wardSelect.appendChild(option);
            });
        })
        .catch(err => console.error('Error loading wards:', err));

    // On Ward change, fetch streets for that ward
    wardSelect.addEventListener('change', function() {
        const wardNo = this.value;
        streetSelect.innerHTML = '<option value="">Select Street</option>'; // Reset streets

        if (wardNo) {
            fetch('get_streets.php?ward_no=' + wardNo)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No streets found';
                        streetSelect.appendChild(option);
                    } else {
                        data.forEach(street => {
                            const option = document.createElement('option');
                            option.value = street;
                            option.textContent = street;
                            streetSelect.appendChild(option);
                        });
                    }
                })
                .catch(err => {
                    console.error('Error loading streets:', err);
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Error loading streets';
                    streetSelect.appendChild(option);
                });
        }
    });
});


    </script>
</body>
</html>