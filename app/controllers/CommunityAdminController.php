<?php
class CommunityAdminController extends Controller {

    private $communityAdminModel;

    public function __construct() {
        require_once "../app/models/CommunityAdmin.php";
        $this->communityAdminModel = new CommunityAdmin();
    }

    public function index() {
        $communities = $this->communityAdminModel->getAllCommunities();
        $data = [
            'title' => 'Community Admin Dashboard',
            'communities' => $communities
        ];
        $this->view('cmmanager/dashboard', $data);
    }

    public function create() {
        $skills = $this->communityAdminModel->getAllSkills();
        $data = [
            'title' => 'Create New Community',
            'community_name' => '',
            'description' => '',
            'name_err' => '',
            'description_err' => '',
            'skills' => $skills
        ];
        $this->view('cmmanager/community_create', $data);
    }

    public function store() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');

            $json = file_get_contents('php://input');
            $communityData = json_decode($json, true);

            $skillId = isset($communityData['skill_id']) ? intval($communityData['skill_id']) : 0;
            $description = trim($communityData['description'] ?? '');

            $skill = $this->communityAdminModel->getSkillById($skillId);

            $errors = [];
            if(empty($skillId) || !$skill) {
                $errors[] = 'Valid skill selection is required';
            }
            if(empty($description)) {
                $errors[] = 'Description is required';
            } elseif(strlen($description) > 1000) {
                $errors[] = 'Description cannot exceed 1000 characters';
            }

            if(!empty($errors)) {
                echo json_encode(['success' => false, 'errors' => $errors]);
                exit;
            }

            $data = [
                'name' => $skill->skill_name,
                'description' => $description,
                'skill_id' => $skillId,
                'created_by' => $_SESSION['user_id'] ?? 1,
            ];

            $communityId = $this->communityAdminModel->create($data);

            if($communityId) {
                $this->communityAdminModel->logAction(
                    $_SESSION['user_id'] ?? 1,
                    $communityId,
                    'create',
                    'Community created: ' . $skill->skill_name
                );

                $_SESSION['success'] = 'Community "' . $skill->skill_name . '" created successfully';

                echo json_encode([
                    'success' => true,
                    'message' => 'Community created successfully!',
                    'redirect' => URLROOT . '/communityAdmin'
                ]);
            } else {
                echo json_encode(['success' => false, 'errors' => ['Failed to create community. Please try again.']]);
            }
            exit;
        } else {
            header('Location: ' . URLROOT . '/communityAdmin');
            exit;
        }
    }

    public function viewCommunity($id) {
        $community = $this->communityAdminModel->getCommunityById($id);

        if(!$community) {
            header('Location: ' . URLROOT . '/communityAdmin');
            exit;
        }

        $posts = $this->communityAdminModel->getCommunityPosts($id);
        $members = $this->communityAdminModel->getCommunityMembers($id);

        $data = [
            'title' => $community->name . ' - Feed',
            'community' => $community,
            'posts' => $posts,
            'members' => $members
        ];

        $this->view('cmmanager/community_view', $data);
    }

    public function activate() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if(empty($data['id'])) {
                echo json_encode(['success' => false, 'message' => 'Community ID is required']);
                exit;
            }

            $community = $this->communityAdminModel->getCommunityById($data['id']);
            if(!$community) {
                echo json_encode(['success' => false, 'message' => 'Community not found']);
                exit;
            }

            if($this->communityAdminModel->activateCommunity($data['id'])) {
                $this->communityAdminModel->logAction(
                    $_SESSION['user_id'] ?? 1,
                    $data['id'],
                    'activate',
                    'Community activated: ' . $community->name
                );

                $_SESSION['success'] = 'Community "' . $community->name . '" activated successfully';

                echo json_encode(['success' => true, 'message' => 'Community activated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to activate community']);
            }
            exit;
        }
    }

    public function deactivate() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if(empty($data['id'])) {
                echo json_encode(['success' => false, 'message' => 'Community ID is required']);
                exit;
            }

            $community = $this->communityAdminModel->getCommunityById($data['id']);
            if(!$community) {
                echo json_encode(['success' => false, 'message' => 'Community not found']);
                exit;
            }

            if($this->communityAdminModel->deactivateCommunity($data['id'])) {
                $this->communityAdminModel->logAction(
                    $_SESSION['user_id'] ?? 1,
                    $data['id'],
                    'deactivate',
                    'Community deactivated: ' . $community->name
                );

                $_SESSION['success'] = 'Community "' . $community->name . '" deactivated successfully';

                echo json_encode(['success' => true, 'message' => 'Community deactivated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to deactivate community']);
            }
            exit;
        }
    }

    public function delete() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if(empty($data['id'])) {
                echo json_encode(['success' => false, 'message' => 'Community ID is required']);
                exit;
            }

            $community = $this->communityAdminModel->getCommunityById($data['id']);
            if(!$community) {
                echo json_encode(['success' => false, 'message' => 'Community not found']);
                exit;
            }

            $this->communityAdminModel->logAction(
                $_SESSION['user_id'] ?? 1,
                $data['id'],
                'delete',
                'Community deleted: ' . $community->name
            );

            // Soft delete
            if($this->communityAdminModel->deactivateCommunity($data['id'])) {
                $_SESSION['success'] = 'Community "' . $community->name . '" deleted successfully';
                echo json_encode(['success' => true, 'message' => 'Community deleted successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete community']);
            }
            exit;
        }
    }

    public function addSkill() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $skillName = trim($data['skill_name'] ?? '');
        $description = trim($data['description'] ?? '');

        if (!$skillName) {
            echo json_encode(['success' => false, 'message' => 'Skill name is required']);
            exit;
        }

        if (strlen($skillName) > 100) {
            echo json_encode(['success' => false, 'message' => 'Skill name cannot exceed 100 characters']);
            exit;
        }

        $result = $this->communityAdminModel->createSkill($skillName, $description);

        if ($result['success']) {
            $this->communityAdminModel->logSkillCreation($_SESSION['user_id'] ?? 0, $skillName);
            echo json_encode([
                'success' => true,
                'message' => 'Skill added successfully',
                'skill_id' => $result['skill_id'],
                'skill_name' => $result['skill_name']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
        exit;
    }
}