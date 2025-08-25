<?php
require "db_connect.php";

// Fetch distinct wards
$wardResult = $conn->query("SELECT DISTINCT ward_no FROM street_list ORDER BY ward_no ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Proportionate Arrear Calculator</title>
<style>
    body { font-family: Arial, sans-serif; background-color: #f4f8fa; margin: 0; padding: 20px; }
    h2 { text-align: center; color: #007bff; }
    section { background: #fff; padding: 20px; border-radius: 8px; max-width: 800px; margin: 20px auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    label { display: block; margin-bottom: 6px; font-weight: bold; }
    input, select { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
    button { background: #28a745; color: white; border: none; padding: 10px 18px; border-radius: 5px; cursor: pointer; }
    button:hover { background: #1e7e34; }
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

</style>
</head>
<body>
<div class="header-container" style="text-align: center;">
        <img src="logo1.png" alt="Howrah Municipal Corporation Logo" width="550">
    </div>
    <header>

<h2>Proportionate Arrear Property Tax Calculator</h2>

<form id="arrearForm" method="post" action="save_arrear.php">
    <label>Application Number:</label>
    <input type="text" id="applicationNumber" name="applicationNumber" required>

    <label>Applicant Name:</label>
    <input type="text" id="applicantName" name="applicantName" required>

    <label>Proposed Holding Number:</label>
    <input type="text" id="proposedHoldingNumber" name="proposedHoldingNumber" required>

    <label>Assessee Number:</label>
    <input type="number" id="assesseeNumber" name="assesseeNumber" required>

    <label>Mother Holding Number:</label>
    <input type="text" id="motherHoldingNumber" name="motherHoldingNumber" required>

    <label>Ward Number:</label>
    <select id="wardNumber" name="wardNumber" required>
        <option value="">-- Select Ward --</option>
        <?php
        for ($i = 1; $i <= 66; $i++) {
            echo "<option value='$i'>$i</option>";
        }
        ?>
    </select>

    <label>Street Name:</label>
    <select id="streetName" name="streetName" required>
        <option value="">-- Select Street --</option>
    </select>

    <label>Outstanding Due up to Qtr FY:</label>
    <select id="dueUpToQtr" name="dueUpToQtr" required>
        <option value="">-- Select Quarter --</option>
    </select>

    <label>Arrear as on Date:</label>
    <input type="date" id="arrearDate" name="arrearDate" required>

    <label>Mother Annual Valuation:</label>
    <input type="number" step="0.01" id="motherAnnualValuation" name="motherAnnualValuation" required>

    <label>Proposed Annual Valuation:</label>
    <input type="number" step="0.01" id="proposedAnnualValuation" name="proposedAnnualValuation" required>

    <label>Outstanding Due without rebate (₹):</label>
    <input type="number" step="0.01" id="totalOutstandingDue" name="totalOutstandingDue" required>

    <input type="hidden" id="calculatedDue" name="calculatedDue">
    <input type="hidden" id="calculatedDueWords" name="calculatedDueWords">

    <button type="button" onclick="calculateProportionalValue()">Generate & Save</button>
	 <button type="button" onclick="window.location.href='search_arrear.php'">Search</button>

</form>

<div id="demandLetter">
    <h3>Demand Letter</h3>
    <p><strong>Applicant:</strong> <span id="demand-name"></span></p>
    <p><strong>Proposed Holding:</strong> <span id="demand-prop-holding"></span></p>
    <p><strong>Arrear Date:</strong> <span id="arrear-date-demand"></span></p>
    <p><strong>Due up to Quarter:</strong> <span id="due-qtr-demand"></span></p>
    <p><strong>Assessee No:</strong> <span id="assessee-no-demand"></span></p>
    <p><strong>Mother Holding No:</strong> <span id="mother-holding-demand"></span></p>
    <p><strong>Street:</strong> <span id="street-demand"></span></p>
    <p><strong>Ward:</strong> <span id="ward-demand"></span></p>
    <p><strong>Calculated Due:</strong> ₹<span id="out-calc2"></span> (<span id="out-calc-words"></span>)</p>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    populateQuarterDropdown();

    document.getElementById("wardNumber").addEventListener("change", function () {
        let ward = this.value;
        let streetDropdown = document.getElementById("streetName");
        streetDropdown.innerHTML = "<option value=''>Loading...</option>";

        if (ward) {
            fetch(`get_streets.php?ward_no=${ward}`)
                .then(res => res.json())
                .then(data => {
                    streetDropdown.innerHTML = "<option value=''>-- Select Street --</option>";
                    data.forEach(street => {
                        let opt = document.createElement("option");
                        opt.value = street;
                        opt.textContent = street;
                        streetDropdown.appendChild(opt);
                    });
                })
                .catch(err => {
                    console.error(err);
                    streetDropdown.innerHTML = "<option value=''>Error loading streets</option>";
                });
        } else {
            streetDropdown.innerHTML = "<option value=''>-- Select Street --</option>";
        }
    });
});

function populateQuarterDropdown() {
    const startYear = 2007;
    const endYear = 2050;
    const qtrSelect = document.getElementById('dueUpToQtr');
    qtrSelect.innerHTML = '<option value="">-- Select Quarter --</option>';

    for (let fyStart = startYear; fyStart <= endYear; fyStart++) {
        let fyEnd = fyStart + 1;
        let fyLabel = `${fyStart}-${fyEnd}`;
        for (let qtr = 1; qtr <= 4; qtr++) {
            let qtrLabel;
            if (qtr === 1) qtrLabel = `1st QTR ${fyLabel}`;
            if (qtr === 2) qtrLabel = `2nd QTR ${fyLabel}`;
            if (qtr === 3) qtrLabel = `3rd QTR ${fyLabel}`;
            if (qtr === 4) qtrLabel = `4th QTR ${fyLabel}`;
            let opt = document.createElement('option');
            opt.value = qtrLabel;
            opt.textContent = qtrLabel;
            qtrSelect.appendChild(opt);
        }
    }
}

function calculateProportionalValue() {
    const name = document.getElementById("applicantName").value.trim();
    const proposedHoldingNumber = document.getElementById("proposedHoldingNumber").value.trim();
    const assesseeNumber = document.getElementById("assesseeNumber").value.trim();
    const motherHoldingNumber = document.getElementById("motherHoldingNumber").value.trim();
    const streetName = document.getElementById("streetName").value;
    const wardNumber = document.getElementById("wardNumber").value;
    const arrearDate = document.getElementById("arrearDate").value;
    const motherAV = parseFloat(document.getElementById("motherAnnualValuation").value);
    const proposedAV = parseFloat(document.getElementById("proposedAnnualValuation").value);
    const totalOutstandingDue = parseFloat(document.getElementById("totalOutstandingDue").value);
    const dueUpToQtr = document.getElementById("dueUpToQtr").value.trim();

    if (!name || !proposedHoldingNumber || !assesseeNumber || !motherHoldingNumber || !streetName || !wardNumber || !arrearDate || !dueUpToQtr) {
        alert("Please fill in all fields.");
        return;
    }
    if (isNaN(motherAV) || motherAV <= 0 || isNaN(proposedAV) || proposedAV <= 0 || isNaN(totalOutstandingDue) || totalOutstandingDue < 0) {
        alert("Please enter valid numerical values.");
        return;
    }

    const proportionFactor = proposedAV / motherAV;
    const proportionateDue = Math.ceil(totalOutstandingDue * proportionFactor);

    document.getElementById("calculatedDue").value = proportionateDue;
    document.getElementById("calculatedDueWords").value = convertNumberToWords(proportionateDue);

    let formattedDate = arrearDate.split('-').reverse().join('-');
    document.getElementById("demand-name").textContent = name;
    document.getElementById("demand-prop-holding").textContent = proposedHoldingNumber;
    document.getElementById("arrear-date-demand").textContent = formattedDate;
    document.getElementById("due-qtr-demand").textContent = dueUpToQtr;
    document.getElementById("assessee-no-demand").textContent = assesseeNumber;
    document.getElementById("mother-holding-demand").textContent = motherHoldingNumber;
    document.getElementById("street-demand").textContent = streetName;
    document.getElementById("ward-demand").textContent = wardNumber;
    document.getElementById("out-calc2").textContent = proportionateDue.toFixed(0);
    document.getElementById("out-calc-words").textContent = convertNumberToWords(proportionateDue);

    document.getElementById("demandLetter").style.display = "block";

    document.getElementById("arrearForm").submit();
}

function convertNumberToWords(num) {
    const a = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
               'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
               'seventeen', 'eighteen', 'nineteen'];
    const b = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

    if ((num = num.toString()).length > 9) return 'overflow';
    let n = ('000000000' + num).substr(-9).match(/^(\d{2})(\d{2})(\d{2})(\d{1})(\d{2})$/);
    if (!n) return;
    let str = '';
    str += (n[1] != 0) ? (a[Number(n[1])] || b[n[1][0]] + ' ' + a[n[1][1]]) + ' crore ' : '';
    str += (n[2] != 0) ? (a[Number(n[2])] || b[n[2][0]] + ' ' + a[n[2][1]]) + ' lakh ' : '';
    str += (n[3] != 0) ? (a[Number(n[3])] || b[n[3][0]] + ' ' + a[n[3][1]]) + ' thousand ' : '';
    str += (n[4] != 0) ? (a[Number(n[4])] || b[n[4][0]] + ' ' + a[n[4][1]]) + ' hundred ' : '';
    str += (n[5] != 0) ? ((str != '') ? 'and ' : '') +
        (a[Number(n[5])] || b[n[5][0]] + ' ' + a[n[5][1]]) + ' ' : '';
    return str.trim() + ' only';
}
</script>

</body>
</html>
