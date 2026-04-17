<?php
class CommunityController extends Controller {

    private $communityModel;

    public function __construct() {
        require_once "../app/models/Community.php";
        $this->communityModel = new Community();
    }

    // Redirect to user dashboard communities
    public function index() {
        header('Location: ' . URLROOT . '/userdashboard/communities');
        exit;
    }
}