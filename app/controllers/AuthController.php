<?php
class AuthController extends Controller {

    public function __construct() {
        // Ensure session is started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index() {
        $this->signin();
    }

    public function signin() {
        $this->view('auth/signin');
    }

    public function register() {
        $this->view('auth/register');
    }

    // Add these method aliases to match your form actions
    public function registerOrganization() {
        $this->processRegisterOrg();
    }

    public function registerIndividual() {
        $this->processRegisterInd();
    }

    public function processRegisterOrg() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $orgName = $_POST['org-name'] ?? '';
            $email = $_POST['org-email'] ?? '';
            $password = $_POST['org-password'] ?? '';
            $confirmPassword = $_POST['org-password-confirm'] ?? '';
            
            // Basic validation
            if (empty($orgName) || empty($email) || empty($password)) {
                // Set error session and redirect
                $_SESSION['error'] = 'All fields are required';
                header('Location: ' . URLROOT . '/auth/register');
                exit;
            }
            
            if ($password !== $confirmPassword) {
                $_SESSION['error'] = 'Passwords do not match';
                header('Location: ' . URLROOT . '/auth/register');
                exit;
            }
            
            // TODO: Save to database
            // For now, just redirect with success
            $_SESSION['success'] = 'Organization registered successfully!';
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }
    }

    public function processRegisterInd() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $fullName = $_POST['ind-fullname'] ?? '';
            $email = $_POST['ind-email'] ?? '';
            $password = $_POST['ind-password'] ?? '';
            $confirmPassword = $_POST['ind-password-confirm'] ?? '';
            
            // Basic validation
            if (empty($fullName) || empty($email) || empty($password)) {
                $_SESSION['error'] = 'All fields are required';
                header('Location: ' . URLROOT . '/auth/register');
                exit;
            }
            
            if ($password !== $confirmPassword) {
                $_SESSION['error'] = 'Passwords do not match';
                header('Location: ' . URLROOT . '/auth/register');
                exit;
            }
            
            // TODO: Save to database
            // For now, just redirect with success
            $_SESSION['success'] = 'Registration successful!';
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // TODO: Validate against database
            // For testing, just set session and redirect
            if (!empty($username) && !empty($password)) {
                $_SESSION['user_id'] = 123; // Mock user ID
                $_SESSION['username'] = $username;
                header('Location: ' . URLROOT . '/users/userprofile');
                exit;
            }
            
            $_SESSION['error'] = 'Invalid credentials';
            header('Location: ' . URLROOT . '/auth/signin');
        } else {
            $this->view('auth/signin');
        }
    }
}