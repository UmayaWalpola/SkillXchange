<?php
class UsersController extends Controller {

    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    private function isAllowedSkill($skillName) {
        return $skillName !== '' && $this->userModel->skillExists($skillName);
    }

    // Profile Setup - GET/POST
    public function profileSetup() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . URLROOT . '/auth/signin');
        exit;
    }

    $userId = $_SESSION['user_id'];
    $user = $this->userModel->getUserById($userId);

    if ($user['profile_completed']) {
        header('Location: ' . URLROOT . '/users/userprofile');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->handleProfileSetup($userId);
        return;
    }

    $data = [
        'user' => $user,
        'errors' => [],
        'old' => [],
        'availableSkills' => $this->userModel->getAllSkills()
    ];

    $this->view('users/profile_setup', $data);
}

 // Handle Profile Setup Form Submission
private function handleProfileSetup($userId) {
    $errors = [];

    $username = trim($_POST['username'] ?? '');
    $teachSkills = $_POST['teach_skills'] ?? [];
    $teachLevels = $_POST['teach_levels'] ?? [];
    $learnSkills = $_POST['learn_skills'] ?? [];
    $learnLevels = $_POST['learn_levels'] ?? [];

    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Username must be between 3 and 20 characters.";
    } elseif ($this->userModel->usernameExists($username, $userId)) {
        $errors[] = "Username already taken.";
    }

    $validLearnSkills = [];
    $validLearnLevels = [];

    foreach ($learnSkills as $index => $skill) {
        $skill = trim($skill);

        if ($skill !== '') {
            $level = trim($learnLevels[$index] ?? '');

            if (!$this->isAllowedSkill($skill)) {
                $errors[] = "Selected learning skill is not available: " . ucwords(str_replace(['-', '_'], ' ', $skill));
            } elseif ($level === '') {
                $skillName = ucwords(str_replace(['-', '_'], ' ', $skill));
                $errors[] = "Please select a proficiency level for learning skill: {$skillName}";
            } else {
                $validLearnSkills[] = $skill;
                $validLearnLevels[] = $level;
            }
        }
    }

    if (empty($validLearnSkills)) {
        $errors[] = "Please select at least one skill you want to learn.";
    }

    $validTeachSkills = [];
    $validTeachLevels = [];

    foreach ($teachSkills as $index => $skill) {
        $skill = trim($skill);

        if ($skill !== '') {
            $level = trim($teachLevels[$index] ?? '');

            if (!$this->isAllowedSkill($skill)) {
                $errors[] = "Selected teaching skill is not available: " . ucwords(str_replace(['-', '_'], ' ', $skill));
            } elseif ($level === '') {
                $skillName = ucwords(str_replace(['-', '_'], ' ', $skill));
                $errors[] = "Please select a proficiency level for teaching skill: {$skillName}";
            } else {
                $validTeachSkills[] = $skill;
                $validTeachLevels[] = $level;
            }
        }
    }

    $profilePicture = null;

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;

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

    if (!empty($errors)) {
        $user = $this->userModel->getUserById($userId);
        $data = [
            'user' => $user,
            'errors' => $errors,
            'old' => $_POST,
            'availableSkills' => $this->userModel->getAllSkills()
        ];
        $this->view('users/profile_setup', $data);
        return;
    }

    try {
        $db = new Database();
        $db->query("START TRANSACTION");

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

        $db->query("UPDATE user_stats SET
            skills_taught_count = :teach_count,
            skills_learning_count = :learn_count
            WHERE user_id = :user_id");
        $db->bind(':teach_count', count($validTeachSkills));
        $db->bind(':learn_count', count($validLearnSkills));
        $db->bind(':user_id', $userId);

        if (!$db->execute()) {
            throw new Exception("Failed to update user stats");
        }

        $db->query("COMMIT");

        $_SESSION['username'] = $username;
        $_SESSION['profile_completed'] = 1;
        $_SESSION['success'] = "Profile setup completed successfully!";

        header('Location: ' . URLROOT . '/users/userprofile');
        exit;

    } catch (Exception $e) {
        if (isset($db)) {
            $db->query("ROLLBACK");
        }

        $errors[] = "Error: " . $e->getMessage();
        error_log("Profile Setup Error: " . $e->getMessage());

        $user = $this->userModel->getUserById($userId);
        $data = [
            'user' => $user,
            'errors' => $errors,
            'old' => $_POST,
            'availableSkills' => $this->userModel->getAllSkills()
        ];
        $this->view('users/profile_setup', $data);
    }
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
        $userStats = $this->userModel->getLiveUserStats($userId);
        $ratingData = $this->userModel->getAverageRating($userId);

        // Prepare data for view
        $data = [
            'user' => [
                'id' => $user['id'],
                'name' => $user['username'],
                'username' => $user['username'],
                'email' => $user['email'],
                'bio' => $user['bio'] ?? 'No bio yet.',
                'profile_picture' => !empty($user['profile_picture'])
                    ? ltrim($user['profile_picture'], '/')
                    : null,
                'avatar' => !empty($user['profile_picture']) 
                    ? ltrim($user['profile_picture'], '/')
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
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . URLROOT . '/auth/signin');
        exit;
    }

    $userId = $_SESSION['user_id'];
    $user = $this->userModel->getUserById($userId);

    if (!$user['profile_completed']) {
        header('Location: ' . URLROOT . '/users/profileSetup');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $this->handleEditProfile($userId);
        return;
    }

    $userSkills = $this->userModel->getUserSkills($userId);

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
        'old' => [],
        'availableSkills' => $this->userModel->getAllSkills()
    ];

    $this->view('users/edit_profile', $data);
}

// Handle Edit Profile Form Submission - POST
private function handleEditProfile($userId) {
    $errors = [];

    $username = trim($_POST['username'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $teachSkills = $_POST['teach_skills'] ?? [];
    $teachLevels = $_POST['teach_levels'] ?? [];
    $learnSkills = $_POST['learn_skills'] ?? [];
    $learnLevels = $_POST['learn_levels'] ?? [];

    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $errors[] = "Username must be between 3 and 20 characters.";
    } elseif ($this->userModel->usernameExists($username, $userId)) {
        $errors[] = "Username already taken.";
    }

    $validLearnSkills = [];
    $validLearnLevels = [];

    foreach ($learnSkills as $index => $skill) {
        $skill = trim($skill);

        if ($skill !== '') {
            $level = trim($learnLevels[$index] ?? '');

            if (!$this->isAllowedSkill($skill)) {
                $errors[] = "Selected learning skill is not available: " . ucwords(str_replace(['-', '_'], ' ', $skill));
            } elseif ($level === '') {
                $skillName = ucwords(str_replace(['-', '_'], ' ', $skill));
                $errors[] = "Please select a proficiency level for learning skill: {$skillName}";
            } else {
                $validLearnSkills[] = $skill;
                $validLearnLevels[] = $level;
            }
        }
    }

    if (empty($validLearnSkills)) {
        $errors[] = "Please select at least one skill you want to learn.";
    }

    $validTeachSkills = [];
    $validTeachLevels = [];

    foreach ($teachSkills as $index => $skill) {
        $skill = trim($skill);

        if ($skill !== '') {
            $level = trim($teachLevels[$index] ?? '');

            if (!$this->isAllowedSkill($skill)) {
                $errors[] = "Selected teaching skill is not available: " . ucwords(str_replace(['-', '_'], ' ', $skill));
            } elseif ($level === '') {
                $skillName = ucwords(str_replace(['-', '_'], ' ', $skill));
                $errors[] = "Please select a proficiency level for teaching skill: {$skillName}";
            } else {
                $validTeachSkills[] = $skill;
                $validTeachLevels[] = $level;
            }
        }
    }

    $profilePicture = $_POST['existing_profile_picture'] ?? null;

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $file = $_FILES['profile_picture'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;

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
            }
        }
    }

    if (!empty($errors)) {
        $user = $this->userModel->getUserById($userId);
        $userSkills = $this->userModel->getUserSkills($userId);

        $data = [
            'user' => $user,
            'skills' => [
                'teaches' => $userSkills['teaches'],
                'learns' => $userSkills['learns']
            ],
            'errors' => $errors,
            'old' => $_POST,
            'availableSkills' => $this->userModel->getAllSkills()
        ];

        $this->view('users/edit_profile', $data);
        return;
    }

    try {
        $db = new Database();
        $db->query("START TRANSACTION");

        $db->query("UPDATE users SET
            username = :username,
            bio = :bio,
            profile_picture = :profile_picture
            WHERE id = :user_id");
        $db->bind(':username', $username);
        $db->bind(':bio', $bio);
        $db->bind(':profile_picture', $profilePicture);
        $db->bind(':user_id', $userId);

        if (!$db->execute()) {
            throw new Exception("Failed to update profile");
        }

        $db->query("DELETE FROM user_skills WHERE user_id = :user_id");
        $db->bind(':user_id', $userId);

        if (!$db->execute()) {
            throw new Exception("Failed to clear existing skills");
        }

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

        $db->query("UPDATE user_stats SET
            skills_taught_count = :teach_count,
            skills_learning_count = :learn_count
            WHERE user_id = :user_id");
        $db->bind(':teach_count', count($validTeachSkills));
        $db->bind(':learn_count', count($validLearnSkills));
        $db->bind(':user_id', $userId);

        if (!$db->execute()) {
            throw new Exception("Failed to update user stats");
        }

        $db->query("COMMIT");

        $_SESSION['username'] = $username;
        $_SESSION['success'] = "Profile updated successfully!";

        header('Location: ' . URLROOT . '/users/userprofile');
        exit;

    } catch (Exception $e) {
        if (isset($db)) {
            $db->query("ROLLBACK");
        }

        $user = $this->userModel->getUserById($userId);
        $userSkills = $this->userModel->getUserSkills($userId);

        $data = [
            'user' => $user,
            'skills' => [
                'teaches' => $userSkills['teaches'],
                'learns' => $userSkills['learns']
            ],
            'errors' => ["Error: " . $e->getMessage()],
            'old' => $_POST,
            'availableSkills' => $this->userModel->getAllSkills()
        ];

        $this->view('users/edit_profile', $data);
    }
}
}
