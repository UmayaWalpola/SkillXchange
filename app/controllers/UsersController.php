<?php
class UsersController extends Controller {

    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    // Profile Setup - GET/POST
    public function profileSetup() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);

        // Redirect if profile already completed
        if ($user['profile_completed']) {
            header('Location: ' . URLROOT . '/users/userprofile');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleProfileSetup($userId);
        } else {
            // Show the form
            $data = [
                'user' => $user,
                'errors' => []
            ];
            $this->view('users/profile_setup', $data);
        }
    }

 // Handle Profile Setup Form Submission
private function handleProfileSetup($userId) {
    $errors = [];
    
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $teachSkills = $_POST['teach_skills'] ?? [];
    $teachLevels = $_POST['teach_levels'] ?? [];
    $learnSkills = $_POST['learn_skills'] ?? [];
    $learnLevels = $_POST['learn_levels'] ?? [];
    
    // Validation - Username
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Username must be between 3 and 20 characters.";
    } elseif ($this->userModel->usernameExists($username, $userId)) {
        $errors[] = "Username already taken.";
    }

    // Validate Learn Skills (AT LEAST 1 REQUIRED)
    $validLearnSkills = [];
    $validLearnLevels = [];
    $hasAtLeastOneLearnSkill = false;
    
    foreach ($learnSkills as $index => $skill) {
        $skill = trim($skill);
        if (!empty($skill)) {
            $hasAtLeastOneLearnSkill = true;
            // If skill is selected, level MUST be selected
            if (empty($learnLevels[$index])) {
                $skillName = ucfirst(str_replace('-', ' ', $skill));
                $errors[] = "Please select a proficiency level for learning skill: {$skillName}";
            } else {
                $validLearnSkills[] = $skill;
                $validLearnLevels[] = $learnLevels[$index];
            }
        }
    }
    
    // Check if at least one learn skill was provided
    if (!$hasAtLeastOneLearnSkill) {
        $errors[] = "Please select at least one skill you want to learn.";
    }

    // Validate Teach Skills (OPTIONAL - but if skill selected, level required)
    $validTeachSkills = [];
    $validTeachLevels = [];
    
    foreach ($teachSkills as $index => $skill) {
        $skill = trim($skill);
        if (!empty($skill)) {
            // If skill is selected, level MUST be selected
            if (empty($teachLevels[$index])) {
                $skillName = ucfirst(str_replace('-', ' ', $skill));
                $errors[] = "Please select a proficiency level for teaching skill: {$skillName}";
            } else {
                $validTeachSkills[] = $skill;
                $validTeachLevels[] = $teachLevels[$index];
            }
        }
    }

    // Handle profile picture upload
    $profilePicture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed.";
        } elseif ($file['size'] > $maxSize) {
            $errors[] = "Profile picture must not exceed 5MB.";
        } else {
            $uploadDir = '../public/uploads/profile_pictures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'user_' . $userId . '_' . uniqid() . '.' . $extension;
            $profilePicture = 'uploads/profile_pictures/' . $fileName;
            $fullPath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                $errors[] = "Failed to upload profile picture.";
                $profilePicture = null;
            }
        }
    }

    // If no errors, save everything
    if (empty($errors)) {
        try {
            $db = new Database();
            
            // Start transaction
            $db->query("START TRANSACTION");
            
            // Update basic profile with profile_completed = 1
            $db->query("UPDATE users SET 
                username = :username, 
                profile_picture = :profile_picture,
                profile_completed = 1
                WHERE id = :user_id");
            $db->bind(':username', $username);
            $db->bind(':profile_picture', $profilePicture);
            $db->bind(':user_id', $userId);
            
            if (!$db->execute()) {
                throw new Exception("Failed to update profile");
            }
            
            // Add teaching skills (only if any were provided)
            if (!empty($validTeachSkills)) {
                foreach ($validTeachSkills as $index => $skill) {
                    $db->query("INSERT INTO user_skills (user_id, skill_name, skill_type, proficiency_level) 
                               VALUES (:user_id, :skill_name, 'teach', :proficiency_level)");
                    $db->bind(':user_id', $userId);
                    $db->bind(':skill_name', $skill);
                    $db->bind(':proficiency_level', $validTeachLevels[$index]);
                    
                    if (!$db->execute()) {
                        throw new Exception("Failed to add teaching skills");
                    }
                }
            }
            
            // Add learning skills (at least one is guaranteed at this point)
            foreach ($validLearnSkills as $index => $skill) {
                $db->query("INSERT INTO user_skills (user_id, skill_name, skill_type, proficiency_level) 
                           VALUES (:user_id, :skill_name, 'learn', :proficiency_level)");
                $db->bind(':user_id', $userId);
                $db->bind(':skill_name', $skill);
                $db->bind(':proficiency_level', $validLearnLevels[$index]);
                
                if (!$db->execute()) {
                    throw new Exception("Failed to add learning skills");
                }
            }
            
            // Commit transaction
            $db->query("COMMIT");
            
            // Update session with profile_completed = 1
            $_SESSION['username'] = $username;
            $_SESSION['profile_completed'] = 1;
            
            // Redirect to profile
            $_SESSION['success'] = "Profile setup completed successfully!";
            header('Location: ' . URLROOT . '/users/userprofile');
            exit;
            
        } catch (Exception $e) {
            // Rollback on error
            $db->query("ROLLBACK");
            
            // Show the actual error message for debugging
            $errors[] = "Error: " . $e->getMessage();
            error_log("Profile Setup Error: " . $e->getMessage());
        }
    }

    // If there are errors, show the form again with errors
    $user = $this->userModel->getUserById($userId);
    $data = [
        'user' => $user,
        'errors' => $errors,
        'old' => $_POST
    ];
    $this->view('users/profile_setup', $data);
}

    // Show user profile
    public function userprofile($userId = null) {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }

        // Use logged-in user's ID if not specified
        if (!$userId) {
            $userId = $_SESSION['user_id'];
        }

        // Get user data from database
        $user = $this->userModel->getUserById($userId);
        
        if (!$user) {
            header('Location: ' . URLROOT . '/home');
            exit;
        }

        // Check if profile is completed
        if (!$user['profile_completed'] && $userId == $_SESSION['user_id']) {
            header('Location: ' . URLROOT . '/users/profileSetup');
            exit;
        }

        // Get all user data
        $userSkills = $this->userModel->getUserSkills($userId);
        $userProjects = $this->userModel->getUserProjects($userId);
        $userBadges = $this->userModel->getUserBadges($userId);
        $userFeedback = $this->userModel->getUserFeedback($userId);
        $userActivity = $this->userModel->getUserActivity($userId);
        $userStats = $this->userModel->getUserStats($userId);
        $ratingData = $this->userModel->getAverageRating($userId);

        // Prepare data for view
        $data = [
            'user' => [
                'id' => $user['id'],
                'name' => $user['username'],
                'username' => $user['username'],
                'email' => $user['email'],
                'bio' => $user['bio'] ?? 'No bio yet.',
                'avatar' => !empty($user['profile_picture']) 
                    ? $user['profile_picture']
                    : strtoupper(substr($user['username'], 0, 2)),
                'connections' => $userStats['connections_count'] ?? 0,
                'skills_taught' => $userStats['skills_taught_count'] ?? 0,
                'skills_learning' => $userStats['skills_learning_count'] ?? 0,
                'hours_exchanged' => $userStats['hours_exchanged'] ?? 0,
                'rating' => $ratingData['rating'],
                'reviews_count' => $ratingData['count']
            ],
            'skills' => [
                'teaches' => array_map(function($skill) {
                    return $this->formatSkillName($skill['name']) . ' (' . ucfirst($skill['level']) . ')';
                }, $userSkills['teaches']),
                'learns' => array_map(function($skill) {
                    return $this->formatSkillName($skill['name']) . ' (' . ucfirst($skill['level']) . ')';
                }, $userSkills['learns'])
            ],
            'projects' => $userProjects,
            'badges' => $userBadges,
            'feedback' => $userFeedback,
            'activity' => $userActivity,
            'is_own_profile' => ($userId == $_SESSION['user_id'])
        ];

        $this->view('users/userprofile', $data);
    }

    // Show manager profile
    public function managerprofile() {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organization') {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }

        $managerData = [
            'name' => $_SESSION['username'],
            'role' => 'Organization Manager',
            'email' => 'manager@skillxchange.com'
        ];

        $data = ['manager' => $managerData];
        $this->view('users/managerprofile', $data);
    }

    // Helper: Format skill names for display
    private function formatSkillName($skill) {
        $skillMap = [
            'web-development' => 'Web Development',
            'graphic-design' => 'Graphic Design',
            'photography' => 'Photography',
            'cooking' => 'Cooking',
            'language-spanish' => 'Spanish Language',
            'language-french' => 'French Language',
            'music-guitar' => 'Guitar',
            'music-piano' => 'Piano',
            'yoga' => 'Yoga',
            'fitness' => 'Fitness Training',
            'writing' => 'Creative Writing',
            'marketing' => 'Digital Marketing',
            'data-science' => 'Data Science',
            'video-editing' => 'Video Editing'
        ];
        
        return $skillMap[$skill] ?? ucwords(str_replace(['-', '_'], ' ', $skill));
    }

// Show Edit Profile Form - GET
public function editProfile() {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . URLROOT . '/auth/signin');
        exit;
    }

    $userId = $_SESSION['user_id'];
    $user = $this->userModel->getUserById($userId);

    // Check if profile is completed
    if (!$user['profile_completed']) {
        header('Location: ' . URLROOT . '/users/profileSetup');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->handleEditProfile($userId);
    } else {
        // Get user skills for pre-filling the form
        $userSkills = $this->userModel->getUserSkills($userId);
        
        // Format skills data for the form
        $teachSkills = [];
        $learnSkills = [];
        
        foreach ($userSkills['teaches'] as $skill) {
            $teachSkills[] = [
                'name' => $skill['name'],
                'level' => $skill['level']
            ];
        }
        
        foreach ($userSkills['learns'] as $skill) {
            $learnSkills[] = [
                'name' => $skill['name'],
                'level' => $skill['level']
            ];
        }

        $data = [
            'user' => $user,
            'skills' => [
                'teaches' => $teachSkills,
                'learns' => $learnSkills
            ],
            'errors' => [],
            'old' => []
        ];
        
        $this->view('users/edit_profile', $data);
    }
}

// Handle Edit Profile Form Submission - POST
private function handleEditProfile($userId) {
    $errors = [];
    
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $teachSkills = $_POST['teach_skills'] ?? [];
    $teachLevels = $_POST['teach_levels'] ?? [];
    $learnSkills = $_POST['learn_skills'] ?? [];
    $learnLevels = $_POST['learn_levels'] ?? [];
    
    // Validation - Username
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Username must be between 3 and 20 characters.";
    } elseif ($this->userModel->usernameExists($username, $userId)) {
        $errors[] = "Username already taken.";
    }

    // Validate Learn Skills (AT LEAST 1 REQUIRED)
    $validLearnSkills = [];
    $validLearnLevels = [];
    $hasAtLeastOneLearnSkill = false;
    
    foreach ($learnSkills as $index => $skill) {
        $skill = trim($skill);
        if (!empty($skill)) {
            $hasAtLeastOneLearnSkill = true;
            if (empty($learnLevels[$index])) {
                $skillName = ucfirst(str_replace('-', ' ', $skill));
                $errors[] = "Please select a proficiency level for learning skill: {$skillName}";
            } else {
                $validLearnSkills[] = $skill;
                $validLearnLevels[] = $learnLevels[$index];
            }
        }
    }
    
    if (!$hasAtLeastOneLearnSkill) {
        $errors[] = "Please select at least one skill you want to learn.";
    }

    // Validate Teach Skills (OPTIONAL)
    $validTeachSkills = [];
    $validTeachLevels = [];
    
    foreach ($teachSkills as $index => $skill) {
        $skill = trim($skill);
        if (!empty($skill)) {
            if (empty($teachLevels[$index])) {
                $skillName = ucfirst(str_replace('-', ' ', $skill));
                $errors[] = "Please select a proficiency level for teaching skill: {$skillName}";
            } else {
                $validTeachSkills[] = $skill;
                $validTeachLevels[] = $teachLevels[$index];
            }
        }
    }

    // Handle profile picture upload
    $profilePicture = null;
    $updateProfilePicture = false;
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed.";
        } elseif ($file['size'] > $maxSize) {
            $errors[] = "Profile picture must not exceed 5MB.";
        } else {
            $uploadDir = '../public/uploads/profile_pictures/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Delete old profile picture if exists
            $oldUser = $this->userModel->getUserById($userId);
            if (!empty($oldUser['profile_picture'])) {
                $oldPath = '../public/' . $oldUser['profile_picture'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'user_' . $userId . '_' . uniqid() . '.' . $extension;
            $profilePicture = 'uploads/profile_pictures/' . $fileName;
            $fullPath = $uploadDir . $fileName;
            
            if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
                $errors[] = "Failed to upload profile picture.";
                $profilePicture = null;
            } else {
                $updateProfilePicture = true;
            }
        }
    }

    // If no errors, update everything
    if (empty($errors)) {
        try {
            $db = new Database();
            
            // Start transaction
            $db->query("START TRANSACTION");
            
            // Update basic profile
            if ($updateProfilePicture) {
                $db->query("UPDATE users SET 
                    username = :username, 
                    bio = :bio,
                    profile_picture = :profile_picture
                    WHERE id = :user_id");
                $db->bind(':profile_picture', $profilePicture);
            } else {
                $db->query("UPDATE users SET 
                    username = :username, 
                    bio = :bio
                    WHERE id = :user_id");
            }
            
            $db->bind(':username', $username);
            $db->bind(':bio', $bio);
            $db->bind(':user_id', $userId);
            
            if (!$db->execute()) {
                throw new Exception("Failed to update profile");
            }
            
            // Delete existing skills
            $db->query("DELETE FROM user_skills WHERE user_id = :user_id");
            $db->bind(':user_id', $userId);
            
            if (!$db->execute()) {
                throw new Exception("Failed to delete old skills");
            }
            
            // Add teaching skills
            if (!empty($validTeachSkills)) {
                foreach ($validTeachSkills as $index => $skill) {
                    $db->query("INSERT INTO user_skills (user_id, skill_name, skill_type, proficiency_level) 
                               VALUES (:user_id, :skill_name, 'teach', :proficiency_level)");
                    $db->bind(':user_id', $userId);
                    $db->bind(':skill_name', $skill);
                    $db->bind(':proficiency_level', $validTeachLevels[$index]);
                    
                    if (!$db->execute()) {
                        throw new Exception("Failed to add teaching skills");
                    }
                }
            }
            
            // Add learning skills
            foreach ($validLearnSkills as $index => $skill) {
                $db->query("INSERT INTO user_skills (user_id, skill_name, skill_type, proficiency_level) 
                           VALUES (:user_id, :skill_name, 'learn', :proficiency_level)");
                $db->bind(':user_id', $userId);
                $db->bind(':skill_name', $skill);
                $db->bind(':proficiency_level', $validLearnLevels[$index]);
                
                if (!$db->execute()) {
                    throw new Exception("Failed to add learning skills");
                }
            }
            
            // Update user_stats
            $teachCount = count($validTeachSkills);
            $learnCount = count($validLearnSkills);
            
            $db->query("UPDATE user_stats SET 
                skills_taught_count = :teach_count,
                skills_learning_count = :learn_count
                WHERE user_id = :user_id");
            $db->bind(':teach_count', $teachCount);
            $db->bind(':learn_count', $learnCount);
            $db->bind(':user_id', $userId);
            $db->execute();
            
            // Commit transaction
            $db->query("COMMIT");
            
            // Update session
            $_SESSION['username'] = $username;
            
            // Redirect to profile with success message
            $_SESSION['success'] = "Profile updated successfully!";
            header('Location: ' . URLROOT . '/users/userprofile');
            exit;
            
        } catch (Exception $e) {
            // Rollback on error
            $db->query("ROLLBACK");
            
            $errors[] = "Error: " . $e->getMessage();
            error_log("Profile Edit Error: " . $e->getMessage());
        }
    }

    // If there are errors, show the form again with errors
    $user = $this->userModel->getUserById($userId);
    $userSkills = $this->userModel->getUserSkills($userId);
    
    $data = [
        'user' => $user,
        'skills' => [
            'teaches' => $userSkills['teaches'],
            'learns' => $userSkills['learns']
        ],
        'errors' => $errors,
        'old' => $_POST
    ];
    
    $this->view('users/edit_profile', $data);
}

}