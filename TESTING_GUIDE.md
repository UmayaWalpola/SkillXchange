# 🧪 Testing Guide - Project Progress Overview

## Pre-Testing Checklist

Before testing, ensure:
- ✅ Apache and MySQL are running (XAMPP)
- ✅ Database `skillxchange` is imported with latest SQL
- ✅ You're logged in as an organization (user role = 'organization')
- ✅ You have at least one project with members and tasks
- ✅ Browser cache is cleared (Ctrl+Shift+Delete)

---

## Test Scenario 1: Projects Page Progress Display

### Test Steps:
1. Navigate to: `http://localhost/SkillXchange/public/organization/projects`
2. Verify each project card displays:
   - ✅ Progress section appears below skills
   - ✅ Completion percentage shows (0.0% to 100.0%)
   - ✅ Progress bar fills correctly
   - ✅ Task count shows "X/Y Tasks" format
   - ✅ Overdue warning appears if tasks are overdue

### Expected Result:
```
Progress            65.5%
████████░░░░░░░░░░
✅ 13/20 Tasks
⚠️ 2 Overdue
```

### Test Cases:

**Case 1A: Project with no tasks**
- Expected: Progress = 0%, "0/0 Tasks", no overdue warning

**Case 1B: Project with all tasks completed**
- Expected: Progress = 100%, "20/20 Tasks", no overdue warning

**Case 1C: Project with overdue tasks**
- Expected: Red "⚠️ X Overdue" badge appears

---

## Test Scenario 2: Members Page Progress Overview

### Test Steps:
1. Navigate to: `http://localhost/SkillXchange/public/organization/projects`
2. Click "Members" button on any project
3. Verify Project Progress Overview section appears at top

### Expected Result:

**7 Metric Cards Display:**
1. ✅ Overall Progress (purple gradient, shows %)
2. ✅ Total Tasks (gray, shows count)
3. ✅ To-Do (yellow, shows pending count)
4. ✅ In Progress (blue, shows active count)
5. ✅ Completed (green, shows done count)
6. ✅ Overdue (red border if > 0)
7. ✅ Active Members (purple, shows member count)

### Test Cases:

**Case 2A: Metric Calculations**
- Create 10 tasks total
- Mark 5 as completed, 3 as in_progress, 2 as pending
- Expected: Total=10, Completed=5, In Progress=3, To-Do=2
- Expected: Progress = 50.0%

**Case 2B: Overdue Detection**
- Create task with due_date = yesterday
- Status = 'pending' or 'in_progress'
- Expected: Overdue count = 1, red border on Overdue card

**Case 2C: Active Members Count**
- Assign tasks to 3 different members
- Expected: Active Members = 3

---

## Test Scenario 3: Member Performance Breakdown

### Test Steps:
1. On members page, scroll to "Member Task Performance" section
2. Verify each member shows:
   - ✅ Avatar or initial circle
   - ✅ Username and role badge
   - ✅ Task statistics (total, completed, overdue)
   - ✅ Progress bar with percentage
   - ✅ Hover effect (card shifts right slightly)

### Expected Result:
```
👤 Devinda          [Developer]
📋 8 tasks  ✓ 6 done  ⚠️ 1 overdue
████████████░░░░░░░░ 75.0% complete
```

### Test Cases:

**Case 3A: Member with no tasks**
- Expected: Shows "No tasks assigned yet"
- Expected: No progress bar displays

**Case 3B: Member with all tasks completed**
- Expected: Progress bar = 100%, green
- Expected: No overdue warning

**Case 3C: Member with overdue tasks**
- Expected: "⚠️ X overdue" appears in red

---

## Test Scenario 4: Overdue Tasks Alert

### Test Steps:
1. Create tasks with due_date in the past
2. Ensure status != 'completed'
3. Navigate to members page
4. Verify "Overdue Tasks" section appears

### Expected Result:
```
⚠️ OVERDUE TASKS (2)

┌──────────────────────────────────────┐
│ 📌 Complete Login Module  [3 days]  │
│ Assigned to: Devinda | Due: Nov 23  │
└──────────────────────────────────────┘
```

### Test Cases:

**Case 4A: No overdue tasks**
- Expected: Overdue section does NOT display

**Case 4B: Multiple overdue tasks**
- Expected: Shows up to 5 tasks
- Expected: "... and X more overdue tasks" if > 5

**Case 4C: Days overdue calculation**
- Task due Nov 23, today Nov 26
- Expected: Shows "[3 days]" badge

---

## Test Scenario 5: Real-Time Updates

### Test Steps:
1. Open members page in browser
2. Note current metrics (e.g., 5/10 completed)
3. Mark 2 more tasks as completed
4. Refresh the page (F5)
5. Verify metrics updated

### Expected Result:
- Metrics change to 7/10 completed
- Progress percentage increases
- Completed count increases
- To-Do or In Progress count decreases

### Test Cases:

**Case 5A: Task status change**
- Change task from 'pending' to 'completed'
- Expected: To-Do count -1, Completed count +1, Progress % increases

**Case 5B: New task created**
- Add new task via task assignment modal
- Refresh page
- Expected: Total tasks +1, metrics recalculate

**Case 5C: Task deleted**
- Delete a task
- Refresh page
- Expected: Total tasks -1, metrics recalculate

---

## Test Scenario 6: Cache Performance

### Test Steps:
1. Open browser DevTools (F12) → Network tab
2. Navigate to projects page (observe load time)
3. Wait 1 second
4. Refresh page (F5)
5. Check load time again

### Expected Result:
- Second load should be faster (cache hit)
- Network tab shows fewer SQL queries

### Test Cases:

**Case 6A: Cache Hit**
- First load: ~100-150ms
- Second load (within 5 min): ~50-80ms
- Expected: Faster response

**Case 6B: Cache Miss**
- Wait 6+ minutes
- Refresh page
- Expected: Load time returns to ~100-150ms (cache expired)

**Case 6C: Cache Invalidation**
- Load page (cache populated)
- Create/update a task
- Load page again
- Expected: Metrics reflect new data (cache cleared)

---

## Test Scenario 7: Responsive Design

### Test Steps:
1. Open organization/projects page
2. Open DevTools (F12)
3. Click "Toggle device toolbar" (Ctrl+Shift+M)
4. Test different viewport sizes

### Expected Result:

**Desktop (1920x1080)**
- Grid: 3-4 project cards per row
- All metrics visible horizontally
- Full progress bars

**Tablet (768x1024)**
- Grid: 2 project cards per row
- Metrics stack in 2 columns
- Progress bars adjust width

**Mobile (375x667)**
- Grid: 1 project card per row
- Metrics stack vertically
- Compact spacing
- Progress section readable

---

## Test Scenario 8: UI/UX Consistency

### Test Steps:
1. Compare new progress cards with existing UI
2. Verify color scheme matches
3. Check font sizes and spacing
4. Test hover effects

### Expected Result:
- ✅ Colors match existing design (purple, blue, green, red)
- ✅ Border radius = 12px (consistent)
- ✅ Font = Poppins
- ✅ Shadows = 0 2px 4px rgba(0,0,0,0.08)
- ✅ Hover effects smooth (0.3s transition)

---

## Test Scenario 9: Error Handling

### Test Cases:

**Case 9A: Project with no members**
- Expected: Active Members = 0
- Expected: No member breakdown section displays

**Case 9B: Invalid project ID**
- Navigate to `/organization/members/99999`
- Expected: Redirect to projects page or error message

**Case 9C: Database connection error**
- Stop MySQL temporarily
- Load page
- Expected: Graceful error (not white screen)

---

## Test Scenario 10: Security & Access Control

### Test Steps:
1. Log out
2. Try to access `/organization/projects`
3. Expected: Redirect to login page

### Test Cases:

**Case 10A: Non-organization user**
- Log in as individual user (role='individual')
- Try to access organization pages
- Expected: Access denied or redirect

**Case 10B: Access another org's project**
- Log in as Organization A
- Try to access Organization B's project members page
- Expected: "Access denied" error

---

## Automated Test Commands (Optional)

If you want to create automated tests, here's a structure:

```php
// tests/TaskMetricsTest.php

class TaskMetricsTest extends TestCase {
    
    public function testProjectMetricsCalculation() {
        $taskModel = new Task();
        $metrics = $taskModel->getProjectMetrics(13);
        
        $this->assertNotNull($metrics);
        $this->assertObjectHasAttribute('total_tasks', $metrics);
        $this->assertObjectHasAttribute('completion_percentage', $metrics);
    }
    
    public function testOverdueDetection() {
        // Create task with past due date
        // Assert it appears in overdue list
    }
    
    public function testCacheInvalidation() {
        // Load metrics (cache)
        // Update task
        // Load metrics again
        // Assert data changed
    }
}
```

---

## Bug Reporting Template

If you find issues, report them using this format:

```
**Bug Title**: Progress bar doesn't fill correctly

**Steps to Reproduce**:
1. Navigate to /organization/projects
2. Click on project "Zcode"
3. Observe progress bar

**Expected Behavior**: 
Progress bar fills to 65.5%

**Actual Behavior**: 
Progress bar is empty or stuck at 0%

**Screenshots**: 
[Attach screenshot]

**Environment**:
- Browser: Chrome 120
- OS: Windows 11
- PHP Version: 8.2.12
- Database: Latest SQL dump
```

---

## Performance Benchmarks

Use these benchmarks to verify optimization:

| Metric | Before | After (Target) | How to Measure |
|--------|--------|----------------|----------------|
| Projects page load | 150-200ms | 50-80ms | DevTools Network tab |
| Members page load | 200-300ms | 100-150ms | DevTools Network tab |
| Cache hit rate | N/A | >80% | Check metrics cache usage |
| Database queries | 10-15/page | 3-5/page | MySQL slow query log |

---

## Final Acceptance Checklist

Before marking as "Production Ready":

- ✅ All 10 test scenarios pass
- ✅ No console errors (F12)
- ✅ No PHP errors in error logs
- ✅ Responsive design works on all devices
- ✅ Performance meets benchmarks
- ✅ UI matches existing design system
- ✅ Security checks pass
- ✅ Cache works as expected
- ✅ All metrics calculate correctly
- ✅ Real-time updates work

---

## Known Limitations

1. **Cache Duration**: 5 minutes (configurable)
2. **Overdue Display Limit**: Shows max 5 overdue tasks
3. **Member Breakdown**: Only shows active members
4. **Browser Support**: Modern browsers only (IE not supported)

---

## Support & Troubleshooting

### Common Issues:

**Issue: Progress shows 0% but tasks exist**
- Solution: Check task status values ('pending', 'in_progress', 'completed')
- Solution: Clear cache by updating any task

**Issue: Overdue tasks don't appear**
- Solution: Verify due_date column has valid dates
- Solution: Check status != 'completed'

**Issue: Member breakdown missing**
- Solution: Verify project_members table has entries
- Solution: Check users are assigned to tasks

**Issue: Styles don't load**
- Solution: Clear browser cache (Ctrl+Shift+Delete)
- Solution: Verify CSS file paths are correct
- Solution: Check file permissions on CSS files

---

## Testing Completion Report

After testing, fill out this report:

```
TESTING COMPLETED: [Date]
TESTER: [Your Name]

┌─────────────────────────────────────────┐
│ TEST SCENARIO         │ PASS │ FAIL │   │
├─────────────────────────────────────────┤
│ 1. Projects Progress  │  ✅  │      │   │
│ 2. Members Overview   │  ✅  │      │   │
│ 3. Member Performance │  ✅  │      │   │
│ 4. Overdue Alerts     │  ✅  │      │   │
│ 5. Real-Time Updates  │  ✅  │      │   │
│ 6. Cache Performance  │  ✅  │      │   │
│ 7. Responsive Design  │  ✅  │      │   │
│ 8. UI Consistency     │  ✅  │      │   │
│ 9. Error Handling     │  ✅  │      │   │
│ 10. Security          │  ✅  │      │   │
└─────────────────────────────────────────┘

OVERALL STATUS: ✅ PASS / ❌ FAIL

NOTES:
[Add any observations or issues found]

PRODUCTION READY: YES / NO
```

---

**Start Testing**: Just open `http://localhost/SkillXchange/public/organization/projects` and follow the scenarios! 🚀
