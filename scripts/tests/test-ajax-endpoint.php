<?php
// Simulate browser AJAX POST request to /organization/updateProfile

session_start();
$_SESSION['user_id'] = 37;
$_SESSION['role'] = 'organization';
$_SESSION['username'] = 'Pretty Software';

// Simulate POST data from the form
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['org_name'] = 'Pretty Software AJAX Test';
$_POST['email'] = 'ajax@prettysoftware.com';
$_POST['description'] = 'Updated via AJAX from test script';
$_POST['phone'] = '+1-555-1234';
$_POST['website'] = 'https://test.prettysoftware.com';
$_POST['address'] = '456 Test Ave';
$_POST['city'] = 'New York';
$_POST['country'] = 'USA';
$_POST['postal_code'] = '10001';
$_POST['linkedin'] = 'https://linkedin.com/test';
$_POST['twitter'] = '@test';
$_POST['github'] = 'https://github.com/test';

require_once "../app/config/config.php";
require_once "../core/Database.php";

echo "=== BEFORE UPDATE ===\n";
$db = new Database();
$db->query("SELECT username, email, bio, phone FROM users WHERE id = 37");
$before = $db->single();
echo json_encode((array)$before) . "\n\n";

// Call the update logic directly
$orgId = $_SESSION['user_id'];
$orgName = isset($_POST['org_name']) ? trim($_POST['org_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$website = isset($_POST['website']) ? trim($_POST['website']) : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$city = isset($_POST['city']) ? trim($_POST['city']) : '';
$country = isset($_POST['country']) ? trim($_POST['country']) : '';
$postalCode = isset($_POST['postal_code']) ? trim($_POST['postal_code']) : '';
$linkedin = isset($_POST['linkedin']) ? trim($_POST['linkedin']) : '';
$twitter = isset($_POST['twitter']) ? trim($_POST['twitter']) : '';
$github = isset($_POST['github']) ? trim($_POST['github']) : '';

$db->query("UPDATE users SET 
            username = :username,
            email = :email,
            bio = :bio,
            phone = :phone,
            website = :website,
            address = :address,
            city = :city,
            country = :country,
            postal_code = :postal_code,
            linkedin = :linkedin,
            twitter = :twitter,
            github = :github
            WHERE id = :id AND role = 'organization'");
$db->bind(':id', $orgId);
$db->bind(':username', $orgName);
$db->bind(':email', $email);
$db->bind(':bio', $description);
$db->bind(':phone', $phone);
$db->bind(':website', $website);
$db->bind(':address', $address);
$db->bind(':city', $city);
$db->bind(':country', $country);
$db->bind(':postal_code', $postalCode);
$db->bind(':linkedin', $linkedin);
$db->bind(':twitter', $twitter);
$db->bind(':github', $github);

if ($db->execute()) {
    echo "✓ Update executed\n\n";
    
    echo "=== AFTER UPDATE ===\n";
    $db->query("SELECT username, email, bio, phone FROM users WHERE id = 37");
    $after = $db->single();
    echo json_encode((array)$after) . "\n\n";
    
    // Restore
    $db->query("UPDATE users SET username = :un, email = :em, bio = :bio, phone = :ph WHERE id = 37");
    $db->bind(':un', $before->username);
    $db->bind(':em', $before->email);
    $db->bind(':bio', $before->bio);
    $db->bind(':ph', $before->phone);
    $db->execute();
    echo "✓ Restored\n";
} else {
    echo "✗ Update failed\n";
}
?>
