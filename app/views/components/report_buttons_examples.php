<!-- Example: Add this to community posts, user profiles, or any content that needs reporting -->

<!-- 1. COMMUNITY POST REPORT BUTTON -->
<!-- Add this inside each post card -->
<button class="btn-report" onclick="openReportModal('content', {type: 'post', id: <?= $post['id'] ?>})">
    <span class="btn-report-icon">🚨</span>
    Report Post
</button>

<!-- 2. CHAT MESSAGE REPORT BUTTON -->
<!-- Add this to each chat message -->
<button class="btn-report" onclick="openReportModal('content', {type: 'chat_message', id: <?= $message['id'] ?>})">
    <span class="btn-report-icon">🚨</span>
    Report Message
</button>

<!-- 3. USER PROFILE REPORT BUTTON -->
<!-- Add this to user profile view -->
<button class="btn-report" onclick="openReportModal('user', <?= $user['id'] ?>)">
    <span class="btn-report-icon">🚨</span>
    Report User
</button>

<!-- 4. PROJECT MEMBER REPORT BUTTON -->
<!-- Add this to project member list (for organizations reporting members) -->
<button class="btn-report" onclick="openReportModal('projectUser', <?= $member['id'] ?>, <?= $project['id'] ?>)">
    <span class="btn-report-icon">🚨</span>
    Report Member
</button>

<!-- IMPORTANT: Include the report modal component at the bottom of your view -->
<?php include __DIR__ . '/../components/report_modal.php'; ?>

<!--
INTEGRATION NOTES:
==================

1. Add the report modal component to any view that needs reporting functionality:
   <?php include __DIR__ . '/../components/report_modal.php'; ?>

2. Place report buttons where appropriate in your existing views:
   - In community/forum posts
   - In chat messages
   - On user profile pages
   - In project member lists

3. The modal and JavaScript are self-contained and will handle:
   - Form validation
   - AJAX submission
   - Success/error messages
   - Proper routing to backend endpoints

4. Backend routes are already configured in ReportController:
   - /report/reportContent (for posts and chat messages)
   - /report/reportUser (for user profiles)
   - /report/reportProjectUser (for project members)

5. Admin/Manager access to reports:
   - Navigate to: /report/manage
   - Filter by type, status, and date
   - Update report status directly from the list

STYLING:
=========
All styles use your existing CSS variables:
- --dark-bg
- --white-bg
- --primary-blue
- --accent-blue
- --blue-bg

The components will automatically match your existing design system.
-->
