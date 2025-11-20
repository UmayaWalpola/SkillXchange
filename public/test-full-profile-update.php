<?php
session_start();

require_once "../app/config/config.php";
require_once "../core/Database.php";

// Simulate organization session
$_SESSION['user_id'] = 37;
$_SESSION['role'] = 'organization';
$_SESSION['username'] = 'Pretty Software';

// First, check current values
echo "=== CURRENT PROFILE VALUES ===\n";
$db = new Database();
$db->query("SELECT username, email, bio, phone, website, address, city, country, postal_code, linkedin, twitter, github FROM users WHERE id = 37");
$before = $db->single();
echo "Before: " . json_encode((array)$before, JSON_PRETTY_PRINT) . "\n\n";

// Simulate POST data with all fields
echo "=== UPDATING WITH ALL FIELDS ===\n";
$_POST['org_name'] = 'Pretty Software - Updated';
$_POST['email'] = 'contact@prettysoftware.com';
$_POST['description'] = 'We create beautiful software solutions.';
$_POST['phone'] = '+1-555-0100';
$_POST['website'] = 'https://prettysoftware.com';
$_POST['address'] = '123 Tech Street';
$_POST['city'] = 'San Francisco';
$_POST['country'] = 'USA';
$_POST['postal_code'] = '94102';
$_POST['linkedin'] = 'https://linkedin.com/company/prettysoftware';
$_POST['twitter'] = '@prettysoftware';
$_POST['github'] = 'https://github.com/prettysoftware';

// Test the update logic
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
    echo "✓ Update successful!\n\n";
    
    // Verify the update
    echo "=== AFTER UPDATE ===\n";
    $db->query("SELECT username, email, bio, phone, website, address, city, country, postal_code, linkedin, twitter, github FROM users WHERE id = 37");
    $after = $db->single();
    echo json_encode((array)$after, JSON_PRETTY_PRINT) . "\n\n";
    
    // Restore original values
    echo "=== RESTORING ORIGINAL VALUES ===\n";
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
                WHERE id = :id");
    $db->bind(':id', 37);
    $db->bind(':username', $before->username);
    $db->bind(':email', $before->email);
    $db->bind(':bio', $before->bio);
    $db->bind(':phone', $before->phone);
    $db->bind(':website', $before->website);
    $db->bind(':address', $before->address);
    $db->bind(':city', $before->city);
    $db->bind(':country', $before->country);
    $db->bind(':postal_code', $before->postal_code);
    $db->bind(':linkedin', $before->linkedin);
    $db->bind(':twitter', $before->twitter);
    $db->bind(':github', $before->github);
    
    if ($db->execute()) {
        echo "✓ Restored original values\n";
    } else {
        echo "✗ Failed to restore\n";
    }
} else {
    echo "✗ Update failed\n";
}
?>
