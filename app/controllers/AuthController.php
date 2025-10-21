<?php
class AuthController extends Controller {

    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    // Default method - redirect to signin
    public function index() {
        $this->signin();
    }

    // Show registration page
    public function register() {
        $data = [
            'errors' => [],
            'success' => ''
        ];
        $this->view('auth/register', $data);
    }

    // Register Organization
    public function registerOrganization() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            
            $name = trim($_POST['org-name'] ?? '');
            $email = trim($_POST['org-email'] ?? '');
            $password = $_POST['org-password'] ?? '';
            $confirm = $_POST['org-password-confirm'] ?? '';
            $file = $_FILES['org-cert'] ?? null;

            // Validation
            if (empty($name)) $errors[] = "Organization name is required.";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Valid email is required.";
            }
            if (strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters.";
            }
            if ($password !== $confirm) {
                $errors[] = "Passwords do not match.";
            }

            // Handle file upload with validation
            $filePath = null;
            if ($file && $file['error'] === 0) {
                $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!in_array($file['type'], $allowedTypes)) {
                    $errors[] = "Only PDF, JPG, and PNG files are allowed.";
                }
                if ($file['size'] > $maxSize) {
                    $errors[] = "File size must not exceed 5MB.";
                }
                
                if (empty($errors)) {
                    $uploadDir = '../public/uploads/org_certs/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = uniqid('org_', true) . '.' . $extension;
                    $filePath = $uploadDir . $fileName;
                    
                    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                        $errors[] = "Failed to upload certificate.";
                        $filePath = null;
                    }
                }
            } else {
                $errors[] = "Certificate file is required.";
            }

            // Register if no errors
            if (empty($errors)) {
                if ($this->userModel->registerOrganization($name, $email, $password, $filePath)) {
                    $_SESSION['success'] = "Organization registered successfully! Please login.";
                    header("Location: " . URLROOT . "/auth/signin");
                    exit;
                } else {
                    $errors[] = "Registration failed. Email may already be in use.";
                    // Delete uploaded file if registration failed
                    if ($filePath && file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            }

            // Show errors
            $data = [
                'errors' => $errors,
                'old' => $_POST
            ];
            $this->view('auth/register', $data);
        } else {
            $this->register();
        }
    }

    // Register Individual
    public function registerIndividual() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            
            $name = trim($_POST['ind-fullname'] ?? '');
            $email = trim($_POST['ind-email'] ?? '');
            $password = $_POST['ind-password'] ?? '';
            $confirm = $_POST['ind-password-confirm'] ?? '';

            // Validation
            if (empty($name)) $errors[] = "Full name is required.";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Valid email is required.";
            }
            if (strlen($password) < 8) {
                $errors[] = "Password must be at least 8 characters.";
            }
            if ($password !== $confirm) {
                $errors[] = "Passwords do not match.";
            }

            // Register if no errors
            if (empty($errors)) {
                $userId = $this->userModel->registerIndividual($name, $email, $password);
                if ($userId) {
                    // Auto-login and redirect to profile setup
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['username'] = $name;
                    $_SESSION['role'] = 'individual';
                    $_SESSION['profile_completed'] = 0;
                    
                    // Award "Early Adopter" badge
                    $this->userModel->awardBadge($userId, 'Early Adopter', '🌟');
                    
                    // Redirect to profile setup
                    header("Location: " . URLROOT . "/users/profileSetup");
                    exit;
                } else {
                    $errors[] = "Registration failed. Email may already be in use.";
                }
            }

            // Show errors
            $data = [
                'errors' => $errors,
                'old' => $_POST
            ];
            $this->view('auth/register', $data);
        } else {
            $this->register();
        }
    }

    // Show signin page
public function signin() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $data = [
                'error' => 'Please provide both email and password.',
                'email' => $email
            ];
            $this->view('auth/signin', $data);
            return;
        }

        $user = $this->userModel->login($email, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['profile_completed'] = $user['profile_completed'];

            // Redirect based on role and profile completion
            if ($user['role'] === 'individual') {
                // Check if profile needs to be completed
                if (!$user['profile_completed']) {
                    header("Location: " . URLROOT . "/users/profileSetup");
                    exit;
                } else {
                    // Profile is complete, go to user profile
                    header("Location: " . URLROOT . "/users/userprofile");
                    exit;
                }
            } elseif ($user['role'] === 'organization') {
                // Organizations go to manager profile
                header("Location: " . URLROOT . "/users/managerprofile");
                exit;
            } elseif ($user['role'] === 'admin') {
                // Admins go to admin profile
                header("Location: " . URLROOT . "/users/adminprofile");
                exit;
            } else {
                // Default fallback
                header("Location: " . URLROOT . "/home");
                exit;
            }
        } else {
            $data = [
                'error' => 'Invalid email or password.',
                'email' => $email
            ];
            $this->view('auth/signin', $data);
        }
    } else {
        $data = [
            'error' => $_SESSION['error'] ?? '',
            'success' => $_SESSION['success'] ?? '',
            'email' => ''
        ];
        unset($_SESSION['error'], $_SESSION['success']);
        $this->view('auth/signin', $data);
    }
}
    // Logout
    public function logout() {
        session_destroy();
        header("Location: " . URLROOT . "/auth/signin");
        exit;
    }
}