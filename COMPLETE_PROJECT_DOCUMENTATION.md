# 📘 SkillXchange - Complete Project Documentation

**Last Updated:** February 20, 2026  
**Version:** 1.0  
**Framework:** Custom PHP MVC  
**Database:** MySQL (skillxchange)  
**Purpose:** Comprehensive reference for AI assistance and development

---

## 📋 Table of Contents

1. [Project Overview](#project-overview)
2. [Complete Database Schema](#complete-database-schema)
3. [Controllers Reference](#controllers-reference)
4. [Models Reference](#models-reference)
5. [Views Structure](#views-structure)
6. [Core Framework Files](#core-framework-files)
7. [API Endpoints](#api-endpoints)
8. [Feature Implementations](#feature-implementations)
9. [Code Examples](#code-examples)
10. [Development Guide](#development-guide)

---

## 📖 PROJECT OVERVIEW

### System Purpose
SkillXchange is a **collaborative skill-sharing platform** where:
- Individual users can teach and learn skills
- Organizations create projects and recruit team members
- Users apply to projects, manage tasks, and collaborate
- Internal currency (BuckX) facilitates skill exchanges
- Communities foster learning and knowledge sharing
- Real-time notifications keep users engaged

### User Types
1. **Individual Users** - Learn/teach skills, join projects, participate in communities
2. **Organizations** - Create projects, recruit members, manage teams
3. **Admins** - Moderate content, manage reports, oversee system
4. **Community Managers** - Manage communities
5. **Quiz Managers** - Create and manage quizzes

### Technology Stack
- **Backend:** PHP 8.2+ (Custom MVC)
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Icons:** Phosphor Icons (NO EMOJIS - all replaced)
- **Payment:** Stripe API integration
- **Session Management:** PHP Sessions

### Project Statistics
- **Total Tables:** 25+ database tables
- **Controllers:** 15+ controller files
- **Models:** 12+ model files
- **Views:** 100+ view files across 15+ folders
- **Lines of Code:** ~15,000+ lines
- **Features:** 10 major feature systems

---

## 🗄️ COMPLETE DATABASE SCHEMA

### Database Name: `skillxchange`

---

### **1. users** - User Accounts (All Types)

**Purpose:** Core user authentication and profile data

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Unique user ID |
| `username` | VARCHAR(50) UNIQUE | Display name |
| `email` | VARCHAR(100) UNIQUE | Login email |
| `password` | VARCHAR(255) | Bcrypt hashed password |
| `role` | ENUM | `individual`, `organization`, `admin`, `quiz_manager`, `manager`, `community_admin` |
| `profile_picture` | VARCHAR(255) | Path to profile image |
| `bio` | VARCHAR(255) | User biography |
| `org_cert` | VARCHAR(255) | Organization certificate (for orgs only) |
| `profile_completed` | TINYINT(1) | 0 or 1 - profile setup status |
| `status` | ENUM | `active`, `suspended`, `banned` |
| `suspension_end_date` | DATETIME | When suspension ends (NULL if not suspended) |
| `created_at` | TIMESTAMP | Registration date |

**Indexes:** UNIQUE(username, email)

**Sample Data:**
- ID 7: admin user
- ID 37: "Pretty Software" (organization)
- ID 41: "Devinda" (individual)

---

### **2. projects** - Project Management

**Purpose:** Organizations create projects to recruit collaborators

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Project ID |
| `organization_id` | INT (FK → users.id) | Organization that owns project |
| `name` | VARCHAR(255) | Project name |
| `description` | TEXT | Full project description |
| `category` | ENUM | `web`, `mobile`, `data`, `design`, `other` |
| `status` | ENUM | `active`, `in-progress`, `completed`, `cancelled` |
| `required_skills` | TEXT | Comma-separated skills needed |
| `max_members` | INT | Maximum team size |
| `current_members` | INT | Current team count (auto-calculated) |
| `start_date` | DATE | Project start date |
| `end_date` | DATE | Project deadline |
| `created_at` | TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP | Last update timestamp |

**Relationships:**
- `organization_id` → `users.id` (CASCADE DELETE)

**Examples:**
- Project ID 7: "Zcode" (mobile app development)
- Project ID 8: "CodeCollab Hub" (completed web project)
- Project ID 13: "kithsara project" (active with 3 members)

---

### **3. project_applications** - User Applications to Projects

**Purpose:** Track user applications to join projects

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Application ID |
| `project_id` | INT (FK → projects.id) | Target project |
| `user_id` | INT (FK → users.id) | Applying user |
| `message` | TEXT | Simple application message |
| `experience` | LONGTEXT | Detailed experience description |
| `skills` | LONGTEXT | Skills matching project needs |
| `contribution` | LONGTEXT | How they plan to contribute |
| `commitment` | VARCHAR(100) | Hours per week (e.g., "20-30") |
| `duration` | VARCHAR(100) | Project duration commitment ("3-6 months") |
| `motivation` | LONGTEXT | Why they want to join |
| `portfolio` | VARCHAR(500) | Portfolio URL (GitHub, etc.) |
| `status` | ENUM | `pending`, `accepted`, `rejected` |
| `applied_at` | TIMESTAMP | Application submission time |
| `reviewed_at` | TIMESTAMP | When reviewed by org |

**Relationships:**
- `project_id` → `projects.id` (CASCADE DELETE)
- `user_id` → `users.id` (CASCADE DELETE)

**Business Logic:**
- One user can only apply ONCE per project
- Organizations review and accept/reject applications
- Accepted users become project members

---

### **4. project_members** - Project Team Members

**Purpose:** Track active project team members and their roles

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Membership ID |
| `project_id` | INT (FK → projects.id) | Project |
| `user_id` | INT (FK → users.id) | Team member |
| `role` | VARCHAR(100) | Custom role (e.g., "Developer", "Project Lead", "Frontend Engineer") |
| `joined_at` | TIMESTAMP | When they joined |
| `status` | ENUM | `active`, `inactive` |

**Relationships:**
- `project_id` → `projects.id` (CASCADE DELETE)
- `user_id` → `users.id` (CASCADE DELETE)
- UNIQUE constraint: (project_id, user_id) - one role per user per project

**Business Logic:**
- Added when application is accepted
- Used to determine project access (chat, tasks, etc.)
- Custom roles defined by organization

---

### **5. project_tasks** - Task Management (Kanban Board)

**Purpose:** Tasks within projects with Kanban-style tracking

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Task ID |
| `project_id` | INT (FK → projects.id) | Parent project |
| `assigned_to` | INT (FK → users.id, NULL) | Assigned team member (optional) |
| `title` | VARCHAR(255) | Task title |
| `description` | TEXT | Task details |
| `status` | ENUM | `todo`, `in-progress`, `done` |
| `priority` | ENUM | `low`, `medium`, `high` |
| `deadline` | DATE | Task due date |
| `created_at` | TIMESTAMP | Task creation time |
| `updated_at` | TIMESTAMP | Last update time |

**Relationships:**
- `project_id` → `projects.id` (CASCADE DELETE)
- `assigned_to` → `users.id` (SET NULL on delete)

**Features:**
- Kanban board visualization (To Do, In Progress, Done)
- Drag-and-drop status updates
- Priority-based sorting
- Deadline warnings (RED if overdue)

---

### **6. project_chat_messages** - Project Team Chat

**Purpose:** Real-time chat for project team members

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Message ID |
| `project_id` | INT (FK → projects.id) | Project chat room |
| `sender_id` | INT (FK → users.id) | Message sender |
| `message` | TEXT | Chat message content |
| `created_at` | TIMESTAMP | Message timestamp |

**Relationships:**
- `project_id` → `projects.id` (CASCADE DELETE)
- `sender_id` → `users.id` (CASCADE DELETE)

**Features:**
- Only team members can chat
- Real-time message loading
- Message history preserved

---

### **7. skills** - Available Skills System

**Purpose:** Master list of skills in the platform

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Skill ID |
| `skill_name` | VARCHAR(100) | Skill name |
| `created_at` | TIMESTAMP | When added |

**Available Skills:**
1. Web Development
2. Frontend Frameworks
3. Backend Development
4. Database Management
5. Mobile App Development
6. Cloud Computing
7. Data Analysis & Visualization
8. Cybersecurity
9. DevOps
10. GitHub and Git
11. AI and ML
12. Digital Marketing
13. Data Science

---

### **8. user_skills** - User's Skills (Teach & Learn)

**Purpose:** Track what skills each user can teach or wants to learn

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Entry ID |
| `user_id` | INT (FK → users.id) | User |
| `skill_name` | VARCHAR(100) | Skill (matches skill slugs) |
| `skill_type` | ENUM | `teach` or `learn` |
| `proficiency_level` | ENUM | `beginner`, `intermediate`, `advanced` |
| `created_at` | TIMESTAMP | When added |

**Relationships:**
- `user_id` → `users.id` (CASCADE DELETE)

**Skill Name Format:**
- Slugs: `web-development`, `frontend`, `backend`, `mobile`, `data-science`, `ai`, `devops`, etc.

---

### **9. exchanges** - Skill Exchange Connections

**Purpose:** Track active skill exchange sessions between users

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Exchange ID |
| `requester_id` | INT (FK → users.id) | User who requested |
| `receiver_id` | INT (FK → users.id) | User who accepted |
| `skill_id` | INT (FK → skills.id) | Skill being exchanged |
| `status` | ENUM | `active`, `completed`, `cancelled` |
| `created_at` | TIMESTAMP | Exchange start time |

**Relationships:**
- `requester_id` → `users.id` (CASCADE DELETE)
- `receiver_id` → `users.id` (CASCADE DELETE)
- `skill_id` → `skills.id` (CASCADE DELETE)

---

### **10. communities** - Community Groups

**Purpose:** User-created communities for skill topics

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Community ID |
| `name` | VARCHAR(100) UNIQUE | Community name |
| `description` | TEXT | About the community |
| `privacy` | ENUM | `public`, `private` |
| `rules` | JSON | Array of community rules |
| `tags` | JSON | Array of topic tags |
| `status` | ENUM | `active`, `inactive` |
| `created_by` | INT (FK → users.id) | Creator user ID |
| `created_at` | DATETIME | Creation time |
| `updated_at` | DATETIME | Last update time |

**Relationships:**
- `created_by` → `users.id`

**Examples:**
- "Web Developers Hub" (public)
- "Online Learning Community" (public)
- "Cloud Computing" (private)

---

### **11. community_members** - Community Membership

**Purpose:** Track users in communities

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Membership ID |
| `community_id` | INT (FK → communities.id) | Community |
| `user_id` | INT (FK → users.id) | Member |
| `role` | ENUM | `admin`, `moderator`, `member` |
| `joined_at` | DATETIME | Join date |

**Relationships:**
- `community_id` → `communities.id` (CASCADE DELETE)
- UNIQUE: (community_id, user_id)

---

### **12. posts** - Community Posts

**Purpose:** Posts within community discussions

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Post ID |
| `community_id` | INT (FK → communities.id) | Parent community |
| `user_id` | INT (FK → users.id) | Post author |
| `title` | VARCHAR(200) | Post title |
| `content` | TEXT | Post content |
| `created_at` | DATETIME | Post time |
| `updated_at` | DATETIME | Edit time |

**Relationships:**
- `community_id` → `communities.id` (CASCADE DELETE)

---

### **13. wallets** - User Wallet (BuckX System)

**Purpose:** Internal currency balance for each user

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Wallet ID |
| `user_id` | INT (FK → users.id) UNIQUE | User |
| `balance` | DECIMAL(10,2) | Current BuckX balance |
| `created_at` | TIMESTAMP | Wallet creation |
| `updated_at` | TIMESTAMP | Last transaction |

**Relationships:**
- `user_id` → `users.id` (CASCADE DELETE)

**Initial Balance:**
- Organizations: 1000 BuckX
- Individuals: 250 BuckX

---

### **14. wallet_transactions** - BuckX Transaction History

**Purpose:** Record of all BuckX transfers between users

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Transaction ID |
| `sender_id` | INT (FK → users.id) | Sender |
| `receiver_id` | INT (FK → users.id) | Receiver |
| `amount` | DECIMAL(10,2) | BuckX amount |
| `note` | VARCHAR(255) | Transaction note |
| `status` | ENUM | `pending`, `completed`, `failed`, `cancelled` |
| `created_at` | TIMESTAMP | Transaction time |

**Relationships:**
- `sender_id` → `users.id` (CASCADE DELETE)
- `receiver_id` → `users.id` (CASCADE DELETE)

---

### **15. notifications** - User Notifications

**Purpose:** System notifications for users

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Notification ID |
| `user_id` | INT (FK → users.id) | Recipient |
| `type` | VARCHAR(50) | Notification type |
| `message` | TEXT | Notification content |
| `project_id` | INT (FK → projects.id, NULL) | Related project |
| `task_id` | INT (FK → project_tasks.id, NULL) | Related task |
| `is_read` | TINYINT(1) | Read status (0/1) |
| `created_at` | TIMESTAMP | Notification time |

**Relationships:**
- `user_id` → `users.id` (CASCADE DELETE)
- `project_id` → `projects.id` (CASCADE DELETE)
- `task_id` → `project_tasks.id` (CASCADE DELETE)

**Notification Types:**
- `application_accepted` - Your application was accepted
- `application_rejected` - Your application was rejected
- `task_assigned` - New task assigned to you
- `task_deadline` - Task deadline approaching
- `project_update` - Project status changed
- `community_post` - New post in your community

---

### **16. reports** - User Reports

**Purpose:** Report other users for violations

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Report ID |
| `reported_user_id` | INT (FK → users.id) | User being reported |
| `reporter_user_id` | INT (FK → users.id) | User who reported |
| `reason` | VARCHAR(255) | Report reason |
| `description` | TEXT | Detailed explanation |
| `status` | ENUM | `pending`, `reviewed`, `resolved`, `dismissed`, `warned` |
| `created_at` | TIMESTAMP | Report time |

**Relationships:**
- `reported_user_id` → `users.id` (CASCADE DELETE)
- `reporter_user_id` → `users.id` (CASCADE DELETE)

---

### **17. content_reports** - Content Report System

**Purpose:** Report posts or chat messages

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Report ID |
| `reporter_id` | INT (FK → users.id) | Reporting user |
| `content_type` | ENUM | `post`, `chat_message` |
| `content_id` | INT | ID of post or message |
| `reason` | VARCHAR(255) | Report reason |
| `description` | TEXT | Details |
| `status` | ENUM | `pending`, `reviewed`, `dismissed`, `resolved` |
| `created_at` | TIMESTAMP | Report time |

**Relationships:**
- `reporter_id` → `users.id` (CASCADE DELETE)

---

### **18. user_reports** - Organization Reports on Project Members

**Purpose:** Organizations report project members for issues

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Report ID |
| `project_id` | INT (FK → projects.id) | Related project |
| `reported_user_id` | INT (FK → users.id) | Reported member |
| `reporter_org_id` | INT (FK → users.id) | Reporting org |
| `reason` | VARCHAR(255) | Report reason |
| `details` | TEXT | Full details |
| `status` | ENUM | `pending`, `reviewed`, `dismissed` |
| `reported_at` | TIMESTAMP | Report time |

**Relationships:**
- `project_id` → `projects.id` (CASCADE DELETE)
- `reported_user_id` → `users.id` (CASCADE DELETE)
- `reporter_org_id` → `users.id` (CASCADE DELETE)

---

### **19. user_feedback** - Project Member Feedback/Ratings

**Purpose:** Organizations rate project members after collaboration

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Feedback ID |
| `user_id` | INT (FK → users.id) | User being rated |
| `reviewer_id` | INT (FK → users.id) | Reviewer (usually org) |
| `project_id` | INT (FK → projects.id, NULL) | Related project |
| `context_type` | ENUM | `project`, `session` |
| `context_id` | INT | Context reference |
| `rating` | INT | Star rating (1-5) |
| `comment` | TEXT | Feedback comment |
| `tags` | VARCHAR(255) | Comma-separated tags (e.g., "teamwork,quality,communication") |
| `created_at` | TIMESTAMP | Feedback time |
| `updated_at` | TIMESTAMP | Last update |

**Relationships:**
- `user_id` → `users.id` (CASCADE DELETE)
- `reviewer_id` → `users.id` (CASCADE DELETE)
- `project_id` → `projects.id` (SET NULL)

**Feedback Tags:**
- `teamwork` - Good team player
- `communication` - Clear communication
- `quality` - High quality work
- `ontime` - Delivered on time

---

### **20. user_badges** - Achievement Badges

**Purpose:** Gamification - award badges to users

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Badge award ID |
| `user_id` | INT (FK → users.id) | User who earned it |
| `badge_name` | VARCHAR(100) | Badge name |
| `badge_icon` | VARCHAR(10) | Icon (emoji or icon class) |
| `earned_at` | TIMESTAMP | When earned |

**Relationships:**
- `user_id` → `users.id` (CASCADE DELETE)

**Badge Examples:**
- "Early Adopter" 🌟 - Awarded on registration
- "Team Player" - Completed first project
- "Skill Master" - Taught 10+ skills

---

### **21. user_stats** - User Statistics

**Purpose:** Track user activity metrics

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Stats ID |
| `user_id` | INT (FK → users.id) UNIQUE | User |
| `connections_count` | INT | Number of connections |
| `skills_taught_count` | INT | Skills taught |
| `skills_learning_count` | INT | Skills learning |
| `hours_exchanged` | INT | Total exchange hours |

**Relationships:**
- `user_id` → `users.id` (CASCADE DELETE)

---

### **22. user_activity** - Activity Log

**Purpose:** Track user actions for analytics

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Activity ID |
| `user_id` | INT (FK → users.id) | User |
| `activity_type` | VARCHAR(50) | Action type |
| `description` | TEXT | Activity details |
| `created_at` | TIMESTAMP | Activity time |

**Relationships:**
- `user_id` → `users.id` (CASCADE DELETE)

---

### **23. user_projects** - User Personal Projects

**Purpose:** Users showcase their own portfolio projects

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Project ID |
| `user_id` | INT (FK → users.id) | Project owner |
| `title` | VARCHAR(200) | Project title |
| `description` | TEXT | Project description |
| `status` | ENUM | `in_progress`, `completed` |
| `created_at` | TIMESTAMP | Creation time |

**Relationships:**
- `user_id` → `users.id` (CASCADE DELETE)

---

### **24. task_history** - Task Change Audit Log

**Purpose:** Track all task modifications

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | History ID |
| `task_id` | INT (FK → project_tasks.id) | Related task |
| `user_id` | INT (FK → users.id) | User who made change |
| `action` | VARCHAR(100) | Action type (e.g., "updated", "marked_done") |
| `timestamp` | TIMESTAMP | Action time |

**Relationships:**
- `task_id` → `project_tasks.id` (CASCADE DELETE)
- `user_id` → `users.id` (CASCADE DELETE)

---

### **25. wallet_notifications** - Wallet-Specific Notifications

**Purpose:** Notify users about wallet events

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT (PK, AI) | Notification ID |
| `user_id` | INT (FK → users.id) | Recipient |
| `type` | ENUM | `low_balance`, `payment_received`, `payment_sent`, `wallet_created` |
| `message` | TEXT | Notification message |
| `is_read` | TINYINT(1) | Read status |
| `created_at` | TIMESTAMP | Notification time |

**Relationships:**
- `user_id` → `users.id` (CASCADE DELETE)

---

## 🎛️ CONTROLLERS REFERENCE

### **Core Controllers:**

---

### **1. AuthController.php**
**Path:** `app/controllers/AuthController.php`

**Purpose:** User authentication and registration

**Methods:**

```php
public function index()
// Redirects to signin page

public function register()
// Shows registration page (choose individual/organization)

public function registerOrganization()
// POST: Handles organization registration
// - Validates name, email, password
// - Uploads organization certificate (PDF/JPG/PNG, max 5MB)
// - Creates user account with role='organization'
// - Redirects to signin

public function registerIndividual()
// POST: Handles individual user registration
// - Validates name, email, password
// - Creates user with role='individual'
// - Awards "Early Adopter" badge
// - Auto-login and redirect to profile setup

public function signin()
// GET: Shows login form
// POST: Validates credentials, creates session, redirects based on role

public function signout()
// Destroys session, redirects to login
```

**Key Features:**
- Bcrypt password hashing
- File upload validation for org certificates
- Session-based authentication
- Role-based redirect after login

---

### **2. UserdashboardController.php**
**Path:** `app/controllers/UserdashboardController.php`  
**Lines:** 1557 lines (LARGE controller)

**Purpose:** Individual user dashboard and profile management

**Methods:**

```php
public function index()
// User profile page - shows:
// - User data, skills, projects, feedback, matches
// View: users/profile

public function notifications()
// User notifications page
// View: users/notifications

public function chats()
// User chat interface
// - Shows active chats
// - If ?partnerId provided, loads specific conversation
// - Verifies connection exists before allowing chat
// View: users/chats

public function matches()
// Skill matching page
// - Shows matched users based on teach/learn skills
// - Smart matching algorithm
// View: users/matches

public function communities()
// User's joined communities
// View: users/communities

public function projects()
// User's project applications and memberships
// - Shows applied projects
// - Shows active project memberships
// View: users/projects

public function profileSetup()
// POST: Complete profile after registration
// - Upload profile picture
// - Add bio
// - Add skills (teach/learn with proficiency)
// Redirects to dashboard after completion

private function getUserData($userId)
// Returns user profile data with stats

private function getUserSkills($userId)
// Returns user's teach/learn skills

private function getUserProjects($userId)
// Returns user's project memberships

private function getUserFeedback($userId)
// Returns feedback/ratings from organizations
```

**Key Features:**
- Profile completion workflow
- Skill matching algorithm
- Project application tracking
- Chat with exchange partners
- Notification center

---

### **3. OrganizationController.php**
**Path:** `app/controllers/OrganizationController.php`  
**Lines:** 875 lines

**Purpose:** Organization dashboard and project management

**Methods:**

```php
public function index()
// Redirects to profile()

public function profile()
// Organization profile page
// - Shows org stats (projects, applications)
// View: organization/profile

public function projects()
// List all organization projects
// - Shows project metrics (task progress)
// View: organization/projects

public function createProject()
// GET: Show project creation form
// POST: Create new project
// - Validates input
// - Stores project in database
// - Redirects to projects list

public function editProject($projectId)
// GET: Show edit form
// POST: Update project details

public function deleteProject($projectId)
// Soft delete or hard delete project

public function viewProject($projectId)
// View single project details
// - Show team members
// - Show task statistics
// - Show applications
// View: organization/viewProject

public function applications($projectId = null)
// View project applications
// - Filter by pending/accepted/rejected
// - Show applicant details
// View: organization/applications

public function acceptApplication($applicationId)
// Accept user application
// - Create project_members entry
// - Send notification to user
// - Update project member count

public function rejectApplication($applicationId)
// Reject user application
// - Update status to 'rejected'
// - Send notification

public function removeMember($projectId, $userId)
// Remove team member from project
// - Update status to 'inactive'
// - Send notification

public function wallet()
// Organization wallet management
// View: organization/wallet

public function tasks($projectId)
// Project task management
// View: organization/tasks

public function feedback($projectId, $userId)
// Give feedback to project member
// POST: Rate user, add comments, tags
```

**Key Features:**
- Project CRUD operations
- Application review workflow
- Team member management
- Feedback/rating system
- Wallet integration

---

### **4. ProjectController.php**
**Path:** `app/controllers/ProjectController.php`  
**Lines:** 246 lines

**Purpose:** Public project views and application handling

**Methods:**

```php
public function detail($id)
// PUBLIC project detail page
// - Shows project info
// - Shows team members
// - Shows application status (if logged in)
// - Shows task statistics
// View: projects/view

public function submitApplication($projectId)
// POST: Submit application to project
// Two modes:
// 1. Simple application (just message)
// 2. Advanced application (experience, skills, commitment, portfolio)
// Returns JSON response

private function sendApplicationNotification($projectId, $userId)
// Send notification to organization
```

**Key Features:**
- Public project listing
- User application submission
- Application preview before submitting

---

### **5. TaskController.php**
**Path:** `app/controllers/TaskController.php`

**Purpose:** Task management for projects

**Methods:**

```php
public function index($projectId)
// Kanban board view
// - Shows tasks grouped by status (todo, in-progress, done)
// - Shows assigned members
// View: tasks/index

public function create($projectId)
// POST: Create new task
// - Validate input
// - Assign to team member
// - Set priority, deadline
// Returns JSON

public function update($taskId)
// POST: Update task details
// Returns JSON

public function updateStatus($taskId)
// POST: Change task status (drag-and-drop)
// - Validates status (todo, in-progress, done)
// Returns JSON

public function delete($taskId)
// DELETE: Remove task
// Returns JSON

public function assign($taskId)
// POST: Assign task to team member
// Returns JSON
```

**Key Features:**
- Kanban board visualization
- Drag-and-drop status updates
- Task assignment
- Deadline tracking with warnings
- Priority sorting

---

### **6. CommunityController.php**
**Path:** `app/controllers/CommunityController.php`  
**Lines:** 257 lines

**Purpose:** Community management (CRUD)

**Methods:**

```php
public function index()
// Community dashboard - list all communities
// View: cmmanager/dashboard

public function create()
// Show create community form
// View: cmmanager/community_create

public function store()
// POST: Create new community
// - JSON payload: name, description, privacy, rules, tags
// - Validates input
// - Auto-adds creator as admin member
// Returns JSON

public function edit($id)
// Show edit community form
// View: cmmanager/community_edit

public function update($id)
// POST: Update community
// - JSON payload
// Returns JSON

public function delete($id)
// DELETE: Remove community
// Returns JSON

public function view($id)
// View community posts and members
// View: community/view

public function getAll()
// API: Get all communities
// Returns JSON
```

**Key Features:**
- Community CRUD
- JSON API responses
- Public/private communities
- Community rules and tags (JSON storage)

---

### **7. WalletController.php**
**Path:** `app/controllers/WalletController.php`

**Purpose:** BuckX wallet management

**Methods:**

```php
public function index()
// Wallet dashboard
// - Shows balance
// - Shows transaction history
// View: users/wallet (individuals) or organization/wallet

public function transfer()
// POST: Transfer BuckX to another user
// - Validates amount
// - Checks balance
// - Creates transaction record
// - Updates balances
// - Sends notifications
// Returns JSON

public function purchase()
// Show BuckX purchase page (Stripe integration)
// View: wallet/purchase

public function processPurchase()
// POST: Process Stripe payment
// - Creates Stripe checkout session
// - Adds BuckX to wallet on success
```

**Key Features:**
- Balance display
- Transfer between users
- Transaction history
- Stripe payment integration
- Low balance notifications

---

### **8. NotificationController.php**
**Path:** `app/controllers/NotificationController.php`

**Purpose:** Notification management

**Methods:**

```php
public function getUnread()
// API: Get unread notification count
// Returns JSON

public function getAll()
// API: Get all notifications
// Returns JSON array

public function markAsRead($notificationId)
// POST: Mark notification as read
// Returns JSON

public function markAllAsRead()
// POST: Mark all user notifications as read
// Returns JSON
```

**Key Features:**
- Real-time unread count
- Mark as read
- Notification bell icon updates

---

### **9. AdminController.php**
**Path:** `app/controllers/AdminController.php`

**Purpose:** Admin panel for moderation

**Methods:**

```php
public function index()
// Admin dashboard - statistics
// View: admin/dashboard

public function users()
// User management - list all users
// View: admin/users

public function reports()
// View all user reports
// - Filter by status
// View: admin/reports

public function reviewReport($reportId)
// Review and resolve report
// - Suspend user
// - Warn user
// - Dismiss report

public function suspendUser($userId)
// POST: Suspend user account
// - Set suspension end date
// - Update status to 'suspended'

public function skills()
// Manage skills list
// View: admin/skills
```

**Key Features:**
- User moderation
- Report review system
- Suspension management
- System statistics

---

### **10. ReportController.php**
**Path:** `app/controllers/ReportController.php`

**Purpose:** Reporting system for users/content

**Methods:**

```php
public function reportUser($userId)
// POST: Report a user
// - Reason, description
// - Creates report record

public function reportContent($contentType, $contentId)
// POST: Report post or chat message
// - Validates content exists
// - Creates content_report record
```

**Key Features:**
- User reporting
- Content reporting
- Abuse prevention

---

### **11. SkillsController.php**
**Path:** `app/controllers/SkillsController.php`

**Purpose:** Skill matching system

**Methods:**

```php
public function findMatches($userId)
// Find matching users based on teach/learn skills
// - Algorithm: Match user's "learn" with other's "teach"
// - Sort by match score

public function requestExchange($matchUserId)
// POST: Request skill exchange with matched user
// - Creates exchange record
// - Sends notification
```

---

### **12. ChatController.php**
**Path:** `app/controllers/ChatController.php`

**Purpose:** Real-time chat for projects

**Methods:**

```php
public function getMessages($projectId)
// API: Get project chat messages
// Returns JSON

public function sendMessage($projectId)
// POST: Send chat message
// - Validates user is team member
// - Stores message
// Returns JSON
```

---

### **13. ProjectApplicationController.php**
**Path:** `app/controllers/ProjectApplicationController.php`

**Purpose:** Application management

**Methods:**

```php
public function submit($projectId)
// POST: Submit application (wrapper for ProjectController)

public function withdraw($applicationId)
// POST: Withdraw pending application
```

---

## 🗃️ MODELS REFERENCE

### **1. User.php**
**Path:** `app/models/User.php`  
**Lines:** 462 lines

**Purpose:** User management and authentication

**Methods:**

```php
public function registerOrganization($name, $email, $password, $certPath)
// Insert organization into users table
// Returns: TRUE on success

public function registerIndividual($name, $email, $password)
// Insert individual user
// Initialize user_stats
// Returns: User ID on success

public function login($email, $password)
// Verify login credentials
// Check suspension status (auto-reactivate if expired)
// Returns: User array or 'suspended|YYYY-MM-DD' or FALSE

public function getUserById($id)
// Fetch user by ID
// Returns: User array

public function completeProfile($userId, $username, $profilePicture, $bio)
// Update profile after registration
// Set profile_completed = 1

public function addUserSkills($userId, $skills, $levels, $type)
// Add multiple skills (teach or learn)
// Update user_stats

public function getUserSkills($userId)
// Fetch user skills grouped by teach/learn
// Returns: ['teaches' => [...], 'learns' => [...]]

public function getUserProjects($userId)
// Fetch user's personal projects
// Returns: ['completed' => [...], 'in_progress' => [...]]

public function getUserBadges($userId)
// Fetch earned badges
// Returns: Array of badges

public function awardBadge($userId, $badgeName, $badgeIcon)
// Award badge to user
// Insert into user_badges

public function updateUserStatus($userId, $status)
// Update user status (active, suspended, banned)

public function clearSuspensionDate($userId)
// Clear suspension_end_date (set to NULL)

private function initializeUserStats($userId)
// Create user_stats entry with zeros

private function updateSkillStats($userId)
// Recalculate skills_taught_count and skills_learning_count
```

**Key Features:**
- Password hashing with bcrypt
- Profile completion workflow
- Skill management
- Suspension system with auto-reactivation
- Badge system

---

### **2. Project.php**
**Path:** `app/models/Project.php`  
**Lines:** 537 lines

**Purpose:** Project management

**Methods:**

```php
public function createProject($data)
// Insert new project
// Returns: Project ID

public function getProjectsByOrganization($org_id)
// Fetch all projects for an organization
// Returns: Array of projects

public function getProjectById($id)
// Fetch single project with current_members count
// Returns: Project object

public function getProjectMembers($projectId)
// Fetch active members with profile info
// Returns: Array of member objects

public function updateProject($data)
// Update project details
// Returns: TRUE on success

public function deleteProject($projectId, $orgId)
// Delete project
// Returns: TRUE on success

public function getOrganizationStats($org_id)
// Count total/active/in-progress/completed projects
// Returns: Stats object

public function getApplicationStats($org_id)
// Count pending/accepted/rejected applications
// Returns: Stats object

public function searchProjects($org_id, $filters)
// Search projects with filters (search, status, category)
// Returns: Array of projects

public function getUserApplication($projectId, $userId)
// Get user's application (latest by ID)
// Returns: Application object or NULL

public function isUserMember($projectId, $userId)
// Check if user is active team member
// Returns: TRUE or FALSE

public function applyToProject($projectId, $userId, $message)
// Submit simple application
// Prevents duplicates
// Returns: TRUE or FALSE

public function applyToProjectAdvanced($projectId, $userId, $experience, $skills, ...)
// Submit advanced application with all fields
// Returns: TRUE or FALSE

public function getApplicationsByProject($projectId)
// Fetch all applications for a project
// Returns: Array of applications

public function acceptApplication($applicationId)
// Accept application
// - Update status to 'accepted'
// - Create project_members entry
// - Increment current_members
// Returns: TRUE or FALSE

public function rejectApplication($applicationId)
// Reject application
// Update status to 'rejected'
// Returns: TRUE or FALSE

public function addMember($projectId, $userId, $role)
// Add user as project member
// Returns: TRUE or FALSE

public function removeMember($projectId, $userId)
// Remove member (set status to 'inactive')
// Decrement current_members
// Returns: TRUE or FALSE

public function getMembersByProject($projectId)
// Fetch active members with roles
// Returns: Array of members

public function getProjectProgress($projectId)
// Calculate project progress metrics
// Returns: Progress object
```

**Key Features:**
- Full CRUD operations
- Application workflow
- Member management
- Stats and search
- Progress tracking

---

### **3. Task.php**
**Path:** `app/models/Task.php`  
**Lines:** 466 lines

**Purpose:** Task management with caching

**Methods:**

```php
public function createTask($data)
// Insert new task
// Clear project metrics cache
// Returns: Task ID

public function getTasksByProjectId($projectId)
// Fetch all tasks with assignee info
// Sorted by deadline, priority, creation date
// Returns: Array of task objects

public function getTaskById($taskId)
// Fetch single task with assignee details
// Returns: Task object

public function updateTask($taskId, $data)
// Update task details
// Log history
// Returns: TRUE or FALSE

public function updateTaskStatus($taskId, $status)
// Update task status (todo, in-progress, done)
// Validate status
// Clear cache
// Log history
// Returns: TRUE or FALSE

public function deleteTask($taskId)
// Delete task
// Clear cache
// Returns: TRUE or FALSE

public function assignTask($taskId, $userId)
// Assign task to team member
// Log history
// Returns: TRUE or FALSE

public function getTaskStats($projectId)
// Count tasks by status
// Returns: Object with todo_count, in_progress_count, done_count

public function getProjectMetrics($projectId)
// Calculate comprehensive metrics with caching
// Returns: Object with counts, percentages, deadlines

private function clearProjectCache($projectId)
// Clear cached metrics for project

private function isCacheValid($projectId)
// Check if cache is fresh (5 minutes)

private function logHistory($taskId, $userId, $action)
// Insert task_history record
```

**Key Features:**
- Kanban board support
- Status validation
- Performance caching (5-minute TTL)
- Audit logging
- Deadline tracking

---

### **4. Wallet.php**
**Path:** `app/models/Wallet.php`  
**Lines:** 292 lines

**Purpose:** BuckX wallet operations

**Methods:**

```php
public function ensureWalletExists($userId, $userRole)
// Create wallet if not exists
// Initial balance: 1000 (org) or 250 (individual)
// Returns: Current balance

public function getBalance($userId)
// Fetch current balance
// Returns: Float balance

public function getTotalSent($userId)
// Sum of completed outgoing transactions
// Returns: Float total

public function getTotalReceived($userId)
// Sum of completed incoming transactions
// Returns: Float total

public function getTransactions($userId)
// Fetch sent and received transactions
// Returns: ['sent' => [...], 'received' => [...]]

public function transfer($senderId, $receiverId, $amount, $note)
// Transfer BuckX between users
// - Validate balance
// - Deduct from sender
// - Add to receiver
// - Create transaction record
// - Send notifications
// Returns: TRUE or FALSE

public function addBalance($userId, $amount)
// Add BuckX to wallet (for purchases)
// Returns: TRUE or FALSE

public function deductBalance($userId, $amount)
// Remove BuckX from wallet
// Returns: TRUE or FALSE

private function sendTransferNotification($userId, $type, $amount, $otherUsername)
// Send wallet notification
```

**Key Features:**
- Transaction atomicity
- Balance validation
- Transaction history
- Notification integration
- Purchase support (Stripe)

---

### **5. Notification.php**
**Path:** `app/models/Notification.php`

**Purpose:** Notification management

**Methods:**

```php
public function create($userId, $type, $message, $projectId, $taskId)
// Insert new notification
// Returns: Notification ID

public function getByUser($userId)
// Fetch all notifications for user (latest first)
// Returns: Array of notifications

public function getUnreadCount($userId)
// Count unread notifications
// Returns: Integer count

public function markAsRead($notificationId)
// Mark single notification as read
// Returns: TRUE or FALSE

public function markAllAsRead($userId)
// Mark all user notifications as read
// Returns: TRUE or FALSE
```

---

### **6. Community.php**
**Path:** `app/models/Community.php`

**Purpose:** Community operations

**Methods:**

```php
public function create($data)
// Insert new community
// Add creator as admin member
// Returns: Community ID

public function getAllCommunities()
// Fetch all active communities
// Returns: Array of communities

public function getCommunityById($id)
// Fetch single community
// Returns: Community object

public function update($id, $data)
// Update community details
// Returns: TRUE or FALSE

public function delete($id)
// Delete community
// Returns: TRUE or FALSE

public function addMember($communityId, $userId, $role)
// Add user to community
// Returns: TRUE or FALSE

public function removeMember($communityId, $userId)
// Remove member
// Returns: TRUE or FALSE

public function getMembers($communityId)
// Fetch community members
// Returns: Array of members
```

---

### **7. SkillMatch.php**
**Path:** `app/models/SkillMatch.php`

**Purpose:** Skill matching algorithm

**Methods:**

```php
public function getAllMatchesWithScores($userId)
// Find users where:
// - My "learn" matches their "teach"
// - Their "learn" matches my "teach"
// Calculate match score (0-100)
// Returns: Array of matched users with scores

public function getTopMatches($userId, $limit)
// Get top N matches
// Returns: Array of best matches

private function calculateMatchScore($mySkills, $theirSkills)
// Calculate similarity score
// Returns: Integer score (0-100)
```

**Key Features:**
- Bidirectional matching
- Score calculation
- Proficiency level consideration

---

### **8. Exchange.php**
**Path:** `app/models/Exchange.php`

**Purpose:** Skill exchange management

**Methods:**

```php
public function createExchange($requesterId, $receiverId, $skillId)
// Create exchange record
// Returns: Exchange ID

public function getActiveExchanges($userId)
// Fetch active exchanges for user
// Returns: Array of exchanges

public function completeExchange($exchangeId)
// Mark exchange as completed
// Returns: TRUE or FALSE
```

---

## 🎨 VIEWS STRUCTURE

### **View Folders:**

```
app/views/
├── home.php                    # Landing page
├── layouts/                    # Reusable layouts
│   ├── header_user.php         # Header with Phosphor Icons
│   ├── footer_user.php         # Footer
│   ├── usersidebar.php         # Individual user sidebar
│   ├── organization_sidebar.php # Organization sidebar
│   ├── managersidebar.php      # Manager sidebar
│   └── admin_sidebar.php       # Admin sidebar
├── auth/                       # Authentication views
│   ├── register.php            # Registration page
│   ├── signin.php              # Login page
│   └── logout.php              # Logout confirmation
├── users/                      # Individual user views
│   ├── profile.php             # User profile
│   ├── profile_setup.php       # Profile completion form
│   ├── projects.php            # User's projects
│   ├── matches.php             # Skill matches
│   ├── communities.php         # Joined communities
│   ├── notifications.php       # Notification center
│   ├── chats.php               # Chat interface
│   └── wallet.php              # User wallet
├── organization/               # Organization views
│   ├── profile.php             # Org profile
│   ├── projects.php            # Org project list
│   ├── createProject.php       # Create project form
│   ├── viewProject.php         # Single project view
│   ├── editProject.php         # Edit project form
│   ├── applications.php        # View applications
│   ├── tasks.php               # Project tasks
│   ├── wallet.php              # Org wallet
│   └── feedback.php            # Give feedback form
├── projects/                   # Public project views
│   ├── view.php                # Public project detail
│   └── tasks.php               # Project task board
├── tasks/                      # Task management
│   └── index.php               # Kanban board
├── community/                  # Community views
│   ├── view.php                # Community detail
│   └── posts.php               # Community posts
├── cmmanager/                  # Community manager views
│   ├── dashboard.php           # Community list
│   ├── community_create.php    # Create community form
│   └── community_edit.php      # Edit community form
├── admin/                      # Admin panel
│   ├── dashboard.php           # Admin dashboard
│   ├── users.php               # User management
│   ├── reports.php             # Report moderation
│   └── skills.php              # Skill management
├── notifications/              # Notification views
│   └── index.php               # Notification list
├── components/                 # Reusable components
│   ├── project_card.php        # Project card component
│   ├── task_card.php           # Task card component
│   └── notification_item.php   # Notification item
└── partials/                   # Small reusable parts
    ├── modals/                 # Modal dialogs
    └── forms/                  # Form components
```

**View Naming Convention:**
- Snake_case filenames
- `.php` extension
- Grouped by user type (users/, organization/, admin/)
- Layouts are reusable header/sidebar/footer

**Icons:**
- **ALL EMOJIS REMOVED** and replaced with Phosphor Icons
- Icon library loaded in header: `<script src="https://unpkg.com/@phosphor-icons/web"></script>`
- Icon pattern: `<i class="ph ph-icon-name"></i>`

---

## ⚙️ CORE FRAMEWORK FILES

### **1. core/Core.php** - Router/Dispatcher

**Lines:** 63 lines

**How It Works:**

```php
class Core {
    protected $currentController = 'PagesController'; // Default
    protected $currentMethod = 'index'; // Default method
    protected $params = []; // URL parameters

    public function __construct() {
        $url = $this->getUrl(); // Parse URL
        
        // 1. Check if controller exists
        if (file_exists('../app/controllers/' . ucwords($url[0]) . '.php')) {
            $this->currentController = ucwords($url[0]);
            unset($url[0]);
        }
        
        // 2. Load controller
        require_once '../app/controllers/' . $this->currentController . '.php';
        $this->currentController = new $this->currentController;
        
        // 3. Check if method exists
        if (isset($url[1])) {
            if (method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                unset($url[1]);
            }
        }
        
        // 4. Get remaining params
        $this->params = $url ? array_values($url) : [];
        
        // 5. Execute: controller->method(params)
        call_user_func_array(
            [$this->currentController, $this->currentMethod],
            $this->params
        );
    }
    
    public function getUrl() {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
    }
}
```

**URL Examples:**
- `/project/detail/8` → `ProjectController->detail(8)`
- `/organization/projects` → `OrganizationController->projects()`
- `/userdashboard` → `UserdashboardController->index()`

---

### **2. core/Controller.php** - Base Controller

**Lines:** 40 lines

```php
class Controller {
    // Load model
    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }
    
    // Load view with data
    public function view($view, $data = []) {
        extract($data); // Makes $data keys available as variables
        require_once '../app/views/' . $view . '.php';
    }
}
```

**Usage:**
```php
class ProjectController extends Controller {
    public function detail($id) {
        $projectModel = $this->model('Project');
        $project = $projectModel->getProjectById($id);
        
        $this->view('projects/view', [
            'project' => $project
        ]);
    }
}
```

---

### **3. core/Database.php** - PDO Wrapper

**Lines:** 69 lines

```php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $dbh; // PDO connection
    private $stmt; // Prepared statement
    
    public function __construct() {
        $dsn = "mysql:host=$this->host;dbname=$this->dbname;charset=utf8mb4";
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ];
        $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
    }
    
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }
    
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            $type = match(true) {
                is_int($value) => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default => PDO::PARAM_STR
            };
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    
    public function execute() {
        return $this->stmt->execute();
    }
    
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }
    
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }
    
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}
```

**Usage Pattern:**
```php
$this->db->query("SELECT * FROM projects WHERE id = :id");
$this->db->bind(':id', $projectId);
$project = $this->db->single();
```

---

### **4. public/index.php** - Entry Point

```php
<?php
// Start session
session_start();

// Load config
require_once '../app/config/config.php';

// Load core files
require_once '../core/Core.php';
require_once '../core/Controller.php';
require_once '../core/Database.php';

// Initialize Core (router)
$init = new Core();
```

---

### **5. config/config.php** - Configuration

```php
<?php
// App settings
define('URLROOT', 'http://localhost/SkillXchange/public');
define('SITENAME', 'SkillXchange');
define('APPROOT', dirname(dirname(__FILE__)));

// Database config
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'skillxchange');
```

---

### **6. public/.htaccess** - URL Rewriting

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.+)$ index.php?url=$1 [QSA,L]
```

**What It Does:**
- Rewrites `/project/detail/8` to `/index.php?url=project/detail/8`
- Enables clean URLs without `.php` extensions

---

## 🔌 API ENDPOINTS

### **Notification API**

```
GET  /notification/getUnread      # Get unread count
GET  /notification/getAll          # Get all notifications
POST /notification/markAsRead/:id  # Mark one as read
POST /notification/markAllAsRead   # Mark all as read
```

**Response Format:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "application_accepted",
      "message": "Your application to 'Zcode' was accepted!",
      "is_read": 0,
      "created_at": "2026-02-18 10:30:00"
    }
  ],
  "count": 5
}
```

---

### **Chat API**

```
GET  /chat/getMessages/:projectId     # Get chat history
POST /chat/sendMessage/:projectId     # Send message
```

**POST Data:**
```json
{
  "message": "Hello team!"
}
```

**Response:**
```json
{
  "success": true,
  "message": {
    "id": 9,
    "sender_id": 37,
    "sender_name": "Pretty Software",
    "message": "Hello team!",
    "created_at": "2026-02-20 14:30:00"
  }
}
```

---

### **Task API**

```
POST /task/create/:projectId          # Create task
POST /task/update/:taskId              # Update task
POST /task/updateStatus/:taskId        # Change status
POST /task/delete/:taskId              # Delete task
POST /task/assign/:taskId              # Assign task
```

**Create Task:**
```json
{
  "task_name": "Implement login feature",
  "description": "Build authentication system",
  "assigned_to": 41,
  "priority": "high",
  "due_date": "2026-03-01"
}
```

---

### **Project Application API**

```
POST /project/submitApplication/:projectId
```

**Simple Application:**
```json
{
  "message": "I would love to join this project!"
}
```

**Advanced Application:**
```json
{
  "experience": "3 years in web development...",
  "skills": "React, Node.js, MySQL...",
  "contribution": "I can build the frontend...",
  "commitment": "20-30",
  "duration": "6-12",
  "motivation": "I am passionate about...",
  "portfolio": "https://github.com/username"
}
```

---

### **Wallet API**

```
POST /wallet/transfer
```

**Transfer Data:**
```json
{
  "receiver_id": 41,
  "amount": 50.00,
  "note": "Payment for React tutorial"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Transfer successful",
  "new_balance": 950.00
}
```

---

## ✨ FEATURE IMPLEMENTATIONS

### **1. Authentication System**

**Files:**
- `AuthController.php`
- `User.php` (model)
- `auth/register.php`, `auth/signin.php` (views)

**Features:**
- Dual registration (Individual + Organization)
- Bcrypt password hashing
- Session-based authentication
- Role-based access control
- Suspension system with auto-reactivation
- Profile completion workflow

**Flow:**
```
1. User visits /auth/register
2. Choose Individual or Organization
3. Fill form + upload org certificate (if org)
4. Submit → AuthController->registerIndividual/Organization()
5. User->register() creates account
6. Individual: Auto-login → redirect to profile setup
7. Organization: Redirect to login page
```

---

### **2. Project Management System**

**Files:**
- `ProjectController.php`
- `OrganizationController.php`
- `Project.php` (model)
- `organization/projects.php`, `projects/view.php` (views)

**Features:**
- Organizations create projects
- Define required skills, team size, timeline
- Categories: web, mobile, data, design, other
- Status tracking: active, in-progress, completed, cancelled
- Team member management
- Project metrics (task progress, member count)

**Flow:**
```
1. Org clicks "Create Project"
2. Fill form: name, description, category, skills, max_members, dates
3. Submit → OrganizationController->createProject()
4. Project->createProject() inserts record
5. Redirect to project list
6. Project appears on public listing
7. Users can view project details at /project/detail/:id
```

---

### **3. Application & Recruitment System**

**Files:**
- `ProjectController.php`
- `ProjectApplicationController.php`
- `Project.php` (model)
- `projects/view.php`, `organization/applications.php` (views)

**Features:**
- Users apply to projects (simple or advanced application)
- Advanced application: experience, skills, commitment, portfolio
- Organizations review applications
- Accept/Reject workflow
- Accepted users become project members
- Notifications sent on status change

**Application Flow:**
```
1. User views project at /project/detail/8
2. Clicks "Apply Now"
3. Fills application form (experience, skills, motivation, etc.)
4. Submit → ProjectController->submitApplication()
5. Project->applyToProject() creates application record
6. Organization sees application in /organization/applications
7. Org clicks "Accept" or "Reject"
8. If accepted:
   - Project->acceptApplication() updates status
   - Project->addMember() creates project_members entry
   - Notification sent to user
   - User can now access project tasks and chat
```

---

### **4. Task Management (Kanban Board)**

**Files:**
- `TaskController.php`
- `Task.php` (model)
- `tasks/index.php` (view)
- `public/assets/js/kanban.js` (drag-and-drop)

**Features:**
- Kanban board with 3 columns: To Do, In Progress, Done
- Create tasks with title, description, priority, deadline, assignee
- Drag-and-drop status updates
- Priority sorting (high, medium, low)
- Deadline warnings (RED for overdue, ORANGE for today)
- Task assignment to team members
- Task history logging
- Performance caching (5-minute cache)

**Usage:**
```
1. Project member visits /task/index/13
2. Sees Kanban board with 3 columns
3. Clicks "Add Task" → modal opens
4. Fills: title, description, assign to member, priority, deadline
5. Submit → TaskController->create()
6. Task->createTask() inserts record
7. Task card appears in "To Do" column
8. Drag task to "In Progress" → AJAX call → Task->updateTaskStatus()
9. Task moves to new column
10. Complete task → drag to "Done"
```

---

### **5. Skill Matching System**

**Files:**
- `SkillsController.php`
- `SkillMatch.php` (model)
- `users/matches.php` (view)

**Features:**
- Smart algorithm matches users' teach/learn skills
- Bidirectional matching (your learn ↔ their teach)
- Match score calculation (0-100)
- Proficiency level consideration
- Top matches displayed first
- Request skill exchange with matched users

**Algorithm:**
```php
1. Get user's "learn" skills
2. Find users who "teach" those skills
3. Get user's "teach" skills
4. Find users who "learn" those skills
5. Calculate match score:
   - Count matching skills
   - Consider proficiency levels
   - Score = (matches / total possible) * 100
6. Sort by score descending
7. Display top matches with "Connect" button
```

---

### **6. BuckX Wallet System**

**Files:**
- `WalletController.php`
- `Wallet.php` (model)
- `users/wallet.php`, `organization/wallet.php` (views)
- `Stripepaymentservice.php` (Stripe integration)

**Features:**
- Internal currency: BuckX
- Initial balance: 1000 (org), 250 (individual)
- Transfer between users
- Purchase BuckX with Stripe
- Transaction history (sent/received)
- Balance display
- Low balance notifications

**Transfer Flow:**
```
1. User visits /wallet
2. Sees balance, transaction history
3. Clicks "Transfer"
4. Enter receiver username, amount, note
5. Submit → WalletController->transfer()
6. Wallet->transfer():
   - Validate balance (sender has enough)
   - Begin transaction
   - Deduct from sender
   - Add to receiver
   - Create wallet_transactions record
   - Send notifications to both users
   - Commit transaction
7. Redirect to wallet page with success message
```

**Purchase Flow:**
```
1. User clicks "Buy BuckX"
2. Select package (100 BuckX = $10)
3. Clicks "Purchase" → WalletController->processPurchase()
4. Stripe API creates checkout session
5. User redirected to Stripe payment page
6. User pays with card
7. Stripe webhook → WalletController->handleWebhook()
8. Wallet->addBalance() adds BuckX
9. User redirected to wallet with success message
```

---

### **7. Notification System**

**Files:**
- `NotificationController.php`
- `Notification.php` (model)
- `users/notifications.php` (view)
- `public/assets/js/notifications.js` (real-time polling)

**Features:**
- Real-time notification bell icon
- Unread count badge
- Mark as read functionality
- Notification types: application accepted/rejected, task assigned, deadline, etc.
- Links to related content (project, task)
- Automatic polling (every 30 seconds)

**Notification Types:**
- `application_accepted` - Project application accepted
- `application_rejected` - Project application rejected
- `task_assigned` - New task assigned
- `task_deadline` - Task deadline approaching
- `project_update` - Project status changed
- `payment_received` - BuckX received
- `payment_sent` - BuckX sent
- `community_post` - New post in community

**Implementation:**
```javascript
// JavaScript polling (every 30 seconds)
setInterval(() => {
    fetch('/notification/getUnread')
        .then(res => res.json())
        .then(data => {
            document.querySelector('.notification-badge').textContent = data.count;
            if (data.count > 0) {
                document.querySelector('.notification-badge').style.display = 'block';
            }
        });
}, 30000);
```

---

### **8. Community System**

**Files:**
- `CommunityController.php`
- `Community.php` (model)
- `community/view.php`, `cmmanager/dashboard.php` (views)

**Features:**
- Create public/private communities
- Community posts with title and content
- Members can join communities
- Roles: admin, moderator, member
- Community rules (JSON array)
- Topic tags (JSON array)
- Post creation and discussion

**Flow:**
```
1. Manager visits /community
2. Sees community list
3. Clicks "Create Community"
4. Fills form: name, description, privacy, rules, tags
5. Submit → CommunityController->store()
6. Community->create() inserts record
7. Creator auto-added as admin member
8. Community appears on list
9. Users can join community
10. Members post discussions
11. Members interact with posts
```

---

### **9. Reporting System**

**Files:**
- `ReportController.php`
- `AdminController.php`
- `admin/reports.php` (view)

**Features:**
- Report users for violations
- Report content (posts, chat messages)
- Report categories: Abusive, Spam, Inappropriate, Fake Info
- Admin moderation dashboard
- Review and resolve reports
- Actions: Suspend user, Warn user, Dismiss report
- Suspension with end date (auto-reactivate)

**Report Flow:**
```
1. User sees inappropriate content
2. Clicks "Report" button
3. Select reason, add description
4. Submit → ReportController->reportUser()
5. Creates report record
6. Admin sees report in /admin/reports
7. Admin reviews report
8. Admin takes action:
   - Suspend user (set suspension_end_date)
   - Warn user (send notification)
   - Dismiss report (mark as dismissed)
9. Report status updated to 'resolved'
10. Reported user receives notification
```

---

### **10. Feedback System**

**Files:**
- `OrganizationController.php`
- `User.php` (model)
- `organization/feedback.php` (view)

**Features:**
- Organizations rate project members
- Star rating (1-5)
- Comment field
- Feedback tags: teamwork, communication, quality, ontime
- Displayed on user profile
- Average rating calculation

**Flow:**
```
1. Project completes
2. Org visits project member list
3. Clicks "Give Feedback" for a member
4. Rates user (1-5 stars)
5. Adds comment
6. Selects tags (teamwork, quality, etc.)
7. Submit → OrganizationController->feedback()
8. Creates user_feedback record
9. Feedback appears on user's profile
10. Average rating displayed on profile
```

---

## 💻 CODE EXAMPLES

### **Example 1: Create a New Controller**

```php
<?php
// app/controllers/MyController.php

class MyController extends Controller {
    
    private $myModel;
    
    public function __construct() {
        // Check authentication
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URLROOT . '/auth/signin');
            exit;
        }
        
        // Load model
        $this->myModel = $this->model('MyModel');
    }
    
    public function index() {
        // Get data from model
        $data = $this->myModel->getSomeData();
        
        // Prepare view data
        $viewData = [
            'title' => 'My Page',
            'data' => $data
        ];
        
        // Load view
        $this->view('myfolder/myview', $viewData);
    }
    
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input
            $errors = [];
            
            if (empty($_POST['name'])) {
                $errors[] = 'Name is required';
            }
            
            if (empty($errors)) {
                // Save to database
                $result = $this->myModel->create($_POST);
                
                if ($result) {
                    $_SESSION['success'] = 'Created successfully!';
                    header('Location: ' . URLROOT . '/mycontroller');
                    exit;
                }
            }
            
            $_SESSION['errors'] = $errors;
        }
        
        $this->view('myfolder/create');
    }
}
```

---

### **Example 2: Create a New Model**

```php
<?php
// app/models/MyModel.php

class MyModel {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function create($data) {
        $this->db->query("INSERT INTO my_table (name, description) VALUES (:name, :description)");
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        
        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    public function getAll() {
        $this->db->query("SELECT * FROM my_table ORDER BY created_at DESC");
        return $this->db->resultSet();
    }
    
    public function getById($id) {
        $this->db->query("SELECT * FROM my_table WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    public function update($id, $data) {
        $this->db->query("UPDATE my_table SET name = :name, description = :description WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        return $this->db->execute();
    }
    
    public function delete($id) {
        $this->db->query("DELETE FROM my_table WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
```

---

### **Example 3: Create a View**

```php
<!-- app/views/myfolder/myview.php -->

<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/usersidebar.php'; ?>

<main class="content">
    <h1><?= htmlspecialchars($data['title']) ?></h1>
    
    <!-- Display success message -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="ph ph-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <!-- Display errors -->
    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>
    
    <!-- Display data -->
    <?php if (!empty($data['items'])): ?>
        <?php foreach ($data['items'] as $item): ?>
            <div class="item-card">
                <h3><?= htmlspecialchars($item->name) ?></h3>
                <p><?= htmlspecialchars($item->description) ?></p>
                <a href="<?= URLROOT ?>/mycontroller/view/<?= $item->id ?>">
                    <i class="ph ph-eye"></i> View Details
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No items found.</p>
    <?php endif; ?>
</main>

<?php require_once '../app/views/layouts/footer_user.php'; ?>
```

---

### **Example 4: AJAX Request (JavaScript)**

```javascript
// Send AJAX request to create task
function createTask(projectId) {
    const taskData = {
        task_name: document.getElementById('task-title').value,
        description: document.getElementById('task-description').value,
        assigned_to: document.getElementById('assigned-to').value,
        priority: document.getElementById('priority').value,
        due_date: document.getElementById('deadline').value
    };
    
    fetch(`/task/create/${projectId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(taskData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('Task created successfully!', 'success');
            // Reload task board
            location.reload();
        } else {
            // Show error
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to create task', 'error');
    });
}
```

---

### **Example 5: Database Transaction**

```php
public function transferBuckX($senderId, $receiverId, $amount) {
    try {
        // Begin transaction
        $this->db->query("START TRANSACTION");
        $this->db->execute();
        
        // Check sender balance
        $this->db->query("SELECT balance FROM wallets WHERE user_id = :user_id FOR UPDATE");
        $this->db->bind(':user_id', $senderId);
        $senderWallet = $this->db->single();
        
        if ($senderWallet->balance < $amount) {
            throw new Exception('Insufficient balance');
        }
        
        // Deduct from sender
        $this->db->query("UPDATE wallets SET balance = balance - :amount WHERE user_id = :user_id");
        $this->db->bind(':amount', $amount);
        $this->db->bind(':user_id', $senderId);
        $this->db->execute();
        
        // Add to receiver
        $this->db->query("UPDATE wallets SET balance = balance + :amount WHERE user_id = :user_id");
        $this->db->bind(':amount', $amount);
        $this->db->bind(':user_id', $receiverId);
        $this->db->execute();
        
        // Create transaction record
        $this->db->query("INSERT INTO wallet_transactions (sender_id, receiver_id, amount, status) VALUES (:sender, :receiver, :amount, 'completed')");
        $this->db->bind(':sender', $senderId);
        $this->db->bind(':receiver', $receiverId);
        $this->db->bind(':amount', $amount);
        $this->db->execute();
        
        // Commit transaction
        $this->db->query("COMMIT");
        $this->db->execute();
        
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $this->db->query("ROLLBACK");
        $this->db->execute();
        
        error_log("Transfer error: " . $e->getMessage());
        return false;
    }
}
```

---

## 🚀 DEVELOPMENT GUIDE

### **Adding a New Feature**

**Step 1: Plan Database Changes**
```sql
-- Example: Add comments system

CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

**Step 2: Create Model**
```php
// app/models/Comment.php

class Comment {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function create($projectId, $userId, $commentText) {
        $this->db->query("INSERT INTO comments (project_id, user_id, comment) VALUES (:project_id, :user_id, :comment)");
        $this->db->bind(':project_id', $projectId);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':comment', $commentText);
        return $this->db->execute();
    }
    
    public function getByProject($projectId) {
        $this->db->query("SELECT c.*, u.username, u.profile_picture FROM comments c JOIN users u ON c.user_id = u.id WHERE c.project_id = :project_id ORDER BY c.created_at DESC");
        $this->db->bind(':project_id', $projectId);
        return $this->db->resultSet();
    }
}
```

**Step 3: Add Controller Method**
```php
// In ProjectController.php

public function addComment($projectId) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $commentModel = $this->model('Comment');
        $result = $commentModel->create($projectId, $_SESSION['user_id'], $_POST['comment']);
        
        echo json_encode(['success' => $result]);
    }
}
```

**Step 4: Update View**
```php
<!-- In projects/view.php -->

<div class="comments-section">
    <h3>Comments</h3>
    
    <!-- Comment form -->
    <form id="comment-form">
        <textarea name="comment" placeholder="Add a comment..."></textarea>
        <button type="submit">Post Comment</button>
    </form>
    
    <!-- Comment list -->
    <div id="comments-list">
        <?php foreach ($data['comments'] as $comment): ?>
            <div class="comment">
                <img src="<?= $comment->profile_picture ?>" alt="<?= $comment->username ?>">
                <div>
                    <strong><?= htmlspecialchars($comment->username) ?></strong>
                    <p><?= htmlspecialchars($comment->comment) ?></p>
                    <small><?= $comment->created_at ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.getElementById('comment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('/project/addComment/<?= $data['project']->id ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
});
</script>
```

---

### **Common Patterns**

**Authentication Check:**
```php
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . URLROOT . '/auth/signin');
    exit;
}
```

**Role Check:**
```php
if ($_SESSION['role'] !== 'organization') {
    $_SESSION['error'] = 'Access denied';
    header('Location: ' . URLROOT . '/');
    exit;
}
```

**Flash Messages:**
```php
// Set message
$_SESSION['success'] = 'Operation successful!';
$_SESSION['error'] = 'Something went wrong';

// Display in view (then unset)
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['success'] ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
```

**JSON Responses:**
```php
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Operation completed',
    'data' => $result
]);
exit;
```

---

### **Testing Checklist**

Before deploying new features:

- [ ] Test authentication (logged in/out states)
- [ ] Test role-based access (individual/org/admin)
- [ ] Test input validation (required fields, formats)
- [ ] Test SQL injection prevention (prepared statements)
- [ ] Test XSS prevention (htmlspecialchars on output)
- [ ] Test file uploads (size, type validation)
- [ ] Test notifications (created correctly)
- [ ] Test database transactions (rollback on error)
- [ ] Test mobile responsiveness
- [ ] Test Phosphor Icons (no emojis)

---

### **File Organization Best Practices**

1. **Controllers:** One controller per feature (ProjectController, TaskController, etc.)
2. **Models:** One model per database table
3. **Views:** Group by user type (users/, organization/, admin/)
4. **JavaScript:** Separate files per feature in public/assets/js/
5. **CSS:** Component-based CSS in public/assets/css/

---

### **Security Best Practices**

```php
// ✅ Always escape output
<?= htmlspecialchars($user->username) ?>

// ✅ Use prepared statements
$this->db->query("SELECT * FROM users WHERE id = :id");
$this->db->bind(':id', $userId);

// ✅ Validate file uploads
$allowedTypes = ['image/jpeg', 'image/png'];
if (!in_array($_FILES['file']['type'], $allowedTypes)) {
    die('Invalid file type');
}

// ✅ Check session before sensitive operations
if (!isset($_SESSION['user_id'])) {
    exit;
}

// ✅ Verify ownership before deletion
$this->db->query("DELETE FROM projects WHERE id = :id AND organization_id = :org_id");
```

---

## 📊 PROJECT STATISTICS

**Database:**
- Total Tables: 25
- Foreign Keys: 40+
- Indexes: 60+

**Code:**
- Controllers: 15 files
- Models: 12 files
- Views: 100+ files
- Total PHP Lines: ~15,000
- JavaScript Files: 20+
- CSS Files: 15+

**Features:**
- Authentication & Authorization
- Project Management
- Task Management (Kanban)
- Skill Matching
- BuckX Wallet
- Communities
- Notifications
- Chat System
- Reporting & Moderation
- Feedback System

**Users:**
- Registered Users: 50+
- Projects Created: 17+
- Applications Submitted: 19+
- Communities: 9+

---

## 🎯 KEY POINTS FOR AI ASSISTANCE

### **When Using This Documentation with ChatGPT:**

1. **Provide Context:** Start with "I'm working on SkillXchange, a custom PHP MVC project. See the documentation for structure."

2. **Be Specific:** Instead of "How do I add a feature?", say "How do I add a real-time notification for when a task deadline is 1 hour away? See Task.php and Notification.php in the docs."

3. **Reference Files:** "In ProjectController.php, the submitApplication() method handles applications. How can I add email validation before submission?"

4. **Database Context:** "According to the DB schema, the user_feedback table has a tags column. How do I query feedback by specific tags?"

5. **Follow Patterns:** "The WalletController uses JSON responses. I want to create a similar API endpoint for comments. What's the pattern?"

6. **Security Focus:** "I'm adding a new delete operation. Based on the security best practices in the docs, how should I verify ownership?"

7. **MVC Structure:** "I need to add a rating system. Based on the MVC structure, should I create RatingController or add to ProjectController?"

---

## 📝 CHANGE LOG

**February 20, 2026:**
- ✅ Removed ALL emojis (200+ replacements)
- ✅ Replaced with Phosphor Icons
- ✅ Fixed project detail routing
- ✅ Added comprehensive documentation
- ✅ Documented all 25 database tables
- ✅ Documented 15+ controllers
- ✅ Documented 12+ models
- ✅ Added code examples
- ✅ Added API documentation

---

## 🔗 USEFUL LINKS

**GitHub:** https://github.com/yourproject/skillxchange  
**Live Demo:** http://localhost/SkillXchange/public  
**Database Name:** skillxchange  
**PHP Version:** 8.2+  
**Icon Library:** https://phosphoricons.com  

---

**END OF DOCUMENTATION**

This documentation provides complete context for AI-assisted development. Use it as a reference when asking ChatGPT for help with features, debugging, or enhancements.
