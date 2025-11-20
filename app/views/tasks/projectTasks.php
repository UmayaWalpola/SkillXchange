<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="container org-dashboard">
        <div class="page-header">
            <div>
                <h1>Project Tasks</h1>
                <p>Project: <strong><?= htmlspecialchars($project->name) ?></strong></p>
            </div>
            <a href="<?= URLROOT ?>/tasks/create/<?= $projectId ?>" class="btn btn-primary">+ Create Task</a>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php
            $overdueCount = isset($overdueTasks) ? count($overdueTasks) : 0;
            $dueTodayCount = isset($dueTodayTasks) ? count($dueTodayTasks) : 0;
            $dueTomorrowCount = isset($dueTomorrowTasks) ? count($dueTomorrowTasks) : 0;
        ?>
        <?php if ($overdueCount || $dueTodayCount || $dueTomorrowCount): ?>
            <div class="alert" style="background:#0f172a;color:#e5e7eb;border-radius:10px;padding:12px 16px;margin-bottom:16px;border:1px solid #1f2937;display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
                <?php if ($overdueCount): ?>
                    <span style="background:#b91c1c;color:#fee2e2;border-radius:999px;padding:4px 10px;font-size:0.85rem;">⚠ You have <?= $overdueCount ?> overdue task<?= $overdueCount>1?'s':''; ?></span>
                <?php endif; ?>
                <?php if ($dueTodayCount): ?>
                    <span style="background:#f97316;color:#fff7ed;border-radius:999px;padding:4px 10px;font-size:0.85rem;">⌛ <?= $dueTodayCount ?> task<?= $dueTodayCount>1?'s':''; ?> due today</span>
                <?php endif; ?>
                <?php if ($dueTomorrowCount): ?>
                    <span style="background:#0ea5e9;color:#e0f2fe;border-radius:999px;padding:4px 10px;font-size:0.85rem;">📅 <?= $dueTomorrowCount ?> task<?= $dueTomorrowCount>1?'s':''; ?> due in 24 hours</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-box total"><div class="stat-number"><?= $stats->total ?? 0 ?></div><div class="stat-label">Total</div></div>
            <div class="stat-box pending"><div class="stat-number"><?= $stats->todo ?? 0 ?></div><div class="stat-label">To Do</div></div>
            <div class="stat-box in-progress"><div class="stat-number"><?= $stats->in_progress ?? 0 ?></div><div class="stat-label">In Progress</div></div>
            <div class="stat-box completed"><div class="stat-number"><?= $stats->done ?? 0 ?></div><div class="stat-label">Done</div></div>
        </div>

        <div class="kanban-board">
            <?php $grouped = $tasksGrouped ?? ['todo'=>[], 'in-progress'=>[], 'done'=>[]]; ?>
            <?php foreach (['todo' => '📋 To Do', 'in-progress' => '⚙️ In Progress', 'done' => '✅ Done'] as $statusKey => $label): ?>
                <div class="kanban-column">
                    <div class="column-header">
                        <h2><?= $label ?></h2>
                        <span class="column-count"><?= isset($grouped[$statusKey]) ? count($grouped[$statusKey]) : 0 ?></span>
                    </div>
                    <div class="tasks-column">
                        <?php if (!empty($grouped[$statusKey])): ?>
                            <?php foreach ($grouped[$statusKey] as $task): ?>
                                <div class="task-card<?= $statusKey === 'done' ? ' completed' : '' ?>" data-task-id="<?= $task->id ?>">
                                    <?php
                                        $badgeLabel = '';
                                        $deadlineClass = '';
                                        if (!empty($task->deadline) && $task->status !== 'done') {
                                            $today = date('Y-m-d');
                                            $d = $task->deadline;
                                            if ($d < $today) {
                                                $days = (new DateTime($d))->diff(new DateTime($today))->days;
                                                $badgeLabel = '⚠ Overdue by ' . $days . ' day' . ($days>1?'s':'');
                                                $deadlineClass = 'badge-overdue';
                                            } elseif ($d === $today) {
                                                $badgeLabel = '⌛ Due Today';
                                                $deadlineClass = 'badge-due-today';
                                            } else {
                                                $days = (new DateTime($today))->diff(new DateTime($d))->days;
                                                $badgeLabel = '📅 Due in ' . $days . ' day' . ($days>1?'s':'');
                                                $deadlineClass = 'badge-due-soon';
                                            }
                                        }
                                    ?>
                                    <div class="task-header">
                                        <h3><?= htmlspecialchars($task->title) ?></h3>
                                        <div style="display:flex;gap:6px;align-items:center;">
                                            <span class="priority-badge priority-<?= $task->priority ?>"><?= ucfirst($task->priority) ?></span>
                                            <?php if ($badgeLabel): ?>
                                                <span class="priority-badge <?= $deadlineClass ?>"><?= $badgeLabel ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="task-description"><?= htmlspecialchars(substr($task->description ?? '', 0, 100)) ?></p>
                                    <div class="task-meta">
                                        <?php if ($task->deadline): ?>
                                            <span class="deadline">📅 <?= date('M d, Y', strtotime($task->deadline)) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="task-assignee">
                                        <?php if ($task->assigned_to): ?>
                                            <div class="assignee-avatar">
                                                <?php if (!empty($task->profile_picture)): ?>
                                                    <img src="<?= URLROOT . '/' . $task->profile_picture ?>" alt="avatar" />
                                                <?php else: ?>
                                                    <div class="avatar-initial"><?= strtoupper(substr($task->username ?? 'U', 0, 1)) ?></div>
                                                <?php endif; ?>
                                                <span class="assignee-name"><?= htmlspecialchars($task->username) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="unassigned">Unassigned</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="task-actions">
                                        <?php if ($statusKey === 'todo'): ?>
                                            <button class="btn-small btn-move" onclick="moveTask(<?= $task->id ?>, 'in-progress')">→ In Progress</button>
                                        <?php elseif ($statusKey === 'in-progress'): ?>
                                            <button class="btn-small btn-move" onclick="moveTask(<?= $task->id ?>, 'done')">→ Done</button>
                                        <?php endif; ?>
                                        <a href="<?= URLROOT ?>/tasks/edit/<?= $task->id ?>" class="btn-small btn-edit">Edit</a>
                                        <button class="btn-small btn-delete" onclick="deleteTask(<?= $task->id ?>)">Delete</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="empty-state">No tasks in this column yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<script>
const URLROOT_JS = '<?= URLROOT ?>';
function moveTask(taskId, newStatus) {
    if (!confirm('Move task to ' + newStatus.replace('-', ' ') + '?')) return;
    fetch(URLROOT_JS + '/tasks/updateStatus/' + taskId + '?status=' + encodeURIComponent(newStatus), { method: 'POST' })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); else alert(data.message || 'Failed to update'); })
        .catch(() => alert('Failed to update task'));
}
function deleteTask(taskId) {
    if (!confirm('Delete this task?')) return;
    window.location.href = URLROOT_JS + '/tasks/delete/' + taskId;
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
