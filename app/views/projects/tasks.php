<?php require_once "../app/views/layouts/header_user.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/tasks.css">

<main class="site-main">
    <div class="container">
        <div class="page-header">
            <div>
                <h1>My Tasks</h1>
                <p>Project: <strong><?= htmlspecialchars($data['project']->name) ?></strong></p>
            </div>
            <a href="<?= URLROOT ?>/project/view/<?= $data['project']->id ?>" class="btn btn-secondary">← Back to Project</a>
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

        <!-- User Tasks List -->
        <div class="user-tasks-container">
            <?php if (empty($data['tasks'])): ?>
                <div class="empty-state">
                    <div class="empty-icon">✓</div>
                    <h3>No Tasks Assigned</h3>
                    <p>You don't have any tasks assigned to you in this project yet.</p>
                </div>
            <?php else: ?>
                <!-- To Do Tasks -->
                <div class="task-section">
                    <h2>📋 To Do</h2>
                    <div class="tasks-grid">
                        <?php foreach ($data['tasks'] as $task): ?>
                            <?php if ($task->status === 'todo'): ?>
                                <div class="user-task-card">
                                    <div class="task-header">
                                        <h3><?= htmlspecialchars($task->title) ?></h3>
                                        <span class="priority-badge priority-<?= $task->priority ?>"><?= ucfirst($task->priority) ?></span>
                                    </div>

                                    <p class="task-description"><?= htmlspecialchars($task->description ?? '') ?></p>

                                    <div class="task-meta">
                                        <?php if ($task->deadline): ?>
                                            <span class="deadline">📅 Due: <?= date('M d, Y', strtotime($task->deadline)) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="task-actions">
                                        <button class="btn-small btn-move" onclick="updateTaskStatus(<?= $task->id ?>, 'in-progress')">Start Working</button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- In Progress Tasks -->
                <div class="task-section">
                    <h2>⚙️ In Progress</h2>
                    <div class="tasks-grid">
                        <?php foreach ($data['tasks'] as $task): ?>
                            <?php if ($task->status === 'in-progress'): ?>
                                <div class="user-task-card in-progress">
                                    <div class="task-header">
                                        <h3><?= htmlspecialchars($task->title) ?></h3>
                                        <span class="priority-badge priority-<?= $task->priority ?>"><?= ucfirst($task->priority) ?></span>
                                    </div>

                                    <p class="task-description"><?= htmlspecialchars($task->description ?? '') ?></p>

                                    <div class="task-meta">
                                        <?php if ($task->deadline): ?>
                                            <span class="deadline">📅 Due: <?= date('M d, Y', strtotime($task->deadline)) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="task-actions">
                                        <button class="btn-small btn-move" onclick="updateTaskStatus(<?= $task->id ?>, 'done')">Mark Complete</button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Completed Tasks -->
                <div class="task-section">
                    <h2>✅ Completed</h2>
                    <div class="tasks-grid">
                        <?php foreach ($data['tasks'] as $task): ?>
                            <?php if ($task->status === 'done'): ?>
                                <div class="user-task-card completed">
                                    <div class="task-header">
                                        <h3><?= htmlspecialchars($task->title) ?></h3>
                                        <span class="priority-badge priority-<?= $task->priority ?>"><?= ucfirst($task->priority) ?></span>
                                    </div>

                                    <p class="task-description"><?= htmlspecialchars($task->description ?? '') ?></p>

                                    <div class="task-meta">
                                        <?php if ($task->deadline): ?>
                                            <span class="deadline">📅 Due: <?= date('M d, Y', strtotime($task->deadline)) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
window.URLROOT = window.URLROOT || '<?= URLROOT ?>';

function updateTaskStatus(taskId, newStatus) {
    const statusText = newStatus === 'in-progress' ? 'In Progress' : 'Completed';
    if (!confirm('Mark task as ' + statusText + '?')) return;

    fetch(URLROOT + '/task/updateStatus/' + taskId + '/' + newStatus, {
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
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
