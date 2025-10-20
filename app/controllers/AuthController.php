<?php
class AuthController extends Controller {

    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        session_start();
    }

    // 🔹 Show registration page
    public function register() {
        $this->view('auth/register');
    }

    // 🔹 Register Organization
    public function registerOrganization() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['org-name']);
            $email = trim($_POST['org-email']);
            $password = $_POST['org-password'];
            $confirm = $_POST['org-password-confirm'];
            $file = $_FILES['org-cert'];

            if ($password !== $confirm) {
                echo "Passwords do not match.";
                return;
            }

            // Handle file upload
            $filePath = null;
            if ($file['error'] === 0) {
                $uploadDir = '../public/uploads/org_certs/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileName = uniqid() . "_" . basename($file['name']);
                $filePath = $uploadDir . $fileName;
                move_uploaded_file($file['tmp_name'], $filePath);
            }

            // Register organization
            if ($this->userModel->registerOrganization($name, $email, $password, $filePath)) {
                echo "✅ Organization registered successfully!";
            } else {
                echo "❌ Registration failed.";
            }
        } else {
            $this->view('auth/register');
        }
    }

    // 🔹 Register Individual
    public function registerIndividual() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['ind-fullname']);
            $email = trim($_POST['ind-email']);
            $password = $_POST['ind-password'];
            $confirm = $_POST['ind-password-confirm'];

            if ($password !== $confirm) {
                echo "Passwords do not match.";
                return;
            }

            if ($this->userModel->registerIndividual($name, $email, $password)) {
                echo "✅ Individual registered successfully!";
            } else {
                echo "❌ Registration failed.";
            }
        } else {
            $this->view('auth/register');
        }
    }

    // 🔹 Show login page
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $user = $this->userModel->login($email, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                header("Location: " . URLROOT . "/home");
                exit;
            } else {
                echo "❌ Invalid email or password.";
            }
        } else {
            $this->view('auth/login');
        }
    }

    // 🔹 Logout
    public function logout() {
        session_destroy();
        header("Location: " . URLROOT . "/auth/login");
        exit;
    }
}

