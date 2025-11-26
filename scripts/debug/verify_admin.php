<?php
require_once '../app/config/config.php';
require_once '../core/Database.php';

try {
    $db = new Database();
    
    // Get admin user
    $db->query("SELECT * FROM users WHERE email = 'admin@skillxchange.com'");
    $admin = $db->single();
    
    if (!$admin) {
        echo "❌ Admin user does not exist!<br><br>";
        echo "Creating admin user...<br>";
        
        // Create admin
        $password = password_hash('admin123', PASSWORD_BCRYPT);
        $db->query("INSERT INTO users (username, email, password, role, profile_completed) 
                   VALUES ('admin', 'admin@skillxchange.com', :password, 'admin', 1)");
        $db->bind(':password', $password);
        $db->execute();
        
        echo "✅ Admin user created!<br>";
    } else {
        echo "✅ Admin user exists!<br><br>";
        echo "Admin Details:<br>";
        echo "ID: " . $admin['id'] . "<br>";
        echo "Username: " . $admin['username'] . "<br>";
        echo "Email: " . $admin['email'] . "<br>";
        echo "Role: " . $admin['role'] . "<br><br>";
        
        // Test password
        $testPassword = 'admin123';
        if (password_verify($testPassword, $admin['password'])) {
            echo "✅ Password verification SUCCESSFUL!<br>";
            echo "You can login with:<br>";
            echo "Email: admin@skillxchange.com<br>";
            echo "Password: admin123<br>";
        } else {
            echo "❌ Password verification FAILED!<br>";
            echo "Updating password to 'admin123'...<br>";
            
            $newPassword = password_hash('admin123', PASSWORD_BCRYPT);
            $db->query("UPDATE users SET password = :password WHERE email = 'admin@skillxchange.com'");
            $db->bind(':password', $newPassword);
            $db->execute();
            
            echo "✅ Password updated!<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>