# SkillXchange - MVC Project Structure Overview

## 📋 Project Summary
**SkillXchange** is a skill exchange platform built with custom PHP MVC architecture where users can teach and learn skills, manage projects, form communities, and exchange value through an internal currency (BuckX).

---

## 🏗️ MVC Architecture

### **Core Components**

#### 1. **Core/Router (core/Core.php)**
- **URL Routing Pattern**: `/Controller/Method/Param1/Param2`
- **Default Controller**: `PagesController`
- **Default Method**: `index`

**How Routing Works:**
```
URL: /project/detail/8
↓
Controller: ProjectController.php
Method: detail()
Parameters: [8]
```

**Routing Logic:**
1. Parse URL → Extract controller name
2. Load controller file from `app/controllers/`
3. Instantiate controller
4. Check if method exists
5. Extract remaining URL segments as parameters
6. Execute: `$controller->method($param1, $param2, ...)`

---

#### 2. **Base Controller (core/Controller.php)**

```php
class Controller {
    // Load models
    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }
    
    // Load views with data
    public function view($view, $data = []) {
        extract($data); // Makes $data keys available as variables
        require_once '../app/views/' . $view . '.php';
    }
}
```

**Usage in Controllers:**
```php
$userModel = $this->model('User');
$this->view('users/profile', ['user' => $userData]);
```

---

#### 3. **Database Class (core/Database.php)**

**PDO Wrapper with Prepared Statements:**
```php
class Database {
    private $dbh; // PDO connection
    private $stmt; // Prepared statement
    
    public function query($sql) { /* Prepare query */ }
    public function bind($param, $value, $type = null) { /* Bind values */ }
    public function execute() { /* Execute statement */ }
    public function resultSet() { /* Fetch multiple rows */ }
    public function single() { /* Fetch single row */ }
}
```

**Configuration:**
- Host: `DB_HOST` (from config)
- Database: `DB_NAME` (skillxchange)
- Fetch Mode: `PDO::FETCH_OBJ` (returns objects)
- Error Mode: `PDO::ERRMODE_EXCEPTION`

---

## 📂 Directory Structure

```
SkillXchange/
├── app/
│   ├── config/
│   │   ├── config.php           # Environment constants (URLROOT, SITENAME)
│   │   ├── db.php               # Database credentials
│   │   └── db.local.php         # Local DB overrides
│   │
│   ├── controllers/             # All controllers
│   │   ├── AdminController.php          # Admin dashboard
│   │   ├── AuthController.php           # Login/Register/Logout
│   │   ├── ChatController.php           # Chat functionality
│   │   ├── CommunityController.php      # Communities
│   │   ├── Home.php                     # Landing page
│   │   ├── ManagerDashboardController.php
│   │   ├── NotificationController.php
│   │   ├── OrganizationController.php   # Organization management
│   │   ├── PagesController.php          # Default/static pages
│   │   ├── ProjectController.php        # Project management
│   │   ├── ProjectApplicationController.php
│   │   ├── ReportController.php         # Reporting system
│   │   ├── SkillsController.php         # Skill matching
│   │   ├── TaskController.php           # Task management
│   │   ├── UserdashboardController.php  # User dashboard
│   │   ├── UsersController.php          # User management
│   │   └── WalletController.php         # BuckX wallet
│   │
│   ├── models/                  # Data layer
│   │   ├── User.php             # User operations
│   │   ├── Project.php          # Project operations
│   │   ├── Task.php             # Task operations
│   │   ├── Notification.php     # Notifications
│   │   ├── Wallet.php           # Wallet transactions
│   │   ├── community.php        # Community operations
│   │   ├── Exchange.php         # Skill exchange
│   │   ├── SkillMatch.php       # Matching algorithm
│   │   └── quiz.php             # Quiz system
│   │
│   ├── views/                   # Presentation layer
│   │   ├── layouts/             # Reusable layouts
│   │   │   ├── header_user.php          # User header (with Phosphor Icons)
│   │   │   ├── footer_user.php
│   │   │   ├── usersidebar.php          # Individual user sidebar
│   │   │   ├── organization_sidebar.php # Organization sidebar
│   │   │   └── managersidebar.php       # Manager sidebar
│   │   │
│   │   ├── users/               # Individual user views
│   │   │   ├── profile.php
│   │   │   ├── projects.php
│   │   │   ├── matches.php
│   │   │   ├── communities.php
│   │   │   └── notifications.php
│   │   │
│   │   ├── organization/        # Organization views
│   │   │   ├── projects.php
│   │   │   ├── createProject.php
│   │   │   ├── viewProject.php
│   │   │   ├── tasks.php
│   │   │   └── wallet.php
│   │   │
│   │   ├── projects/            # Project views
│   │   │   ├── view.php         # Project detail page
│   │   │   └── tasks.php
│   │   │
│   │   ├── tasks/               # Task views
│   │   ├── admin/               # Admin views
│   │   ├── community/           # Community views
│   │   └── components/          # Reusable components
│   │
│   └── services/
│       └── Stripepaymentservice.php  # Payment integration
│
├── core/
│   ├── Core.php                 # Router
│   ├── Controller.php           # Base controller
│   └── Database.php             # Database wrapper
│
├── public/                      # Public web root
│   ├── index.php                # Entry point
│   ├── .htaccess                # URL rewriting
│   ├── assets/
│   │   ├── css/                 # Stylesheets
│   │   ├── js/                  # JavaScript files
│   │   └── images/              # Image assets
│   └── uploads/                 # User uploads
│
└── database/
    └── migrations/              # SQL migration files
```

---

## 🔑 Key Features Implemented

### **1. User System**
- **Types**: Individual Users, Organizations, Admins, Managers
- **Authentication**: Session-based
- **Profiles**: Skills, bio, badges, achievements
- **Roles**: Different dashboards per user type

### **2. Project Management**
- Organizations create projects
- Users apply to join projects
- Categories: Web, Mobile, Data, Design, Other
- Team collaboration with roles (Leader/Member)
- Task management within projects
- Project chat system

### **3. Skill Exchange & Matching**
- Users offer skills (teaching)
- Users request skills (learning)
- Smart matching algorithm
- Exchange history tracking

### **4. Community System**
- Create/join communities
- Community posts
- Member management
- Report system for content moderation

### **5. Task Management**
- Kanban-style boards (To Do, In Progress, Done)
- Task assignment to team members
- Priority levels (Low, Medium, High)
- Deadline tracking with warnings
- Status tracking

### **6. Wallet System (BuckX)**
- Internal currency for skill exchange
- Purchase BuckX (Stripe integration)
- Transfer between users
- Transaction history
- Balance management

### **7. Notification System**
- Real-time notifications
- Types: application accepted/rejected, task assigned, deadlines, etc.
- In-app notification bell (Phosphor Icons)
- Mark as read functionality

### **8. Reporting System**
- Report users, content, chat messages
- Report categories: Abusive, Spam, Inappropriate, Fake Info
- Admin moderation dashboard
- Status tracking (pending, reviewed, resolved)

### **9. Quiz System**
- Skill assessment quizzes
- Multiple choice questions
- Score tracking
- Quiz attempts history

### **10. Admin Dashboard**
- User management
- Report moderation
- Skills management
- System statistics
- Analytics

---

## 🎨 Frontend Stack

### **Icons**
- **Phosphor Icons** (CDN loaded in header)
- **NO EMOJIS** - All replaced with professional icons for academic evaluation
- Icon pattern: `<i class="ph ph-icon-name"></i>`

### **Key Icon Mappings**
```php
Folder:     <i class="ph ph-folder-open"></i>
Users:      <i class="ph ph-users"></i>
Chat:       <i class="ph ph-chat-circle-dots"></i>
Calendar:   <i class="ph ph-calendar"></i>
Target:     <i class="ph ph-target"></i>
Code:       <i class="ph ph-code"></i>
Warning:    <i class="ph ph-warning"></i>
Success:    <i class="ph ph-check-circle"></i>
```

### **Styling**
- Custom CSS files in `public/assets/css/`
- Responsive design
- Dark/light UI themes per section

---

## 🔐 Authentication Flow

```php
// Login check in controllers
protected function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . URLROOT . '/auth/login');
        exit();
    }
    return $_SESSION['user_id'];
}

// User type checks
if ($_SESSION['user_type'] !== 'organization') {
    // Redirect or deny
}
```

---

## 📊 Database Structure

### **Key Tables**
- `users` - User accounts (all types)
- `projects` - Projects created by organizations
- `project_members` - Team membership
- `tasks` - Tasks within projects
- `skills` - Available skills
- `user_skills` - User's skill offerings/requests
- `skill_exchanges` - Exchange records
- `communities` - Community groups
- `community_members` - Community membership
- `posts` - Community posts
- `notifications` - User notifications
- `wallet_transactions` - BuckX transactions
- `buckx_purchases` - Purchase history
- `reports` - User/content reports
- `project_chat_messages` - Project chats
- `quizzes` - Quiz system
- `user_quiz_attempts` - Quiz attempts
- `badges` - Achievement badges
- `user_badges` - User's earned badges

---

## 🛠️ Common Patterns

### **Controller Pattern**
```php
class ProjectController extends Controller {
    private $db;
    private $projectModel;
    
    public function __construct() {
        $this->db = new Database();
        $this->projectModel = $this->model('Project');
    }
    
    public function detail($id) {
        // Check authentication
        $userId = $this->checkAuth();
        
        // Get data from model
        $project = $this->projectModel->getProjectById($id);
        
        // Load view with data
        $this->view('projects/view', [
            'project' => $project,
            'user_id' => $userId
        ]);
    }
}
```

### **Model Pattern**
```php
class Project {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getProjectById($id) {
        $this->db->query("SELECT * FROM projects WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
}
```

### **View Pattern**
```php
<!-- users/projects.php -->
<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/usersidebar.php'; ?>

<main>
    <h1><?= htmlspecialchars($data['title']) ?></h1>
    
    <?php foreach ($data['projects'] as $project): ?>
        <div class="project-card">
            <h3><?= htmlspecialchars($project->name) ?></h3>
            <p><?= htmlspecialchars($project->description) ?></p>
        </div>
    <?php endforeach; ?>
</main>

<?php require_once '../app/views/layouts/footer_user.php'; ?>
```

---

## 🚀 How Request Flows

**Example: User clicks "View Project" button**

```
1. Browser: GET /project/detail/8

2. .htaccess: Rewrite to /public/index.php?url=project/detail/8

3. index.php: 
   - Load config
   - Start session
   - Initialize Core router

4. Core.php:
   - Parse URL: ['project', 'detail', '8']
   - Load ProjectController.php
   - Instantiate ProjectController
   - Call detail(8)

5. ProjectController::detail(8):
   - Check authentication
   - Load Project model
   - Call $projectModel->getProjectById(8)

6. Project Model:
   - Query database
   - Return project object

7. Controller:
   - Prepare data array
   - Call $this->view('projects/view', $data)

8. View (projects/view.php):
   - Include header/sidebar
   - Render HTML with project data
   - Include footer

9. Response: Send HTML to browser
```

---

## 🔧 Configuration Files

### **config.php**
```php
define('URLROOT', 'http://localhost/SkillXchange/public');
define('SITENAME', 'SkillXchange');
define('APPROOT', dirname(__DIR__));
```

### **db.php**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'skillxchange');
```

---

## 📝 Important Notes for AI Assistance

1. **Icon Library**: Use Phosphor Icons only (`<i class="ph ph-icon-name"></i>`)
2. **No Emojis**: All emojis removed for professional appearance
3. **Security**: All user input is escaped with `htmlspecialchars()`
4. **Database**: PDO with prepared statements (prevents SQL injection)
5. **Session Handling**: Session-based authentication
6. **URL Structure**: Clean URLs via .htaccess rewriting
7. **Fetch Mode**: Database returns objects (PDO::FETCH_OBJ)
8. **Error Handling**: Development mode shows all errors

---

## 🎯 Current Status

### **Working Features:**
✅ User authentication (login/register/logout)
✅ Project creation and management
✅ Task management (Kanban boards)
✅ Skill matching system
✅ Community system
✅ Wallet/BuckX system
✅ Notification system
✅ Reporting system
✅ Quiz system
✅ Admin dashboard
✅ Chat system
✅ All emojis replaced with Phosphor Icons

### **Recent Changes:**
- Replaced all UI emojis with Phosphor Icons (200+ replacements)
- Fixed project detail routing
- Added projects view for individual users
- Implemented project member role display
- Enhanced task deadline warnings

---

## 💡 Tips for Getting Help from ChatGPT

**When asking for help, provide:**
1. **What you're trying to do**: "I want to add a feature where..."
2. **Which controller/model**: "In ProjectController.php..."
3. **Current code snippet**: Show relevant section
4. **Error message**: If any error occurs
5. **Database tables involved**: Which tables you're querying

**Example Good Question:**
```
I'm working on ProjectController.php. I want to add a feature where 
users can favorite projects. I have a project_favorites table with 
(id, user_id, project_id, created_at). How do I:
1. Add a favorite button to projects/view.php
2. Create a favorite() method in ProjectController
3. Store the favorite in the database
4. Show favorited projects in user dashboard
```

---

## 🔍 Quick Reference

**Base URL**: `http://localhost/SkillXchange/public`

**Key Routes:**
- `/auth/login` - Login page
- `/userdashboard` - Individual user dashboard
- `/organization/projects` - Organization projects
- `/project/detail/{id}` - View project
- `/task/index/{project_id}` - Project tasks
- `/community/view/{id}` - Community page
- `/admin` - Admin dashboard
- `/wallet` - Wallet management

**Session Variables:**
- `$_SESSION['user_id']` - Logged in user ID
- `$_SESSION['user_type']` - User type (individual/organization/admin/manager)
- `$_SESSION['user_name']` - Username
- `$_SESSION['user_email']` - Email

**Common Database Methods:**
```php
$this->db->query($sql);              // Prepare query
$this->db->bind(':param', $value);   // Bind parameter
$this->db->execute();                // Execute query
$results = $this->db->resultSet();   // Get multiple rows
$result = $this->db->single();       // Get single row
$count = $this->db->rowCount();      // Get row count
```

---

**This document provides complete context for AI assistance with your SkillXchange project!** 🚀
