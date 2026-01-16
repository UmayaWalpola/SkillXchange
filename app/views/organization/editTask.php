<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="container org-dashboard">
        <div class="page-header">
            <div>
                <h1>Edit Task</h1>
                <p>Project: <strong><?= htmlspecialchars($data['project']->name) ?></strong></p>
            </div>
            <a href="<?= URLROOT ?>/task/index/<?= $data['task']->project_id ?>" class="btn btn-secondary">← Back to Tasks</a>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($data['errors'])): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($data['errors'] as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Edit Task Form -->
        <div class="form-container">
            <form method="POST" class="form">
                <div class="form-section">
                    <h3>Task Details</h3>

                    <div class="form-group">
                        <label for="title"><strong>Task Title *</strong></label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="Enter task title" required value="<?= htmlspecialchars($data['task']->title) ?>">
                    </div>

                    <div class="form-group">
                        <label for="description"><strong>Description</strong></label>
                        <textarea id="description" name="description" class="form-control" rows="5" placeholder="Enter task description"><?= htmlspecialchars($data['task']->description ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="priority"><strong>Priority *</strong></label>
                            <select id="priority" name="priority" class="form-control" required>
                                <option value="">Select Priority</option>
                                <option value="low" <?= $data['task']->priority === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= $data['task']->priority === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= $data['task']->priority === 'high' ? 'selected' : '' ?>>High</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="deadline"><strong>Deadline</strong></label>
                            <input type="date" id="deadline" name="deadline" class="form-control" value="<?= $data['task']->deadline ?? '' ?>">
                        </div>

                        <div class="form-group">
                            <label for="status"><strong>Status</strong></label>
                            <select id="status" name="status" class="form-control" disabled>
                                <option value="todo" <?= $data['task']->status === 'todo' ? 'selected' : '' ?>>To Do</option>
                                <option value="in-progress" <?= $data['task']->status === 'in-progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="done" <?= $data['task']->status === 'done' ? 'selected' : '' ?>>Done</option>
                            </select>
                            <small class="form-text">Use the Kanban board to change status</small>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Assignment</h3>

                    <div class="form-group">
                        <label for="assigned_to"><strong>Assign To</strong></label>
                        <select id="assigned_to" name="assigned_to" class="form-control">
                            <option value="">Unassigned</option>
                            <?php if (!empty($data['members'])): ?>
                                <?php foreach ($data['members'] as $member): ?>
                                    <option value="<?= $member->user_id ?>" <?= $data['task']->assigned_to == $member->user_id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($member->username) ?> (<?= htmlspecialchars($member->role) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Task</button>
                    <a href="<?= URLROOT ?>/task/index/<?= $data['task']->project_id ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
