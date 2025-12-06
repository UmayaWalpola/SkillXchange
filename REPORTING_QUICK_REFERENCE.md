# ⚡ Quick Reference - Reporting System Integration

## 🎯 Add Reporting to Any Page (3 Steps)

### Step 1: Include Assets in View
```php
<!-- Add to <head> or after other CSS -->
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/reporting.css">

<!-- Add before closing </body> -->
<script>window.URLROOT = '<?= URLROOT ?>';</script>
<script src="<?= URLROOT ?>/assets/js/reporting.js"></script>
```

### Step 2: Add Report Button
Choose the appropriate button type:

#### A) Report User Profile
```php
<button class="report-btn report-user-btn" data-user-id="<?= $user->id ?>">
    <span class="report-btn-icon">⚠</span>
    Report Profile
</button>
```

#### B) Report Project Member
```php
<button class="report-btn-small report-project-member-btn" 
        data-user-id="<?= $member->user_id ?>" 
        data-project-id="<?= $project->id ?>"
        title="Report this member">
    <span>⚠</span>
</button>
```

#### C) Report Community Post
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

#### D) Report Chat Message (Dynamic JavaScript)
```javascript
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

### Step 3: That's It!
The reporting system handles everything else automatically:
- ✅ Modal opening
- ✅ Form submission
- ✅ Validation
- ✅ Success/error notifications
- ✅ Modal closing

---

## 📋 Button Classes Reference

| Button Class | Purpose | Size | Style |
|-------------|---------|------|-------|
| `report-btn` | User profiles | Regular | Icon + Text |
| `report-btn-small` | Posts/Messages | 32x32px | Icon Only |
| `report-user-btn` | User reports | - | Grey button |
| `report-project-member-btn` | Member reports | - | Small icon |
| `report-content-btn` | Content reports | - | Small icon |

---

## 🔧 Data Attributes Required

### User Profile Report
- `data-user-id` - ID of user being reported

### Project Member Report
- `data-user-id` - ID of member being reported
- `data-project-id` - ID of project (context)

### Content Report
- `data-content-type` - Either `"post"` or `"chat_message"`
- `data-content-id` - ID of the content

---

## 🎨 CSS Classes Available

### Button Styles
```css
.report-btn              /* Regular button with text */
.report-btn-small        /* Icon-only button */
.report-btn-icon         /* Icon inside button */
```

### Modal Styles
```css
.modal-overlay          /* Background overlay */
.modal-container        /* Modal box */
.modal-header           /* Modal top section */
.modal-title            /* Modal heading */
.modal-close            /* Close button */
.modal-body             /* Modal content area */
.modal-actions          /* Button container */
```

### Form Styles
```css
.form-group             /* Form field wrapper */
.form-control           /* Input/select/textarea */
.btn                    /* Generic button */
.btn-primary            /* Primary action button */
.btn-secondary          /* Secondary action button */
```

### Toast Styles
```css
.toast-notification     /* Notification container */
.toast-success          /* Success message */
.toast-error            /* Error message */
.toast-info             /* Info message */
```

---

## 🔄 API Endpoints

### Report User
```
POST /report/reportUser
Params: reported_user_id, reason, description (optional)
```

### Report Project Member
```
POST /report/reportProjectUser
Params: reported_user_id, project_id, reason, description (optional)
```

### Report Content
```
POST /report/reportContent
Params: content_type, content_id, reason, description (optional)
```

All endpoints return JSON:
```json
{
  "success": true,
  "message": "Your report has been submitted successfully."
}
```

---

## 🗄️ Database Tables

### `reports` - User Profile Reports
- reporter_id, reported_user_id, reason, description, status, created_at

### `user_reports` - Project Member Reports
- reporter_id, reported_user_id, project_id, reason, description, status, created_at

### `content_reports` - Post/Message Reports
- reporter_id, content_type, content_id, reason, description, status, created_at

---

## ⚠️ Important Notes

### Automatic Features
- ✅ Self-reporting prevention (users can't report themselves)
- ✅ Duplicate prevention (one report per content per user)
- ✅ Login check (must be authenticated)
- ✅ Event delegation (works with dynamic content)

### Conditional Display
Always check user is not viewing their own content:
```php
<?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $content_user_id): ?>
    <!-- Report button here -->
<?php endif; ?>
```

### URLROOT Required
Must be defined for AJAX to work:
```javascript
window.URLROOT = '<?= URLROOT ?>';
```

---

## 📖 Full Documentation
- **Complete Guide:** See `REPORTING_SYSTEM.md`
- **Implementation Summary:** See `REPORTING_IMPLEMENTATION_SUMMARY.md`
- **Component Examples:** See `app/views/components/report_button.php`
- **Demo Page:** `public/demo/reporting-demo.html`

---

## 🧪 Testing

### Quick Test
1. Open demo page: `http://localhost/SkillXchange/public/demo/reporting-demo.html`
2. Click any report button
3. Fill modal and submit
4. Verify success toast appears

### Database Verification
```sql
-- Check reports were created
SELECT * FROM content_reports ORDER BY created_at DESC LIMIT 10;
SELECT * FROM reports ORDER BY created_at DESC LIMIT 10;
SELECT * FROM user_reports ORDER BY created_at DESC LIMIT 10;
```

---

## 💡 Pro Tips

1. **For Lists:** Use `report-btn-small` for compact display
2. **For Profiles:** Use `report-btn` with text for clarity
3. **Dynamic Content:** Event delegation handles it automatically
4. **Custom Styling:** Override CSS classes as needed
5. **Mobile:** Responsive by default, works on all screen sizes

---

**Need Help?** Check the comprehensive documentation or inspect the demo page source code.

**Last Updated:** November 26, 2025
