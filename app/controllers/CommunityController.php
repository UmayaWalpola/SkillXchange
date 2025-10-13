<?php
class CommunityController extends Controller {

    public function index() {
        // Demo data
        $data = [
            'communities' => [
                ['name' => 'Web Dev Enthusiasts', 'members' => '1.2k', 'image' => 'image1.jpg', 'description' => 'A community for web developers'],
                ['name' => 'UI Design Innovators', 'members' => '980', 'image' => 'image2.jpg', 'description' => 'A community for designers'],
                ['name' => 'Tech Innovators', 'members' => '2.3k', 'image' => 'image3.jpg', 'description' => 'For those passionate about technology'],
            ]
        ];

        $this->view('community/index', $data);
    }
}
