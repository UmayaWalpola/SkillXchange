<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="container org-dashboard">
        <div class="page-header">
            <div>
                <h1>Create New Task</h1>
                <p>Project: <strong><?= htmlspecialchars($project->name) ?></strong></p>
            </div>
            <a href="<?= URLROOT ?>/tasks/project/<?= $projectId ?>" class="btn btn-secondary">← Back to Tasks</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" class="form">
                <div class="form-section">
                    <h3>Task Details</h3>
                    <div class="form-group">
                        <label for="title"><strong>Task Title *</strong></label>
                        <input type="text" id="title" name="title" class="form-control" required value="<?= $_POST['title'] ?? '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="description"><strong>Description</strong></label>
                        <textarea id="description" name="description" class="form-control" rows="5"><?= $_POST['description'] ?? '' ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="priority"><strong>Priority *</strong></label>
                            <select id="priority" name="priority" class="form-control" required>
                                <option value="">Select Priority</option>
                                <option value="low" <?= (($_POST['priority'] ?? '') === 'low') ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= (($_POST['priority'] ?? 'medium') === 'medium') ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= (($_POST['priority'] ?? '') === 'high') ? 'selected' : '' ?>>High</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="deadline"><strong>Deadline</strong></label>
                            <input type="date" id="deadline" name="deadline" class="form-control" value="<?= $_POST['deadline'] ?? '' ?>">
                        </div>
                    </div>
                </div>
                <div class="form-section">
                    <h3>Assignment</h3>
                    <div class="form-group">
                        <label for="assigned_to"><strong>Assign To *</strong></label>
                        <select id="assigned_to" name="assigned_to" class="form-control" required>
                            <option value="">Select Team Member</option>
                            <?php foreach ($members as $member): ?>
                                <option value="<?= $member->user_id ?>" <?= (($_POST['assigned_to'] ?? '') == $member->user_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($member->username) ?> (<?= htmlspecialchars($member->role) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Create Task</button>
                    <a href="<?= URLROOT ?>/tasks/project/<?= $projectId ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
