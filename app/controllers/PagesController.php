<?php
class PagesController extends Controller {

    public function index() {
        $db = new Database();

        $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'individual' AND status = 'active'");
        $users = $db->single();

        $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'organization' AND status = 'active'");
        $orgs = $db->single();

        $db->query("SELECT COUNT(*) as total FROM projects");
        $projects = $db->single();

        $db->query("SELECT COUNT(*) as total FROM skills");
        $skills = $db->single();

        $data = [
            'stats' => [
                'users'         => (int)($users->total ?? 0),
                'organizations' => (int)($orgs->total ?? 0),
                'projects'      => (int)($projects->total ?? 0),
                'skills'        => (int)($skills->total ?? 0),
            ]
        ];

        $this->view('home', $data);
    }
}