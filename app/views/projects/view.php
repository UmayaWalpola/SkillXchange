<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">

<style>
.project-detail-wrapper {
    max-width: 1400px;
    width: 100%;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.project-header-container {
    background: linear-gradient(135deg, #658396 0%, #5a7a8c 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 15px rgba(101, 131, 150, 0.2);
    display: flex;
    gap: 2rem;
    align-items: flex-start;
    width: 100%;
    max-width: 1400px;
}

.project-icon-wrapper {
    font-size: 3.5rem;
    flex-shrink: 0;
}

.project-header-content {
    flex: 1;
}

.project-header-content h1 {
    font-size: 2.2rem;
    margin: 0 0 1rem 0;
    font-weight: 700;
}

.header-badges {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.badge {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
}

.badge.status {
    background: rgba(255, 255, 255, 0.3);
}

.project-meta-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.meta-item {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.meta-label {
    font-size: 0.85rem;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.meta-value {
    font-size: 1.1rem;
    font-weight: 600;
}

.content-sections {
    display: grid;
    gap: 2rem;
    width: 100%;
    max-width: 1400px;
}

.progress-overview-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
    gap: 1.5rem;
}

.progress-bar-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.progress-label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    color: #4b5563;
}

.progress-container {
    width: 100%;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
    height: 12px;
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.12);
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #0ea5e9);
    box-shadow: 0 1px 3px rgba(37, 99, 235, 0.45);
}

.progress-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
}

.progress-metric-card {
    background: #f9fafb;
    border-radius: 12px;
    padding: 0.9rem 1rem;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
}

.progress-metric-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b7280;
    margin-bottom: 0.35rem;
}

.progress-metric-value {
    font-size: 1.15rem;
    font-weight: 600;
    color: #111827;
}

.progress-overview-grid {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(0, 3fr);
    gap: 1.5rem;
}

.progress-bar-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.progress-label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    color: #4b5563;
}

.progress-container {
    width: 100%;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
    height: 12px;
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.12);
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #0ea5e9);
    box-shadow: 0 1px 3px rgba(37, 99, 235, 0.45);
}

.progress-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
}

.progress-metric-card {
    background: #f9fafb;
    border-radius: 12px;
    padding: 0.9rem 1rem;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
}

.progress-metric-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #6b7280;
    margin-bottom: 0.35rem;
}

.progress-metric-value {
    font-size: 1.15rem;
    font-weight: 600;
    color: #111827;
}

.card {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    width: 100%;
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
}

.card-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.7rem;
    border-bottom: 2px solid #658396;
    padding-bottom: 0.8rem;
}

.description-text {
    color: #4b5563;
    line-height: 1.8;
    font-size: 1rem;
}

.skills-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 0.8rem;
    margin-top: 1rem;
}

.skill-badge {
    background: linear-gradient(135deg, #658396 0%, #5a7a8c 100%);
    color: white;
    padding: 0.6rem 1.2rem;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 500;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.detail-item {
    background: #f3f4f6;
    padding: 1.2rem;
    border-radius: 8px;
    border-left: 4px solid #658396;
}

.detail-label {
    color: #6b7280;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.detail-value {
    color: #1f2937;
    font-size: 1.1rem;
    font-weight: 600;
}

.members-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.member-card {
    text-align: center;
    padding: 1.5rem;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

.member-card:hover {
    box-shadow: 0 4px 12px rgba(101, 131, 150, 0.15);
    border-color: #658396;
}

.member-avatar {
    width: 60px;
    height: 60px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, #658396 0%, #5a7a8c 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.3rem;
}

.member-name {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.3rem;
}

.member-role {
    font-size: 0.85rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.application-section {
    background: linear-gradient(135deg, rgba(101, 131, 150, 0.05) 0%, rgba(90, 122, 140, 0.05) 100%);
    padding: 2rem;
    border-radius: 10px;
    border: 1px solid rgba(101, 131, 150, 0.2);
    width: 100%;
    max-width: 1400px;
}

.status-message {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.2rem;
    background: white;
    border-radius: 8px;
    margin-bottom: 1rem;
    border-left: 4px solid #658396;
}

.status-badge {
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.9rem;
}

.status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.approved {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.rejected {
    background: #fee2e2;
    color: #991b1b;
}

.btn-primary {
    background: linear-gradient(135deg, #658396 0%, #5a7a8c 100%);
    color: white;
    padding: 0.85rem 1.8rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(101, 131, 150, 0.3);
}

.btn-secondary {
    background: white;
    color: #658396;
    padding: 0.85rem 1.8rem;
    border: 2px solid #658396;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background: #f3f4f6;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #1f2937;
}

.form-group textarea {
    width: 100%;
    padding: 1rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-family: inherit;
    resize: vertical;
    min-height: 100px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-group textarea:focus {
    outline: none;
    border-color: #658396;
    box-shadow: 0 0 0 3px rgba(101, 131, 150, 0.1);
}

.form-group input[type="url"],
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-group input[type="url"]:focus,
.form-group select:focus {
    outline: none;
    border-color: #658396;
    box-shadow: 0 0 0 3px rgba(101, 131, 150, 0.1);
}

.form-group input[type="checkbox"] {
    margin-right: 0.5rem;
    cursor: pointer;
}

.form-group label input[type="checkbox"] {
    display: inline;
}

.form-group small {
    display: block;
    margin-top: 0.3rem;
    color: #888;
    font-size: 0.85rem;
}

.form-section {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border: 1px solid #e5e7eb;
    width: 100%;
}

.form-section h4 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #658396;
    color: #658396;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.apply-form {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    margin-top: 1.5rem;
    display: none;
}

.apply-form.show {
    display: block;
}

.empty-state {
    text-align: center;
    color: #6b7280;
    padding: 2rem;
}

@media (max-width: 768px) {
    .project-detail-wrapper {
        max-width: 100%;
        padding: 1rem;
    }

    .project-header-container {
        flex-direction: column;
        align-items: center;
        text-align: center;
        max-width: 100%;
        padding: 2rem 1rem;
    }

    .project-header-content h1 {
        font-size: 1.8rem;
    }

    .card {
        max-width: 100%;
        padding: 1.5rem;
    }

    .content-sections {
        max-width: 100%;
    }

    .application-section {
        max-width: 100%;
        padding: 1.5rem;
    }

    .members-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }

    .form-actions {
        flex-direction: column;
    }

    .progress-overview-grid {
        grid-template-columns: 1fr;
    }

    .details-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main project-detail-wrapper">

        <!-- Project Header -->
        <div class="project-header-container">
            <div class="project-icon-wrapper">
                <?php 
                    $icons = [
                        'web' => '💻',
                        'mobile' => '📱',
                        'data' => '📊',
                        'design' => '🎨',
                        'other' => '📁'
                    ];
                    echo $icons[$project->category] ?? '📁';
                ?>
            </div>
            <div class="project-header-content">
                <h1><?= htmlspecialchars($project->name ?? 'Project') ?></h1>
                
                <div class="header-badges">
                    <span class="badge status"><?= ucfirst(str_replace('-', ' ', $project->status ?? 'pending')) ?></span>
                    <span class="badge"><?= ucfirst(htmlspecialchars($project->category ?? 'other')) ?></span>
                </div>

                <div class="project-meta-info">
                    <div class="meta-item">
                        <span class="meta-label">Start Date</span>
                        <span class="meta-value"><?= $project->start_date ? date('M d, Y', strtotime($project->start_date)) : 'TBD' ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">End Date</span>
                        <span class="meta-value"><?= $project->end_date ? date('M d, Y', strtotime($project->end_date)) : 'TBD' ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Team Size</span>
                        <span class="meta-value"><?= intval($project->current_members ?? 0) ?>/<?= htmlspecialchars($project->max_members) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Sections -->
        <div class="content-sections">

            <!-- Project Progress Section -->
            <div class="card">
                <h2 class="card-title">📈 Project Progress</h2>
                <?php 
                // Use task statistics passed from controller
                $totalTasks = (int)($taskStats->total ?? 0);
                $completedTasks = (int)($taskStats->completed ?? $taskStats->done ?? 0);
                $inProgressTasks = (int)($taskStats->in_progress ?? 0);
                $todoTasks = (int)($taskStats->pending ?? $taskStats->todo ?? 0);
                $overdueTasks = (int)($taskStats->overdue ?? 0);
                
                $percent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;
                ?>
                <div class="progress-overview-grid">
                    <div class="progress-bar-wrapper">
                        <div class="progress-label-row">
                            <span>Overall completion</span>
                            <span><strong><?= $percent ?></strong>%</span>
                        </div>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?= $percent ?>%"></div>
                        </div>
                        <div style="margin-top:0.25rem;font-size:0.85rem;color:#6b7280;">
                            <?= $completedTasks ?> of <?= $totalTasks ?> tasks completed
                        </div>
                    </div>
                    <div class="progress-metrics-grid">
                        <div class="progress-metric-card">
                            <div class="progress-metric-label">Total Tasks</div>
                            <div class="progress-metric-value"><?= $totalTasks ?></div>
                        </div>
                        <div class="progress-metric-card">
                            <div class="progress-metric-label">To-Do</div>
                            <div class="progress-metric-value" style="color:#856404;"><?= $todoTasks ?></div>
                        </div>
                        <div class="progress-metric-card">
                            <div class="progress-metric-label">In Progress</div>
                            <div class="progress-metric-value" style="color:#084298;"><?= $inProgressTasks ?></div>
                        </div>
                        <div class="progress-metric-card">
                            <div class="progress-metric-label">Completed</div>
                            <div class="progress-metric-value" style="color:#0f5132;"><?= $completedTasks ?></div>
                        </div>
                        <div class="progress-metric-card">
                            <div class="progress-metric-label">Overdue</div>
                            <div class="progress-metric-value" style="color:#991b1b;"><?= $overdueTasks ?></div>
                        </div>
                        <div class="progress-metric-card">
                            <div class="progress-metric-label">Active Members</div>
                            <div class="progress-metric-value"><?= (int)($project->current_members ?? 0) ?></div>
                        </div>
                    </div>
                </div>
                
                <?php if ($totalTasks > 0 && !empty($members)): ?>
                    <!-- Tasks per Member -->
                    <div style="margin-top:30px;padding-top:30px;border-top:2px solid #f3f4f6;">
                        <h3 style="font-size:1.1rem;margin-bottom:15px;color:#1f2937;">📊 Tasks per Member</h3>
                        <?php 
                        foreach ($members as $member) {
                            $memberTasks = $taskModel->getTasksByMember($project->id, $member->user_id);
                            $memberTaskCount = count($memberTasks);
                            $memberCompleted = count(array_filter($memberTasks, fn($t) => $t->status === 'done'));
                            $memberPercent = $memberTaskCount > 0 ? round(($memberCompleted / $memberTaskCount) * 100) : 0;
                            
                            if ($memberTaskCount > 0):
                        ?>
                            <div style="margin-bottom:12px;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                                    <span style="font-weight:500;font-size:0.9rem;"><?= htmlspecialchars($member->username ?? 'Unknown') ?></span>
                                    <span style="font-size:0.85rem;color:#6b7280;"><?= $memberCompleted ?>/<?= $memberTaskCount ?> tasks</span>
                                </div>
                                <div class="progress-container" style="height:8px;">
                                    <div class="progress-bar" style="width: <?= $memberPercent ?>%;height:100%;"></div>
                                </div>
                            </div>
                        <?php 
                            endif;
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- About Section -->
            <div class="card">
                <h2 class="card-title">📝 About This Project</h2>
                <p class="description-text"><?= nl2br(htmlspecialchars($project->description ?? '')) ?></p>
            </div>

            <!-- Project Details Section -->
            <div class="card">
                <h2 class="card-title">ℹ️ Project Details</h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Status</div>
                        <div class="detail-value"><?= ucfirst(str_replace('-', ' ', $project->status ?? 'pending')) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Category</div>
                        <div class="detail-value"><?= ucfirst(htmlspecialchars($project->category ?? 'other')) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Team Size</div>
                        <div class="detail-value"><?= intval($project->current_members ?? 0) ?>/<?= htmlspecialchars($project->max_members) ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Budget</div>
                        <div class="detail-value"><?= htmlspecialchars($project->budget ?? 'Not specified') ?></div>
                    </div>
                </div>
            </div>

            <!-- Required Skills Section -->
            <div class="card">
                <h2 class="card-title">🎯 Required Skills</h2>
                <div class="skills-grid">
                    <?php if (!empty($project->required_skills)): ?>
                        <?php foreach (explode(',', $project->required_skills) as $skill): ?>
                            <span class="skill-badge"><?= htmlspecialchars(trim($skill)) ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="empty-state">No specific skills required</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Team Members Section -->
            <?php if (!empty($members)): ?>
            <div class="card">
                <h2 class="card-title">👥 Team Members</h2>
                <div class="members-grid">
                    <?php foreach ($members as $member): ?>
                        <?php 
                            $fullName = ($member->first_name ?? '') . ' ' . ($member->last_name ?? '');
                            $initials = strtoupper(
                                substr($member->first_name ?? 'U', 0, 1) . 
                                substr($member->last_name ?? '', 0, 1)
                            );
                        ?>
                        <div class="member-card">
                            <div class="member-avatar"><?= $initials ?></div>
                            <div class="member-name"><?= htmlspecialchars(trim($fullName)) ?: 'Team Member' ?></div>
                            <div class="member-role"><?= htmlspecialchars($member->role ?? 'Member') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Application Section -->
            <div class="card">
                <h2 class="card-title">✨ Join This Project</h2>
                <div class="application-section">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (!empty($is_member)): ?>
                            <div class="alert alert-success" style="border-radius:8px;padding:12px;margin-bottom:12px;">
                                You are a team member of this project.
                            </div>
                        <?php else: ?>
                            <?php if (isset($application) && $application): ?>
                                <?php $status = strtolower($application->status); ?>
                                <?php if ($status === 'pending'): ?>
                                    <div class="alert alert-warning" style="border-radius:8px;padding:12px;margin-bottom:12px;">
                                        Your application is pending.
                                    </div>
                                    <form method="post" action="<?= URLROOT . '/ProjectApplication/cancel/' . $project->id ?>" style="margin-top: 1rem;">
                                        <button class="btn-secondary" type="submit">Cancel Application</button>
                                    </form>
                                <?php elseif ($status === 'accepted'): ?>
                                    <div class="alert alert-success" style="border-radius:8px;padding:12px;margin-bottom:12px;">
                                        Your application is accepted — you are now a member of this project.
                                    </div>
                                <?php elseif ($status === 'rejected'): ?>
                                    <div class="alert alert-error" style="border-radius:8px;padding:12px;margin-bottom:12px;">
                                        Your application was rejected.
                                    </div>
                                    <div style="margin-top:1rem;">
                                        <button id="applyAgainBtn" class="btn-primary">Apply Again</button>
                                    </div>
                                <?php else: ?>
                                    <div class="alert" style="border-radius:8px;padding:12px;margin-bottom:12px;">
                                        Your application status: <strong><?= htmlspecialchars(ucfirst($status)) ?></strong>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <button id="applyToggle" class="btn-primary">Apply to Join This Project</button>
                            <form id="applyForm" class="apply-form" method="post" action="<?= URLROOT . '/ProjectApplication/apply/' . $project->id ?>">
                                
                                <!-- Personal Statement Section -->
                                <div class="form-section">
                                    <h4 style="color: #658396; margin-bottom: 1rem;">📝 Your Application</h4>
                                    
                                    <div class="form-group">
                                        <label for="relevant_experience">📚 Relevant Experience *</label>
                                        <textarea name="relevant_experience" id="relevant_experience" placeholder="Describe your relevant experience, past projects, and achievements in this field..." rows="4" required></textarea>
                                        <small style="color: #888;">Tell us about similar projects you've worked on</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="matching_skills">🛠️ How Your Skills Match This Project *</label>
                                        <textarea name="matching_skills" id="matching_skills" placeholder="Explain which of the required skills you have and at what level (beginner/intermediate/advanced)..." rows="4" required></textarea>
                                        <small style="color: #888;">Match your skills with the project requirements</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="contribution">💡 How Will You Contribute? *</label>
                                        <textarea name="contribution" id="contribution" placeholder="Describe specific ways you can contribute to this project's success..." rows="4" required></textarea>
                                        <small style="color: #888;">Be specific about your potential contributions</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="availability">⏱️ Time Commitment *</label>
                                        <select name="availability" id="availability" required style="padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                                            <option value="">-- Select Your Available Time --</option>
                                            <option value="5-10">5-10 hours per week</option>
                                            <option value="10-20">10-20 hours per week</option>
                                            <option value="20-30">20-30 hours per week</option>
                                            <option value="30+">30+ hours per week (Full-time)</option>
                                        </select>
                                        <small style="color: #888;">How much time can you dedicate weekly?</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="expected_duration">📅 Expected Duration of Involvement *</label>
                                        <select name="expected_duration" id="expected_duration" required style="padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem;">
                                            <option value="">-- Select Duration --</option>
                                            <option value="1-3">1-3 months</option>
                                            <option value="3-6">3-6 months</option>
                                            <option value="6-12">6-12 months</option>
                                            <option value="ongoing">Ongoing (indefinite)</option>
                                        </select>
                                        <small style="color: #888;">How long do you plan to participate?</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="motivation">🎯 Why Are You Interested in This Project? *</label>
                                        <textarea name="motivation" id="motivation" placeholder="Share your passion and motivation for this particular project..." rows="4" required></textarea>
                                        <small style="color: #888;">Help us understand your genuine interest</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="portfolio">🔗 Portfolio/GitHub Link (Optional)</label>
                                        <input type="url" name="portfolio" id="portfolio" placeholder="https://github.com/yourprofile or your portfolio website">
                                        <small style="color: #888;">Share your work to strengthen your application</small>
                                    </div>

                                    <div class="form-group">
                                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                                            <input type="checkbox" name="agreement" id="agreement" required>
                                            I agree to the project guidelines and team collaboration rules *
                                        </label>
                                    </div>
                                </div>

                                <div class="form-actions" style="margin-top: 2rem; display: flex; gap: 1rem;">
                                    <button class="btn-primary" type="submit" style="flex: 1; padding: 0.75rem;">Submit Application</button>
                                    <button id="cancelApply" type="button" class="btn-secondary" style="flex: 1; padding: 0.75rem;">Cancel</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php else: ?>
                        <p style="color: #4b5563; margin-bottom: 1rem;">Sign in to apply for this project</p>
                        <a href="<?= URLROOT . '/auth/signin' ?>" class="btn-primary">Sign In to Apply</a>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('applyToggle');
    var form = document.getElementById('applyForm');
    var cancel = document.getElementById('cancelApply');
    
    if (toggle && form) {
        toggle.addEventListener('click', function() {
            form.classList.toggle('show');
        });
    }
    
    if (cancel && form) {
        cancel.addEventListener('click', function() {
            form.classList.remove('show');
        });
    }
    // Apply Again button: when clicked, show the form and clear inputs
    var applyAgain = document.getElementById('applyAgainBtn');
    if (applyAgain && form) {
        applyAgain.addEventListener('click', function(e) {
            e.preventDefault();
            // clear inputs
            form.querySelectorAll('textarea, input[type="text"], input[type="url"], select').forEach(function(el){
                if (el.tagName.toLowerCase() === 'select') el.selectedIndex = 0;
                else el.value = '';
            });
            // uncheck checkboxes
            form.querySelectorAll('input[type="checkbox"]').forEach(function(cb){ cb.checked = false; });
            form.classList.add('show');
            form.scrollIntoView({behavior: 'smooth', block: 'center'});
        });
    }
});
</script>

<script>
// AJAX submit fallback for Apply form
document.addEventListener('DOMContentLoaded', function() {
    const applyForm = document.getElementById('applyForm');
    if (!applyForm) return;

    applyForm.addEventListener('submit', function(e) {
        // If fetch is supported and the form has data-ajax attribute (we'll always attempt),
        // prevent normal submit and perform AJAX instead.
        e.preventDefault();

        const submitBtn = applyForm.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        const formData = new FormData(applyForm);

        fetch(applyForm.getAttribute('action'), {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
            if (submitBtn) submitBtn.disabled = false;
            if (data.success) {
                // Hide form and show status message block
                applyForm.classList.remove('show');

                // Remove any existing status-message
                const container = document.querySelector('.application-section');
                if (container) {
                    // Remove old status message if present
                    const old = container.querySelector('.status-message');
                    if (old) old.remove();

                    const statusDiv = document.createElement('div');
                    statusDiv.className = 'status-message';
                    const badge = document.createElement('span');
                    badge.className = 'status-badge pending';
                    badge.textContent = 'Pending';
                    const txt = document.createElement('div');
                    txt.innerHTML = '<p style="margin:0;color:#4b5563;">Your application status: <strong>Pending</strong> - Your application is pending review by the organization.</p>';
                    statusDiv.appendChild(badge);
                    statusDiv.appendChild(txt);
                    container.insertBefore(statusDiv, container.firstChild);
                }

                // Show a transient success message
                const message = document.createElement('div');
                message.className = 'alert alert-success';
                message.textContent = data.message || 'Application submitted successfully.';
                const main = document.querySelector('.project-detail-wrapper') || document.body;
                main.insertBefore(message, main.firstChild);
                setTimeout(() => message.remove(), 4000);
            } else {
                // show error
                alert(data.message || 'Failed to submit application.');
            }
        })
        .catch(err => {
            if (submitBtn) submitBtn.disabled = false;
            console.error('Application submit error:', err);
            alert('Failed to submit application — please try again.');
        });
    });
});
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
