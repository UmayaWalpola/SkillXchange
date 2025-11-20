<?php

class UserdashboardController extends Controller {
    
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    private function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /auth/signin');
            exit;
        }
        return $_SESSION['user_id'];
    }

    public function index() {
        $userId = $this->checkAuth();
        
        $userData = $this->getUserData($userId);
        $userSkills = $this->getUserSkills($userId);
        $userProjects = $this->getUserProjects($userId);
        $userFeedback = $this->getUserFeedback($userId);
        
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

    public function notifications() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        $notifications = $this->getNotifications($userId);
        
        $data = [
            'title' => 'Notifications',
            'user' => $user,
            'page' => 'notifications',
            'notifications' => $notifications
        ];
        
        $this->view('users/notifications', $data);
    }

    public function chats() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        $chats = $this->getChats($userId);
        
        $data = [
            'title' => 'Chats',
            'user' => $user,
            'page' => 'chats',
            'chats' => $chats
        ];
        
        $this->view('users/chats', $data);
    }

    public function matches() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        $teachMatches = $this->getTeachMatches($userId);
        $learnMatches = $this->getLearnMatches($userId);
        
        $data = [
            'title' => 'Matches',
            'user' => $user,
            'page' => 'matches',
            'teachMatches' => $teachMatches,
            'learnMatches' => $learnMatches
        ];
        
        $this->view('users/matches', $data);
    }

    public function communities() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        $communities = $this->getAllCommunities();
        
        $data = [
            'title' => 'Communities',
            'user' => $user,
            'page' => 'communities',
            'communities' => $communities
        ];
        
        $this->view('users/communities', $data);
    }

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

    public function takeQuiz($quizId = null) {
        $userId = $this->checkAuth();
        
        if (!$quizId) {
            header('Location: ' . URLROOT . '/userdashboard/quiz');
            exit;
        }
        
        $user = $this->getUserData($userId);
        $quiz = $this->getQuizById($quizId);
        
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

    public function projects() {
        $userId = $this->checkAuth();
        
        $projectModel = $this->model('Project');
        $projects = $projectModel->getProjectsForUser($userId);
        
        // Debug: Log what we're getting
        error_log('DEBUG: User ID = ' . $userId);
        error_log('DEBUG: Projects returned: ' . count($projects ?? []));
        
        // If no projects found, show all active projects (for testing/browse)
        if (empty($projects)) {
            error_log('DEBUG: No projects found for user, loading all active projects');
            $projects = $projectModel->getAllActiveProjects();
            error_log('DEBUG: Loaded ' . count($projects ?? []) . ' active projects');
        }
        
        $user = $this->getUserData($userId);
        
        $data = [
            'title' => 'Projects',
            'user' => $user,
            'page' => 'projects',
            'projects' => $projects ?? []
        ];
        
        $this->view('users/projects', $data);
    }

    public function wallet() {
        $userId = $this->checkAuth();
        
        $user = $this->getUserData($userId);
        
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
        
        $totalSent = array_sum(array_column($sentTransactions, 'amount'));
        $totalReceived = array_sum(array_column($receivedTransactions, 'amount'));
        $balance = 250;
        
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

    private function getNotifications($userId) {
        return [
            [
                'id' => 1,
                'type' => 'match',
                'icon' => '❤️',
                'title' => 'New Match!',
                'message' => 'You have a new match with Dr. Kamal Silva who teaches Data Science',
                'time' => '5 minutes ago',
                'read' => false
            ],
            [
                'id' => 2,
                'type' => 'message',
                'icon' => '💬',
                'title' => 'New Message',
                'message' => 'Sophia Chen sent you a message',
                'time' => '1 hour ago',
                'read' => false
            ],
            [
                'id' => 3,
                'type' => 'community',
                'icon' => '👥',
                'title' => 'Community Activity',
                'message' => 'New post in Web Development community',
                'time' => '2 hours ago',
                'read' => false
            ],
            [
                'id' => 4,
                'type' => 'project',
                'icon' => '📁',
                'title' => 'Project Update',
                'message' => 'AI Chatbot project deadline is coming up in 3 days',
                'time' => '5 hours ago',
                'read' => true
            ],
            [
                'id' => 5,
                'type' => 'match',
                'icon' => '❤️',
                'title' => 'Connection Request',
                'message' => 'Maya Patel wants to learn Web Development from you',
                'time' => '1 day ago',
                'read' => true
            ],
            [
                'id' => 6,
                'type' => 'system',
                'icon' => '🔔',
                'title' => 'Profile Complete',
                'message' => 'Your profile is now 100% complete! Start matching with others',
                'time' => '2 days ago',
                'read' => true
            ],
            [
                'id' => 7,
                'type' => 'community',
                'icon' => '👥',
                'title' => 'New Community',
                'message' => 'Check out the new Cloud Computing community',
                'time' => '3 days ago',
                'read' => true
            ],
            [
                'id' => 8,
                'type' => 'message',
                'icon' => '💬',
                'title' => 'Message Reply',
                'message' => 'Ethan Williams replied to your message',
                'time' => '4 days ago',
                'read' => true
            ]
        ];
    }

    private function getChats($userId) {
        return [
            [
                'id' => 1,
                'name' => 'Sophia Chen',
                'lastMessage' => 'Hey! Would love to learn Web Development from you',
                'time' => '5 min ago',
                'unread' => true,
                'unreadCount' => 3,
                'online' => true,
                'messages' => [
                    ['sender' => 'them', 'text' => 'Hi! I saw your profile', 'time' => '10:30 AM'],
                    ['sender' => 'them', 'text' => 'I\'m interested in learning Web Development', 'time' => '10:31 AM'],
                    ['sender' => 'me', 'text' => 'That\'s great! I\'d be happy to help', 'time' => '10:35 AM'],
                    ['sender' => 'them', 'text' => 'Hey! Would love to learn Web Development from you', 'time' => '10:40 AM']
                ]
            ],
            [
                'id' => 2,
                'name' => 'Dr. Kamal Silva',
                'lastMessage' => 'You: Thanks for the Data Science tips!',
                'time' => '1 hour ago',
                'unread' => false,
                'unreadCount' => 0,
                'online' => false,
                'messages' => [
                    ['sender' => 'them', 'text' => 'Ready to start your Data Science journey?', 'time' => '9:00 AM'],
                    ['sender' => 'me', 'text' => 'Yes! Where should I begin?', 'time' => '9:15 AM'],
                    ['sender' => 'them', 'text' => 'Start with Python basics and statistics', 'time' => '9:20 AM'],
                    ['sender' => 'me', 'text' => 'Thanks for the Data Science tips!', 'time' => '9:25 AM']
                ]
            ],
            [
                'id' => 3,
                'name' => 'Ethan Williams',
                'lastMessage' => 'Can we schedule a session for tomorrow?',
                'time' => '2 hours ago',
                'unread' => true,
                'unreadCount' => 1,
                'online' => true,
                'messages' => [
                    ['sender' => 'them', 'text' => 'Hi! I need help with UI/UX', 'time' => '8:00 AM'],
                    ['sender' => 'me', 'text' => 'Sure! What specifically?', 'time' => '8:05 AM'],
                    ['sender' => 'them', 'text' => 'Can we schedule a session for tomorrow?', 'time' => '8:10 AM']
                ]
            ],
            [
                'id' => 4,
                'name' => 'Maya Patel',
                'lastMessage' => 'You: Let me know when you\'re available',
                'time' => '1 day ago',
                'unread' => false,
                'unreadCount' => 0,
                'online' => false,
                'messages' => [
                    ['sender' => 'them', 'text' => 'Hello! Interested in JavaScript lessons', 'time' => 'Yesterday 3:00 PM'],
                    ['sender' => 'me', 'text' => 'Great! I can help with that', 'time' => 'Yesterday 3:15 PM'],
                    ['sender' => 'me', 'text' => 'Let me know when you\'re available', 'time' => 'Yesterday 3:20 PM']
                ]
            ],
            [
                'id' => 5,
                'name' => 'Linda Zhang',
                'lastMessage' => 'Thanks! That was really helpful',
                'time' => '2 days ago',
                'unread' => false,
                'unreadCount' => 0,
                'online' => false,
                'messages' => [
                    ['sender' => 'them', 'text' => 'Can you help me with Machine Learning?', 'time' => '2 days ago 10:00 AM'],
                    ['sender' => 'me', 'text' => 'I\'m also learning ML. Let\'s learn together!', 'time' => '2 days ago 10:30 AM'],
                    ['sender' => 'them', 'text' => 'Thanks! That was really helpful', 'time' => '2 days ago 11:00 AM']
                ]
            ]
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

    // ⭐ THIS IS THE CORRECTED METHOD - REPLACES YOUR OLD ONE
    private function getAllCommunities() {
        return [
            [
                'id' => 1,
                'name' => 'Web Development',
                'description' => 'HTML, CSS, JavaScript, React, and modern web technologies',
                'icon' => '🌐',
                'members' => 1250,
                'totalPosts' => 342,
                'about' => 'A community for web developers to share knowledge, ask questions, and collaborate on web development projects. From frontend frameworks to backend architectures.',
                'membersList' => [
                    ['id' => 2, 'name' => 'Sarah Chen', 'role' => 'Senior Developer'],
                    ['id' => 3, 'name' => 'Mike Johnson', 'role' => 'Full Stack Dev'],
                    ['id' => 4, 'name' => 'Emma Davis', 'role' => 'Frontend Specialist'],
                    ['id' => 5, 'name' => 'Alex Rodriguez', 'role' => 'React Expert'],
                    ['id' => 6, 'name' => 'Lisa Wang', 'role' => 'UI Developer']
                ],
                'posts' => [
                    [
                        'id' => 1,
                        'author' => 'Sarah Chen',
                        'authorId' => 2,
                        'title' => 'Best practices for React hooks?',
                        'content' => 'I\'ve been using hooks for a while now, but I\'m curious about everyone\'s best practices. What are your go-to patterns for useState and useEffect?',
                        'time' => '2 hours ago',
                        'likes' => 15,
                        'replies' => [
                            [
                                'id' => 1,
                                'author' => 'Mike Johnson',
                                'authorId' => 3,
                                'content' => 'Great question! I always try to keep my useEffect hooks focused on one thing.',
                                'time' => '1 hour ago'
                            ],
                            [
                                'id' => 2,
                                'author' => 'Emma Davis',
                                'authorId' => 4,
                                'content' => 'Custom hooks are your friend! Extract complex logic into reusable hooks.',
                                'time' => '45 minutes ago'
                            ]
                        ]
                    ],
                    [
                        'id' => 2,
                        'author' => 'Mike Johnson',
                        'authorId' => 3,
                        'title' => 'CSS Grid vs Flexbox in 2025',
                        'content' => 'Still debating when to use Grid vs Flexbox. Here\'s my take: Grid for 2D layouts, Flexbox for 1D. What do you think
                        ?',
                        'time' => '5 hours ago',
                        'likes' => 23,
                        'replies' => [
                            [
                                'id' => 1,
                                'author' => 'Alex Rodriguez',
                                'authorId' => 5,
                                'content' => 'Spot on! I use Grid for page layouts and Flexbox for components.',
                                'time' => '4 hours ago'
                            ]
                        ]
                    ],
                    [
                        'id' => 3,
                        'author' => 'Emma Davis',
                        'authorId' => 4,
                        'title' => 'Anyone tried the new Next.js 15?',
                        'content' => 'Just upgraded to Next.js 15 and the performance improvements are incredible! The new caching system is a game changer.',
                        'time' => '1 day ago',
                        'likes' => 45,
                        'replies' => []
                    ]
                ]
            ],
            [
                'id' => 2,
                'name' => 'Data Science & AI',
                'description' => 'Python, Machine Learning, Deep Learning, and Data Analytics',
                'icon' => '🤖',
                'members' => 856,
                'totalPosts' => 213,
                'about' => 'Connect with data scientists and AI enthusiasts. Share insights on ML algorithms, discuss latest research papers, and collaborate on data-driven projects.',
                'membersList' => [
                    ['id' => 7, 'name' => 'Dr. Kamal Silva', 'role' => 'ML Researcher'],
                    ['id' => 8, 'name' => 'Linda Zhang', 'role' => 'Data Analyst'],
                    ['id' => 9, 'name' => 'James Wilson', 'role' => 'AI Engineer']
                ],
                'posts' => [
                    [
                        'id' => 1,
                        'author' => 'Dr. Kamal Silva',
                        'authorId' => 7,
                        'title' => 'Getting started with PyTorch',
                        'content' => 'For those new to deep learning, I highly recommend starting with PyTorch. The documentation is excellent and the community is very helpful.',
                        'time' => '3 hours ago',
                        'likes' => 28,
                        'replies' => [
                            [
                                'id' => 1,
                                'author' => 'Linda Zhang',
                                'authorId' => 8,
                                'content' => 'Thanks for the tip! I\'ve been wanting to learn PyTorch.',
                                'time' => '2 hours ago'
                            ]
                        ]
                    ],
                    [
                        'id' => 2,
                        'author' => 'Linda Zhang',
                        'authorId' => 8,
                        'title' => 'Data visualization tips',
                        'content' => 'Just finished a project using Plotly. The interactive charts really helped stakeholders understand the data better. Highly recommend!',
                        'time' => '1 day ago',
                        'likes' => 19,
                        'replies' => []
                    ]
                ]
            ],
            [
                'id' => 3,
                'name' => 'Mobile Development',
                'description' => 'iOS, Android, Flutter, and cross-platform development',
                'icon' => '📱',
                'members' => 920,
                'totalPosts' => 187,
                'about' => 'Mobile developers unite! Discuss native and cross-platform development, share app ideas, and get feedback on your mobile projects.',
                'membersList' => [],
                'posts' => []
            ],
            [
                'id' => 4,
                'name' => 'UI/UX Design',
                'description' => 'User interface design, user experience, and design systems',
                'icon' => '🎨',
                'members' => 745,
                'totalPosts' => 156,
                'about' => 'For designers passionate about creating beautiful and functional user experiences. Share your designs, get critique, and learn from others.',
                'membersList' => [],
                'posts' => []
            ],
            [
                'id' => 5,
                'name' => 'Backend & DevOps',
                'description' => 'Server-side development, databases, cloud infrastructure, and CI/CD',
                'icon' => '⚙️',
                'members' => 680,
                'totalPosts' => 201,
                'about' => 'Backend engineers and DevOps specialists sharing knowledge about scalable architectures, database optimization, and deployment strategies.',
                'membersList' => [],
                'posts' => []
            ],
            [
                'id' => 6,
                'name' => 'Cybersecurity',
                'description' => 'Security best practices, ethical hacking, and penetration testing',
                'icon' => '🔒',
                'members' => 530,
                'totalPosts' => 142,
                'about' => 
'Learn about application security, secure coding practices, and stay updated on the latest security vulnerabilities and solutions.',
                'membersList' => [],
                'posts' => []
            ],
            [
                'id' => 7,
                'name' => 'Game Development',
                'description' => 'Unity, Unreal Engine, and indie game development',
                'icon' => '🎮',
                'members' => 890,
                'totalPosts' => 198,
                'about' => 'Game developers sharing tips, showcasing projects, and discussing game design principles and development tools.',
                'membersList' => [],
                'posts' => []
            ],
            [
                'id' => 8,
                'name' => 'Cloud Computing',
                'description' => 'AWS, Azure, Google Cloud, and cloud architecture patterns',
                'icon' => '☁️',
                'members' => 612,
                'totalPosts' => 134,
                'about' => 'Discuss cloud platforms, serverless architectures, and learn how to build scalable cloud-native applications.',
                'membersList' => [],
                'posts' => []
            ]
        ];
    }

    private function getAllProjects() {
        return [
            [
                'id' => 1,
                'title' => 'AI Chatbot Assistant',
                'description' => 'Develop a chatbot that can handle user queries using AI and NLP techniques.',
                'category' => 'Web Development',
                'categoryClass' => 'web',
                'icon' => '🤖',
                'status' => 'active',
                'overview' => 'This project aims to create an AI-driven chatbot that can understand and respond to user questions intelligently using NLP models like Dialogflow or GPT-based frameworks.',
                'goals' => ['Build NLP model integration', 'Design a conversational UI', 'Test chatbot responses', 'Deploy on web platform'],
                'skills' => ['JavaScript', 'Node.js', 'Dialogflow', 'NLP', 'UX Design'],
                'progress' => 70,
                'creator' => 'Ayesha Rahman',
                'totalMembers' => 5,
                'createdDate' => '2025-09-10',
                'deadline' => '2025-11-30',
                'lead' => ['name' => 'Ayesha Rahman', 'role' => 'AI Developer', 'avatar' => '👩‍💻'],
                'members' => [
                    ['name' => 'Imran Khan', 'role' => 'Frontend Dev', 'avatar' => '👨‍💻'],
                    ['name' => 'Sara Ali', 'role' => 'Backend Dev', 'avatar' => '🧑‍💻'],
                    ['name' => 'Lahiru Perera', 'role' => 'UX Designer', 'avatar' => '🎨']
                ],
                'resources' => [
                    ['name' => 'Dialogflow Docs', 'icon' => '📘'],
                    ['name' => 'TensorFlow JS', 'icon' => '🧠'],
                    ['name' => 'UI Prototype', 'icon' => '🎨']
                ]
            ],
            [
                'id' => 2,
                'title' => 'SkillXchange Mobile App',
                'description' => 'Create a cross-platform app to help users exchange and learn skills collaboratively.',
                'category' => 'Mobile App',
                'categoryClass' => 'mobile',
                'icon' => '📱',
                'status' => 'in-progress',
                'overview' => 'SkillXchange app allows users to post their skills, find partners to exchange knowledge, and collaborate on micro-projects using a community-driven model.',
                'goals' => ['Design UI using Flutter', 'Integrate Firebase authentication', 'Implement skill matching algorithm', 'Publish to Play Store'],
                'skills' => ['Flutter', 'Firebase', 'UX Design', 'Dart', 'REST API'],
                'progress' => 45,
                'creator' => 'Namal Perera',
                'totalMembers' => 6,
                'createdDate' => '2025-08-15',
                'deadline' => '2025-12-15',
                'lead' => ['name' => 'Namal Perera', 'role' => 'Mobile App Lead', 'avatar' => '👨‍💻'],
                'members' => [
                    ['name' => 'Hassan Rafi', 'role' => 'Backend Dev', 'avatar' => '🧑‍💻'],
                    ['name' => 'Mithila Fernando', 'role' => 'UI Designer', 'avatar' => '🎨'],
                    ['name' => 'Pasan Jayasuriya', 'role' => 'Tester', 'avatar' => '🧪']
                ],
                'resources' => [
                    ['name' => 'Flutter Docs', 'icon' => '📘'],
                    ['name' => 'Firebase Setup Guide', 'icon' => '🔥'],
                    ['name' => 'UI Kit Figma', 'icon' => '🎨']
                ]
            ],
            [
                'id' => 3,
                'title' => 'Data Visualization Dashboard',
                'description' => 'A web dashboard to visualize company analytics with real-time charts.',
                'category' => 'Data Science',
                'categoryClass' => 'data',
                'icon' => '📊',
                'status' => 'completed',
                'overview' => 'The project delivers a responsive data analytics dashboard with interactive visualizations powered by Chart.js and D3.js. It helps businesses make data-driven decisions quickly.',
                'goals' => ['Integrate Chart.js and D3.js', 'Implement backend with Node.js', 'Add user authentication', 'Host on cloud'],
                'skills' => ['D3.js', 'Chart.js', 'Node.js', 'Express', 'HTML/CSS'],
                'progress' => 100,
                'creator' => 'Ravindu Silva',
                'totalMembers' => 4,
                'createdDate' => '2025-06-01',
                'deadline' => '2025-08-30',
                'lead' => ['name' => 'Ravindu Silva', 'role' => 'Data Engineer', 'avatar' => '👨‍💻'],
                'members' => [
                    ['name' => 'Maya Fernando', 'role' => 'UI Dev', 'avatar' => '👩‍🎨'],
                    ['name' => 'Kavindu Perera', 'role' => 'Backend Dev', 'avatar' => '🧑‍💻']
                ],
                'resources' => [
                    ['name' => 'Chart.js Docs', 'icon' => '📈'],
                    ['name' => 'D3.js Guide', 'icon' => '📊'],
                    ['name' => 'Cloud Hosting', 'icon' => '☁️']
                ]
            ],
            [
                'id' => 4,
                'title' => 'UX Design System',
                'description' => 'Develop a unified design system for SkillXchange components.',
                'category' => 'UI/UX Design',
                'categoryClass' => 'design',
                'icon' => '🎨',
                'status' => 'active',
                'overview' => 'A project focused on building a reusable design system with typography, color schemes, and UI components for SkillXchange web and mobile platforms.',
                'goals' => ['Research design standards', 'Create Figma component library', 'Ensure accessibility compliance', 'Deliver design documentation'],
                'skills' => ['Figma', 'UI Design', 'Accessibility', 'Branding'],
                'progress' => 60,
                'creator' => 'Dilani Madushani',
                'totalMembers' => 3,
                'createdDate' => '2025-09-20',
                'deadline' => '2025-12-01',
                'lead' => ['name' => 'Dilani Madushani', 'role' => 'UX Designer', 'avatar' => '👩‍🎨'],
                'members' => [
                    ['name' => 'Sahan Wijesinghe', 'role' => 'UI Dev', 'avatar' => '👨‍💻'],
                    ['name' => 'Naduni Jayasekara', 'role' => 'Brand Designer', 'avatar' => '🎨']
                ],
                'resources' => [
                    ['name' => 'Figma Library', 'icon' => '🎨'],
                    ['name' => 'Design Guidelines', 'icon' => '📘'],
                    ['name' => 'Accessibility Checklist', 'icon' => '✅']
                ]
            ]
        ];
    }

    private function getTeachMatches($userId) {
        return [
            ['id' => 101, 'name' => 'Sophia Chen', 'skill' => 'Wants to learn Web Development'],
            ['id' => 102, 'name' => 'Ethan Williams', 'skill' => 'Wants to learn UI/UX Design'],
            ['id' => 103, 'name' => 'Maya Patel', 'skill' => 'Wants to learn JavaScript'],
            ['id' => 104, 'name' => 'Lucas Martinez', 'skill' => 'Wants to learn React'],
            ['id' => 105, 'name' => 'Aisha Ahmed', 'skill' => 'Wants to learn CSS'],
            ['id' => 106, 'name' => 'James Cooper', 'skill' => 'Wants to learn HTML']
        ];
    }

    private function getLearnMatches($userId) {
        return [
            ['id' => 201, 'name' => 'Dr. Kamal Silva', 'skill' => 'Teaches Data Science'],
            ['id' => 202, 'name' => 'Linda Zhang', 'skill' => 'Teaches Machine Learning'],
            ['id' => 203, 'name' => 'Ahmed Hassan', 'skill' => 'Teaches Python'],
            ['id' => 204, 'name' => 'Emily Rodriguez', 'skill' => 'Teaches AI'],
            ['id' => 205, 'name' => 'David Kim', 'skill' => 'Teaches Data Analytics']
        ];
    }

    public function viewProfile($userId = null) {
        $this->checkAuth();
        
        if (!$userId) {
            header('Location: ' . URLROOT . '/userdashboard/matches');
            exit;
        }
        
        $userData = null;
        $allMatches = array_merge($this->getTeachMatches($_SESSION['user_id']), $this->getLearnMatches($_SESSION['user_id']));
        
        foreach ($allMatches as $match) {
            if ($match['id'] == $userId) {
                $userData = $this->createUserDataFromMatch($match);
                break;
            }
        }
        
        if (!$userData) {
            header('Location: ' . URLROOT . '/userdashboard/matches');
            exit;
        }
        
        $data = [
            'title' => $userData['name'] . "'s Profile",
            'user' => $userData,
            'skills' => $this->getSkillsForMatch($userId),
            'projects' => $this->getProjectsForMatch($userId),
            'feedback' => $this->getFeedbackForMatch($userId),
            'page' => 'matches'
        ];
        
        $this->view('users/view_profile', $data);
    }

    private function createUserDataFromMatch($match) {
        return [
            'id' => $match['id'],
            'name' => $match['name'],
            'username' => strtolower(str_replace(' ', '', $match['name'])),
            'email' => strtolower(str_replace(' ', '', $match['name'])) . '@example.com',
            'bio' => $match['skill'],
            'avatar' => strtoupper(substr($match['name'], 0, 2)),
            'connections' => rand(20, 100),
            'skills_taught' => rand(3, 10),
            'skills_learning' => rand(2, 8),
            'rating' => 4.5,
            'reviews_count' => rand(5, 50)
        ];
    }

    private function getSkillsForMatch($userId) {
        return [
            'teaches' => [
                ['name' => 'Web Development', 'level' => 'Intermediate'],
            ],
            'learns' => [
                ['name' => 'Advanced Topics', 'level' => 'Beginner'],
            ]
        ];
    }

    private function getProjectsForMatch($userId) {
        return [
            'completed' => [
                ['title' => 'Sample Project', 'description' => 'A completed project.'],
            ],
            'in_progress' => []
        ];
    }

    private function getFeedbackForMatch($userId) {
        return [
            ['reviewer_name' => 'John Doe', 'date' => '1 week ago', 'rating' => 5, 'comment' => 'Great to work with!'],
        ];
    }
}