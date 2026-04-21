<?php
/* 
🎓 APRIL 20 CODE CHECK - ADVANCED TASKS MASTER GUIDE
Task Focus: File Upload, Pinned Items, Advanced Search, and Reporting
*/

// ==========================================
// 1. FILE UPLOAD (Controller Logic)
// (Task: Upload Profile Photo / PDF)
// ==========================================
/* 
LOCATE: Your Controller method (e.g., updateProfile)
*/
if (!empty($_FILES['profile_photo']['name'])) {
    $targetDir = "../public/uploads/";
    $fileName = time() . "_" . basename($_FILES['profile_photo']['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Validation (Size + Type)
    if($_FILES['profile_photo']['size'] < 2000000 && in_array($fileType, ['jpg','png','pdf'])) {
        if(move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetFilePath)) {
            // Success! Send $fileName to DB
            $db_value = $fileName;
        }
    }
}

// ==========================================
// 2. PINNED / FEATURED ITEMS (Logic)
// (Task: Show pinned items on top)
// ==========================================
/*
DB: ALTER TABLE announcements ADD COLUMN is_pinned TINYINT(1) DEFAULT 0;

Model Query: 
"SELECT * FROM announcements ORDER BY is_pinned DESC, created_at DESC"

View Logic:
*/
?>
<?php foreach($announcements as $a): ?>
    <div class="item" style="<?= $a->is_pinned ? 'border-left: 5px solid gold; background: #fffdf0;' : '' ?>">
        <?php if($a->is_pinned): ?> 📌 PINNED <?php endif; ?>
        <h3><?= $a->title ?></h3>
    </div>
<?php endforeach; ?>

<?php
// ==========================================
// 3. SEARCH with PARTIAL MATCHING (Logic)
// (Task: Search by District / Name)
// ==========================================
/*
Model Query:
*/
/*
public function getSearchResults($keyword) {
    // Partial match using LIKE and % wildcards
    $this->db->query("SELECT * FROM users WHERE district LIKE :keyword OR name LIKE :keyword");
    $this->db->bind(':keyword', '%' . $keyword . '%'); 
    return $this->db->resultSet();
}
*/

// ==========================================
// 4. ACTUAL VALUES & AGGREGATES (Reports)
// (Task: Remove percentages, print actual value)
// ==========================================
/*
Model:
"SELECT SUM(budget) as total, COUNT(*) as count, MAX(budget) as highest FROM projects"

View:
*/
?>
<div class="report-card">
    <div class="stat">Total Projects: <?= $data['report']->count ?></div>
    <div class="stat">Total Investment: LKR <?= number_format($data['report']->total, 2) ?></div>
    <div class="stat">Highest Valued: LKR <?= number_format($data['report']->highest, 2) ?></div>
</div>

<?php
/*
🎯 EXAM PRO TIPS - "HOW TO PROOF IT WORKS":
Examiner "Proof karanna" (පෙන්නන්න) කිව්වොත්:
1. මුලින්ම DB එකේ අගයක් අතින් වෙනස් කරලා UI එකේ ඒක පිළිඹිබු වෙන හැටි පෙන්වන්න.
2. වැරදි format එකකින් data එකක් දාලා (උදා: phone number < 10) වැදුණු error එක පෙන්වන්න.
3. අලුතින් data එකක් දාලා ඒක අලුත්ම result එක විදිහට list එකේ උඩට එන හැටි පෙන්වන්න.

Teachers Method:
"Always explain WHY you chose the logic. E.g., 'I used time() in the filename to prevent name duplicate issues when uploading photos'."
*/
?>
