<?php

class ProjectController extends Controller
{
    private $projectModel;

    public function __construct()
    {
        $this->projectModel = $this->model('Project');
    }

    // List all projects for logged org
    public function index()
    {
        $org_id = $_SESSION['user_id'];

        $projects = $this->projectModel->getByOrg($org_id);

        $this->view('organization/projects', ['projects' => $projects]);
    }

    // Show create form
    public function create()
    {
        $this->view('organization/createProject');
    }

    // Store project
    public function store()
    {
        $data = [
            'organization_id' => $_SESSION['user_id'],
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'required_skills' => $_POST['required_skills'],
            'max_members' => $_POST['max_members'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date']
        ];

        $this->projectModel->create($data);

        header("Location: /SkillXchange/public/ProjectController/index");
        exit;
    }

    // Edit form
    public function edit($id)
    {
        $project = $this->projectModel->getProject($id);
        $this->view('organization/editProject', ['project' => $project]);
    }

    // Update action
    public function update($id)
    {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'category' => $_POST['category'],
            'required_skills' => $_POST['required_skills'],
            'max_members' => $_POST['max_members'],
            'start_date' => $_POST['start_date'],
            'end_date' => $_POST['end_date']
        ];

        $this->projectModel->update($id, $data);

        header("Location: /SkillXchange/public/ProjectController/index");
        exit;
    }

    // Delete
    public function delete($id)
    {
        $this->projectModel->delete($id);

        header("Location: /SkillXchange/public/ProjectController/index");
        exit;
    }
}
