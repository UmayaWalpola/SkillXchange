<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="project-detail-container">
        
        <!-- Navigation -->
        <div class="detail-nav">
            <button class="back-btn" onclick="window.location.href='<?= URLROOT ?>/organization/projects'">
                ← Back to Projects
            </button>
            <div class="detail-actions">
                <button class="action-btn edit-btn" onclick="window.location.href='<?= URLROOT ?>/organization/editProject/<?= $data['project']->id ?>'">
                    ✏️ Edit Project
                </button>
                <button class="action-btn delete-btn" onclick="deleteProject(<?= $data['project']->id ?>, '<?= htmlspecialchars($data['project']->name) ?>')">
                    🗑️ Delete Project
                </button>
            </div>
        </div>

        <!-- Project Header -->
        <div class="project-detail-header">
            <div class="project-icon-large <?= $data['project']->category ?>">
                <?php 
                    $icons = [
                        'web' => '💻',
                        'mobile' => '📱',
                        'data' => '📊',
                        'design' => '🎨',
                        'other' => '📁'
                    ];
                    echo $icons[$data['project']->category] ?? '📁';
                ?>
            </div>
            <div class="header-content">
                <h1><?= htmlspecialchars($data['project']->name) ?></h1>
                <div class="header-meta">
                    <span class="status-badge <?= $data['project']->status ?>">
                        <?= ucfirst(str_replace('-', ' ', $data['project']->status)) ?>
                    </span>
                    <span class="category-badge">
                        📂 <?= ucfirst($data['project']->category) ?>
                    </span>
                    <span class="members-badge">
                        👥 <?= $data['project']->current_members ?? 0 ?>/<?= $data['project']->max_members ?> Members
                    </span>
                </div>
            </div>
        </div>

        <!-- Project Details Grid -->
        <div class="project-details-grid">
            
            <!-- Description Section -->
            <div class="detail-card full-width">
                <h2 class="card-title">📝 Project Description</h2>
                <p class="project-description-full">
                    <?= nl2br(htmlspecialchars($data['project']->description)) ?>
                </p>
            </div>

            <!-- Skills Section -->
            <div class="detail-card">
                <h2 class="card-title">🎯 Required Skills</h2>
                <div class="skills-list">
                    <?php 
                    $skills = explode(',', $data['project']->required_skills);
                    foreach($skills as $skill): 
                    ?>
                        <span class="skill-badge"><?= trim(htmlspecialchars($skill)) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Timeline Section -->
            <div class="detail-card">
                <h2 class="card-title">📅 Timeline</h2>
                <div class="timeline-info">
                    <div class="timeline-item">
                        <span class="timeline-label">Created:</span>
                        <span class="timeline-value"><?= date('M d, Y', strtotime($data['project']->created_at)) ?></span>
                    </div>
                    <?php if($data['project']->start_date): ?>
                    <div class="timeline-item">
                        <span class="timeline-label">Start Date:</span>
                        <span class="timeline-value"><?= date('M d, Y', strtotime($data['project']->start_date)) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if($data['project']->end_date): ?>
                    <div class="timeline-item">
                        <span class="timeline-label">End Date:</span>
                        <span class="timeline-value"><?= date('M d, Y', strtotime($data['project']->end_date)) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="timeline-item">
                        <span class="timeline-label">Last Updated:</span>
                        <span class="timeline-value"><?= date('M d, Y', strtotime($data['project']->updated_at)) ?></span>
                    </div>
                </div>
            </div>

            <!-- Project Info Section -->
            <div class="detail-card full-width">
                <h2 class="card-title">ℹ️ Project Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Project ID:</span>
                        <span class="info-value">#<?= $data['project']->id ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Category:</span>
                        <span class="info-value"><?= ucfirst($data['project']->category) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status:</span>
                        <span class="info-value"><?= ucfirst(str_replace('-', ' ', $data['project']->status)) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Team Size:</span>
                        <span class="info-value"><?= $data['project']->current_members ?? 0 ?> / <?= $data['project']->max_members ?> members</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<style>
.project-detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.detail-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.back-btn {
    background: none;
    border: none;
    color: #6366f1;
    font-size: 14px;
    cursor: pointer;
    padding: 8px 16px;
}

.back-btn:hover {
    color: #4f46e5;
}

.detail-actions {
    display: flex;
    gap: 10px;
}

.project-detail-header {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    gap: 25px;
    align-items: flex-start;
}

.project-icon-large {
    font-size: 64px;
    width: 100px;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    flex-shrink: 0;
}

.project-icon-large.web { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.project-icon-large.mobile { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.project-icon-large.data { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.project-icon-large.design { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.project-icon-large.other { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }

.header-content {
    flex: 1;
}

.header-content h1 {
    color: #1a1a1a;
    margin-bottom: 15px;
    font-size: 28px;
}

.header-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.status-badge, .category-badge, .members-badge {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
}

.status-badge.active { background: #d1fae5; color: #065f46; }
.status-badge.in-progress { background: #dbeafe; color: #1e40af; }
.status-badge.completed { background: #e0e7ff; color: #4338ca; }
.status-badge.cancelled { background: #fee2e2; color: #991b1b; }

.category-badge {
    background: #f3f4f6;
    color: #374151;
}

.members-badge {
    background: #fef3c7;
    color: #92400e;
}

.project-details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.detail-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.detail-card.full-width {
    grid-column: 1 / -1;
}

.card-title {
    font-size: 18px;
    color: #1a1a1a;
    margin-bottom: 20px;
    font-weight: 600;
}

.project-description-full {
    color: #4b5563;
    line-height: 1.8;
    font-size: 15px;
}

.skills-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.skill-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
}

.timeline-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.timeline-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f3f4f6;
}

.timeline-item:last-child {
    border-bottom: none;
}

.timeline-label {
    color: #6b7280;
    font-size: 14px;
}

.timeline-value {
    color: #1a1a1a;
    font-weight: 500;
    font-size: 14px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.info-label {
    color: #6b7280;
    font-size: 13px;
}

.info-value {
    color: #1a1a1a;
    font-weight: 500;
    font-size: 15px;
}

@media (max-width: 768px) {
    .project-details-grid {
        grid-template-columns: 1fr;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .project-detail-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .detail-nav {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .detail-actions {
        width: 100%;
    }
    
    .action-btn {
        flex: 1;
    }
}
</style>

<script>
const URLROOT = '<?= URLROOT ?>';

function deleteProject(projectId, projectName) {
    if (confirm(`Are you sure you want to delete "${projectName}"? This action cannot be undone.`)) {
        const formData = new FormData();
        formData.append('project_id', projectId);
        
        fetch(URLROOT + '/organization/deleteProject', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = URLROOT + '/organization/projects';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the project');
        });
    }
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>