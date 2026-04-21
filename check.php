1. ALTER TABLE users ADD COLUMN phone VARCHAR(15) NULL;
$phone = trim($_POST['phone'] ?? ''); - form view 

<label for="ind-phone">Phone Number</label>
<input type="text" id="ind-phone" name="ind-phone" required />

registerIndividual()
$phone = trim($_POST['ind-phone'] ?? '');

if (empty($phone)) {
    $errors[] = "Phone number is required.";
} elseif (!preg_match('/^07[0-9]{8}$/', $phone)) {
    $errors[] = "Phone number must start with 07 and contain 10 digits.";
}

$userId = $this->userModel->registerIndividual($name, $email, $phone, $password);

models/User.php
public function registerIndividual($name, $email, $phone, $password) {

    $sql = "INSERT INTO users (username, email, phone, password, role, profile_completed)
        VALUES (:name, :email, :phone, :password, 'individual', 0)";

        
        
$stmt->bindValue(':name', $name);
$stmt->bindValue(':email', $email);
$stmt->bindValue(':phone', $phone);
$stmt->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));

userprofile.php
<p class="profile-phone"><?= htmlspecialchars($data['user']['phone'] ?? '') ?></p>

<?php if (!empty($data['user']['phone'])): ?>
    <p class="profile-phone">Phone: <?= htmlspecialchars($data['user']['phone']) ?></p>
<?php endif; ?>


UsersController.php
'phone' => $user['phone'] ?? '',

2. <option value="question">Question</option>

$postType    = trim($_POST['post_type'] ?? 'discussion');

$allowedPostTypes = ['discussion', 'question', 'announcement'];
if (!in_array($postType, $allowedPostTypes, true)) {
    $postType = 'discussion';
}

3. $date = trim($_POST['registered_date'] ?? '');
if ($date === '') {
    $errors[] = 'Registered date is required.';
} elseif ($date > date('Y-m-d')) {
    $errors[] = 'Registered date cannot be in the future.';
}

4. $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
if (!in_array($file['type'], $allowedTypes)) {
    $errors[] = 'Only JPG, PNG, and PDF files are allowed.';
}

5. Auth Date Field + Future Date Validation
Task:
Add date_of_birth or registered_date, save it, show it, block future dates.

Where:
Same as task 1.

Steps:

DB:
ALTER TABLE users ADD COLUMN date_of_birth DATE NULL;
Add input:

<label for="ind-dob">Date of Birth</label>
<input type="date" id="ind-dob" name="ind-dob" required>

In registerIndividual():
$dob = trim($_POST['ind-dob'] ?? '');
Validate:
if (empty($dob)) {
    $errors[] = "Date of birth is required.";
} elseif ($dob > date('Y-m-d')) {
    $errors[] = "Date of birth cannot be in the future.";
}

Pass to model:
$userId = $this->userModel->registerIndividual($name, $email, $password, $dob);
If phone already exists too, pass both:

$userId = $this->userModel->registerIndividual($name, $email, $password, $phone, $dob);
In User::registerIndividual():
public function registerIndividual($name, $email, $password, $dob)
SQL:
INSERT INTO users (username, email, password, date_of_birth, role, profile_completed)
VALUES (:name, :email, :password, :date_of_birth, 'individual', 0)
Bind:
$stmt->bindValue(':date_of_birth', $dob);
In UsersController::userprofile():
'date_of_birth' => $user['date_of_birth'] ?? '',
In userprofile.php:
<?php if (!empty($data['user']['date_of_birth'])): ?>
    <p>Date of Birth: <?= htmlspecialchars($data['user']['date_of_birth']) ?></p>
<?php endif; ?>


6. Task:
Add upload field to post form and validate file type, e.g. only PDF or only image.

Where:
Same community files as task 3.

Steps:

DB if new file path column needed:
ALTER TABLE community_posts ADD COLUMN file_path VARCHAR(255) NULL;
If using existing image_path, no new column needed for images.

In community_detail.php, add file input:
<input type="file" id="postFile" name="file" accept="application/pdf">
For images:

<input type="file" id="postImage" name="image" accept="image/*">
In community_forum.js, read file:
const file = document.getElementById('postFile')?.files[0] || null;
Append to FormData:
if (file) formData.append('file', file);
In UserdashboardController::postToCommunity(), validate PDF:
if (!empty($_FILES['file']['name'])) {
    $file = $_FILES['file'];
    $allowed = ['application/pdf'];
    $detectedType = mime_content_type($file['tmp_name']);

    if (!in_array($detectedType, $allowed, true)) {
        echo json_encode(['success' => false, 'message' => 'Only PDF files are allowed']);
        exit;
    }
}
For image validation:

$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
Move uploaded file:
$uploadDir = dirname(__DIR__, 2) . '/public/assets/uploads/community_posts/';
$filename = 'post_file_' . $userId . '_' . time() . '.pdf';
$destPath = $uploadDir . $filename;

move_uploaded_file($file['tmp_name'], $destPath);

$filePath = 'assets/uploads/community_posts/' . $filename;
Pass $filePath to model.

In Community::createPost(), add method parameter:

$filePath = null
Add DB insert column:
file_path
Add placeholder:
:file_path
Bind:
$this->db->bind(':file_path', $filePath);
In feed view:
<?php if (!empty($post->file_path)): ?>
    <a href="<?= URLROOT ?>/<?= htmlspecialchars($post->file_path) ?>" target="_blank">View PDF</a>
<?php endif; ?>


8.profile setup 
ALTER TABLE users ADD COLUMN preferred_learning_mode VARCHAR(20) NULL;

 Add Field In Profile Setup Form
File:
app/views/users/profile_setup.php

Add this inside the Basic Information section, after username or profile picture:

<div class="form-group">
    <label for="preferred_learning_mode">Preferred Learning Mode <span class="required-star">*</span></label>
    <select id="preferred_learning_mode" name="preferred_learning_mode" required>
        <option value="">Select learning mode</option>
        <option value="online" <?= (($data['old']['preferred_learning_mode'] ?? '') === 'online') ? 'selected' : '' ?>>Online</option>
        <option value="physical" <?= (($data['old']['preferred_learning_mode'] ?? '') === 'physical') ? 'selected' : '' ?>>Physical</option>
        <option value="hybrid" <?= (($data['old']['preferred_learning_mode'] ?? '') === 'hybrid') ? 'selected' : '' ?>>Hybrid</option>
    </select>
    <small class="field-hint">Choose how you prefer to learn or teach skills.</small>
</div>

Read Field In Controller
File:
app/controllers/UsersController.php

Inside handleProfileSetup($userId), near this:

$username = trim($_POST['username'] ?? '');
Add:

$preferredLearningMode = trim($_POST['preferred_learning_mode'] ?? '');

Still inside handleProfileSetup($userId), after username validation, add:

$allowedLearningModes = ['online', 'physical', 'hybrid'];

if (empty($preferredLearningMode)) {
    $errors[] = "Preferred learning mode is required.";
} elseif (!in_array($preferredLearningMode, $allowedLearningModes, true)) {
    $errors[] = "Invalid preferred learning mode selected.";
}

Still in UsersController.php, find the update query:

$db->query("UPDATE users SET 
    username = :username,
    profile_picture = :profile_picture,
    profile_completed = 1
    WHERE id = :user_id");
Change it to:

$db->query("UPDATE users SET 
    username = :username,
    profile_picture = :profile_picture,
    preferred_learning_mode = :preferred_learning_mode,
    profile_completed = 1
    WHERE id = :user_id");

    Then after:

$db->bind(':profile_picture', $profilePicture);
Add:

$db->bind(':preferred_learning_mode', $preferredLearningMode);

File:
app/controllers/UsersController.php

Inside userprofile(), find the $data = [ section and inside 'user' => [ ... ], add:

'preferred_learning_mode' => $user['preferred_learning_mode'] ?? '',
Example:

'user' => [
    'id' => $user['id'],
    'name' => $user['username'],
    'username' => $user['username'],
    'email' => $user['email'],
    'preferred_learning_mode' => $user['preferred_learning_mode'] ?? '',
    'bio' => $user['bio'] ?? 'No bio yet.',

    Show It On Profile Page
File:
app/views/users/userprofile.php

Add this under username or bio:

<?php if (!empty($data['user']['preferred_learning_mode'])): ?>
    <p class="profile-learning-mode">
        Preferred Mode: <?= htmlspecialchars(ucfirst($data['user']['preferred_learning_mode'])) ?>
    </p>
<?php endif; ?>
Example placement:

<p class="profile-username">@<?= htmlspecialchars($data['user']['username']); ?></p>

<?php if (!empty($data['user']['preferred_learning_mode'])): ?>
    <p class="profile-learning-mode">
        Preferred Mode: <?= htmlspecialchars(ucfirst($data['user']['preferred_learning_mode'])) ?>
    </p>
<?php endif; ?>

<p class="profile-bio">
    <?= htmlspecialchars($data['user']['bio']); ?>
</p>

public/assets/css/profile.css

Add:

.profile-learning-mode {
    margin-top: 0.35rem;
    color: #2563eb;
    font-weight: 600;
}

!!Use the same field: Preferred Learning Mode, but now make it editable from Edit Profile too.

1. Add Field To Edit Profile Form
File:
app/views/users/edit_profile.php

Find the Basic Information section where username and bio are edited.

Add this field near them:

<div class="form-group">
    <label for="preferred_learning_mode">Preferred Learning Mode</label>
    <select id="preferred_learning_mode" name="preferred_learning_mode">
        <option value="">Select learning mode</option>
        <option value="online" <?= (($data['old']['preferred_learning_mode'] ?? $data['user']['preferred_learning_mode'] ?? '') === 'online') ? 'selected' : '' ?>>Online</option>
        <option value="physical" <?= (($data['old']['preferred_learning_mode'] ?? $data['user']['preferred_learning_mode'] ?? '') === 'physical') ? 'selected' : '' ?>>Physical</option>
        <option value="hybrid" <?= (($data['old']['preferred_learning_mode'] ?? $data['user']['preferred_learning_mode'] ?? '') === 'hybrid') ? 'selected' : '' ?>>Hybrid</option>
    </select>
    <small class="field-hint">Update how you prefer to learn or teach skills.</small>
</div>
2. Read Field In Controller
File:
app/controllers/UsersController.php

Inside handleEditProfile($userId), find:

$username = trim($_POST['username'] ?? '');
$bio = trim($_POST['bio'] ?? '');
Add:

$preferredLearningMode = trim($_POST['preferred_learning_mode'] ?? '');
3. Validate Field
Still inside handleEditProfile($userId), after username/bio validation, add:

$allowedLearningModes = ['online', 'physical', 'hybrid'];

if ($preferredLearningMode !== '' && !in_array($preferredLearningMode, $allowedLearningModes, true)) {
    $errors[] = "Invalid preferred learning mode selected.";
}
If the field must be required, use this instead:

if (empty($preferredLearningMode)) {
    $errors[] = "Preferred learning mode is required.";
} elseif (!in_array($preferredLearningMode, $allowedLearningModes, true)) {
    $errors[] = "Invalid preferred learning mode selected.";
}
4. Save Field In Update Query
Still in handleEditProfile($userId), find the UPDATE users SET ... query.

It may look like this:

UPDATE users SET 
    username = :username,
    bio = :bio,
    profile_picture = COALESCE(:profile_picture, profile_picture)
WHERE id = :user_id
Add:

preferred_learning_mode = :preferred_learning_mode,
Example:

$db->query("UPDATE users SET 
    username = :username,
    bio = :bio,
    preferred_learning_mode = :preferred_learning_mode,
    profile_picture = COALESCE(:profile_picture, profile_picture)
    WHERE id = :user_id");
Then bind it:

$db->bind(':preferred_learning_mode', $preferredLearningMode ?: null);
Put that near the other binds:

$db->bind(':username', $username);
$db->bind(':bio', $bio);
$db->bind(':preferred_learning_mode', $preferredLearningMode ?: null);
$db->bind(':profile_picture', $profilePicture);
$db->bind(':user_id', $userId);
5. Preserve Old Input If Validation Fails
Inside the error handling block in handleEditProfile(), make sure this exists:

'old' => $_POST,
Your file likely already does this. If yes, you don’t need to add anything.

6. Make Sure Profile Display Already Has It
If you already did the profile setup task, these should already exist:

In UsersController::userprofile():

'preferred_learning_mode' => $user['preferred_learning_mode'] ?? '',
In userprofile.php:

<?php if (!empty($data['user']['preferred_learning_mode'])): ?>
    <p class="profile-learning-mode">
        Preferred Mode: <?= htmlspecialchars(ucfirst($data['user']['preferred_learning_mode'])) ?>
    </p>
<?php endif; ?>
Manual Test

Go to Edit Profile.
Change Preferred Learning Mode to Hybrid.
Save.
Return to profile.
Confirm it shows Preferred Mode: Hybrid.
Try editing the HTML value to something invalid like fake.
Submit.
Confirm validation blocks it.
Important Trap
If the field shows in Edit Profile but does not save, the issue is usually one of these:

You added the <select> but forgot name="preferred_learning_mode".
You read the value but forgot to add it to the UPDATE users SET.
You added the SQL placeholder but forgot:
$db->bind(':preferred_learning_mode', $preferredLearningMode ?: null);

Community Task 2: Add “Pin Post” Checkbox

Task:
Add a checkbox called Pin this post, save it in DB, show Pinned badge, and keep pinned posts at top.

Files:

app/views/users/community_detail.php
public/assets/js/community_forum.js
app/controllers/UserdashboardController.php
app/models/community.php
What to change:

DB if missing:
ALTER TABLE community_posts ADD COLUMN is_pinned TINYINT(1) DEFAULT 0;
In community_detail.php, add checkbox:
<?php if (in_array($data['community']->user_role, ['admin', 'moderator'])): ?>
    <label class="pin-post-option">
        <input type="checkbox" id="isPinned" value="1">
        Pin this post
    </label>
<?php endif; ?>
In community_forum.js, read checkbox:
const isPinned = document.getElementById('isPinned')?.checked ? 1 : 0;
Append to form data:

formData.append('is_pinned', isPinned);
In UserdashboardController.php, read:
$isPinned = isset($_POST['is_pinned']) && $_POST['is_pinned'] == '1' ? 1 : 0;
After getting member role, restrict:

if (!in_array($member->role, ['admin', 'moderator'])) {
    $isPinned = 0;
}
Pass to model:
$postId = $communityModel->createPost(
    $userId,
    $communityId,
    $title ?: null,
    $content,
    $postType ?: 'discussion',
    $linkUrl ?: null,
    $imagePath,
    $isPinned
);
In community.php, update method:
public function createPost($userId, $communityId, $title, $content, $postType = 'discussion', $linkUrl = null, $imagePath = null, $isPinned = 0)
Add to INSERT:
is_pinned,
Add placeholder:

:is_pinned,
Bind:

$this->db->bind(':is_pinned', $isPinned);
Confirm query orders pinned first:
ORDER BY p.is_pinned DESC, p.created_at ASC
Show badge in community_detail.php:
<?php if (!empty($post->is_pinned)): ?>
    <span class="pinned-badge">Pinned</span>
<?php endif; ?>

Skill Matching Task 1: Show “Top Match” Badge For Strong Matches

Task:
Show a text tag called Top Match if a user has 2 or more matched skills.

This matches the common code-check pattern:
“Show a text tag if value is higher than X.”

Files:

app/models/SkillMatch.php
app/views/users/matches.php
optionally public/assets/css/matches.css
What to change:

In SkillMatch.php, your match data already has:
'total_skills' => 0
and later:

$match['total_skills'] = count($match['i_teach']) + count($match['they_teach']);
So you can use total_skills directly.

In app/views/users/matches.php, where each match card is rendered, add:
<?php if (($match['total_skills'] ?? 0) >= 2): ?>
    <span class="match-badge top-match">Top Match</span>
<?php endif; ?>
Optional CSS in public/assets/css/matches.css:
.match-badge.top-match {
    display: inline-block;
    padding: 0.35rem 0.7rem;
    border-radius: 999px;
    background: #dcfce7;
    color: #166534;
    font-weight: 700;
    font-size: 0.8rem;
}
Manual test:

User with 1 matched skill should not show badge.
User with 2+ matched skills should show Top Match.
Skill Matching Task 2: Add Minimum Proficiency Filter

Task:
Add a dropdown called Minimum Teacher Level to the matches page:

Beginner
Intermediate
Advanced
Then only show matches where the teacher’s level matches or is higher.

Files:

app/views/users/matches.php
public/assets/js/matches.js
app/models/SkillMatch.php
Simpler exam version:
Do it as frontend filtering only.

In matches.php, add dropdown:
<select id="minTeacherLevel" class="match-filter">
    <option value="">Any Teacher Level</option>
    <option value="beginner">Beginner+</option>
    <option value="intermediate">Intermediate+</option>
    <option value="advanced">Advanced Only</option>
</select>
Add teacher level as data attribute on match cards.
Where match card starts, add something like:

<div class="match-card" data-teacher-level="<?= strtolower($match['teacher_level'] ?? '') ?>">
For mutual/multi matches, if teacher level is inside skill arrays, use the first one:

<?php
$teacherLevel = '';
if (!empty($match['they_teach'][0]['their_level'])) {
    $teacherLevel = strtolower($match['they_teach'][0]['their_level']);
} elseif (!empty($match['i_teach'][0]['my_level'])) {
    $teacherLevel = strtolower($match['i_teach'][0]['my_level']);
}
?>
<div class="match-card" data-teacher-level="<?= htmlspecialchars($teacherLevel) ?>">
In matches.js, add level ranking:
const levelRank = {
    beginner: 1,
    intermediate: 2,
    advanced: 3
};
Add filter logic:
const minTeacherLevel = document.getElementById('minTeacherLevel')?.value || '';

document.querySelectorAll('.match-card').forEach(card => {
    const cardLevel = card.dataset.teacherLevel || '';

    const matchesLevel =
        !minTeacherLevel ||
        (levelRank[cardLevel] || 0) >= (levelRank[minTeacherLevel] || 0);

    card.style.display = matchesLevel ? '' : 'none';
});
Add event listener:
document.getElementById('minTeacherLevel')?.addEventListener('change', filterMatches);