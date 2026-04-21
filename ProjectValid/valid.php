<?php
/* 
🎓 
Components: Project Management, Feedback, Reporting
This file contains logic for all scenarios shared by batch mates.
*/

// ============================================================
// SCENARIO 1: Number Validation + Conditional Badge 
// (Task: If value > 100,000 show 'High Value')
// ============================================================
/*
1. DB: ALTER TABLE projects ADD COLUMN budget DECIMAL(15,2) DEFAULT 0;
2. Controller: $budget = (float)$_POST['budget'];
3. VIEW (Card/Table Display):
*/
?>
<div class="project-budget">
    Budget: LKR <?= number_format($project->budget) ?>
    <?php if($project->budget > 100000): ?>
        <span class="badge" style="background:gold; color:black;">💎 High Value Project</span>
    <?php endif; ?>
</div>

<?php
// ============================================================
// SCENARIO 2: Phone Number (10 digits, starts with 07)
// ============================================================
/* 
Controller Logic:
*/
$phone = trim($_POST['phone']);
if (!preg_match('/^07[0-9]{8}$/', $phone)) {
    $errors['phone'] = "Phone must be 10 digits and start with 07";
}

// ------------------------------------------------------------
// SCENARIO 2.1: Country Code (Dropdown) + 9 Digits
// ------------------------------------------------------------
/*
Controller: 
$full_phone = $_POST['country_code'] . $_POST['phone_number'];
Validation: 
if(!preg_match('/^[0-9]{9}$/', $_POST['phone_number'])) { ... }
*/
?>

<!-- HTML View -->
<select name="country_code">
    <option value="+94">+94 (SL)</option>
    <option value="+1">+1 (USA)</option>
</select>
<input type="text" name="phone_number" placeholder="771234567">


<?php
// ============================================================
// SCENARIO 3: Date Validation (No Future / No Past)
// ============================================================
/*
Controller Logic:
*/
$reg_date = $_POST['reg_date'];
$today = date('Y-m-d');

// A. Cannot be a FUTURE date (e.g. Registration Date)
if ($reg_date > $today) {
    $errors['reg_date'] = "Registration date cannot be in the future";
}

// B. Cannot be a PAST date (e.g. Project Start Date)
if ($reg_date < $today) {
    $errors['start_date'] = "Start date cannot be in the past";
}


// ============================================================
// SCENARIO 4: File Upload Validation (PDF / Image Only)
// ============================================================
/*
Controller Logic:
*/
if (!empty($_FILES['document']['name'])) {
    $fileName = $_FILES['document']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['pdf', 'jpg', 'png']; // Choose based on task

    if (!in_array($fileExt, $allowed)) {
        $errors['document'] = "Only PDF and Images are allowed!";
    }
}


// ============================================================
// SCENARIO 5: Format Validation (NIC / Chassis Number)
// ============================================================
/*
A. NIC Validation:
Old: 9 digits + V/X  |  New: 12 digits
*/
$nic = trim($_POST['nic']);
if (!preg_match('/^([0-9]{9}[vVxX]|[0-9]{12})$/', $nic)) {
    $errors['nic'] = "Invalid NIC format";
}

/*
B. Vehicle Chassis (1st/last text, middle digits):
Example: A1234567B
*/
$chassis = trim($_POST['chassis']);
if (!preg_match('/^[A-Za-z][0-9]+[A-Za-z]$/', $chassis)) {
    $errors['chassis'] = "Format: Letter + Digits + Letter";
}


// ============================================================
// SCENARIO 6: Search/Filter by District (Partial Matching)
// ============================================================
/*
Model Query:
*/
/*
public function searchByDistrict($district) {
    $this->db->query("SELECT * FROM farmers WHERE district LIKE :dist");
    $this->db->bind(':dist', '%' . $district . '%'); // Partial matching logic
    return $this->db->resultSet();
}
*/


// ============================================================
// SCENARIO 7: Highest Value Logic (Aggregates)
// ============================================================
/*
Model Query:
*/
/*
public function getHighestBudget() {
    $this->db->query("SELECT MAX(budget) as max_val FROM projects");
    return $this->db->single()->max_val;
}
*/

// ============================================================
// SCENARIO 8: Conditional Input based on Dropdown
// (Example: If status is 'offered', show salary input)
// ============================================================
?>
<select name="status" onchange="toggleSalary(this.value)">
    <option value="pending">Pending</option>
    <option value="offered">Offered</option>
</select>

<div id="salary_field" style="display:none;">
    <label>Offer Amount:</label>
    <input type="number" name="salary">
</div>

<script>
function toggleSalary(val) {
    document.getElementById('salary_field').style.display = (val === 'offered') ? 'block' : 'none';
}
</script>


<?php
// ============================================================
// 🎯 FINAL TIP FOR CODE CHECK:
// 1. start_date/end_date difference in months:
//    $diff = abs(strtotime($end) - strtotime($start));
//    $months = floor($diff / (30*60*60*24));

// 2. Group By Category Count:
//    "SELECT category, COUNT(*) as count FROM projects GROUP BY category"
?>
