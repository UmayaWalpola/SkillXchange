<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="container org-dashboard">
        <div class="page-header">
            <div>
                <h1>Manage Team Members</h1>
                <p>Project: <strong><?= htmlspecialchars($data['project']->name) ?></strong></p>
            </div>
            <a href="<?= URLROOT ?>/organization/projects" class="btn btn-secondary">← Back to Projects</a>
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

        <!-- Project Progress Overview -->
        <?php if(isset($data['projectMetrics'])): 
            $metrics = $data['projectMetrics'];
            $totalTasks = (int)($metrics->total_tasks ?? 0);
            $completedTasks = (int)($metrics->completed_tasks ?? 0);
            $inProgressTasks = (int)($metrics->in_progress_tasks ?? 0);
            $todoTasks = (int)($metrics->todo_tasks ?? 0);
            $overdueTasks = (int)($metrics->overdue_tasks ?? 0);
            $activeMembers = (int)($metrics->active_members ?? 0);
            $completionPct = $metrics->completion_percentage ?? 0;
        ?>
        <div class="progress-overview-container" style="margin-bottom:30px;background:#ffffff;border:2px solid var(--blue-bg);border-radius:16px;padding:35px;">
            <h2 style="font-size:22px;margin-bottom:25px;color:var(--dark-bg);font-weight:600;padding-bottom:15px;border-bottom:2px solid var(--blue-bg);">📊 Project Progress Overview</h2>
            
            <!-- Main Metrics Grid -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:25px;">
                <!-- Overall Progress Card -->
                <div style="background:linear-gradient(135deg,var(--primary-blue),var(--accent-blue));border-radius:12px;padding:25px;color:white;box-shadow:0 8px 20px rgba(101,131,150,0.2);">
                    <div style="font-size:14px;opacity:0.9;margin-bottom:8px;font-weight:600;">Overall Progress</div>
                    <div style="font-size:36px;font-weight:700;margin-bottom:5px;"><?= number_format($completionPct, 1) ?>%</div>
                    <div style="font-size:13px;opacity:0.9;"><?= $completedTasks ?> of <?= $totalTasks ?> tasks</div>
                </div>
                
                <!-- Total Tasks Card -->
                <div style="background:white;border:2px solid var(--blue-bg);border-radius:12px;padding:25px;transition:all 0.3s ease;">
                    <div style="font-size:14px;color:#666;margin-bottom:8px;font-weight:600;">📋 Total Tasks</div>
                    <div style="font-size:36px;font-weight:700;color:var(--dark-bg);margin-bottom:5px;"><?= $totalTasks ?></div>
                    <div style="font-size:13px;color:#666;">All assigned tasks</div>
                </div>
                
                <!-- To-Do Tasks Card -->
                <div style="background:white;border:2px solid var(--blue-bg);border-radius:12px;padding:25px;transition:all 0.3s ease;">
                    <div style="font-size:14px;color:#666;margin-bottom:8px;font-weight:600;">📌 To-Do</div>
                    <div style="font-size:36px;font-weight:700;color:#f59e0b;margin-bottom:5px;"><?= $todoTasks ?></div>
                    <div style="font-size:13px;color:#666;">Pending tasks</div>
                </div>
                
                <!-- In Progress Card -->
                <div style="background:white;border:2px solid var(--blue-bg);border-radius:12px;padding:25px;transition:all 0.3s ease;">
                    <div style="font-size:14px;color:#666;margin-bottom:8px;font-weight:600;">🔄 In Progress</div>
                    <div style="font-size:36px;font-weight:700;color:var(--primary-blue);margin-bottom:5px;"><?= $inProgressTasks ?></div>
                    <div style="font-size:13px;color:#666;">Active work</div>
                </div>
                
                <!-- Completed Card -->
                <div style="background:white;border:2px solid var(--blue-bg);border-radius:12px;padding:25px;transition:all 0.3s ease;">
                    <div style="font-size:14px;color:#666;margin-bottom:8px;font-weight:600;">✅ Completed</div>
                    <div style="font-size:36px;font-weight:700;color:#10b981;margin-bottom:5px;"><?= $completedTasks ?></div>
                    <div style="font-size:13px;color:#666;">Finished tasks</div>
                </div>
                
                <!-- Overdue Card -->
                <div style="background:white;border:2px solid <?= $overdueTasks > 0 ? '#ef4444' : 'var(--blue-bg)' ?>;border-radius:12px;padding:25px;transition:all 0.3s ease;<?= $overdueTasks > 0 ? 'background:#fee2e2;' : '' ?>">
                    <div style="font-size:14px;color:#666;margin-bottom:8px;font-weight:600;">⚠️ Overdue</div>
                    <div style="font-size:36px;font-weight:700;color:#ef4444;margin-bottom:5px;"><?= $overdueTasks ?></div>
                    <div style="font-size:13px;color:#666;">Needs attention</div>
                </div>
                
                <!-- Active Members Card -->
                <div style="background:white;border:2px solid var(--blue-bg);border-radius:12px;padding:25px;transition:all 0.3s ease;">
                    <div style="font-size:14px;color:#666;margin-bottom:8px;font-weight:600;">👥 Active Members</div>
                    <div style="font-size:36px;font-weight:700;color:var(--primary-blue);margin-bottom:5px;"><?= $activeMembers ?></div>
                    <div style="font-size:13px;color:#666;">Team members</div>
                </div>
            </div>

            <!-- Member Performance Breakdown -->
            <?php if(isset($data['memberBreakdown']) && !empty($data['memberBreakdown'])): ?>
            <div style="background:white;border:2px solid var(--blue-bg);border-radius:12px;padding:25px;margin-bottom:25px;">
                <h3 style="font-size:18px;margin-bottom:20px;color:var(--dark-bg);font-weight:600;display:flex;align-items:center;gap:10px;">
                    <span style="background:linear-gradient(135deg,var(--primary-blue),var(--accent-blue));color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;">👤</span>
                    Member Task Performance
                </h3>
                <div style="display:grid;gap:15px;">
                    <?php foreach($data['memberBreakdown'] as $member): 
                        $memberPct = $member->completion_percentage ?? 0;
                        $memberTotal = (int)($member->total_tasks ?? 0);
                        $memberCompleted = (int)($member->completed_tasks ?? 0);
                        $memberOverdue = (int)($member->overdue_tasks ?? 0);
                    ?>
                    <div style="display:flex;align-items:center;gap:15px;padding:15px;background:white;border:2px solid var(--blue-bg);border-radius:12px;transition:all 0.3s ease;">
                        <?php if (!empty($member->profile_picture)): ?>
                            <img src="<?= URLROOT . '/' . $member->profile_picture ?>" alt="<?= htmlspecialchars($member->username) ?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--blue-bg);">
                        <?php else: ?>
                            <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--primary-blue),var(--accent-blue));color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;border:2px solid var(--blue-bg);">
                                <?= strtoupper(substr($member->username ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:5px;">
                                <strong style="font-size:15px;color:var(--dark-bg);"><?= htmlspecialchars($member->username) ?></strong>
                                <span style="font-size:12px;color:var(--primary-blue);background:var(--blue-bg);padding:4px 10px;border-radius:6px;font-weight:600;"><?= htmlspecialchars($member->role ?? 'Member') ?></span>
                            </div>
                            
                            <div style="display:flex;align-items:center;gap:15px;margin-bottom:10px;">
                                <span style="font-size:13px;color:#666;font-weight:500;">📋 <?= $memberTotal ?> tasks</span>
                                <span style="font-size:13px;color:#10b981;font-weight:500;">✓ <?= $memberCompleted ?> done</span>
                                <?php if($memberOverdue > 0): ?>
                                    <span style="font-size:13px;color:#ef4444;font-weight:600;">⚠️ <?= $memberOverdue ?> overdue</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($memberTotal > 0): ?>
                            <div style="background:var(--blue-bg);height:10px;border-radius:6px;overflow:hidden;">
                                <div style="background:linear-gradient(90deg,var(--primary-blue),var(--accent-blue));height:100%;width:<?= $memberPct ?>%;transition:width 0.4s ease;box-shadow:0 2px 4px rgba(101,131,150,0.2);"></div>
                            </div>
                            <div style="font-size:13px;color:#666;margin-top:5px;font-weight:600;"><?= number_format($memberPct, 1) ?>% complete</div>
                            <?php else: ?>
                            <div style="font-size:13px;color:#666;font-style:italic;background:var(--blue-bg);padding:8px 12px;border-radius:6px;">No tasks assigned yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Overdue Tasks Alert -->
            <?php if($overdueTasks > 0 && isset($data['overdueTasks']) && !empty($data['overdueTasks'])): ?>
            <div style="background:#fee2e2;border:2px solid #ef4444;border-radius:12px;padding:25px;box-shadow:0 4px 12px rgba(239,68,68,0.15);">
                <h3 style="font-size:18px;margin-bottom:20px;color:#ef4444;font-weight:600;display:flex;align-items:center;gap:10px;">
                    <span style="background:#ef4444;color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;">⚠️</span>
                    Overdue Tasks (<?= $overdueTasks ?>)
                </h3>
                <div style="display:grid;gap:12px;">
                    <?php foreach(array_slice($data['overdueTasks'], 0, 5) as $task): ?>
                    <div style="background:white;border-left:4px solid #ef4444;padding:15px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                        <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">
                            <strong style="font-size:15px;color:var(--dark-bg);"><?= htmlspecialchars($task->title ?? $task->task_name ?? 'Untitled Task') ?></strong>
                            <span style="background:#fee2e2;color:#991b1b;padding:6px 12px;border-radius:6px;font-size:12px;white-space:nowrap;margin-left:10px;font-weight:600;">
                                <?= $task->days_overdue ?> days overdue
                            </span>
                        </div>
                        <div style="font-size:13px;color:#666;font-weight:500;">
                            Assigned to: <strong style="color:var(--dark-bg);"><?= htmlspecialchars($task->username ?? 'Unassigned') ?></strong> | 
                            Due: <strong style="color:#ef4444;"><?= date('M d, Y', strtotime($task->deadline ?? $task->due_date)) ?></strong>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if(count($data['overdueTasks']) > 5): ?>
                    <div style="text-align:center;padding:12px;color:#666;font-size:13px;background:white;border-radius:8px;font-weight:500;">
                        ... and <?= count($data['overdueTasks']) - 5 ?> more overdue tasks
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Members Count -->
        <div class="members-header">
            <div class="members-count">
                <span class="count-number"><?= count($data['members']) ?></span>
                <span class="count-label">Active Members</span>
            </div>
        </div>

        <!-- Members List -->
        <div class="members-list">
            <?php if(empty($data['members'])): ?>
                <div class="card empty-card">
                    <div class="card-body">
                        <div class="empty-icon">👥</div>
                        <h3>No Members Yet</h3>
                        <p>Once you accept project applications, members will appear here for role assignment.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach($data['members'] as $member): ?>
                    <div class="card member-card">
                        <div class="card-left">
                            <?php if (!empty($member->profile_picture)): ?>
                                <img src="<?= URLROOT . '/' . $member->profile_picture ?>" alt="avatar" class="avatar" />
                            <?php else: ?>
                                <div class="avatar-initial"><?= strtoupper(substr($member->username ?? 'U', 0, 1)) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="member-header">
                                <div class="member-info">
                                    <h3 class="member-name"><?= htmlspecialchars($member->username) ?></h3>
                                    <p class="member-email"><?= htmlspecialchars($member->email) ?></p>
                                </div>
                                <div class="member-stats">
                                    <span class="stat-item">
                                        <span class="icon">⭐</span> Rating: <?= $member->user_rating ?? '0' ?>
                                    </span>
                                    <span class="stat-item">
                                        <span class="icon">✓</span> Completed: <?= $member->completed_projects ?? 0 ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Skills -->
                            <?php if (!empty($member->user_skills)): ?>
                                <div class="member-skills">
                                    <strong>Skills:</strong>
                                    <div class="skills-list">
                                        <?php foreach (explode(',', $member->user_skills) as $skill): ?>
                                            <span class="skill-tag"><?= htmlspecialchars(trim($skill)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Role Assignment (form POST to updateMemberRole) -->
                            <div class="role-assignment">
                                <form method="POST" action="<?= URLROOT ?>/organization/updateMemberRole" class="role-form" style="display:flex;align-items:center;gap:10px;">
                                    <input type="hidden" name="member_id" value="<?= $member->id ?>" />
                                    <input type="hidden" name="project_id" value="<?= $data['projectId'] ?>" />

                                    <label for="role_<?= $member->id ?>" style="margin:0 6px 0 0;"><strong>Assign Role:</strong></label>

                                    <?php
                                        $presetRoles = ['Member','Designer','Developer','UI/UX','Backend Engineer','Frontend Engineer','Data Analyst','QA Tester','Project Lead'];
                                        $currentRole = $member->role ?? 'Member';
                                        $isCustom = !in_array($currentRole, $presetRoles);
                                    ?>

                                    <select name="role" class="role-select" id="role_<?= $member->id ?>">
                                        <?php foreach ($presetRoles as $r): ?>
                                            <option value="<?= htmlspecialchars($r) ?>" <?= ($currentRole === $r ? 'selected' : '') ?>><?= htmlspecialchars($r) ?></option>
                                        <?php endforeach; ?>
                                        <option value="custom" <?= ($isCustom ? 'selected' : '') ?>>Custom Role</option>
                                    </select>

                                    <input type="text" name="custom_role" class="custom-role-input" id="custom_<?= $member->id ?>" placeholder="Enter custom role..." value="<?= ($isCustom ? htmlspecialchars($currentRole) : '') ?>" style="display:<?= ($isCustom ? 'inline-block' : 'none') ?>;padding:6px;border-radius:6px;border:1px solid #e1eefb;" />

                                    <button type="submit" class="btn btn-primary">Save Role</button>
                                </form>
                            </div>

                            <!-- Tasks Section for this Member -->
                            <div class="member-tasks-section" style="margin-top:20px;padding-top:20px;border-top:2px solid #f0f0f0;">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                                    <h4 style="margin:0;color:#1a1a1a;font-size:16px;">📋 Tasks for this Member</h4>
                                    <button class="btn btn-primary assign-task-btn" data-member-id="<?= $member->user_id ?>" data-member-name="<?= htmlspecialchars($member->username) ?>">
                                        ➕ Assign Task
                                    </button>
                                </div>

                                <?php 
                                // Fetch tasks for this member using taskModel from controller
                                $memberTasks = $data['taskModel']->getTasksByMember($data['projectId'], $member->user_id);
                                
                                // Group tasks by status
                                $tasksByStatus = [
                                    'pending' => [],
                                    'in_progress' => [],
                                    'completed' => []
                                ];
                                
                                foreach ($memberTasks as $task) {
                                    if ($task->status === 'pending') {
                                        $tasksByStatus['pending'][] = $task;
                                    } elseif ($task->status === 'in_progress' || $task->status === 'on_hold') {
                                        $tasksByStatus['in_progress'][] = $task;
                                    } elseif ($task->status === 'completed') {
                                        $tasksByStatus['completed'][] = $task;
                                    }
                                }
                                
                                $totalTasks = count($memberTasks);
                                $completedCount = count($tasksByStatus['completed']);
                                ?>

                                <?php if ($totalTasks === 0): ?>
                                    <p style="color:#999;font-size:14px;padding:15px;background:#f9f9f9;border-radius:8px;text-align:center;">
                                        No tasks assigned yet
                                    </p>
                                <?php else: ?>
                                    <div class="task-summary" style="display:flex;gap:10px;margin-bottom:15px;flex-wrap:wrap;">
                                        <span class="task-stat" style="background:#fff3cd;color:#856404;padding:6px 12px;border-radius:6px;font-size:13px;">
                                            📌 To-Do: <?= count($tasksByStatus['pending']) ?>
                                        </span>
                                        <span class="task-stat" style="background:#cfe2ff;color:#084298;padding:6px 12px;border-radius:6px;font-size:13px;">
                                            🔄 In Progress: <?= count($tasksByStatus['in_progress']) ?>
                                        </span>
                                        <span class="task-stat" style="background:#d1e7dd;color:#0f5132;padding:6px 12px;border-radius:6px;font-size:13px;">
                                            ✓ Completed: <?= $completedCount ?>
                                        </span>
                                    </div>

                                    <div class="tasks-container" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
                                        <!-- To-Do Column -->
                                        <div class="task-column">
                                            <div class="column-header" style="background:#fff3cd;padding:8px;border-radius:6px;margin-bottom:8px;">
                                                <strong style="font-size:13px;color:#856404;">To-Do</strong>
                                            </div>
                                            <?php foreach ($tasksByStatus['pending'] as $task): ?>
                                                <?php 
                                                    $isOverdue = !empty($task->due_date) && strtotime($task->due_date) < time();
                                                ?>
                                                <div class="task-card" style="background:white;border:1px solid #e0e0e0;border-radius:6px;padding:10px;margin-bottom:8px;">
                                                    <div style="font-weight:600;font-size:13px;color:#333;margin-bottom:5px;">
                                                        <?= htmlspecialchars($task->task_name) ?>
                                                    </div>
                                                    <div style="display:flex;gap:5px;flex-wrap:wrap;margin-bottom:5px;">
                                                        <?php if ($task->priority === 'high'): ?>
                                                            <span style="background:#fee2e2;color:#991b1b;padding:2px 6px;border-radius:4px;font-size:11px;">🔴 High</span>
                                                        <?php elseif ($task->priority === 'medium'): ?>
                                                            <span style="background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:4px;font-size:11px;">🟡 Medium</span>
                                                        <?php else: ?>
                                                            <span style="background:#dbeafe;color:#1e40af;padding:2px 6px;border-radius:4px;font-size:11px;">🟢 Low</span>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($task->due_date)): ?>
                                                            <span style="background:<?= $isOverdue ? '#fee2e2' : '#e0e7ff' ?>;color:<?= $isOverdue ? '#991b1b' : '#4338ca' ?>;padding:2px 6px;border-radius:4px;font-size:11px;">
                                                                <?= $isOverdue ? '⚠️' : '📅' ?> <?= date('M d', strtotime($task->due_date)) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($task->description)): ?>
                                                        <div style="font-size:11px;color:#666;margin-bottom:8px;max-height:40px;overflow:hidden;">
                                                            <?= htmlspecialchars(substr($task->description, 0, 60)) ?><?= strlen($task->description) > 60 ? '...' : '' ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <select class="task-status-select" data-task-id="<?= $task->id ?>" style="width:100%;padding:4px;border-radius:4px;border:1px solid #ddd;font-size:11px;">
                                                        <option value="pending" selected>To-Do</option>
                                                        <option value="in_progress">In Progress</option>
                                                        <option value="completed">Completed</option>
                                                    </select>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- In Progress Column -->
                                        <div class="task-column">
                                            <div class="column-header" style="background:#cfe2ff;padding:8px;border-radius:6px;margin-bottom:8px;">
                                                <strong style="font-size:13px;color:#084298;">In Progress</strong>
                                            </div>
                                            <?php foreach ($tasksByStatus['in_progress'] as $task): ?>
                                                <?php 
                                                    $isOverdue = !empty($task->due_date) && strtotime($task->due_date) < time();
                                                ?>
                                                <div class="task-card" style="background:white;border:1px solid #e0e0e0;border-radius:6px;padding:10px;margin-bottom:8px;">
                                                    <div style="font-weight:600;font-size:13px;color:#333;margin-bottom:5px;">
                                                        <?= htmlspecialchars($task->task_name) ?>
                                                    </div>
                                                    <div style="display:flex;gap:5px;flex-wrap:wrap;margin-bottom:5px;">
                                                        <?php if ($task->priority === 'high'): ?>
                                                            <span style="background:#fee2e2;color:#991b1b;padding:2px 6px;border-radius:4px;font-size:11px;">🔴 High</span>
                                                        <?php elseif ($task->priority === 'medium'): ?>
                                                            <span style="background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:4px;font-size:11px;">🟡 Medium</span>
                                                        <?php else: ?>
                                                            <span style="background:#dbeafe;color:#1e40af;padding:2px 6px;border-radius:4px;font-size:11px;">🟢 Low</span>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($task->due_date)): ?>
                                                            <span style="background:<?= $isOverdue ? '#fee2e2' : '#e0e7ff' ?>;color:<?= $isOverdue ? '#991b1b' : '#4338ca' ?>;padding:2px 6px;border-radius:4px;font-size:11px;">
                                                                <?= $isOverdue ? '⚠️' : '📅' ?> <?= date('M d', strtotime($task->due_date)) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($task->description)): ?>
                                                        <div style="font-size:11px;color:#666;margin-bottom:8px;max-height:40px;overflow:hidden;">
                                                            <?= htmlspecialchars(substr($task->description, 0, 60)) ?><?= strlen($task->description) > 60 ? '...' : '' ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <select class="task-status-select" data-task-id="<?= $task->id ?>" style="width:100%;padding:4px;border-radius:4px;border:1px solid #ddd;font-size:11px;">
                                                        <option value="pending">To-Do</option>
                                                        <option value="in_progress" selected>In Progress</option>
                                                        <option value="completed">Completed</option>
                                                    </select>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- Completed Column -->
                                        <div class="task-column">
                                            <div class="column-header" style="background:#d1e7dd;padding:8px;border-radius:6px;margin-bottom:8px;">
                                                <strong style="font-size:13px;color:#0f5132;">Completed</strong>
                                            </div>
                                            <?php foreach ($tasksByStatus['completed'] as $task): ?>
                                                <div class="task-card" style="background:#f8f9fa;border:1px solid #e0e0e0;border-radius:6px;padding:10px;margin-bottom:8px;opacity:0.8;">
                                                    <div style="font-weight:600;font-size:13px;color:#333;margin-bottom:5px;text-decoration:line-through;">
                                                        <?= htmlspecialchars($task->task_name) ?>
                                                    </div>
                                                    <div style="display:flex;gap:5px;flex-wrap:wrap;margin-bottom:5px;">
                                                        <?php if ($task->priority === 'high'): ?>
                                                            <span style="background:#fee2e2;color:#991b1b;padding:2px 6px;border-radius:4px;font-size:11px;">🔴 High</span>
                                                        <?php elseif ($task->priority === 'medium'): ?>
                                                            <span style="background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:4px;font-size:11px;">🟡 Medium</span>
                                                        <?php else: ?>
                                                            <span style="background:#dbeafe;color:#1e40af;padding:2px 6px;border-radius:4px;font-size:11px;">🟢 Low</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <select class="task-status-select" data-task-id="<?= $task->id ?>" style="width:100%;padding:4px;border-radius:4px;border:1px solid #ddd;font-size:11px;">
                                                        <option value="pending">To-Do</option>
                                                        <option value="in_progress">In Progress</option>
                                                        <option value="completed" selected>Completed</option>
                                                    </select>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Owner actions: Remove / Report -->
                            <div class="member-actions" style="margin-top:20px;">
                                <button class="btn btn-danger remove-member-btn" data-member-id="<?= $member->id ?>" data-user-id="<?= $member->user_id ?>" data-project-id="<?= $data['projectId'] ?>">Remove</button>
                                <button class="btn btn-warning report-member-btn" data-member-id="<?= $member->id ?>" data-user-id="<?= $member->user_id ?>" data-project-id="<?= $data['projectId'] ?>">Report</button>
                            </div>

                            <!-- Member Metadata -->
                            <div class="member-meta">
                                <small class="muted">Joined: <?= date('M d, Y', strtotime($member->joined_at)) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Task Assignment Modal -->
<div id="taskModal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div class="modal-content" style="background:white;border-radius:12px;padding:30px;max-width:500px;width:90%;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h2 style="margin:0;font-size:20px;">➕ Assign New Task</h2>
            <button id="closeModal" style="background:none;border:none;font-size:24px;cursor:pointer;color:#999;">&times;</button>
        </div>
        
        <form id="taskAssignForm">
            <input type="hidden" id="modal_project_id" name="project_id" value="<?= $data['projectId'] ?>">
            <input type="hidden" id="modal_assigned_to" name="assigned_to">
            
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Member:</label>
                <input type="text" id="modal_member_name" readonly style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;background:#f5f5f5;">
            </div>
            
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Task Title *</label>
                <input type="text" name="task_name" id="modal_task_name" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
            </div>
            
            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">Description</label>
                <textarea name="description" id="modal_description" rows="3" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;resize:vertical;"></textarea>
            </div>
            
            <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                <div class="form-group">
                    <label style="display:block;font-weight:600;margin-bottom:5px;">Priority *</label>
                    <select name="priority" id="modal_priority" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                        <option value="low">🟢 Low</option>
                        <option value="medium" selected>🟡 Medium</option>
                        <option value="high">🔴 High</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label style="display:block;font-weight:600;margin-bottom:5px;">Deadline</label>
                    <input type="date" name="due_date" id="modal_deadline" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                </div>
            </div>
            
            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="btn btn-primary" style="flex:1;padding:12px;border-radius:6px;font-weight:600;">Create Task</button>
                <button type="button" id="cancelModal" class="btn btn-secondary" style="flex:1;padding:12px;border-radius:6px;font-weight:600;">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.URLROOT = window.URLROOT || '<?= URLROOT ?>';

// Handle custom role input visibility
document.querySelectorAll('.role-select').forEach(select => {
    select.addEventListener('change', function() {
        const memberId = this.id.replace('role_', '');
        const customInput = document.getElementById('custom_' + memberId);
        
        if (this.value === 'custom') {
            customInput.style.display = 'block';
            customInput.focus();
        } else {
            customInput.style.display = 'none';
            customInput.value = '';
        }
    });
});

// Handle role update
// Note: role updates now use a POST form to /organization/updateMemberRole. The legacy AJAX handler was removed.

// Handle remove member
document.querySelectorAll('.remove-member-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const memberId = this.dataset.memberId;
        const projectId = this.dataset.projectId;
        const userId = this.dataset.userId;

        if (!confirm('Are you sure you want to remove this member from the project? This cannot be undone.')) return;

        const formData = new FormData();
        formData.append('member_id', memberId);
        formData.append('project_id', projectId);

        // Disable button while processing
        btn.disabled = true;

        fetch(URLROOT + '/organization/removeMember', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Remove the member card from DOM
                const card = btn.closest('.member-card');
                if (card) card.remove();
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed to remove member');
            btn.disabled = false;
        });
    });
});

// Handle report member
document.querySelectorAll('.report-member-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const memberId = this.dataset.memberId;
        const projectId = this.dataset.projectId;
        const userId = this.dataset.userId;

        const reason = prompt('Enter a short reason for reporting this user (required):');
        if (!reason || !reason.trim()) {
            alert('Report reason is required');
            return;
        }

        const details = prompt('Optional: add additional details (leave blank if none):') || '';

        const formData = new FormData();
        formData.append('project_id', projectId);
        formData.append('reported_user_id', userId);
        formData.append('reason', reason.trim());
        formData.append('details', details.trim());

        btn.disabled = true;

        fetch(URLROOT + '/organization/reportUser', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Report submitted');
            } else {
                alert('Error: ' + data.message);
            }
            btn.disabled = false;
        })
        .catch(err => {
            console.error(err);
            alert('Failed to submit report');
            btn.disabled = false;
        });
    });
});

// Utility function to escape HTML
function htmlEscape(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Task Assignment Modal Handler
document.querySelectorAll('.assign-task-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const memberId = this.dataset.memberId;
        const memberName = this.dataset.memberName;
        
        document.getElementById('modal_assigned_to').value = memberId;
        document.getElementById('modal_member_name').value = memberName;
        document.getElementById('modal_task_name').value = '';
        document.getElementById('modal_description').value = '';
        document.getElementById('modal_priority').value = 'medium';
        document.getElementById('modal_deadline').value = '';
        
        document.getElementById('taskModal').style.display = 'flex';
    });
});

// Close Modal
document.getElementById('closeModal').addEventListener('click', function() {
    document.getElementById('taskModal').style.display = 'none';
});

document.getElementById('cancelModal').addEventListener('click', function() {
    document.getElementById('taskModal').style.display = 'none';
});

// Task Assignment Form Submission
document.getElementById('taskAssignForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating...';
    
    fetch(URLROOT + '/TaskController/create', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Task created successfully');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to create task'));
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Task';
        }
    })
    .catch(err => {
        console.error(err);
        alert('An error occurred while creating the task');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Task';
    });
});

// Task Status Update Handler
document.querySelectorAll('.task-status-select').forEach(select => {
    select.addEventListener('change', function() {
        const taskId = this.dataset.taskId;
        const newStatus = this.value;
        
        if (!confirm('Update task status to ' + newStatus + '?')) {
            // Reset select to original value
            this.value = this.querySelector('option[selected]').value;
            return;
        }
        
        const formData = new FormData();
        formData.append('task_id', taskId);
        formData.append('status', newStatus);
        
        this.disabled = true;
        
        fetch(URLROOT + '/TaskController/updateStatus', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Reload page to show updated task in correct column
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to update status'));
                this.disabled = false;
                this.value = this.querySelector('option[selected]').value;
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred while updating task status');
            this.disabled = false;
            this.value = this.querySelector('option[selected]').value;
        });
    });
});
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
