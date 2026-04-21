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

    // CODECHECK GUIDE: GET request for registration only loads the form.
    // Add new registration fields in app/views/auth/register.php first, then read them in the POST handlers below.
    public function register() {
        $data = [
            'errors' => [],
            'success' => ''
        ];
        $this->view('auth/register', $data);
    }

    // CODECHECK GUIDE: Organization registration flow.
    // Form names use the "org-" prefix, validation happens here, and the final insert is in User::registerOrganization().
    public function registerOrganization() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            
            $name = trim($_POST['org-name'] ?? '');
            $email = trim($_POST['org-email'] ?? '');
            $password = $_POST['org-password'] ?? '';
            $confirm = $_POST['org-password-confirm'] ?? '';
            $file = $_FILES['org-cert'] ?? null;

            // Validate required organization fields before touching the database.
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

            // Validate and store the organization certificate before creating the account.
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
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $fileName = uniqid('org_', true) . '.' . $extension;
                    $diskDir = __DIR__ . '/../../public/uploads/org_certs/';
                    if (!is_dir($diskDir)) {
                        mkdir($diskDir, 0755, true);
                    }

                    $diskPath = $diskDir . $fileName;
                    $publicRelativePath = 'uploads/org_certs/' . $fileName;
                    
                    if (!move_uploaded_file($file['tmp_name'], $diskPath)) {
                        $errors[] = "Failed to upload certificate.";
                        $publicRelativePath = null;
                    }
                }
            } else {
                $errors[] = "Certificate file is required.";
            }

            // Only call the model when every validation rule has passed.
            if (empty($errors)) {
                if ($this->userModel->registerOrganization($name, $email, $password, $publicRelativePath)) {
                    $_SESSION['success'] = "Organization registered successfully! Please login.";
                    header("Location: " . URLROOT . "/auth/signin");
                    exit;
                } else {
                    $errors[] = "Registration failed. Email may already be in use.";
                    // Delete uploaded file if registration failed
                    if (!empty($diskPath) && file_exists($diskPath)) {
                        unlink($diskPath);
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

    // CODECHECK GUIDE: Individual registration flow.
    // For tasks like "add phone", read $_POST here, validate it, then pass it to User::registerIndividual().
    public function registerIndividual() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            
            $name = trim($_POST['ind-fullname'] ?? '');
            $email = trim($_POST['ind-email'] ?? '');
            $password = $_POST['ind-password'] ?? '';
            $confirm = $_POST['ind-password-confirm'] ?? '';

            // Validate required individual fields before creating the user row.
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

            // Successful registration auto-logs the user in and sends them to profile setup.
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

    // CODECHECK GUIDE: Login flow.
    // User::login() verifies the password hash; this controller only sets session values and redirects by role.
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

            // 1. Check for Suspension String (Special return value we created)
            if (is_string($user) && strpos($user, 'suspended|') === 0) {
                http_response_code(404);
                $this->view('errors/404');
                return;
            }

            // 2. Standard Login Success
            if (is_array($user)) {
                // Session variables control role-based access across the app.
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_completed'] = $user['profile_completed'] ?? 1;

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
                    // Organizations go to their profile page (landing page)
                    header("Location: " . URLROOT . "/organization/profile");
                    exit;
                } elseif ($user['role'] === 'manager') {
                    // Manager go to manager dashboard
                    header("Location: " . URLROOT . "/manager");
                    exit;
                } elseif ($user['role'] === 'admin') {
                    // Admins go to admin dashboard
                    header("Location: " . URLROOT . "/admin");
                    exit;
                } elseif ($user['role'] === 'community_admin') {
                    // Community admins go to community dashboard
                    header("Location: " . URLROOT . "/community");
                    exit;
                } elseif ($user['role'] === 'quiz_manager') {
                    // Quiz managers go to quiz management dashboard
                    header("Location: " . URLROOT . "/quizmanager");
                    exit;
                } else {
                    // Default fallback
                    header("Location: " . URLROOT . "/home");
                    exit;
                }
            } else {
                // 3. Login Failed (Wrong password)
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

    public function forgotPassword() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
 
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $data = ['error' => 'Please enter a valid email.', 'email' => $email];
            $this->view('auth/forgot_password', $data);
            return;
        }
 
        $otp = $this->userModel->createPasswordResetOTP($email);
 
        if ($otp) {
            // Send OTP email
            require_once dirname(__DIR__) . '/helpers/Mailer.php';
            $mailer = new Mailer();
            $sent = $mailer->sendPasswordResetOTP($email, $otp);
 
            if (!$sent) {
                error_log("OTP email failed for {$email}, OTP: {$otp}");
            }
        }
 
        // Always show success (don't reveal if email exists)
        $_SESSION['reset_email'] = $email;
        $data = [
            'success' => 'If that email exists, an OTP has been sent. Check your inbox.',
            'email' => $email
        ];
        $this->view('auth/forgot_password', $data);
 
    } else {
        $data = ['error' => '', 'email' => ''];
        $this->view('auth/forgot_password', $data);
    }
}
 
    // Handle OTP verification + password reset
    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $otp      = trim($_POST['otp'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';
    
            $errors = [];
    
            if (empty($otp) || strlen($otp) !== 6) {
                $errors[] = 'Please enter the 6-digit OTP.';
            }
            if (strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters.';
            }
            if ($password !== $confirm) {
                $errors[] = 'Passwords do not match.';
            }
    
            if (empty($errors)) {
                if ($this->userModel->resetPasswordByOTP($email, $otp, $password)) {
                    $_SESSION['success'] = 'Password reset successful! Please log in.';
                    header("Location: " . URLROOT . "/auth/signin");
                    exit;
                } else {
                    $errors[] = 'Invalid or expired OTP. Please try again.';
                }
            }
    
            $data = [
                'error' => implode(' ', $errors),
                'email' => $email,
                'otp'   => $otp
            ];
            $this->view('auth/reset_password', $data);
    
        } else {
            $data = [
            'error' => '',
            'email' => $_GET['email'] ?? '',
            'otp'   => ''
        ];
        $this->view('auth/reset_password', $data);
            }
    }
 
}
