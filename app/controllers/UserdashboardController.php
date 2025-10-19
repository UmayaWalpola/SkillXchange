<?php

class UserdashboardController extends Controller {
    
    public function __construct() {
        // Start session if not started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // TODO: Load User model when database is ready
        // $this->userModel = $this->model('User');
    }
    
    // Check if user is logged in and is a regular user
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/signin');
            exit;
        }
        
        // Optional: Check if user role is 'user' (not admin or manager)
        // Uncomment if you're using roles
        // if (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'user') {
        //     header('Location: /dashboard');
        //     exit;
        // }
        
        return $_SESSION['user_id'];
    }

    // MAIN PAGE - Profile/Dashboard Landing Page
    public function index() {
        $userId = $this->checkAuth();
        
        // Get user data
        $userData = $this->getUserData($userId);
        $userSkills = $this->getUserSkills($userId);
        $userProjects = $this->getUserProjects($userId);
        $userFeedback = $this->getUserFeedback($userId);
        
        // DEBUG: Check if data is correct
        if (!is_array($userData)) {
            die("ERROR: getUserData returned: " . print_r($userData, true));
        }
        
        $data = [
            'title' => 'My Profile',
            'user' => $userData,
            'skills' => $userSkills,
            'projects' => $userProjects,
            'feedback' => $userFeedback,
            'page' => 'profile'
        ];
        
        $this->view('users/profile', $data);
    }

    // Notifications page
    public function notifications() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        // TODO: Get notifications from database
        
        $data = [
            'title' => 'Notifications',
            'user' => $user,
            'page' => 'notifications',
            'notifications' => [] // Add your notifications data here
        ];
        
        $this->view('users/notifications', $data);
    }

    // Chats page
    public function chats() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        // TODO: Get chats from database
        
        $data = [
            'title' => 'Chats',
            'user' => $user,
            'page' => 'chats',
            'chats' => [] // Add your chats data here
        ];
        
        $this->view('users/chats', $data);
    }

    // Matches page
    public function matches() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        // TODO: Get matches from database
        
        $data = [
            'title' => 'Matches',
            'user' => $user,
            'page' => 'matches',
            'matches' => [] // Add your matches data here
        ];
        
        $this->view('users/matches', $data);
    }

    // Communities page
    public function communities() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        // TODO: Get communities from database
        
        $data = [
            'title' => 'Communities',
            'user' => $user,
            'page' => 'communities',
            'communities' => [] // Add your communities data here
        ];
        
        $this->view('users/communities', $data);
    }

    // Quiz page - List all quizzes
    public function quiz() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        $quizzes = $this->getAllQuizzes();
        
        $data = [
            'title' => 'Quiz',
            'user' => $user,
            'page' => 'quiz',
            'quizzes' => $quizzes
        ];
        
        $this->view('users/quiz', $data);
    }

    // Take Quiz page - Individual quiz
    public function takeQuiz($quizId = null) {
        $userId = $this->checkAuth();
        
        // If no quiz ID provided, redirect back to quiz list
        if (!$quizId) {
            header('Location: ' . URLROOT . '/userdashboard/quiz');
            exit;
        }
        
        $user = $this->getUserData($userId);
        $quiz = $this->getQuizById($quizId);
        
        // If quiz not found, redirect back
        if (!$quiz) {
            header('Location: ' . URLROOT . '/userdashboard/quiz');
            exit;
        }
        
        $data = [
            'title' => $quiz['title'],
            'user' => $user,
            'page' => 'quiz',
            'quiz' => $quiz
        ];
        
        $this->view('users/take_quiz', $data);
    }

    // Projects page
    public function projects() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        // TODO: Get projects from database
        
        $data = [
            'title' => 'Projects',
            'user' => $user,
            'page' => 'projects',
            'projects' => [] // Add your projects data here
        ];
        
        $this->view('users/projects', $data);
    }

    // Wallet page
    public function wallet() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        
        // Mock wallet data
        $sentTransactions = [
            ['receiver' => 'John Doe', 'amount' => 50, 'timestamp' => '2 hours ago'],
            ['receiver' => 'Jane Smith', 'amount' => 30, 'timestamp' => '1 day ago'],
            ['receiver' => 'Bob Wilson', 'amount' => 20, 'timestamp' => '3 days ago']
        ];
        
        $receivedTransactions = [
            ['sender' => 'Alice Brown', 'amount' => 75, 'timestamp' => '1 hour ago'],
            ['sender' => 'Mike Johnson', 'amount' => 45, 'timestamp' => '2 days ago'],
            ['sender' => 'Sarah Davis', 'amount' => 60, 'timestamp' => '4 days ago']
        ];
        
        // Calculate totals
        $totalSent = array_sum(array_column($sentTransactions, 'amount'));
        $totalReceived = array_sum(array_column($receivedTransactions, 'amount'));
        $balance = 250; // Mock balance
        
        $data = [
            'title' => 'Wallet',
            'user' => $user,
            'page' => 'wallet',
            'balance' => $balance,
            'totalSent' => $totalSent,
            'totalReceived' => $totalReceived,
            'sentTransactions' => $sentTransactions,
            'receivedTransactions' => $receivedTransactions
        ];
        
        $this->view('users/wallet', $data);
    }

    // ==================================================
    // MOCK DATA METHODS (Replace with database later)
    // ==================================================
    
    private function getUserData($userId) {
        return [
            'id' => $userId,
            'name' => 'Sarah Johnson',
            'username' => 'sarahjohnson',
            'email' => 'sarah@example.com',
            'bio' => 'Passionate educator and developer.',
            'avatar' => 'SJ',
            'connections' => 87,
            'skills_taught' => 24,
            'skills_learning' => 12,
            'rating' => 4.8,
            'reviews_count' => 24
        ];
    }

    private function getUserSkills($userId) {
        return [
            'teaches' => [
                ['name' => 'Web Development', 'level' => 'Advanced'],
                ['name' => 'UI/UX Design', 'level' => 'Intermediate'],
                ['name' => 'JavaScript', 'level' => 'Advanced'],
                ['name' => 'React', 'level' => 'Intermediate']
            ],
            'learns' => [
                ['name' => 'Data Science', 'level' => 'Beginner'],
                ['name' => 'Machine Learning', 'level' => 'Beginner'],
                ['name' => 'Python', 'level' => 'Intermediate']
            ]
        ];
    }

    private function getUserProjects($userId) {
        return [
            'completed' => [
                ['title' => 'E-commerce Redesign', 'description' => 'Improved conversion by 25%.'],
                ['title' => 'Portfolio Website', 'description' => 'Built responsive portfolio site.']
            ],
            'in_progress' => [
                ['title' => 'AI Chatbot', 'description' => 'Building an NLP chatbot.']
            ]
        ];
    }

    private function getUserFeedback($userId) {
        return [
            ['reviewer_name' => 'Alex Chen', 'date' => '2 weeks ago', 'rating' => 5, 'comment' => 'Excellent mentor! Very helpful.'],
            ['reviewer_name' => 'Maria Garcia', 'date' => '1 month ago', 'rating' => 5, 'comment' => 'Great teacher, patient and clear.']
        ];
    }

    private function getAllQuizzes() {
        return [
            [
                'id' => 1,
                'title' => 'Programming Fundamentals',
                'description' => 'Explore core programming concepts using Python, JavaScript, or Java—covering variables, loops, functions, OOP, and error handling.',
                'category' => 'Programming',
                'difficulty' => 'Beginner',
                'status' => 'not_started'
            ],
            [
                'id' => 2,
                'title' => 'Frontend Development',
                'description' => 'Dive into HTML, CSS, and JavaScript fundamentals, plus component-based frameworks like React or Vue and state management tools.',
                'category' => 'Frontend',
                'difficulty' => 'Advanced',
                'status' => 'completed'
            ],
            [
                'id' => 3,
                'title' => 'System Design & Architecture',
                'description' => 'Understand microservices, caching, scalability, and architectural trade-offs like the CAP theorem.',
                'category' => 'System',
                'difficulty' => 'Intermediate',
                'status' => 'saved'
            ],
            [
                'id' => 4,
                'title' => 'UI/UX Design',
                'description' => 'Master wireframing, user flows, accessibility, responsive layouts, and palette-driven design systems.',
                'category' => 'UI/UX',
                'difficulty' => 'Intermediate',
                'status' => 'not_started'
            ],
            [
                'id' => 5,
                'title' => 'Database Design & Management',
                'description' => 'Learn SQL and NoSQL fundamentals, normalization, indexing, ER modeling, and query optimization.',
                'category' => 'Database',
                'difficulty' => 'Advanced',
                'status' => 'saved',
                'isPremium' => true
            ],
            [
                'id' => 6,
                'title' => 'Cybersecurity Basics',
                'description' => 'Cover authentication, secure coding, encryption, and role-based access control with threat modeling.',
                'category' => 'Cyber',
                'difficulty' => 'Beginner',
                'status' => 'completed'
            ],
            [
                'id' => 7,
                'title' => 'DevOps & Deployment',
                'description' => 'Build CI/CD pipelines, work with Docker, and deploy to cloud platforms like Vercel, Netlify, or AWS.',
                'category' => 'Devops',
                'difficulty' => 'Advanced',
                'status' => 'not_started'
            ],
            [
                'id' => 8,
                'title' => 'Version Control & Collaboration',
                'description' => 'Master Git workflows, branching strategies, pull requests, and collaborative code reviews.',
                'category' => 'Version Control',
                'difficulty' => 'Intermediate',
                'status' => 'not_started'
            ]
        ];
    }

   private function getQuizById($quizId) {
    $quizzes = [
        1 => [
            'id' => 1,
            'title' => 'Programming Fundamentals',
            'description' => 'Test your knowledge of core programming concepts',
            'category' => 'Programming',
            'difficulty' => 'Beginner',
            'questions' => [
                [
                    'id' => 1,
                    'question' => 'What is a variable?',
                    'options' => [
                        'A container for storing data values',
                        'A type of loop',
                        'A function declaration',
                        'A class definition'
                    ],
                    'correct' => 0
                ],
                [
                    'id' => 2,
                    'question' => 'Which of these is a programming loop?',
                    'options' => [
                        'if-else',
                        'for',
                        'switch',
                        'function'
                    ],
                    'correct' => 1
                ],
                [
                    'id' => 3,
                    'question' => 'What does OOP stand for?',
                    'options' => [
                        'Object Oriented Programming',
                        'Only One Process',
                        'Open Object Protocol',
                        'Operative Output Processing'
                    ],
                    'correct' => 0
                ],
                [
                    'id' => 4,
                    'question' => 'What is a function?',
                    'options' => [
                        'A reusable block of code',
                        'A type of variable',
                        'A loop structure',
                        'A database query'
                    ],
                    'correct' => 0
                ],
                [
                    'id' => 5,
                    'question' => 'What is debugging?',
                    'options' => [
                        'Writing new code',
                        'Finding and fixing errors in code',
                        'Deleting old code',
                        'Compiling code'
                    ],
                    'correct' => 1
                ]
            ]
        ],
        2 => [
            'id' => 2,
            'title' => 'Frontend Development',
            'description' => 'Test your HTML, CSS, and JavaScript knowledge',
            'category' => 'Frontend',
            'difficulty' => 'Advanced',
            'questions' => [
                [
                    'id' => 1,
                    'question' => 'What does HTML stand for?',
                    'options' => [
                        'Hyper Text Markup Language',
                        'High Tech Modern Language',
                        'Home Tool Markup Language',
                        'Hyperlinks and Text Markup Language'
                    ],
                    'correct' => 0
                ],
                [
                    'id' => 2,
                    'question' => 'Which CSS property controls text size?',
                    'options' => [
                        'text-style',
                        'font-size',
                        'text-size',
                        'font-style'
                    ],
                    'correct' => 1
                ],
                [
                    'id' => 3,
                    'question' => 'What is the correct syntax for referring to an external script?',
                    'options' => [
                        '<script href="app.js">',
                        '<script name="app.js">',
                        '<script src="app.js">',
                        '<script file="app.js">'
                    ],
                    'correct' => 2
                ],
                [
                    'id' => 4,
                    'question' => 'Which HTML tag is used to define an internal style sheet?',
                    'options' => [
                        '<css>',
                        '<script>',
                        '<style>',
                        '<link>'
                    ],
                    'correct' => 2
                ],
                [
                    'id' => 5,
                    'question' => 'How do you select an element with id "demo" in CSS?',
                    'options' => [
                        '.demo',
                        '*demo',
                        '#demo',
                        'demo'
                    ],
                    'correct' => 2
                ]
            ]
        ],
        3 => [
            'id' => 3,
            'title' => 'System Design & Architecture',
            'description' => 'Test your system design knowledge',
            'category' => 'System',
            'difficulty' => 'Intermediate',
            'questions' => [
                [
                    'id' => 1,
                    'question' => 'What does CAP theorem stand for?',
                    'options' => [
                        'Consistency, Availability, Partition tolerance',
                        'Cache, API, Protocol',
                        'Client, Application, Protocol',
                        'Code, Analysis, Performance'
                    ],
                    'correct' => 0
                ],
                [
                    'id' => 2,
                    'question' => 'What is a microservice?',
                    'options' => [
                        'A small database',
                        'An independent deployable service',
                        'A type of API',
                        'A caching mechanism'
                    ],
                    'correct' => 1
                ],
                [
                    'id' => 3,
                    'question' => 'What is horizontal scaling?',
                    'options' => [
                        'Adding more RAM to a server',
                        'Adding more servers',
                        'Upgrading CPU',
                        'Increasing disk space'
                    ],
                    'correct' => 1
                ],
                [
                    'id' => 4,
                    'question' => 'What is a load balancer?',
                    'options' => [
                        'A database backup tool',
                        'A system that distributes traffic across servers',
                        'A caching system',
                        'A monitoring tool'
                    ],
                    'correct' => 1
                ],
                [
                    'id' => 5,
                    'question' => 'What is caching used for?',
                    'options' => [
                        'Storing passwords',
                        'Improving performance by storing frequently accessed data',
                        'Creating backups',
                        'Encrypting data'
                    ],
                    'correct' => 1
                ]
            ]
        ],
        4 => [
            'id' => 4,
            'title' => 'UI/UX Design',
            'description' => 'Test your design principles knowledge',
            'category' => 'UI/UX',
            'difficulty' => 'Intermediate',
            'questions' => [
                [
                    'id' => 1,
                    'question' => 'What does UX stand for?',
                    'options' => [
                        'User Exchange',
                        'User Experience',
                        'Universal Experience',
                        'User Extension'
                    ],
                    'correct' => 1
                ],
                [
                    'id' => 2,
                    'question' => 'What is a wireframe?',
                    'options' => [
                        'A finished design',
                        'A basic visual guide of a layout',
                        'A color palette',
                        'A type of font'
                    ],
                    'correct' => 1
                ],
                [
                    'id' => 3,
                    'question' => 'What is the purpose of user personas?',
                    'options' => [
                        'To decorate the design',
                        'To represent target users',
                        'To test the website',
                        'To choose colors'
                    ],
                    'correct' => 1
                ],
                [
                    'id' => 4,
                    'question' => 'What is responsive design?',
                    'options' => [
                        'Design that responds to user clicks',
                        'Design that adapts to different screen sizes',
                        'Design that loads quickly',
                        'Design with animations'
                    ],
                    'correct' => 1
                ],
                [
                    'id' => 5,
                    'question' => 'What is accessibility in web design?',
                    'options' => [
                        'Making websites load faster',
                        'Making websites usable for people with disabilities',
                        'Making websites look modern',
                        'Making websites secure'
                    ],
                    'correct' => 1
                ]
            ]
        ],
        5 => [
            'id' => 5,
            'title' => 'Database Design & Management',
            'description' => 'Test your database knowledge',
            'category' => 'Database',
            'difficulty' => 'Advanced',
            'questions' => [
                [
                    'id' => 1,
                    'question' => 'What does SQL stand for?',
                    'options' => [
                        'Structured Query Language',
                        'Simple Question Language',
                        'System Query Language',
                        'Standard Question Language'
                    ],
                    'correct' => 0
                ],
                [
                    'id' => 2,
                    'question' => 'What is a primary key?',
                    'options' => [
                        'A password',
                        'A unique identifier for a record',
                        'A foreign key',
                        'An index'
                    ],
                    'correct' => 1
                ],
                [
                    'id' => 3,
                    'question' => 'What is normalization?',
                    'options' => [
                        'Making data normal',
                        'Organizing data to reduce redundancy',
                        'Backing up data',
                        'Encrypting data'
                    ],
                    'correct' => 1
                ]
            ]
        ]
    ];
    
    return $quizzes[$quizId] ?? null;
}
}