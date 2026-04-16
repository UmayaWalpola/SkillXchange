# Exam Module Map Notebook Pages

Use this as the clean version of the 5 notebook pages you wanted. The goal is to know, for each module, where the DB logic, controller logic, model logic, view files, JS, and main business rules live.

## 1. Projects

### DB / Tables
- `projects`
- `project_applications`
- `project_members`

### Controller
- [OrganizationController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/OrganizationController.php)
  - `projects()`
  - `createProject()`
  - `editProject()`
  - `deleteProject()`
  - `handleApplication()`
  - `updateRole()`
  - `removeMember()`
  - `getStats()`

### Model
- [Project.php](/c:/xampp/htdocs/SkillXchange/app/models/Project.php)
  - `createProject()`
  - `getProjectsByOrganization()`
  - `getProjectById()`
  - `updateProject()`
  - `deleteProject()`
  - `acceptApplication()`
  - `rejectApplication()`
  - `updateMemberRoleWithOrg()`
  - `getProjectProgress()`

### Views
- [createProject.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/createProject.php)
- [projects.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/projects.php)
- [viewProject.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/viewProject.php)
- [applications.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/applications.php)
- [members.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/members.php)

### JS
- [projects.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/projects.php)
  - inline filter and AJAX delete logic
- [organizations.js](/c:/xampp/htdocs/SkillXchange/public/assets/js/organizations.js)
  - project-related filter/application/chat helper logic

### Main Business Rules
- only logged-in organization can manage own projects
- required fields: name, description, required skills, category
- max members must be valid
- only project owner can edit/delete/update roles/remove members
- applications can be accepted/rejected
- project capacity is checked before accepting members
- progress stats are calculated from tasks and members

### Best Reuse Cases
- simple CRUD task
- CRUD with same create/edit form
- AJAX delete
- owner-based access checks
- status/filter/search style tasks

## 2. Tasks

### DB / Tables
- `project_tasks`
- task history-related records if needed

### Controller
- [TaskController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/TaskController.php)
  - `projectTasks()`
  - `create()`
  - `edit()`
  - `status()`
  - `delete()`
  - `userTasks()`
  - `checkDeadlines()`

### Model
- [Task.php](/c:/xampp/htdocs/SkillXchange/app/models/Task.php)
  - `createTask()`
  - `getTasksByProjectId()`
  - `getTaskById()`
  - `updateTask()`
  - `updateTaskStatus()`
  - `deleteTask()`
  - `getProjectMetrics()`
  - `getTaskStats()`
  - `getTasksGrouped()`

### Views
- [addTask.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/addTask.php)
- [editTask.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/editTask.php)
- [projectTasks.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/projectTasks.php)
- [userTasks.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/userTasks.php)

### JS
- [projectTasks.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/projectTasks.php)
  - inline status update logic

### Main Business Rules
- task belongs to a project
- org must own the project to create/edit/delete tasks
- valid statuses are controlled
- assigned member can be notified
- deadlines can be overdue / due soon / due today
- task metrics feed project progress

### Best Reuse Cases
- child CRUD using `project_id`
- status update logic
- deadline logic
- kanban/list grouping
- notification trigger after CRUD

## 3. Reporting

### DB / Tables
- `reports`
- `user_reports`
- `content_reports`
- related moderation columns/statuses in user/content tables

### Controller
- [ReportController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/ReportController.php)
  - `reportUser()`
  - `reportProjectUser()`
  - `reportContent()`
  - moderation / action methods lower in file

### Model
- mostly handled inside controller with `Database`

### Views
- [reports.php](/c:/xampp/htdocs/SkillXchange/app/views/admin/reports.php)
- [admin_reports.php](/c:/xampp/htdocs/SkillXchange/app/views/users/admin_reports.php)
- report button components and modals

### JS
- [reporting.js](/c:/xampp/htdocs/SkillXchange/public/assets/js/reporting.js)

### Main Business Rules
- must be logged in
- cannot report self
- duplicate pending reports are blocked
- only valid content types allowed
- moderation actions include resolve / warn / ban / remove content
- notifications can be sent after actions

### Best Reuse Cases
- duplicate prevention
- self-action restriction
- moderation action logic
- JSON response pattern

## 4. Feedback

### DB / Tables
- `user_feedback`
- `feedback_reports`

### Controller
- [FeedbackController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/FeedbackController.php)
  - `store()`
  - `stats()`
  - `list()`
  - `verifyProjectAccess()`
  - `createFeedbackNotification()`
- [FeedbackReportController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/FeedbackReportController.php)
  - report abusive feedback
  - admin review
  - remove reported feedback

### Model
- [Feedback.php](/c:/xampp/htdocs/SkillXchange/app/models/Feedback.php)
  - `submitFeedback()`
  - `feedbackExists()`
  - `getUserFeedback()`
  - `getFeedbackById()`
  - `updateFeedback()`
  - `deleteFeedback()`
  - `getUserStats()`
  - `getFilteredFeedback()`
- [FeedbackReport.php](/c:/xampp/htdocs/SkillXchange/app/models/FeedbackReport.php)

### Views
- [view.php](/c:/xampp/htdocs/SkillXchange/app/views/feedback/view.php)
- [report_modal.php](/c:/xampp/htdocs/SkillXchange/app/views/feedback/report_modal.php)
- [reported_feedback.php](/c:/xampp/htdocs/SkillXchange/app/views/admin/reported_feedback.php)
- [feedback.php](/c:/xampp/htdocs/SkillXchange/app/views/managerdashboard/feedback.php)

### JS
- [feedback.js](/c:/xampp/htdocs/SkillXchange/public/assets/js/feedback.js)
- [feedback_filters.js](/c:/xampp/htdocs/SkillXchange/public/assets/js/feedback_filters.js)
- [feedback_report.js](/c:/xampp/htdocs/SkillXchange/public/assets/js/feedback_report.js)

### Main Business Rules
- must be logged in
- rating must be in valid range
- duplicate feedback for same context is blocked
- project context access is checked
- stats/filter/sort are derived from stored feedback
- abusive feedback can be reported and removed

### Best Reuse Cases
- duplicate prevention
- AJAX form submit
- filter/sort UI
- report + moderation extension of base CRUD

## 5. Notifications

### DB / Tables
- `notifications`

### Controller
- [NotificationController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/NotificationController.php)
  - `index()`
  - `markAsRead()`
  - `markAllAsRead()`

### Model
- [Notification.php](/c:/xampp/htdocs/SkillXchange/app/models/Notification.php)
  - `createNotification()`
  - `getUserNotifications()`
  - `getUnreadCount()`
  - `markAsRead()`
  - `markAllAsRead()`

### Views
- [index.php](/c:/xampp/htdocs/SkillXchange/app/views/notifications/index.php)
- [header_user.php](/c:/xampp/htdocs/SkillXchange/app/views/layouts/header_user.php)

### JS
- notification display behavior is mostly tied to views/controller flow

### Main Business Rules
- notifications are created as side effects of events
- unread count appears in nav bell
- single notification and bulk notification read state can be updated

### Best Reuse Cases
- event trigger -> notification insert
- unread badge
- mark-as-read logic

## Quick Use Rule

If the exam task is:
- `simple CRUD` -> start from Projects
- `CRUD with parent_id / project_id` -> start from Tasks
- `business rule on reporting` -> start from ReportController
- `business rule on feedback` -> start from FeedbackController / FeedbackReportController
- `event trigger notification` -> start from Notification model + the controller where the event happens
