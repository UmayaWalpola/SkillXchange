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
                <button class="btn-primary" onclick="openAddAnnouncementModal()">+ New Announcement</button>
            </div>

            <!-- Announcements List -->
            <div class="announcements-container">
                <?php if(!empty($data['announcements'])): ?>
                    <?php foreach($data['announcements'] as $announcement): ?>
                        <div class="announcement-card" data-announcement-id="<?= $announcement['id'] ?>">
                            <div class="announcement-header">
                                <div>
                                    <h3 class="announcement-title"><?= htmlspecialchars($announcement['title']) ?></h3>
                                    <p class="announcement-meta">
                                        By <?= htmlspecialchars($announcement['author']) ?> • 
                                        <?= date('M d, Y', strtotime($announcement['created_at'])) ?>
                                    </p>
                                </div>
                                <button class="btn-icon btn-delete" title="Delete Announcement" onclick="deleteAnnouncement(<?= $announcement['id'] ?>, '<?= htmlspecialchars($announcement['title']) ?>')">
                                    Remove
                                </button>
                            </div>
                            <p class="announcement-content"><?= nl2br(htmlspecialchars($announcement['content'])) ?></p>
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

<!-- Add Announcement Modal -->
<div id="addAnnouncementModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <span class="close" onclick="closeAddAnnouncementModal()">&times;</span>
        <h2>Post New Announcement</h2>
        <form id="addAnnouncementForm" style="margin-top: 20px;">
            <div style="margin-bottom: 15px;">
                <label for="announcementTitle">Announcement Title</label>
                <input type="text" id="announcementTitle" name="title" required placeholder="Enter announcement title">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label for="announcementContent">Content</label>
                <textarea id="announcementContent" name="content" required rows="8" style="width: 100%; padding: 15px 20px; border-radius: 10px; border: 2px solid var(--primary-blue); font-size: 1rem; background-color: var(--blue-bg); font-family: Arial, sans-serif; resize: vertical;" placeholder="Enter announcement content"></textarea>
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%;">Post Announcement</button>
        </form>
    </div>
</div>

<script>
function openAddAnnouncementModal() {
    document.getElementById('addAnnouncementModal').style.display = 'block';
}

function closeAddAnnouncementModal() {
    document.getElementById('addAnnouncementModal').style.display = 'none';
    document.getElementById('addAnnouncementForm').reset();
}

document.getElementById('addAnnouncementForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= URLROOT ?>/managerdashboard/addAnnouncement', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeAddAnnouncementModal();
            location.reload(); // Reload to show new announcement
        } else {
            alert('Error posting announcement');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error posting announcement');
    });
});

function deleteAnnouncement(announcementId, title) {
    if (confirm(Are you sure you want to delete "${title}"? This action cannot be undone.)) {
        fetch('<?= URLROOT ?>/managerdashboard/deleteAnnouncement', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: announcement_id=${announcementId}
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                document.querySelector(div[data-announcement-id="${announcementId}"]).remove();
            } else {
                alert('Error deleting announcement');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting announcement');
        });
    }
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('addAnnouncementModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>