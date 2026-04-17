<?php
class CommunityController extends Controller {

    private $communityModel;

    public function __construct() {
        require_once "../app/models/Community.php";
        $this->communityModel = new Community();
    }

    /**
     * Route dispatcher - Send to appropriate dashboard based on role
     */
    public function index() {
        // Check user role from session
        $role = $_SESSION['role'] ?? null;

        // Admin roles go to community admin dashboard
        if ($role === 'community_admin' || $role === 'manager' || $role === 'admin') {
            header('Location: ' . URLROOT . '/communityAdmin');
            exit;
        }

        // Regular users go to user community list
        header('Location: ' . URLROOT . '/userdashboard/communities');
        exit;
    }
}