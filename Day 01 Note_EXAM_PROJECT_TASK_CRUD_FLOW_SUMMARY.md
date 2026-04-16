# Project and Task CRUD Flow Summary

This is the compact summary for Task 1 prep. The goal is to know what changes usually happen in `DB + Model + Controller + View (+ JS if needed)`.

## A. Project CRUD

### Main Files
- [OrganizationController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/OrganizationController.php)
- [Project.php](/c:/xampp/htdocs/SkillXchange/app/models/Project.php)
- [createProject.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/createProject.php)
- [projects.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/projects.php)

### DB Side
Usually touches:
- `projects` table
- fields like `organization_id`, `name`, `description`, `category`, `status`, `required_skills`, `max_members`, `start_date`, `end_date`

If exam task asks for a new field:
1. add column in DB
2. add input to form
3. read `$_POST[...]` in controller
4. bind value in model create/update queries
5. display in list/detail views if needed

### Model Side
Core CRUD methods:
- `createProject($data)`
- `getProjectsByOrganization($org_id)`
- `getProjectById($id)`
- `updateProject($data)`
- `deleteProject($projectId, $orgId)`

What model does:
- holds SQL
- binds values
- returns rows / success flags

### Controller Side
Core CRUD methods:
- `projects()`
- `createProject()`
- `editProject($projectId = null)`
- `deleteProject()`

Typical controller responsibilities:
- check login / role
- validate input
- build `$data` or `$projectData`
- call model
- set `$_SESSION['success']` or `$_SESSION['error']`
- redirect or return JSON

### View Side
- list page: [projects.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/projects.php)
- form page: [createProject.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/createProject.php)

Typical view changes:
- add new input field
- prefill field in edit mode
- print flash messages
- show table/card/list values
- edit/delete buttons

### JS Side
Mostly in:
- [projects.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/projects.php)

Used for:
- filter/search
- AJAX delete
- button actions

### Full Project Create Flow
1. user opens create page
2. form in `createProject.php` submits data
3. `OrganizationController::createProject()` reads `$_POST`
4. validation runs
5. `Project::createProject()` inserts row
6. success message is set
7. redirect to projects list

### Full Project Edit Flow
1. user opens edit URL with project id
2. controller loads project using `getProjectById()`
3. same form view is reused
4. user submits changes
5. controller validates and builds update array
6. model `updateProject()` runs SQL
7. redirect with success/error

### Full Project Delete Flow
1. user clicks delete
2. controller receives project id
3. ownership check happens
4. model `deleteProject()` runs delete query
5. success/error response returned

### What to change if exam asks “add field to project CRUD”
- DB column
- form input
- create controller POST mapping
- edit controller POST mapping
- model create bind
- model update bind
- list/detail UI display

## B. Task CRUD

### Main Files
- [TaskController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/TaskController.php)
- [Task.php](/c:/xampp/htdocs/SkillXchange/app/models/Task.php)
- [addTask.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/addTask.php)
- [editTask.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/editTask.php)
- [projectTasks.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/projectTasks.php)

### DB Side
Usually touches:
- `project_tasks`
- fields like `project_id`, `assigned_to`, `title`, `description`, `status`, `priority`, `deadline`

If exam task asks for a new task field:
1. add DB column
2. add input to add/edit task view
3. read it in controller
4. bind in model create/update
5. display in task list/board if needed

### Model Side
Core CRUD methods:
- `createTask($data)`
- `getTasksByProjectId($projectId)`
- `getTaskById($taskId)`
- `updateTask($taskId, $data)`
- `deleteTask($taskId)`
- `updateTaskStatus($taskId, $status, $userId = null)`

What model does:
- SQL insert/update/delete/select
- status validation
- grouping/metrics helpers
- task statistics

### Controller Side
Core CRUD methods:
- `projectTasks($projectId = null)`
- `create($projectId = null)`
- `edit($taskId = null)`
- `status($taskId = null, $status = null)`
- `delete($taskId = null)`

Typical controller responsibilities:
- verify project exists
- verify organization owns project
- validate title / priority / assigned member
- map task form fields into model format
- call notification logic after important events

### View Side
- add form: [addTask.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/addTask.php)
- edit form: [editTask.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/editTask.php)
- task board/list: [projectTasks.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/projectTasks.php)

Typical view changes:
- add/edit form fields
- show task cards/rows
- render status, priority, deadline
- buttons for edit/delete/status moves

### JS Side
Mostly in:
- [projectTasks.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/projectTasks.php)

Used for:
- status updates
- board actions

### Full Task Create Flow
1. user opens create task page for a project
2. controller verifies org owns that project
3. addTask form submits task data
4. controller validates fields
5. model `createTask()` inserts into `project_tasks`
6. optional notification is created for assigned member
7. redirect or JSON success response

### Full Task Edit Flow
1. user opens edit task page with task id
2. controller loads task
3. controller verifies ownership via parent project
4. edit form shows current values
5. controller validates new values
6. model `updateTask()` updates row
7. notification may be triggered
8. redirect

### Full Task Delete Flow
1. user requests delete with task id
2. controller loads task
3. controller loads parent project and checks ownership
4. model `deleteTask()` deletes row
5. redirect with flash message

### Full Task Status Update Flow
1. user moves/changes task status
2. controller checks task and project ownership
3. model `updateTaskStatus()` validates allowed status
4. DB row updates
5. task history / notification can be triggered

### What to change if exam asks “add field to task CRUD”
- DB column in `project_tasks`
- form input in add/edit views
- controller POST mapping in create/edit
- model create/update SQL and binds
- list/kanban display

## C. Task 1 Exam Checklist

When building a simple CRUD from scratch:
1. decide table + fields
2. add DB changes
3. create model with 5 methods
4. create controller with list/create/edit/delete
5. create list view
6. create form view
7. add validation + flash messages
8. test create/edit/delete

## D. Task 2 Extension Checklist

If Task 2 is an extension of Task 1, common extensions are:
- only owner can edit/delete
- prevent duplicate insert
- allow only valid statuses
- trigger notification after action
- check parent-child ownership before update

Best source files for those patterns:
- [OrganizationController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/OrganizationController.php)
- [TaskController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/TaskController.php)
- [ReportController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/ReportController.php)
- [FeedbackController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/FeedbackController.php)
- [Notification.php](/c:/xampp/htdocs/SkillXchange/app/models/Notification.php)
