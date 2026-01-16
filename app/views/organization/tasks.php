<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/tasks.css">

<main class="site-main">
    <div class="container org-dashboard">
        <div class="page-header">
            <div>
                <h1>Task Manager</h1>
                <p>Project: <strong><?= htmlspecialchars($data['project']->name) ?></strong></p>
            </div>
            <a href="<?= URLROOT ?>/task/create/<?= $data['projectId'] ?>" class="btn btn-primary">+ Create Task</a>
        </div>

        <!-- Success/Error Messages -->
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- Task Statistics -->
        <div class="stats-grid">
            <div class="stat-box total">
                <div class="stat-number"><?= $data['stats']->total ?? 0 ?></div>
                <div class="stat-label">Total Tasks</div>
            </div>
            <div class="stat-box pending">
                <div class="stat-number"><?= $data['stats']->todo ?? 0 ?></div>
                <div class="stat-label">To Do</div>
            </div>
            <div class="stat-box in-progress">
                <div class="stat-number"><?= $data['stats']->in_progress ?? 0 ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-box completed">
                <div class="stat-number"><?= $data['stats']->done ?? 0 ?></div>
                <div class="stat-label">Done</div>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="kanban-board">
            <!-- TO DO COLUMN -->
            <div class="kanban-column">
                <div class="column-header">
                    <h2>📋 To Do</h2>
                    <span class="column-count"><?= count(array_filter($data['tasks'], fn($t) => $t->status === 'todo')) ?></span>
                </div>
                <div class="tasks-column">
                    <?php foreach ($data['tasks'] as $task): ?>
                        <?php if ($task->status === 'todo'): ?>
                            <div class="task-card" data-task-id="<?= $task->id ?>">
                                <div class="task-header">
                                    <h3><?= htmlspecialchars($task->title) ?></h3>
                                    <span class="priority-badge priority-<?= $task->priority ?>"><?= ucfirst($task->priority) ?></span>
                                </div>

                                <p class="task-description"><?= htmlspecialchars(substr($task->description ?? '', 0, 100)) ?></p>

                                <div class="task-meta">
                                    <?php if ($task->deadline): ?>
                                        <span class="deadline">📅 Due: <?= date('M d, Y', strtotime($task->deadline)) ?></span>
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
                                    <button class="btn-small btn-move" onclick="moveTask(<?= $task->id ?>, 'in-progress')">→ Move</button>
                                    <a href="<?= URLROOT ?>/task/edit/<?= $task->id ?>" class="btn-small btn-edit">Edit</a>
                                    <button class="btn-small btn-delete" onclick="deleteTask(<?= $task->id ?>)">Delete</button>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- IN PROGRESS COLUMN -->
            <div class="kanban-column">
                <div class="column-header">
                    <h2>⚙️ In Progress</h2>
                    <span class="column-count"><?= count(array_filter($data['tasks'], fn($t) => $t->status === 'in-progress')) ?></span>
                </div>
                <div class="tasks-column">
                    <?php foreach ($data['tasks'] as $task): ?>
                        <?php if ($task->status === 'in-progress'): ?>
                            <div class="task-card" data-task-id="<?= $task->id ?>">
                                <div class="task-header">
                                    <h3><?= htmlspecialchars($task->title) ?></h3>
                                    <span class="priority-badge priority-<?= $task->priority ?>"><?= ucfirst($task->priority) ?></span>
                                </div>

                                <p class="task-description"><?= htmlspecialchars(substr($task->description ?? '', 0, 100)) ?></p>

                                <div class="task-meta">
                                    <?php if ($task->deadline): ?>
                                        <span class="deadline">📅 Due: <?= date('M d, Y', strtotime($task->deadline)) ?></span>
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
                                    <button class="btn-small btn-move" onclick="moveTask(<?= $task->id ?>, 'done')">→ Done</button>
                                    <a href="<?= URLROOT ?>/task/edit/<?= $task->id ?>" class="btn-small btn-edit">Edit</a>
                                    <button class="btn-small btn-delete" onclick="deleteTask(<?= $task->id ?>)">Delete</button>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- DONE COLUMN -->
            <div class="kanban-column">
                <div class="column-header">
                    <h2>✅ Done</h2>
                    <span class="column-count"><?= count(array_filter($data['tasks'], fn($t) => $t->status === 'done')) ?></span>
                </div>
                <div class="tasks-column">
                    <?php foreach ($data['tasks'] as $task): ?>
                        <?php if ($task->status === 'done'): ?>
                            <div class="task-card completed" data-task-id="<?= $task->id ?>">
                                <div class="task-header">
                                    <h3><?= htmlspecialchars($task->title) ?></h3>
                                    <span class="priority-badge priority-<?= $task->priority ?>"><?= ucfirst($task->priority) ?></span>
                                </div>

                                <p class="task-description"><?= htmlspecialchars(substr($task->description ?? '', 0, 100)) ?></p>

                                <div class="task-meta">
                                    <?php if ($task->deadline): ?>
                                        <span class="deadline">📅 Due: <?= date('M d, Y', strtotime($task->deadline)) ?></span>
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
                                    <a href="<?= URLROOT ?>/task/edit/<?= $task->id ?>" class="btn-small btn-edit">Edit</a>
                                    <button class="btn-small btn-delete" onclick="deleteTask(<?= $task->id ?>)">Delete</button>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    window.URLROOT = window.URLROOT || '<?= URLROOT ?>';
const projectId = '<?= $data['projectId'] ?>';

function moveTask(taskId, newStatus) {
    if (!confirm('Move task to ' + newStatus.replace('-', ' ') + '?')) return;

    fetch(URLROOT + '/task/status/' + taskId + '/' + newStatus, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update task');
    });
}

function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) return;

    window.location.href = URLROOT + '/task/delete/' + taskId;
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
