<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/reporting.css">

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
                        'web' => '<i class="ph ph-code" style="font-size: 2.5rem;"></i>',
                        'mobile' => '<i class="ph ph-device-mobile" style="font-size: 2.5rem;"></i>',
                        'data' => '<i class="ph ph-chart-bar" style="font-size: 2.5rem;"></i>',
                        'design' => '<i class="ph ph-paint-brush" style="font-size: 2.5rem;"></i>',
                        'other' => '<i class="ph ph-folder-open" style="font-size: 2.5rem;"></i>'
                    ];
                    echo $icons[$project->category] ?? '<i class="ph ph-folder-open" style="font-size: 2.5rem;"></i>';
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
                <h2 class="card-title"><i class="ph ph-chart-line-up"></i> Project Progress</h2>
                <?php 
                // Use task statistics passed from controller (guard against null/void)
                $taskStats       = isset($taskStats) && is_object($taskStats) ? $taskStats : null;
                $totalTasks      = (int)($taskStats->total      ?? 0);
                $completedTasks  = (int)($taskStats->completed  ?? $taskStats->done ?? 0);
                $inProgressTasks = (int)($taskStats->in_progress ?? 0);
                $todoTasks       = (int)($taskStats->pending    ?? $taskStats->todo ?? 0);
                $overdueTasks    = (int)($taskStats->overdue    ?? 0);
                
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
                        <h3 style="font-size:1.1rem;margin-bottom:15px;color:#1f2937;"><i class="ph ph-chart-bar"></i> Tasks per Member</h3>
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
                <h2 class="card-title"><i class="ph ph-notepad"></i> About This Project</h2>
                <p class="description-text"><?= nl2br(htmlspecialchars($project->description ?? '')) ?></p>
            </div>

            <!-- Required Skills Section -->
            <div class="card">
                <h2 class="card-title"><i class="ph ph-target"></i> Required Skills</h2>
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
                <h2 class="card-title"><i class="ph ph-users"></i> Team Members</h2>
                <div class="members-grid">
                    <?php foreach ($members as $member): ?>
                        <?php 
                            // Query returns u.username — use that as display name
                            $displayName = trim($member->username ?? '');
                            // Generate smart initials: first 1-2 chars of username
                            $nameParts = array_filter(explode(' ', $displayName));
                            if (count($nameParts) >= 2) {
                                $initials = strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1));
                            } else {
                                $initials = strtoupper(substr($displayName ?: '?', 0, 2));
                            }
                            $memberSkills = array_filter(array_map('trim', explode(',', $member->user_skills ?? '')));
                        ?>
                        <div class="member-card">
                            <div class="member-avatar"><?= htmlspecialchars($initials) ?></div>
                            <div class="member-name"><?= htmlspecialchars($displayName) ?: 'Unknown' ?></div>
                            <div class="member-role"><?= htmlspecialchars(ucfirst($member->role ?? 'Member')) ?></div>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
            <?php endif; ?>

            <!-- MY TASKS — only visible to members -->
            <?php if (!empty($is_member) && !empty($myTasks)): ?>
            <div class="card" id="myTasksCard" style="border:2px solid #658396;position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;width:4px;height:100%;background:linear-gradient(180deg,#658396,#9cc7df);"></div>
                <h2 class="card-title" style="cursor:pointer;user-select:none;" onclick="toggleMyTasks()">
                    <i class="ph ph-clipboard-text"></i>
                    My Tasks
                    <span id="myTasksBadge" style="background:linear-gradient(135deg,#658396,#9cc7df);color:#fff;font-size:0.75rem;padding:4px 10px;border-radius:20px;font-weight:700;margin-left:8px;"><?= count($myTasks) ?></span>
                    <i id="myTasksChevron" class="ph ph-caret-down" style="margin-left:auto;font-size:1.1rem;transition:transform 0.3s;"></i>
                </h2>

                <div id="myTasksList" style="margin-top:4px;">
                    <div style="display:grid;gap:12px;">
                    <?php foreach ($myTasks as $t):
                        $status   = $t->status ?? 'todo';
                        $priority = strtolower($t->priority ?? 'medium');
                        $deadline = $t->deadline ?? null;
                        $isOverdue = $deadline && $status !== 'done' && strtotime($deadline) < time();
                        $isDone   = $status === 'done';

                        $statusColor = match($status) {
                            'done'        => '#356b86',
                            'in-progress' => '#4f6d82',
                            default       => '#b7791f'
                        };
                        $statusBg = match($status) {
                            'done'        => '#dff0fa',
                            'in-progress' => '#d5eaf6',
                            default       => '#fff2cf'
                        };
                        $statusLabel = match($status) {
                            'done'        => 'Completed',
                            'in-progress' => 'In Progress',
                            default       => 'To-Do'
                        };
                        $prioColor = match($priority) {
                            'high'   => '#c24141',
                            'low'    => '#4a6f86',
                            default  => '#b7791f'
                        };
                        $prioBg = match($priority) {
                            'high'   => '#fee6e6',
                            'low'    => '#e5f2fa',
                            default  => '#fff2cf'
                        };
                    ?>
                    <div class="my-task-row" onclick="openTaskDetail(<?= $t->id ?>, <?= $project->id ?>)"
                         style="display:flex;align-items:center;gap:14px;padding:16px 18px;background:<?= $isDone ? '#edf7fc' : ($isOverdue ? '#fef4f4' : '#f8fbfd') ?>;border:1.5px solid <?= $isDone ? '#b9d7e8' : ($isOverdue ? '#f2c8c8' : '#dde7ee') ?>;border-radius:12px;cursor:pointer;transition:all 0.2s;<?= $isDone ? 'opacity:0.7;' : '' ?>"
                         onmouseenter="this.style.boxShadow='0 4px 16px rgba(101,131,150,0.15)';this.style.transform='translateY(-1px)'"
                         onmouseleave="this.style.boxShadow='none';this.style.transform='none'"
                         data-task-id="<?= $t->id ?>"
                         data-task-title="<?= htmlspecialchars($t->title ?? '') ?>"
                         data-task-desc="<?= htmlspecialchars($t->description ?? 'No description provided.') ?>"
                         data-task-priority="<?= htmlspecialchars(ucfirst($priority)) ?>"
                         data-task-deadline="<?= $deadline ? date('M d, Y', strtotime($deadline)) : 'No deadline' ?>"
                         data-task-status="<?= htmlspecialchars($statusLabel) ?>"
                         data-task-status-raw="<?= htmlspecialchars($status) ?>"
                         data-task-done="<?= $isDone ? '1' : '0' ?>">

                        <!-- Status icon -->
                        <div style="width:38px;height:38px;border-radius:50%;background:<?= $statusBg ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <?php if ($isDone): ?>
                                <i class="ph ph-check-circle" style="font-size:20px;color:<?= $statusColor ?>;"></i>
                            <?php elseif ($status === 'in-progress'): ?>
                                <i class="ph ph-spinner" style="font-size:20px;color:<?= $statusColor ?>;"></i>
                            <?php else: ?>
                                <i class="ph ph-circle-dashed" style="font-size:20px;color:<?= $statusColor ?>;"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Task info -->
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;font-size:0.95rem;color:#1f2937;margin-bottom:4px;<?= $isDone ? 'text-decoration:line-through;color:#6b7280;' : '' ?>">
                                <?= htmlspecialchars($t->title ?? 'Untitled Task') ?>
                            </div>
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <span style="font-size:0.72rem;font-weight:700;padding:3px 8px;border-radius:20px;background:<?= $statusBg ?>;color:<?= $statusColor ?>;"><?= $statusLabel ?></span>
                                <span style="font-size:0.72rem;font-weight:700;padding:3px 8px;border-radius:20px;background:<?= $prioBg ?>;color:<?= $prioColor ?>;"><?= ucfirst($priority) ?></span>
                                <?php if ($deadline): ?>
                                <span style="font-size:0.72rem;padding:3px 8px;border-radius:20px;background:<?= $isOverdue ? '#fbe5e5' : '#e5f2fa' ?>;color:<?= $isOverdue ? '#9d3c3c' : '#4a6f86' ?>;display:flex;align-items:center;gap:3px;">
                                    <i class="ph <?= $isOverdue ? 'ph-warning-circle' : 'ph-calendar' ?>" style="font-size:11px;"></i>
                                    <?= $isOverdue ? 'Overdue · ' : '' ?><?= date('M d', strtotime($deadline)) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <i class="ph ph-caret-right" style="color:#7f95a5;font-size:1.1rem;flex-shrink:0;"></i>
                    </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Application Section — ONLY shown to individual users, NEVER to organizations -->
            <?php
                $isOrgAccount      = isset($_SESSION['role']) && $_SESSION['role'] === 'organization';
                $isProjectOwner    = isset($project->organization_id) && isset($_SESSION['user_id'])
                                     && (int)$project->organization_id === (int)$_SESSION['user_id'];
                $projectClosed     = in_array(strtolower($project->status ?? ''), ['completed','cancelled','closed']);
                $hideJoinSection   = $isOrgAccount || $isProjectOwner || $projectClosed;
            ?>
            <?php if (!$hideJoinSection): ?>
            <div class="card">
                <h2 class="card-title"><i class="ph ph-sparkle"></i> Join This Project</h2>
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
                            <form id="applyForm" class="apply-form" method="post" action="<?= URLROOT . '/project/submitApplication/' . $project->id ?>">
                                
                                <!-- Personal Statement Section -->
                                <div class="form-section">
                                    <h4 style="color: #658396; margin-bottom: 1rem;"><i class="ph ph-notepad"></i> Your Application</h4>
                                    
                                    <div class="form-group">
                                        <label for="relevant_experience"><i class="ph ph-books"></i> Relevant Experience *</label>
                                        <textarea name="relevant_experience" id="relevant_experience" placeholder="Describe your relevant experience, past projects, and achievements in this field..." rows="4" required></textarea>
                                        <small style="color: #888;">Tell us about similar projects you've worked on</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="matching_skills"><i class="ph ph-wrench"></i> How Your Skills Match This Project *</label>
                                        <textarea name="matching_skills" id="matching_skills" placeholder="Explain which of the required skills you have and at what level (beginner/intermediate/advanced)..." rows="4" required></textarea>
                                        <small style="color: #888;">Match your skills with the project requirements</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="contribution"><i class="ph ph-lightbulb"></i> How Will You Contribute? *</label>
                                        <textarea name="contribution" id="contribution" placeholder="Describe specific ways you can contribute to this project's success..." rows="4" required></textarea>
                                        <small style="color: #888;">Be specific about your potential contributions</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="availability"><i class="ph ph-clock"></i> Time Commitment *</label>
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
                                        <label for="expected_duration"><i class="ph ph-calendar"></i> Expected Duration of Involvement *</label>
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
                                        <label for="motivation"><i class="ph ph-target"></i> Why Are You Interested in This Project? *</label>
                                        <textarea name="motivation" id="motivation" placeholder="Share your passion and motivation for this particular project..." rows="4" required></textarea>
                                        <small style="color: #888;">Help us understand your genuine interest</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="portfolio"><i class="ph ph-link"></i> Portfolio/GitHub Link (Optional)</label>
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
            <?php endif; /* end !$hideJoinSection */ ?>


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
                // Hide form and the toggle button — application is submitted
                applyForm.classList.remove('show');
                const toggleBtn = document.getElementById('applyToggle');
                if (toggleBtn) toggleBtn.style.display = 'none';


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

// Define URLROOT for reporting system
window.URLROOT = '<?= URLROOT ?>';
</script>

<!-- Task Detail Overlay Panel -->
<div id="taskDetailOverlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.55);z-index:10000;align-items:center;justify-content:center;backdrop-filter:blur(3px);" onclick="closeTaskDetail(event)">
    <div id="taskDetailPanel" style="background:#fff;border-radius:20px;padding:0;max-width:520px;width:92%;max-height:88vh;overflow:hidden;box-shadow:0 25px 60px rgba(15,23,42,0.25);display:flex;flex-direction:column;animation:slideUp 0.3s ease;">

        <!-- Header -->
        <div id="taskPanelHeader" style="padding:24px 28px 20px;background:linear-gradient(135deg,#658396,#5a7a8c);color:#fff;position:relative;">
            <button onclick="closeTaskDetail()" style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.2);border:none;color:#fff;width:32px;height:32px;border-radius:50%;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;line-height:1;">×</button>
            <div style="font-size:0.75rem;font-weight:600;letter-spacing:1px;text-transform:uppercase;opacity:0.85;margin-bottom:8px;">Task Detail</div>
            <h3 id="tdTitle" style="margin:0;font-size:1.25rem;font-weight:700;line-height:1.3;padding-right:40px;"></h3>
        </div>

        <!-- Badges row -->
        <div id="tdBadges" style="padding:14px 28px;display:flex;gap:10px;flex-wrap:wrap;border-bottom:1px solid #e6edf2;background:#f6f9fc;"></div>

        <!-- Body -->
        <div style="padding:24px 28px;overflow-y:auto;flex:1;">
            <div style="margin-bottom:20px;">
                <div style="font-size:0.75rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Description</div>
                <div id="tdDesc" style="color:#374151;line-height:1.7;font-size:0.95rem;white-space:pre-wrap;"></div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
                <div style="background:#f4f8fb;border-radius:10px;padding:14px;">
                    <div style="font-size:0.7rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Priority</div>
                    <div id="tdPriority" style="font-size:1rem;font-weight:700;"></div>
                </div>
                <div style="background:#f4f8fb;border-radius:10px;padding:14px;">
                    <div style="font-size:0.7rem;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Deadline</div>
                    <div id="tdDeadline" style="font-size:1rem;font-weight:700;color:#1f2937;"></div>
                </div>
            </div>

            <!-- Task action button -->
            <div id="tdCompleteWrap">
                <button id="tdCompleteBtn" onclick="markTaskDone()" style="width:100%;padding:15px;background:linear-gradient(135deg,#658396,#5a7a8c);color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:all 0.2s;box-shadow:0 4px 15px rgba(101,131,150,0.35);" onmouseenter="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(101,131,150,0.45)'" onmouseleave="this.style.transform='none';this.style.boxShadow='0 4px 15px rgba(101,131,150,0.35)'">
                    <i class="ph ph-check-circle" style="font-size:1.2rem;"></i>
                    Mark as Complete
                </button>
                <p id="tdActionHint" style="text-align:center;font-size:0.8rem;color:#9ca3af;margin-top:10px;">Completing this task will notify the project organization.</p>
            </div>
            <div id="tdDoneMsg" style="display:none;text-align:center;padding:16px;background:#d5eaf6;border-radius:12px;color:#355266;font-weight:700;font-size:1rem;">
                <i class="ph ph-check-circle" style="font-size:1.4rem;vertical-align:middle;margin-right:6px;"></i>
                Task Completed!
            </div>
        </div>
    </div>
</div>

<style>
@keyframes slideUp {
    from { opacity:0; transform:translateY(30px) scale(0.97); }
    to   { opacity:1; transform:translateY(0)    scale(1); }
}
</style>

<script>
window.URLROOT = '<?= URLROOT ?>';

/* ---- My Tasks collapse ---- */
let myTasksOpen = true;
function toggleMyTasks() {
    const list = document.getElementById('myTasksList');
    const chevron = document.getElementById('myTasksChevron');
    if (!list) return;
    myTasksOpen = !myTasksOpen;
    list.style.display = myTasksOpen ? 'block' : 'none';
    chevron.style.transform = myTasksOpen ? 'rotate(0deg)' : 'rotate(-90deg)';
}

/* ---- Task Detail Panel ---- */
let _currentTaskId = null;
let _currentProjectId = null;
let _currentTaskStatusRaw = 'todo';

function openTaskDetail(taskId, projectId) {
    const row = document.querySelector(`.my-task-row[data-task-id="${taskId}"]`);
    if (!row) return;

    _currentTaskId    = taskId;
    _currentProjectId = projectId;

    const title    = row.dataset.taskTitle;
    const desc     = row.dataset.taskDesc;
    const priority = row.dataset.taskPriority;
    const deadline = row.dataset.taskDeadline;
    const status   = row.dataset.taskStatus;
    const statusRaw = row.dataset.taskStatusRaw || 'todo';
    const isDone   = row.dataset.taskDone === '1';

    document.getElementById('tdTitle').textContent   = title;
    document.getElementById('tdDesc').textContent    = desc;
    document.getElementById('tdDeadline').textContent = deadline;
    _currentTaskStatusRaw = statusRaw;

    // Priority pill
    const prioColors = { High:'#c24141', Medium:'#b7791f', Low:'#4a6f86' };
    const prioBgs    = { High:'#fee6e6', Medium:'#fff2cf', Low:'#e5f2fa' };
    const pc = prioColors[priority] || '#6b7280';
    const pb = prioBgs[priority]    || '#f3f4f6';
    document.getElementById('tdPriority').innerHTML = `<span style="color:${pc};background:${pb};padding:4px 12px;border-radius:20px;font-size:0.85rem;">${priority}</span>`;

    // Status badge
    const stColors = { 'Completed':'#356b86', 'In Progress':'#4f6d82', 'To-Do':'#b7791f' };
    const stBgs    = { 'Completed':'#dff0fa', 'In Progress':'#d5eaf6', 'To-Do':'#fff2cf' };
    const sc = stColors[status] || '#6b7280';
    const sb = stBgs[status]    || '#f3f4f6';
    document.getElementById('tdBadges').innerHTML =
        `<span style="font-size:0.78rem;font-weight:700;padding:5px 12px;border-radius:20px;background:${sb};color:${sc};">${status}</span>` +
        `<span style="font-size:0.78rem;font-weight:700;padding:5px 12px;border-radius:20px;background:${pb};color:${pc};">${priority} Priority</span>`;

    // Header gradient
    if (isDone) {
        document.getElementById('taskPanelHeader').style.background = 'linear-gradient(135deg,#5f7f92,#658396)';
    } else if (status === 'In Progress') {
        document.getElementById('taskPanelHeader').style.background = 'linear-gradient(135deg,#658396,#7c9bb0)';
    } else {
        document.getElementById('taskPanelHeader').style.background = 'linear-gradient(135deg,#658396,#5a7a8c)';
    }

    // Show/hide task action button
    document.getElementById('tdCompleteWrap').style.display = isDone ? 'none' : 'block';
    document.getElementById('tdDoneMsg').style.display = isDone ? 'block' : 'none';
    syncTaskActionButton(statusRaw);

    const overlay = document.getElementById('taskDetailOverlay');
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeTaskDetail(e) {
    if (e && e.target !== document.getElementById('taskDetailOverlay')) return;
    document.getElementById('taskDetailOverlay').style.display = 'none';
    document.body.style.overflow = '';
}

function syncTaskActionButton(statusRaw) {
    const btn = document.getElementById('tdCompleteBtn');
    const hint = document.getElementById('tdActionHint');
    if (!btn || !hint) return;

    if (statusRaw === 'in-progress') {
        btn.innerHTML = '<i class="ph ph-check-circle" style="font-size:1.2rem;"></i> Mark as Complete';
        btn.onclick = markTaskDone;
        hint.textContent = 'Completing this task will notify the project organization.';
    } else {
        btn.innerHTML = '<i class="ph ph-play-circle" style="font-size:1.2rem;"></i> Start Task';
        btn.onclick = startTaskProgress;
        hint.textContent = 'Starting this task will notify the project organization.';
    }
}

function updateTaskRowStatus(taskId, nextStatusRaw) {
    const row = document.querySelector(`.my-task-row[data-task-id="${taskId}"]`);
    if (!row) return;

    const title = row.querySelector('div > div:first-child');
    const iconWrap = row.querySelector('div:first-child');
    const statusLabel = nextStatusRaw === 'done' ? 'Completed' : (nextStatusRaw === 'in-progress' ? 'In Progress' : 'To-Do');
    const statusColor = nextStatusRaw === 'done' ? '#356b86' : (nextStatusRaw === 'in-progress' ? '#4f6d82' : '#b7791f');
    const statusBg = nextStatusRaw === 'done' ? '#dff0fa' : (nextStatusRaw === 'in-progress' ? '#d5eaf6' : '#fff2cf');

    row.dataset.taskStatusRaw = nextStatusRaw;
    row.dataset.taskStatus = statusLabel;
    row.dataset.taskDone = nextStatusRaw === 'done' ? '1' : '0';

    if (nextStatusRaw === 'done') {
        row.style.background = '#edf7fc';
        row.style.border = '1.5px solid #b9d7e8';
        row.style.opacity = '0.75';
        if (title) {
            title.style.textDecoration = 'line-through';
            title.style.color = '#6b7280';
        }
        if (iconWrap) {
            iconWrap.innerHTML = '<i class="ph ph-check-circle" style="font-size:20px;color:#356b86;"></i>';
        }
    } else if (nextStatusRaw === 'in-progress') {
        row.style.background = '#f4f8fb';
        row.style.border = '1.5px solid #c7d9e6';
        row.style.opacity = '1';
        if (title) {
            title.style.textDecoration = 'none';
            title.style.color = '#1f2937';
        }
        if (iconWrap) {
            iconWrap.innerHTML = '<i class="ph ph-spinner" style="font-size:20px;color:#4f6d82;"></i>';
        }
    }

    const statusChip = row.querySelector('div[style*="display:flex;align-items:center;gap:8px;flex-wrap:wrap;"] span:first-child');
    if (statusChip) {
        statusChip.textContent = statusLabel;
        statusChip.style.background = statusBg;
        statusChip.style.color = statusColor;
    }
}

function startTaskProgress() {
    if (!_currentTaskId) return;

    const btn = document.getElementById('tdCompleteBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-spinner" style="font-size:1.1rem;"></i> Starting...';

    const formData = new FormData();
    formData.append('task_id', _currentTaskId);
    formData.append('status', 'in-progress');

    fetch(window.URLROOT + '/task/updateStatus', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => {
        if (r.status === 401) {
            return r.json().then(data => { throw new Error(data.message || 'Your session has expired. Please sign in again.'); });
        }
        if (!r.ok) return r.text().then(t => { throw new Error(t.substring(0, 200)); });
        const contentType = r.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            return r.text().then(t => {
                const lower = t.toLowerCase();
                if (lower.includes('<!doctype') || lower.includes('/auth/signin') || lower.includes('sign in')) {
                    throw new Error('Your session has expired. Please sign in again.');
                }
                throw new Error(t.substring(0, 200));
            });
        }
        return r.json();
    })
    .then(data => {
        if (data.success) {
            _currentTaskStatusRaw = 'in-progress';
            updateTaskRowStatus(_currentTaskId, 'in-progress');
            document.getElementById('taskPanelHeader').style.background = 'linear-gradient(135deg,#658396,#7c9bb0)';
            document.getElementById('tdBadges').innerHTML =
                document.getElementById('tdBadges').innerHTML.replace(/To-Do|Completed|In Progress/, 'In Progress');
            syncTaskActionButton('in-progress');
            btn.disabled = false;
            alert(data.message || 'Task moved to in progress');
            setTimeout(() => location.reload(), 1200);
        } else {
            alert('Error: ' + (data.message || 'Failed to start task'));
            btn.disabled = false;
            syncTaskActionButton(_currentTaskStatusRaw);
        }
    })
    .catch(err => {
        console.error(err);
        alert('An error occurred: ' + err.message);
        btn.disabled = false;
        syncTaskActionButton(_currentTaskStatusRaw);
    });
}

function markTaskDone() {
    if (!_currentTaskId || !_currentProjectId) return;

    const btn = document.getElementById('tdCompleteBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="ph ph-spinner" style="font-size:1.1rem;"></i> Completing...';

    fetch(window.URLROOT + '/project/completeTask', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: JSON.stringify({ task_id: _currentTaskId, project_id: _currentProjectId })
    })
    .then(r => {
        if (r.status === 401) {
            return r.json().then(data => { throw new Error(data.message || 'Your session has expired. Please sign in again.'); });
        }
        if (!r.ok) return r.text().then(t => { throw new Error(t.substring(0, 200)); });
        const contentType = r.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            return r.text().then(t => {
                const lower = t.toLowerCase();
                if (lower.includes('<!doctype') || lower.includes('/auth/signin') || lower.includes('sign in')) {
                    throw new Error('Your session has expired. Please sign in again.');
                }
                throw new Error(t.substring(0, 200));
            });
        }
        return r.json();
    })
    .then(data => {
        if (data.success) {
            // Update the row in the list
            _currentTaskStatusRaw = 'done';
            updateTaskRowStatus(_currentTaskId, 'done');

            // Show done state in panel
            document.getElementById('tdCompleteWrap').style.display = 'none';
            document.getElementById('tdDoneMsg').style.display      = 'block';
            document.getElementById('taskPanelHeader').style.background = 'linear-gradient(135deg,#5f7f92,#658396)';
            document.getElementById('tdBadges').innerHTML =
                '<span style="font-size:0.78rem;font-weight:700;padding:5px 12px;border-radius:20px;background:#dff0fa;color:#356b86;">Completed ✓</span>';

            // Reload after delay so progress stats update
            setTimeout(() => location.reload(), 2200);
        } else {
            alert('Error: ' + (data.message || 'Failed to complete task'));
            btn.disabled = false;
            syncTaskActionButton(_currentTaskStatusRaw);
        }
    })
    .catch(err => {
        console.error(err);
        alert('An error occurred: ' + err.message);
        btn.disabled = false;
        syncTaskActionButton(_currentTaskStatusRaw);
    });
}

// Close panel on Escape key
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.getElementById('taskDetailOverlay').style.display = 'none';
        document.body.style.overflow = '';
    }
});
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
