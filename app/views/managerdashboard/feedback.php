<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/managersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/manager_dashboard.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">
            
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Platform Feedback</h1>
                    <p>View and manage user feedback</p>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="tab-btn active" onclick="filterFeedback('all')">All Feedback</button>
                <button class="tab-btn" onclick="filterFeedback('new')">New</button>
                <button class="tab-btn" onclick="filterFeedback('reviewed')">Reviewed</button>
            </div>

            <!-- Feedback Cards -->
            <div class="feedback-container">
                <?php if(!empty($data['feedbacks'])): ?>
                    <?php foreach($data['feedbacks'] as $feedback): ?>
                        <div class="feedback-card" data-status="<?= $feedback['status'] ?>" data-feedback-id="<?= $feedback['id'] ?>">
                            <div class="feedback-header">
                                <div class="feedback-user-info">
                                    <div class="feedback-avatar">
                                        <?= strtoupper(substr($feedback['user_name'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <h4 class="feedback-user-name"><?= htmlspecialchars($feedback['user_name']) ?></h4>
                                        <p class="feedback-user-email"><?= htmlspecialchars($feedback['user_email']) ?></p>
                                    </div>
                                </div>
                                <div class="feedback-status-badge">
                                    <?php if($feedback['status'] == 'new'): ?>
                                        <span class="badge badge-warning">New</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Reviewed</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="feedback-rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <span class="star <?= $i <= $feedback['rating'] ? 'filled' : '' ?>">⭐</span>
                                <?php endfor; ?>
                                <span class="rating-text"><?= $feedback['rating'] ?>/5</span>
                            </div>
                            
                            <h3 class="feedback-subject"><?= htmlspecialchars($feedback['subject']) ?></h3>
                            <p class="feedback-message"><?= nl2br(htmlspecialchars($feedback['message'])) ?></p>
                            
                            <div class="feedback-footer">
                                <span class="feedback-date">
                                    📅 <?= date('M d, Y', strtotime($feedback['created_at'])) ?>
                                </span>
                                <?php if($feedback['status'] == 'new'): ?>
                                    <button class="btn-mark-reviewed" onclick="markAsReviewed(<?= $feedback['id'] ?>)">
                                        Mark as Reviewed
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="section-card">
                        <p class="no-data">No feedback received yet</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</main>

<script>
function filterFeedback(status) {
    const cards = document.querySelectorAll('.feedback-card');
    const tabs = document.querySelectorAll('.tab-btn');
    
    // Update active tab
    tabs.forEach(tab => tab.classList.remove('active'));
    event.target.classList.add('active');
    
    // Filter cards
    cards.forEach(card => {
        if (status === 'all') {
            card.style.display = 'block';
        } else {
            if (card.dataset.status === status) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        }
    });
}

function markAsReviewed(feedbackId) {
    if (confirm('Mark this feedback as reviewed?')) {
        fetch('<?= URLROOT ?>/managerdashboard/markFeedbackReviewed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: feedback_id=${feedbackId}
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload(); // Reload to update status
            } else {
                alert('Error updating feedback status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating feedback status');
        });
    }
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>