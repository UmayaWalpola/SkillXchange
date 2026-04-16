# If Task Comes, Open These Files First

This is the fast cheat sheet. Use this first during exam prep or when identifying what to copy/adapt.

## 1. If the task is a simple CRUD

Open these first:
- [OrganizationController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/OrganizationController.php)
- [Project.php](/c:/xampp/htdocs/SkillXchange/app/models/Project.php)
- [createProject.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/createProject.php)
- [projects.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/projects.php)
- [DB.sql](/c:/xampp/htdocs/SkillXchange/DB.sql)

Use for:
- create/list/edit/delete
- same form reuse
- validation + redirect + flash message

## 2. If the task is CRUD with a parent-child relation

Open these first:
- [TaskController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/TaskController.php)
- [Task.php](/c:/xampp/htdocs/SkillXchange/app/models/Task.php)
- [addTask.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/addTask.php)
- [editTask.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/editTask.php)
- [projectTasks.php](/c:/xampp/htdocs/SkillXchange/app/views/tasks/projectTasks.php)

Use for:
- CRUD with `project_id`
- child records under a parent
- status updates
- deadline logic

## 3. If the task is “add filter / search / sort”

Open these first:
- [projects.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/projects.php)
- [feedback_filters.js](/c:/xampp/htdocs/SkillXchange/public/assets/js/feedback_filters.js)
- [organizations.js](/c:/xampp/htdocs/SkillXchange/public/assets/js/organizations.js)

Use for:
- search bar
- select filters
- client-side filtering
- query-string style filtering ideas

## 4. If the task is “AJAX delete” or “return JSON”

Open these first:
- [OrganizationController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/OrganizationController.php)
- [projects.php](/c:/xampp/htdocs/SkillXchange/app/views/organization/projects.php)
- [FeedbackController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/FeedbackController.php)
- [ReportController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/ReportController.php)

Use for:
- `header('Content-Type: application/json')`
- `fetch()`
- success/error JSON responses

## 5. If the task is “prevent duplicates”

Open these first:
- [FeedbackController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/FeedbackController.php)
- [Feedback.php](/c:/xampp/htdocs/SkillXchange/app/models/Feedback.php)
- [ReportController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/ReportController.php)
- [Project.php](/c:/xampp/htdocs/SkillXchange/app/models/Project.php)

Typical patterns:
- check existing row first
- if found, return error
- otherwise insert

## 6. If the task is “only owner/admin/org can do this”

Open these first:
- [OrganizationController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/OrganizationController.php)
- [TaskController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/TaskController.php)
- [FeedbackController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/FeedbackController.php)
- [ReportController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/ReportController.php)

Typical patterns:
- check `$_SESSION['user_id']`
- check `$_SESSION['role']`
- load parent record
- compare owner id

## 7. If the task is “valid status only”

Open these first:
- [Task.php](/c:/xampp/htdocs/SkillXchange/app/models/Task.php)
- [OrganizationController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/OrganizationController.php)
- [ReportController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/ReportController.php)

Typical patterns:
- whitelist array
- `in_array()`
- reject invalid statuses

## 8. If the task is “create notification when event happens”

Open these first:
- [Notification.php](/c:/xampp/htdocs/SkillXchange/app/models/Notification.php)
- [NotificationController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/NotificationController.php)
- [TaskController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/TaskController.php)
- [FeedbackController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/FeedbackController.php)
- [ReportController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/ReportController.php)

Use for:
- task assigned/update notifications
- feedback received notifications
- warning/ban/content action notifications

## 9. If the task is “show unread count in navbar”

Open these first:
- [Notification.php](/c:/xampp/htdocs/SkillXchange/app/models/Notification.php)
- [header_user.php](/c:/xampp/htdocs/SkillXchange/app/views/layouts/header_user.php)
- [NotificationController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/NotificationController.php)

## 10. If the task is “mark read / mark all read”

Open these first:
- [NotificationController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/NotificationController.php)
- [Notification.php](/c:/xampp/htdocs/SkillXchange/app/models/Notification.php)
- [index.php](/c:/xampp/htdocs/SkillXchange/app/views/notifications/index.php)

## 11. If the task is “report content / report user / report message”

Open these first:
- [ReportController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/ReportController.php)
- [reporting.js](/c:/xampp/htdocs/SkillXchange/public/assets/js/reporting.js)
- [reports.php](/c:/xampp/htdocs/SkillXchange/app/views/admin/reports.php)

## 12. If the task is “warn / ban / resolve / remove content”

Open these first:
- [ReportController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/ReportController.php)
- [reports.php](/c:/xampp/htdocs/SkillXchange/app/views/admin/reports.php)
- [admin_reports.php](/c:/xampp/htdocs/SkillXchange/app/views/users/admin_reports.php)

## 13. If the task is “feedback submit / feedback list / feedback stats”

Open these first:
- [FeedbackController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/FeedbackController.php)
- [Feedback.php](/c:/xampp/htdocs/SkillXchange/app/models/Feedback.php)
- [feedback.js](/c:/xampp/htdocs/SkillXchange/public/assets/js/feedback.js)
- [feedback_filters.js](/c:/xampp/htdocs/SkillXchange/public/assets/js/feedback_filters.js)

## 14. If the task is “report fake feedback / remove reported feedback”

Open these first:
- [FeedbackReportController.php](/c:/xampp/htdocs/SkillXchange/app/controllers/FeedbackReportController.php)
- [FeedbackReport.php](/c:/xampp/htdocs/SkillXchange/app/models/FeedbackReport.php)
- [reported_feedback.php](/c:/xampp/htdocs/SkillXchange/app/views/admin/reported_feedback.php)
- [feedback_report.js](/c:/xampp/htdocs/SkillXchange/public/assets/js/feedback_report.js)

## Fast Decision Rule

When a task comes, ask:
- Is this `simple CRUD`?
- Is this `CRUD with parent record`?
- Is this `business rule`?
- Is this `notification trigger`?
- Is this `filter/search`?

Then open the matching files above before doing anything else.
