# SkillXchange Reporting System Documentation

## Overview
The SkillXchange reporting system allows users to report inappropriate content, spam, harassment, and policy violations across the platform. The system is fully integrated into the existing MVC structure and follows the project's vanilla PHP, HTML, CSS, and JavaScript architecture.

---

## Features

### 1. **Report Types**
- **User Profiles** - Report fake profiles, spam accounts, or harassment
- **Project Members** - Report members within a project context
- **Community Posts** - Report inappropriate forum posts
- **Project Chat Messages** - Report offensive or spam messages

### 2. **Report Reasons**
- Spam
- Harassment
- Hate speech
- Fake profile
- Inappropriate content
- Other

### 3. **Security Features**
- ✅ Users cannot report themselves
- ✅ Duplicate report prevention (one report per user per content)
- ✅ Login required to submit reports
- ✅ Content ownership verification
- ✅ AJAX-based submission (no page refresh)

---

## Architecture

### Database Tables

#### 1. `content_reports` - For posts and chat messages
```sql
CREATE TABLE content_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  content_type ENUM('post','chat_message') NOT NULL,
  content_id INT NOT NULL,
  reason VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  status ENUM('pending','reviewed','dismissed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 2. `reports` - For user profiles
```sql
CREATE TABLE reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  reported_user_id INT NOT NULL,
  reason VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  status ENUM('pending','reviewed','dismissed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 3. `user_reports` - For project members
```sql
CREATE TABLE user_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reporter_id INT NOT NULL,
  reported_user_id INT NOT NULL,
  project_id INT NOT NULL,
  reason VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  status ENUM('pending','reviewed','dismissed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### MVC Components

#### Controller
- **File**: `app/controllers/ReportController.php`
- **Methods**:
  - `reportUser()` - POST /report/reportUser
  - `reportProjectUser()` - POST /report/reportProjectUser
  - `reportContent()` - POST /report/reportContent

#### Views
- **Modal Component**: `app/views/components/report_button.php`
- **Integrated into**:
  - User profiles (`app/views/users/view_profile.php`)
  - Community posts (`app/views/community/view.php`)
  - Project members (`app/views/projects/view.php`)
  - Project chat (`app/views/organization/chats.php`)

#### Assets
- **CSS**: `public/assets/css/reporting.css`
- **JavaScript**: `public/assets/js/reporting.js`

---

## Implementation Guide

### Step 1: Database Setup
```powershell
# Run the migration to create all tables
& "C:\xampp\mysql\bin\mysql.exe" -u root skillxchange < database/migrations/setup_reporting_system.sql
```

### Step 2: Add Report Buttons to Views

#### Example 1: User Profile Report Button
```php
<button class="report-btn report-user-btn" data-user-id="<?= $user->id ?>">
    <span class="report-btn-icon">⚠</span>
    Report Profile
</button>
```

#### Example 2: Project Member Report Button
```php
<button class="report-btn-small report-project-member-btn" 
        data-user-id="<?= $member->user_id ?>" 
        data-project-id="<?= $project->id ?>"
        title="Report this member">
    <span>⚠</span>
</button>
```

#### Example 3: Community Post Report Button
```php
<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $post->user_id): ?>
<button class="report-btn-small report-content-btn" 
        data-content-type="post" 
        data-content-id="<?= $post->id ?>"
        title="Report this post">
    <span>⚠</span>
</button>
<?php endif; ?>
```

#### Example 4: Chat Message Report Button
```javascript
// In JavaScript for dynamically rendered messages
if (!isMine) {
    const reportBtn = document.createElement('button');
    reportBtn.className = 'report-btn-small report-content-btn';
    reportBtn.setAttribute('data-content-type', 'chat_message');
    reportBtn.setAttribute('data-content-id', messageId);
    reportBtn.title = 'Report this message';
    reportBtn.innerHTML = '<span>⚠</span>';
    messageElement.appendChild(reportBtn);
}
```

### Step 3: Include Assets in Views
```php
<!-- Add CSS -->
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/reporting.css">

<!-- Add JavaScript -->
<script>window.URLROOT = '<?= URLROOT ?>';</script>
<script src="<?= URLROOT ?>/assets/js/reporting.js"></script>
```

---

## API Endpoints

### POST /report/reportUser
Report a user profile.

**Parameters:**
- `reported_user_id` (int) - ID of the user being reported
- `reason` (string) - Reason for report
- `description` (text, optional) - Additional details

**Response:**
```json
{
  "success": true,
  "message": "Your report has been submitted successfully."
}
```

### POST /report/reportProjectUser
Report a project member.

**Parameters:**
- `reported_user_id` (int) - ID of the member being reported
- `project_id` (int) - ID of the project
- `reason` (string) - Reason for report
- `description` (text, optional) - Additional details

**Response:**
```json
{
  "success": true,
  "message": "Your report has been submitted successfully."
}
```

### POST /report/reportContent
Report a post or chat message.

**Parameters:**
- `content_type` (enum: 'post', 'chat_message')
- `content_id` (int) - ID of the content
- `reason` (string) - Reason for report
- `description` (text, optional) - Additional details

**Response:**
```json
{
  "success": true,
  "message": "Your report has been submitted successfully."
}
```

---

## UI Design

### Modal Design
- **Background**: Soft white (#FFFFFF)
- **Header**: Blue gradient (matching project palette)
- **Rounded corners**: 12px
- **Shadow**: Subtle drop shadow for depth
- **Backdrop**: Blurred overlay (rgba + backdrop-filter)

### Button Styles
- **Regular Button**: Grey background with icon and text
- **Small Button**: Icon-only, transparent, 32x32px
- **Hover**: Darker grey background
- **Colors**: Matches existing project design system

### Toast Notifications
- **Success**: Green gradient (#10b981 to #059669)
- **Error**: Red gradient (#ef4444 to #dc2626)
- **Position**: Top-right corner
- **Auto-dismiss**: 3 seconds
- **Animation**: Slide in from right

---

## Testing

### Test Scenarios

1. **Report User Profile**
   - Go to any user profile
   - Click "Report Profile" button
   - Fill out modal with reason
   - Submit and verify success toast
   - Try submitting duplicate (should fail)

2. **Report Community Post**
   - Navigate to a community
   - Find a post from another user
   - Click small ⚠ icon
   - Submit report
   - Verify toast confirmation

3. **Report Project Member**
   - Open a project detail page
   - Find team members section
   - Click ⚠ on a member card
   - Submit report with project context

4. **Report Chat Message**
   - Open project chat
   - Find message from another user
   - Click ⚠ button on message bubble
   - Submit report

### Validation Tests
- ✅ Cannot report own profile/content
- ✅ Cannot submit duplicate reports
- ✅ Must be logged in to report
- ✅ All fields validated before submission
- ✅ Modal closes automatically on success

---

## Files Modified/Created

### Created Files
1. `app/controllers/ReportController.php`
2. `app/views/components/report_button.php`
3. `public/assets/css/reporting.css`
4. `public/assets/js/reporting.js`
5. `database/migrations/create_content_reports_table.sql`
6. `database/migrations/setup_reporting_system.sql`

### Modified Files
1. `app/views/users/view_profile.php` - Added report button to user profiles
2. `app/views/community/view.php` - Added report buttons to posts
3. `app/views/users/community_forum.php` - Added report buttons to forum posts
4. `app/views/projects/view.php` - Added report buttons to project members
5. `app/views/organization/chats.php` - Added report buttons to chat messages
6. `public/assets/js/community_forum.js` - Added report buttons to dynamic posts

---

## Admin Features (Future Enhancement)

The system is ready for admin reporting dashboard implementation:

### Suggested Admin Views
1. **Reports Overview** - All pending reports
2. **Filter by Type** - User/Content/Project reports
3. **Bulk Actions** - Approve/Dismiss multiple reports
4. **Report Details** - View reporter, reported user, content, reason
5. **Actions** - Mark as reviewed, dismiss, ban user, remove content

### Database Query Examples
```sql
-- Get all pending reports
SELECT * FROM content_reports WHERE status = 'pending' ORDER BY created_at DESC;

-- Get reports for a specific user
SELECT * FROM reports WHERE reported_user_id = ? ORDER BY created_at DESC;

-- Get content reports with user details
SELECT cr.*, u1.username as reporter_name, u2.username as content_author
FROM content_reports cr
JOIN users u1 ON cr.reporter_id = u1.id
LEFT JOIN posts p ON cr.content_id = p.id AND cr.content_type = 'post'
LEFT JOIN users u2 ON p.user_id = u2.id
WHERE cr.status = 'pending';
```

---

## Maintenance

### Regular Tasks
1. **Review Reports** - Check pending reports daily
2. **Database Cleanup** - Archive old reviewed reports monthly
3. **Analytics** - Track report patterns to identify problematic users
4. **Policy Updates** - Update report reasons as needed

### Performance Considerations
- Indexes added on frequently queried columns (status, content_type, content_id)
- Foreign keys ensure referential integrity
- Soft cascading on user deletion maintains report history

---

## Support

For issues or enhancements:
1. Check that all migrations have been run
2. Verify URLROOT is defined in views
3. Ensure user is logged in before testing
4. Check browser console for JavaScript errors
5. Verify database tables exist and have correct schema

---

## Version History

**v1.0.0** - Initial Release
- User profile reporting
- Content reporting (posts & chat messages)
- Project member reporting
- Unified modal interface
- AJAX submission
- Success/error notifications
- Duplicate prevention
- Self-reporting prevention

---

*Built with pure PHP, HTML, CSS, and JavaScript - No frameworks required!*
