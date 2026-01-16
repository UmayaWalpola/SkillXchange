# SkillXchange MVC Architecture

## 📐 Complete MVC Structure

```
SkillXchange/
│
├── 📁 app/                          # Application Layer
│   ├── 📁 config/                   # Configuration
│   │   └── config.php               # Database & app settings
│   │
│   ├── 📁 controllers/              # Controllers (Business Logic)
│   │   ├── AdminController.php      # Admin panel management
│   │   ├── AuthController.php       # Authentication (login/register)
│   │   ├── ChatController.php       # Chat functionality
│   │   ├── CommunityController.php  # Community management
│   │   ├── DahboardController.php   # Dashboard views
│   │   ├── Home.php                 # Homepage
│   │   ├── ManagerDashboardController.php
│   │   ├── NotificationController.php
│   │   ├── OrganizationController.php
│   │   ├── PagesController.php      # Static pages
│   │   ├── ProjectApplicationController.php
│   │   ├── ProjectController.php    # Project CRUD
│   │   ├── QuizManagerController.php
│   │   ├── TaskController.php       # Task management
│   │   ├── UserdashboardController.php
│   │   ├── UsersController.php      # User management
│   │   └── WalletController.php     # Wallet transactions
│   │
│   ├── 📁 models/                   # Models (Data Layer)
│   │   ├── community.php            # Community model
│   │   ├── Notification.php         # Notification model
│   │   ├── Project.php              # Project model
│   │   ├── Task.php                 # Task model
│   │   ├── User.php                 # User model
│   │   └── UsersService.php         # User services
│   │
│   ├── 📁 views/                    # Views (Presentation Layer)
│   │   ├── 📁 auth/                 # Authentication views
│   │   │   ├── register.php
│   │   │   └── signin.php
│   │   │
│   │   ├── 📁 layouts/              # Reusable layouts
│   │   │   ├── header.php
│   │   │   ├── header_user.php
│   │   │   ├── footer.php
│   │   │   ├── footer_user.php
│   │   │   ├── adminsidebar.php
│   │   │   ├── usersidebar.php
│   │   │   ├── organization_sidebar.php
│   │   │   ├── commanagersidebar.php
│   │   │   └── qmansidebar.php
│   │   │
│   │   ├── 📁 users/                # User-related views
│   │   │   ├── admin.php
│   │   │   ├── admin_users.php
│   │   │   ├── admin_skills.php
│   │   │   ├── admin_reports.php
│   │   │   ├── userprofile.php
│   │   │   ├── edit_profile.php
│   │   │   ├── profile_setup.php
│   │   │   ├── view_profile.php
│   │   │   ├── projects.php
│   │   │   ├── communities.php
│   │   │   ├── community_forum.php
│   │   │   ├── chats.php
│   │   │   ├── matches.php
│   │   │   ├── notifications.php
│   │   │   ├── wallet.php
│   │   │   ├── quiz.php
│   │   │   └── take_quiz.php
│   │   │
│   │   ├── 📁 organization/         # Organization views
│   │   │   ├── profile.php
│   │   │   ├── projects.php
│   │   │   ├── createProject.php
│   │   │   ├── viewProject.php
│   │   │   ├── members.php
│   │   │   ├── applications.php
│   │   │   ├── tasks.php
│   │   │   ├── createTask.php
│   │   │   ├── editTask.php
│   │   │   └── chats.php
│   │   │
│   │   ├── 📁 projects/             # Project views
│   │   │   ├── view.php
│   │   │   └── tasks.php
│   │   │
│   │   ├── 📁 tasks/                # Task views
│   │   │   ├── addTask.php
│   │   │   ├── editTask.php
│   │   │   ├── projectTasks.php
│   │   │   ├── userTasks.php
│   │   │   └── taskHistory.php
│   │   │
│   │   ├── 📁 userdashboard/        # User dashboard
│   │   │   └── project_chats.php
│   │   │
│   │   ├── 📁 managerdashboard/     # Manager dashboard
│   │   │   ├── users.php
│   │   │   └── organizations.php
│   │   │
│   │   ├── 📁 cmmanager/            # Community manager
│   │   │   ├── dashboard.php
│   │   │   ├── community_create.php
│   │   │   └── community_edit.php
│   │   │
│   │   ├── 📁 quizmanager/          # Quiz manager
│   │   │   ├── dashboard.php
│   │   │   └── quiz_create.php
│   │   │
│   │   ├── 📁 community/            # Community views
│   │   ├── 📁 notifications/        # Notification views
│   │   └── home.php                 # Homepage view
│   │
│   ├── 📁 helpers/                  # Helper functions
│   │   └── (utility functions)
│   │
│   ├── 📁 middlewares/              # Middleware
│   │   └── (auth, validation, etc.)
│   │
│   └── 📁 validators/               # Input validation
│       └── (form validators)
│
├── 📁 core/                         # Core Framework
│   ├── Core.php                     # Router (URL dispatcher)
│   ├── Controller.php               # Base controller
│   └── Database.php                 # Database wrapper
│
├── 📁 public/                       # Public Web Root
│   ├── index.php                    # Front Controller (entry point)
│   ├── .htaccess                    # URL rewriting
│   │
│   ├── 📁 assets/                   # Static assets
│   │   ├── 📁 css/                  # Stylesheets
│   │   │   ├── global.css
│   │   │   ├── organizations.css
│   │   │   ├── profile.css
│   │   │   └── (other CSS files)
│   │   │
│   │   ├── 📁 js/                   # JavaScript files
│   │   │   ├── main.js
│   │   │   ├── organizations.js
│   │   │   ├── projects.js
│   │   │   ├── chats.js
│   │   │   ├── notifications.js
│   │   │   └── (other JS files)
│   │   │
│   │   └── 📁 images/               # Images
│   │
│   ├── 📁 uploads/                  # User uploads
│   │   └── (profile pictures, files)
│   │
│   └── 📁 api/                      # API endpoints (optional)
│       └── (REST API endpoints)
│
├── 📁 database/                     # Database files
│   └── 📁 migrations/               # Database migrations
│       └── (SQL migration files)
│
├── 📁 scripts/                      # Utility scripts
│   ├── 📁 tests/                    # Test scripts
│   │   └── (test-*.php files)
│   │
│   └── 📁 debug/                    # Debug utilities
│       └── (debug-*.php files)
│
├── .htaccess                        # Root htaccess
├── README.md                        # Project documentation
├── SETUP_INSTRUCTIONS.txt           # Setup guide
├── TESTING_GUIDE.md                 # Testing guide
├── MVC_STRUCTURE.md                 # This file
└── insert-test-data.sql             # Test data
```

---

## 🎯 MVC Pattern Explained

### **Model** (Data Layer)
**Location**: `app/models/`

**Responsibility**: 
- Database interactions
- Business logic related to data
- Data validation
- CRUD operations

**Example**: `app/models/Project.php`
```php
class Project {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getAll() {
        $this->db->query("SELECT * FROM projects");
        return $this->db->resultSet();
    }
    
    public function create($data) {
        $this->db->query("INSERT INTO projects ...");
        // ... bind and execute
    }
}
```

---

### **View** (Presentation Layer)
**Location**: `app/views/`

**Responsibility**:
- Display data to users
- HTML templates
- User interface
- NO business logic

**Example**: `app/views/organization/projects.php`
```php
<?php require_once "../app/views/layouts/header_user.php"; ?>

<div class="projects-container">
    <h1>My Projects</h1>
    <?php foreach($data['projects'] as $project): ?>
        <div class="project-card">
            <h3><?= htmlspecialchars($project->name) ?></h3>
            <p><?= htmlspecialchars($project->description) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
```

---

### **Controller** (Business Logic Layer)
**Location**: `app/controllers/`

**Responsibility**:
- Handle user requests
- Call appropriate models
- Pass data to views
- Business logic orchestration

**Example**: `app/controllers/OrganizationController.php`
```php
class OrganizationController extends Controller {
    private $projectModel;
    
    public function __construct() {
        $this->projectModel = $this->model('Project');
    }
    
    public function projects() {
        // Get data from model
        $projects = $this->projectModel->getByOrganization($_SESSION['org_id']);
        
        // Pass to view
        $data = ['projects' => $projects];
        $this->view('organization/projects', $data);
    }
    
    public function createProject() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            $result = $this->projectModel->create($_POST);
            if($result) {
                redirect('organization/projects');
            }
        } else {
            // Show form
            $this->view('organization/createProject');
        }
    }
}
```

---

## 🔄 Request Flow

```
1. User Request
   ↓
2. public/index.php (Front Controller)
   ↓
3. core/Core.php (Router)
   - Parses URL
   - Determines Controller/Method
   ↓
4. app/controllers/*Controller.php
   - Processes request
   - Calls Model if needed
   ↓
5. app/models/*.php (if data needed)
   - Queries database
   - Returns data
   ↓
6. Controller passes data to View
   ↓
7. app/views/*/*.php
   - Renders HTML
   - Displays data
   ↓
8. Response sent to User
```

---

## 🌐 URL Structure

**Pattern**: `http://localhost/SkillXchange/public/[controller]/[method]/[params]`

**Examples**:
```
http://localhost/SkillXchange/public/
→ PagesController → index()

http://localhost/SkillXchange/public/auth/signin
→ AuthController → signin()

http://localhost/SkillXchange/public/organization/projects
→ OrganizationController → projects()

http://localhost/SkillXchange/public/organization/members/13
→ OrganizationController → members(13)

http://localhost/SkillXchange/public/project/view/42
→ ProjectController → view(42)
```

---

## 🔐 Core Framework Components

### 1. **Core.php** (Router)
- Parses URLs
- Routes to correct controller/method
- Handles parameters

### 2. **Controller.php** (Base Controller)
```php
class Controller {
    // Load model
    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }
    
    // Load view with data
    public function view($view, $data = []) {
        if(file_exists('../app/views/' . $view . '.php')) {
            require_once '../app/views/' . $view . '.php';
        }
    }
}
```

### 3. **Database.php** (PDO Wrapper)
- Prepared statements
- Secure queries
- Result handling

---

## 📝 Naming Conventions

### Controllers
- **Format**: `PascalCaseController.php`
- **Examples**: 
  - `OrganizationController.php`
  - `ProjectController.php`
  - `AuthController.php`

### Models
- **Format**: `PascalCase.php`
- **Examples**:
  - `Project.php`
  - `User.php`
  - `Task.php`

### Views
- **Format**: `snake_case.php` or `camelCase.php`
- **Location**: `app/views/[section]/[view].php`
- **Examples**:
  - `app/views/organization/projects.php`
  - `app/views/auth/signin.php`
  - `app/views/users/profile.php`

### Methods
- **Format**: `camelCase`
- **Examples**: `getProjects()`, `createTask()`, `updateProfile()`

---

## 🛡️ Security Features

1. **Prepared Statements** - SQL injection prevention
2. **Password Hashing** - `password_hash()` / `password_verify()`
3. **Session Management** - Secure session handling
4. **XSS Protection** - `htmlspecialchars()` in views
5. **CSRF Protection** - Session tokens (recommended to add)
6. **Input Validation** - Server-side validation

---

## 📦 Directory Purposes

| Directory | Purpose |
|-----------|---------|
| `app/config/` | Configuration files |
| `app/controllers/` | Business logic controllers |
| `app/models/` | Data models |
| `app/views/` | HTML templates |
| `app/helpers/` | Utility functions |
| `app/middlewares/` | Request/response middleware |
| `app/validators/` | Input validation |
| `core/` | Framework core files |
| `public/` | Web-accessible files |
| `public/assets/` | CSS, JS, images |
| `public/uploads/` | User-uploaded files |
| `database/migrations/` | Database changes |
| `scripts/` | Development utilities |

---

## 🚀 Best Practices

### ✅ Do's
- Keep controllers thin (delegate to models)
- Put business logic in models
- Use prepared statements always
- Validate all user input
- Use meaningful names
- Separate concerns (MVC pattern)
- Keep views clean (minimal PHP logic)

### ❌ Don'ts
- Don't put SQL in controllers
- Don't put business logic in views
- Don't use raw SQL queries
- Don't mix HTML and business logic
- Don't expose sensitive data
- Don't trust user input

---

## 🔧 Adding New Features

### 1. Create Model (if needed)
```php
// app/models/NewFeature.php
class NewFeature {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getData() {
        // Database operations
    }
}
```

### 2. Create Controller
```php
// app/controllers/NewFeatureController.php
class NewFeatureController extends Controller {
    private $model;
    
    public function __construct() {
        $this->model = $this->model('NewFeature');
    }
    
    public function index() {
        $data = $this->model->getData();
        $this->view('newfeature/index', ['data' => $data]);
    }
}
```

### 3. Create View
```php
// app/views/newfeature/index.php
<?php require_once "../app/views/layouts/header_user.php"; ?>

<div class="feature-container">
    <h1>New Feature</h1>
    <!-- Display data -->
</div>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
```

### 4. Access via URL
```
http://localhost/SkillXchange/public/newfeature
```

---

## 📚 Additional Resources

- **Setup**: See `SETUP_INSTRUCTIONS.txt`
- **Testing**: See `TESTING_GUIDE.md`
- **Database**: See `database/migrations/`
- **Main README**: See `README.md`

---

## ✅ MVC Checklist

When building features, ensure:

- [ ] Model handles all database operations
- [ ] Controller orchestrates logic
- [ ] View only displays data
- [ ] No SQL in controllers or views
- [ ] All input is validated
- [ ] Output is escaped (XSS protection)
- [ ] Prepared statements used
- [ ] Proper error handling
- [ ] Session management secure
- [ ] Following naming conventions

---

**Last Updated**: November 26, 2025  
**Architecture**: Pure PHP MVC (No Frameworks)  
**Status**: Production Ready ✅
