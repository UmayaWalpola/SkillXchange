<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/managersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/manager_dashboard.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">

            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>System Announcements</h1>
                    <p>Post and manage platform announcements</p>
                </div>
                <button class="btn-primary" onclick="toggleAddForm()">+ New Announcement</button>
            </div>

            <!-- Success / Error Messages -->
            <?php if (!empty($data['success'])): ?>
                <div class="success-message"><?= htmlspecialchars($data['success']) ?></div>
            <?php endif; ?>
            <?php if (!empty($data['error'])): ?>
                <div class="error-message"><?= htmlspecialchars($data['error']) ?></div>
            <?php endif; ?>

            <!-- Add Announcement Form (hidden by default) -->
            <div id="addAnnouncementForm" class="section-card inline-form-panel">
                <h2 class="section-title">New Announcement</h2>
                <form method="POST" action="<?= URLROOT ?>/manager/addAnnouncement">
                    <div class="form-group">
                        <label for="announcement-title">Title</label>
                        <input id="announcement-title" type="text" name="title" required placeholder="Enter announcement title">
                    </div>
                    <div class="form-group">
                        <label for="announcement-content">Content</label>
                        <textarea id="announcement-content" name="content" rows="5" required
                            placeholder="Enter announcement content..."></textarea>
                    </div>
                    <div class="form-footer">
                        <button type="submit" class="btn-primary">Post Announcement</button>
                        <button type="button" onclick="toggleAddForm()" class="btn-cancel">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Announcements List -->
            <div class="announcements-container">
                <?php if (!empty($data['announcements'])): ?>
                    <?php foreach ($data['announcements'] as $announcement): ?>
                        <div class="announcement-card">

                            <!-- Announcement Header -->
                            <div class="announcement-header">
                                <div>
                                    <h3 class="announcement-title"><?= htmlspecialchars($announcement->title) ?></h3>
                                    <p class="announcement-meta">
                                        By <?= htmlspecialchars($announcement->author) ?> &bull;
                                        <?= date('M d, Y', strtotime($announcement->created_at)) ?>
                                    </p>
                                </div>
                                <button class="btn-outline" onclick="toggleEditForm(<?= $announcement->id ?>)">
                                    Edit
                                </button>
                            </div>

                            <!-- Announcement Content -->
                            <p class="announcement-content"><?= nl2br(htmlspecialchars($announcement->content)) ?></p>

                            <!-- Inline Edit Form -->
                            <div id="edit-<?= $announcement->id ?>" class="inline-form-panel">
                                <form method="POST" action="<?= URLROOT ?>/manager/updateAnnouncement">
                                    <input type="hidden" name="announcement_id" value="<?= $announcement->id ?>">
                                    <div class="form-group">
                                        <label for="edit-title-<?= $announcement->id ?>">Title</label>
                                        <input id="edit-title-<?= $announcement->id ?>" type="text" name="title" required
                                            value="<?= htmlspecialchars($announcement->title) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-content-<?= $announcement->id ?>">Content</label>
                                        <textarea id="edit-content-<?= $announcement->id ?>" name="content" rows="4" required><?= htmlspecialchars($announcement->content) ?></textarea>
                                    </div>
                                    <div class="form-footer">
                                        <button type="submit" class="btn-primary">
                                            Save Changes
                                        </button>
                                        <button type="button" onclick="toggleEditForm(<?= $announcement->id ?>)" class="btn-cancel">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="section-card">
                        <p class="no-data">No announcements posted yet</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</main>

<script>
function toggleAddForm() {
    const form = document.getElementById('addAnnouncementForm');
    form.classList.toggle('open');
}

function toggleEditForm(id) {
    const form = document.getElementById('edit-' + id);
    form.classList.toggle('open');
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>