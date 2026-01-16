<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">
            <div class="page-header">
                <h1>My Tasks</h1>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
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
                                    <span style="background:#b91c1c;color:#fee2e2;border-radius:999px;padding:4px 10px;">⚠ You have <?= $overdueCount ?> overdue task<?= $overdueCount>1?'s':''; ?></span>
                    <?php endif; ?>
                    <?php if ($dueTodayCount): ?>
                                    <span style="background:#f97316;color:#fff7ed;border-radius:999px;padding:4px 10px;">⌛ <?= $dueTodayCount ?> task<?= $dueTodayCount>1?'s':''; ?> due today</span>
                    <?php endif; ?>
                    <?php if ($dueTomorrowCount): ?>
                                    <span style="background:#0ea5e9;color:#e0f2fe;border-radius:999px;padding:4px 10px;">📅 <?= $dueTomorrowCount ?> task<?= $dueTomorrowCount>1?'s':''; ?> due in 24 hours</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($tasks)): ?>
                <p class="empty-state">You have no assigned tasks yet.</p>
            <?php else: ?>
                <div class="cards-grid">
                    <?php foreach ($tasks as $task): ?>
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
                        <div class="card">
                            <h3><?= htmlspecialchars($task->title) ?></h3>
                            <p class="text-muted">Project: <?= htmlspecialchars($task->project_name) ?></p>
                            <p><?= htmlspecialchars(substr($task->description ?? '', 0, 140)) ?></p>
                            <div class="badge-row">
                                <span class="badge">Priority: <?= ucfirst($task->priority) ?></span>
                                <?php if ($task->deadline): ?>
                                    <span class="badge">Due <?= date('M d, Y', strtotime($task->deadline)) ?></span>
                                <?php endif; ?>
                                <span class="badge status-<?= $task->status ?>"><?= ucfirst(str_replace('-', ' ', $task->status)) ?></span>
                                <?php if ($badgeLabel): ?>
                                    <span class="badge <?= $deadlineClass ?>"><?= $badgeLabel ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-actions" style="margin-top:1rem;">
                                <?php if ($task->status === 'todo'): ?>
                                    <form method="post" action="<?= URLROOT ?>/tasks/updateStatus/<?= $task->id ?>" style="display:inline;">
                                        <input type="hidden" name="status" value="in-progress">
                                        <button type="submit" class="btn btn-primary">Mark as In Progress</button>
                                    </form>
                                <?php elseif ($task->status === 'in-progress'): ?>
                                    <form method="post" action="<?= URLROOT ?>/tasks/updateStatus/<?= $task->id ?>" style="display:inline;">
                                        <input type="hidden" name="status" value="done">
                                        <button type="submit" class="btn btn-primary">Mark as Done</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Completed</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
