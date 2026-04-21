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
        $skills = $this->communityAdminModel->getSkillsWithoutCommunities();
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
            if($skill && $this->communityAdminModel->skillHasCommunity($skill->skill_name)) {
                $errors[] = 'A community for this skill already exists';
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

    public function postAnnouncement($communityId = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$communityId) {
            header('Location: ' . URLROOT . '/communityAdmin');
            exit;
        }

        $community = $this->communityAdminModel->getCommunityById($communityId);
        if (!$community) {
            $_SESSION['error'] = 'Community not found';
            header('Location: ' . URLROOT . '/communityAdmin');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if ($title === '' || $content === '') {
            $_SESSION['error'] = 'Announcement title and content are required';
            header('Location: ' . URLROOT . '/communityAdmin/viewCommunity/' . $communityId);
            exit;
        }

        $announcementId = $this->communityAdminModel->createAnnouncement(
            (int)$communityId,
            (int)($_SESSION['user_id'] ?? 1),
            $title,
            $content
        );

        if ($announcementId) {
            $this->communityAdminModel->logAction(
                (int)($_SESSION['user_id'] ?? 1),
                (int)$communityId,
                'announcement_posted',
                'Posted announcement in community: ' . $community->name
            );
            $_SESSION['success'] = 'Announcement posted successfully';
        } else {
            $_SESSION['error'] = 'Failed to post announcement';
        }

        header('Location: ' . URLROOT . '/communityAdmin/viewCommunity/' . $communityId);
        exit;
    }

    public function removePost() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/communityAdmin');
            exit;
        }

        $communityId = (int)($_POST['community_id'] ?? 0);
        $postId = (int)($_POST['post_id'] ?? 0);

        if (!$communityId || !$postId) {
            $_SESSION['error'] = 'Invalid post removal request';
            header('Location: ' . URLROOT . '/communityAdmin');
            exit;
        }

        $post = $this->communityAdminModel->getCommunityPostById($postId, $communityId);
        if (!$post) {
            $_SESSION['error'] = 'Post not found';
            header('Location: ' . URLROOT . '/communityAdmin/viewCommunity/' . $communityId);
            exit;
        }

        if ($this->communityAdminModel->deleteCommunityPost($postId, $communityId)) {
            $this->communityAdminModel->logAction(
                (int)($_SESSION['user_id'] ?? 1),
                $communityId,
                'post_removed',
                'Removed post by ' . ($post->author_name ?? 'user') . ': ' . ($post->title ?: (strlen((string)$post->content) > 60 ? substr((string)$post->content, 0, 57) . '...' : (string)$post->content))
            );
            $_SESSION['success'] = 'Post removed successfully';
        } else {
            $_SESSION['error'] = 'Failed to remove post';
        }

        header('Location: ' . URLROOT . '/communityAdmin/viewCommunity/' . $communityId);
        exit;
    }

    public function warnPostAuthor() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/communityAdmin');
            exit;
        }

        $communityId = (int)($_POST['community_id'] ?? 0);
        $postId = (int)($_POST['post_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if (!$communityId || !$postId || $reason === '') {
            $_SESSION['error'] = 'Warning reason is required';
            header('Location: ' . URLROOT . '/communityAdmin/viewCommunity/' . $communityId);
            exit;
        }

        $post = $this->communityAdminModel->getCommunityPostById($postId, $communityId);
        if (!$post) {
            $_SESSION['error'] = 'Post not found';
            header('Location: ' . URLROOT . '/communityAdmin/viewCommunity/' . $communityId);
            exit;
        }

        if ($this->communityAdminModel->sendPostWarning($postId, $communityId, (int)($_SESSION['user_id'] ?? 1), $reason)) {
            $this->communityAdminModel->logAction(
                (int)($_SESSION['user_id'] ?? 1),
                $communityId,
                'post_author_warned',
                'Warned post author ' . ($post->author_name ?? 'user') . ' for post #' . $postId
            );
            $_SESSION['success'] = 'Warning sent to post author';
        } else {
            $_SESSION['error'] = 'Failed to send warning';
        }

        header('Location: ' . URLROOT . '/communityAdmin/viewCommunity/' . $communityId);
        exit;
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
