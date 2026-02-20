<!-- Report Button Component -->
<!-- This file demonstrates the report button patterns used across the site -->

<!-- USAGE EXAMPLES -->

<!-- 1. REPORT USER PROFILE BUTTON -->
<!-- Add this to user profile pages -->
<button class="report-btn report-user-btn" data-user-id="<?= $user->id ?>">
    <span class="report-btn-icon"><i class="ph ph-warning"></i></span>
    Report Profile
</button>

<!-- Small icon-only version -->
<button class="report-btn-small report-user-btn" data-user-id="<?= $user->id ?>" title="Report this profile">
    <span><i class="ph ph-warning"></i></span>
</button>


<!-- 2. REPORT PROJECT MEMBER BUTTON -->
<!-- Add this to project member lists or cards -->
<button class="report-btn report-project-member-btn" 
        data-user-id="<?= $member->user_id ?>" 
        data-project-id="<?= $project->id ?>">
    <span class="report-btn-icon"><i class="ph ph-warning"></i></span>
    Report Member
</button>


<!-- 3. REPORT COMMUNITY POST BUTTON -->
<!-- Add this to community post cards -->
<button class="report-btn-small report-content-btn" 
        data-content-type="post" 
        data-content-id="<?= $post->id ?>"
        title="Report this post">
    <span><i class="ph ph-warning"></i></span>
</button>


<!-- 4. REPORT CHAT MESSAGE BUTTON -->
<!-- Add this to project chat messages -->
<button class="report-btn-small report-content-btn" 
        data-content-type="chat_message" 
        data-content-id="<?= $message->id ?>"
        title="Report this message">
    <span><i class="ph ph-warning"></i></span>
</button>


<!-- PROGRAMMATIC OPENING (if needed in custom JavaScript) -->
<!-- 
<script>
// Open report modal for a user
openReportModal('user', { userId: 123 });

// Open report modal for project member
openReportModal('projectUser', { userId: 456, projectId: 789 });

// Open report modal for content
openReportModal('content', { contentType: 'post', contentId: 101 });
</script>
-->
