<?php

class SkillsController extends Controller {
    
    public function __construct() {
        // Initialize if needed
    }

    /**
     * AJAX endpoint to load more skills
     * Accessed via: /skills/loadMore?offset=4
     */
    public function loadMore() {
        header('Content-Type: application/json');
        
        // Get offset from query parameter
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $limit = 4; // Load 4 skills per request
        
        // Define all available skills (hard-coded for now)
        $allSkills = [
            // First 4 are already shown on page load
            [
                'icon' => '💻',
                'title' => 'Web Development',
                'description' => 'Build modern, responsive websites and web applications using the latest technologies and frameworks.',
                'learners' => '150+ learners',
                'dataSkill' => 'webdev'
            ],
            [
                'icon' => '🎨',
                'title' => 'UI/UX Design',
                'description' => 'Create intuitive and beautiful user interfaces that enhance user experience and engagement.',
                'learners' => '120+ learners',
                'dataSkill' => 'uidesign'
            ],
            [
                'icon' => '🤖',
                'title' => 'Artificial Intelligence',
                'description' => 'Explore machine learning, deep learning, and AI applications in real-world scenarios.',
                'learners' => '200+ learners',
                'dataSkill' => 'ai'
            ],
            [
                'icon' => '📱',
                'title' => 'Mobile Development',
                'description' => 'Develop native and cross-platform mobile applications for iOS and Android devices.',
                'learners' => '80+ learners',
                'dataSkill' => 'mobile'
            ],
            // Additional skills for loading more
            [
                'icon' => '🔧',
                'title' => 'DevOps & CI/CD',
                'description' => 'Learn automation, continuous integration, deployment pipelines, and cloud infrastructure management.',
                'learners' => '90+ learners',
                'dataSkill' => 'devops'
            ],
            [
                'icon' => '🗄️',
                'title' => 'Database Management',
                'description' => 'Master SQL, NoSQL, schema design, query optimization, and database performance tuning.',
                'learners' => '110+ learners',
                'dataSkill' => 'database'
            ],
            [
                'icon' => '🔒',
                'title' => 'Cybersecurity',
                'description' => 'Understand security principles, ethical hacking, network security, and data protection strategies.',
                'learners' => '95+ learners',
                'dataSkill' => 'cybersecurity'
            ],
            [
                'icon' => '📊',
                'title' => 'Data Science',
                'description' => 'Analyze data, build predictive models, and derive insights using statistical methods and tools.',
                'learners' => '130+ learners',
                'dataSkill' => 'datascience'
            ],
            [
                'icon' => '☁️',
                'title' => 'Cloud Computing',
                'description' => 'Work with AWS, Azure, and Google Cloud to build scalable and reliable cloud-based solutions.',
                'learners' => '105+ learners',
                'dataSkill' => 'cloud'
            ],
            [
                'icon' => '🎮',
                'title' => 'Game Development',
                'description' => 'Create engaging games using Unity, Unreal Engine, and modern game development frameworks.',
                'learners' => '75+ learners',
                'dataSkill' => 'gamedev'
            ],
            [
                'icon' => '🌐',
                'title' => 'Blockchain',
                'description' => 'Learn blockchain technology, smart contracts, and decentralized application development.',
                'learners' => '65+ learners',
                'dataSkill' => 'blockchain'
            ],
            [
                'icon' => '📈',
                'title' => 'Digital Marketing',
                'description' => 'Master SEO, social media marketing, content strategy, and analytics for business growth.',
                'learners' => '140+ learners',
                'dataSkill' => 'marketing'
            ]
        ];
        
        // Get the slice of skills to return
        $skillsToReturn = array_slice($allSkills, $offset, $limit);
        $hasMore = ($offset + $limit) < count($allSkills);
        
        // Generate HTML for skill cards
        $html = '';
        foreach ($skillsToReturn as $index => $skill) {
            $animationDelay = ($index % 4) + 1;
            $html .= '<div class="skill-card fade-in animate-delay-' . $animationDelay . '" data-skill="' . $skill['dataSkill'] . '">';
            $html .= '  <div class="skill-icon">' . $skill['icon'] . '</div>';
            $html .= '  <h3 class="skill-name">' . htmlspecialchars($skill['title']) . '</h3>';
            $html .= '  <p class="skill-description">' . htmlspecialchars($skill['description']) . '</p>';
            $html .= '  <div class="skill-stats">';
            $html .= '    <span class="skill-learners">' . htmlspecialchars($skill['learners']) . '</span>';
            $html .= '  </div>';
            $html .= '</div>';
        }
        
        // Return JSON response
        echo json_encode([
            'success' => true,
            'html' => $html,
            'hasMore' => $hasMore,
            'nextOffset' => $offset + count($skillsToReturn)
        ]);
        
        exit;
    }
}
