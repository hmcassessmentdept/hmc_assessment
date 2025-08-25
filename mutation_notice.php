<?php
include 'db_connect.php';

// Generate current financial year string
function currentFY() {
    $m = date('n');
    $y = date('Y');
    return ($m >= 4) ? "$y-" . ($y + 1) : ($y - 1) . "-$y";
}

// Generate next memo number (resets each financial year)
function nextMemo($conn, $fy) {
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM mutation_records WHERE fy = ?");
    $stmt->bind_param('s', $fy);
    $stmt->execute();
    $stmt->bind_result($cnt);
    $stmt->fetch();
    $stmt->close();
    return str_pad($cnt + 1, 4, '0', STR_PAD_LEFT) . "/$fy";
}

// Handle save-record AJAX call
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_GET['action']) && $_GET['action']==='save') {
    $in = json_decode(file_get_contents('php://input'), true);
    $fy = currentFY();
    $memo = nextMemo($conn, $fy);

    $stmt = $conn->prepare("INSERT INTO mutation_records
        (reference_no, application_date, application_no, applicant_name, 
         proposed_holding_no, mother_assessee_no, street_name, ward_no, mother_holding_no, hearing_date, hearing_time, fy, memo_number)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssss",
        $in['refNo'], $in['appDate'], $in['appNo'], $in['applicantName'],
        $in['proposedHoldingNo'], $in['motherAssesseeNo'], $in['streetName'],
        $in['wardNo'], $in['motherHoldingNo'], $in['hearingDate'],
        $in['hearingTime'], $fy, $memo);
    $success = $stmt->execute();
    $stmt->close();
    echo json_encode(['success'=>$success, 'memo'=>$memo, 'fy'=>$fy]);
    exit;
}

// Handle get-records AJAX call
if ($_SERVER['REQUEST_METHOD']==='GET' && isset($_GET['action']) && $_GET['action']==='records') {
    $fy = $_GET['fy'] ?? '';
    $ward = $_GET['ward'] ?? '';
    $query = "SELECT memo_number, fy, applicant_name, ward_no, hearing_date, application_no, reference_no
              FROM mutation_records WHERE 1=1";
    $types = '';
    $params = [];
    if ($fy) {
        $query .= " AND fy=?";
        $types .= 's';
        $params[] = $fy;
    }
    if ($ward) {
        $query .= " AND ward_no=?";
        $types .= 's';
        $params[] = $ward;
    }
    $query .= " ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    $stmt->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Mutation Hearing Notice Generator</title>
  <!-- Include your CSS here exactly as provided -->
  <style>/* Your full CSS from the original HTML page */</style>
</head>
<body>
<div class="main-wrapper">
  <div class="container" id="inputFormSection">
    <!-- Your existing HTML form structure -->
    <!-- ... -->
    <div class="form-actions">
      <button type="button" onclick="submitDataAndGenerateNotice()">Generate Notice and Save</button>
    </div>
  </div>

  <div id="printableOutput">
    <!-- Printable notice markup unchanged -->
    <!-- ... -->
  </div>

  <div class="container" id="recordViewer" style="margin-top:40px;">
    <h3>Saved Records (Filter by FY / Ward)</h3>
    <div class="input-fields-grid">
      <div class="input-group">
        <label for="filterFY">Financial Year:</label>
        <select id="filterFY" onchange="loadRecords()"><option value="">All</option></select>
      </div>
      <div class="input-group">
        <label for="filterWard">Ward No:</label>
        <input type="text" id="filterWard" placeholder="e.g. 12" oninput="loadRecords()">
      </div>
    </div>
    <div style="overflow-x:auto;">
      <table border="1" style="width:100%; border-collapse:collapse; margin-top:10px;">
        <thead>
          <tr><th>Memo No</th><th>FY</th><th>Applicant</th><th>Ward</th><th>Hearing</th><th>App No</th><th>Ref No</th></tr>
        </thead>
        <tbody id="recordsTableBody"><tr><td colspan="7">Loading...</td></tr></tbody>
      </table>
    </div>
  </div>
</div>
<script>
// Populate FY filter dropdown
(function(){
  const sel = document.getElementById('filterFY');
  const year = new Date().getFullYear();
  for(let y=year+1; y>=2019; y--){
    const fy = `${y-1}-${String(y).slice(-2)}`;
    sel.innerHTML += `<option value="${fy}">${fy}</option>`;
  }
})();

// Save and generate notice
async function submitDataAndGenerateNotice() {
  // Gather values as before
  const data = { refNo: ..., appDate: ..., /* etc */ };
  const resp = await fetch('?action=save', {
    method: 'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify(data)
  });
  const j = await resp.json();
  if (j.success) {
    alert(`Saved! Memo No: ${j.memo} (FY ${j.fy})`);
    loadRecords();
    // Then display printable notice using existing JS logic
  } else alert('Save failed.');
}

// Load records with filters
async function loadRecords(){
  const fy = document.getElementById('filterFY').value;
  const ward = document.getElementById('filterWard').value;
  const resp = await fetch(`?action=records&fy=${fy}&ward=${ward}`);
  const data = await resp.json();
  const tbody = document.getElementById('recordsTableBody');
  tbody.innerHTML = data.length  
    ? data.map(r=>`<tr><td>${r.memo_number}</td><td>${r.fy}</td><td>${r.applicant_name}</td><td>${r.ward_no}</td><td>${new Date(r.hearing_date).toLocaleDateString()}</td><td>${r.application_no}</td><td>${r.reference_no}</td></tr>`).join('')
    : '<tr><td colspan="7">No records</td></tr>';
}
loadRecords();
</script>
</body>
</html>
