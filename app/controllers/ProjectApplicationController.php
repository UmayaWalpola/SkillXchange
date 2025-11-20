<?php
// app/controllers/ProjectApplicationController.php

class ProjectApplicationController extends Controller {
    private $projectModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit();
        }

        $this->projectModel = $this->model('Project');
    }

    // POST: /ProjectApplication/apply/{id}
    public function apply($projectId = null) {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (!$projectId) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid project.']);
                return;
            }
            $_SESSION['error'] = 'Invalid project.';
            header('Location: ' . URLROOT . '/');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
                return;
            }
            header('Location: ' . URLROOT . '/project/detail/' . $projectId);
            exit();
        }

        // Prevent organizations from applying
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'organization') {
            $_SESSION['error'] = 'Organizations cannot apply to projects.';
            header('Location: ' . URLROOT . '/project/detail/' . $projectId);
            exit();
        }

        // Accept both legacy and new field names for compatibility
        $experience = trim($_POST['experience'] ?? $_POST['relevant_experience'] ?? '');
        $skills = trim($_POST['skills'] ?? $_POST['matching_skills'] ?? '');
        $contribution = trim($_POST['contribution'] ?? '');
        $commitment = trim($_POST['commitment'] ?? $_POST['availability'] ?? '');
        $duration = trim($_POST['duration'] ?? $_POST['expected_duration'] ?? '');
        $motivation = trim($_POST['motivation'] ?? '');
        $portfolio = isset($_POST['portfolio']) ? trim($_POST['portfolio']) : (isset($_POST['portfolio_link']) ? trim($_POST['portfolio_link']) : '');

        // Validate required fields
        $missing = [];
        if ($experience === '') $missing[] = 'experience';
        if ($skills === '') $missing[] = 'skills';
        if ($contribution === '') $missing[] = 'contribution';
        if ($commitment === '') $missing[] = 'commitment';
        if ($duration === '') $missing[] = 'duration';
        if ($motivation === '') $missing[] = 'motivation';

        // Validate agreement (legacy name agree_terms or new name agreement)
        $agreed = false;
        if (isset($_POST['agree_terms']) && ($_POST['agree_terms'] === 'on' || $_POST['agree_terms'] === '1')) $agreed = true;
        if (isset($_POST['agreement']) && ($_POST['agreement'] === 'on' || $_POST['agreement'] === '1' || $_POST['agreement'] === 'true')) $agreed = true;

        if (!$agreed) $missing[] = 'agreement';

        if (!empty($missing)) {
            $msg = 'Please fill in all required fields.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $msg]);
                return;
            }
            $_SESSION['error'] = $msg;
            header('Location: ' . URLROOT . '/project/detail/' . $projectId);
            exit();
        }

        // Collect application data
        $projectId = (int)$projectId;
        // Fields already normalized above
        
        $userId = $_SESSION['user_id'];

        $applied = $this->projectModel->applyToProjectAdvanced($projectId, $userId, $experience, $skills, $contribution, $commitment, $duration, $motivation, $portfolio);

        if ($isAjax) {
            header('Content-Type: application/json');
            if ($applied) {
                echo json_encode(['success' => true, 'message' => 'Application submitted successfully! The organization will review your application.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'You have already applied to this project or an error occurred.']);
            }
            return;
        }

        if ($applied) {
            $_SESSION['success'] = 'Application submitted successfully! The organization will review your application.';
        } else {
            $_SESSION['error'] = 'You have already applied to this project or an error occurred.';
        }

        header('Location: ' . URLROOT . '/project/detail/' . $projectId);
        exit();
    }

    // POST: /ProjectApplication/cancel/{id}
    public function cancel($projectId = null) {
        if (!$projectId) {
            $_SESSION['error'] = 'Invalid project.';
            header('Location: ' . URLROOT . '/');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URLROOT . '/project/detail/' . $projectId);
            exit();
        }

        $userId = $_SESSION['user_id'];
        if ($this->projectModel->cancelApplication($projectId, $userId)) {
            $_SESSION['success'] = 'Application cancelled successfully.';
        } else {
            $_SESSION['error'] = 'Unable to cancel application.';
        }

        header('Location: ' . URLROOT . '/project/detail/' . $projectId);
        exit();
    }
}
?>
