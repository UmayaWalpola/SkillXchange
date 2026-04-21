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
