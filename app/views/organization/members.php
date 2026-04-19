<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/feedback.css">

<!-- Include Feedback Modal early so elements exist -->
<?php require_once "../app/views/partials/modals/feedback_modal.php"; ?>

<!-- Load Feedback JavaScript after modal HTML -->
<script src="<?= URLROOT ?>/assets/js/feedback.js"></script>

<main class="site-main">
    <div class="container org-dashboard">
        <div class="page-header">
            <div>
                <h1>Manage Team Members</h1>
                <p>Project: <strong><?= htmlspecialchars($data['project']->name) ?></strong></p>
            </div>
            <div class="page-header-actions">
                <a href="<?= URLROOT ?>/chat/index/<?= (int)$data['projectId'] ?>" class="btn btn-primary btn-chat-entry">
                    <i class="ph ph-chat-circle-dots"></i>
                    Enter Project Chat
                </a>
                <a href="<?= URLROOT ?>/organization/projects" class="btn btn-secondary">← Back to Projects</a>
            </div>
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
        <div style="margin-bottom: 30px; padding: 25px; background: #fff; border: 2px solid var(--blue-bg, #e5e7eb); border-radius: 12px;">
            <h2 style="font-size: 20px; margin-bottom: 20px; color: var(--dark-bg, #111827); font-weight: 600; display: flex; align-items: center; gap: 10px; padding-bottom: 12px; border-bottom: 1px solid var(--blue-bg, #e5e7eb);">
                <i class="ph ph-chart-bar" style="color: var(--primary-blue, #2563eb); font-size: 24px;"></i> Project Progress Overview
            </h2>
            
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 140px; padding: 20px; background: var(--blue-bg, #f4f6f8); border-radius: 8px; border: 1px solid #d1d9e6; display: flex; flex-direction: column; align-items: center;">
                    <div style="font-size: 14px; color: #555; margin-bottom: 8px; font-weight: 500;">Overall</div>
                    <div style="font-size: 28px; font-weight: bold; color: var(--primary-blue, #0f172a);"><?= number_format($completionPct, 1) ?>%</div>
                </div>
                
                <div style="flex: 1; min-width: 140px; padding: 20px; background: #fff; border-radius: 8px; border: 1px solid #d1d9e6; display: flex; flex-direction: column; align-items: center;">
                    <div style="font-size: 14px; color: #555; margin-bottom: 8px; font-weight: 500;">Total Tasks</div>
                    <div style="font-size: 28px; font-weight: bold; color: #333;"><?= $totalTasks ?></div>
                </div>
                
                <div style="flex: 1; min-width: 140px; padding: 20px; background: #fffdf5; border-radius: 8px; border: 1px solid #fde68a; display: flex; flex-direction: column; align-items: center;">
                    <div style="font-size: 14px; color: #b45309; margin-bottom: 8px; font-weight: 500;">To-Do</div>
                    <div style="font-size: 28px; font-weight: bold; color: #d97706;"><?= $todoTasks ?></div>
                </div>
                
                <div style="flex: 1; min-width: 140px; padding: 20px; background: #f0f7ff; border-radius: 8px; border: 1px solid #bfdbfe; display: flex; flex-direction: column; align-items: center;">
                    <div style="font-size: 14px; color: #1d4ed8; margin-bottom: 8px; font-weight: 500;">In Progress</div>
                    <div style="font-size: 28px; font-weight: bold; color: #2563eb;"><?= $inProgressTasks ?></div>
                </div>
                
                <div style="flex: 1; min-width: 140px; padding: 20px; background: #f2fdf5; border-radius: 8px; border: 1px solid #bbf7d0; display: flex; flex-direction: column; align-items: center;">
                    <div style="font-size: 14px; color: #15803d; margin-bottom: 8px; font-weight: 500;">Completed</div>
                    <div style="font-size: 28px; font-weight: bold; color: #16a34a;"><?= $completedTasks ?></div>
                </div>
            </div>

            <!-- Member Performance Breakdown -->
            <?php if(isset($data['memberBreakdown']) && !empty($data['memberBreakdown'])): ?>
            <div style="background:white;border:2px solid var(--blue-bg);border-radius:12px;padding:25px;margin-top:25px;margin-bottom:25px;">
                <h3 style="font-size:18px;margin-bottom:20px;color:var(--dark-bg);font-weight:600;display:flex;align-items:center;gap:10px;border-bottom: 1px solid var(--blue-bg);padding-bottom: 12px;">
                    <i class="ph ph-users" style="color: var(--primary-blue); font-size: 24px;"></i>
                    Member Task Performance
                </h3>
                <div style="display:grid;gap:15px;">
                    <?php foreach($data['memberBreakdown'] as $member): 
                        $memberPct = $member->completion_percentage ?? 0;
                        $memberTotal = (int)($member->total_tasks ?? 0);
                        $memberCompleted = (int)($member->completed_tasks ?? 0);
                        $memberOverdue = (int)($member->overdue_tasks ?? 0);
                    ?>
                    <div style="display:flex;align-items:center;gap:15px;padding:15px;background:white;border:1px solid #d1d9e6;border-radius:8px;">
                        <?php if (!empty($member->profile_picture)): ?>
                            <img src="<?= URLROOT . '/' . $member->profile_picture ?>" alt="<?= htmlspecialchars($member->username) ?>" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--blue-bg);">
                        <?php else: ?>
                            <div style="width:48px;height:48px;border-radius:50%;background:var(--blue-bg);color:var(--primary-blue);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;border:1px solid #d1d9e6;">
                                <?= strtoupper(substr($member->username ?? 'U', 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:5px;">
                                <strong style="font-size:15px;color:var(--dark-bg);"><?= htmlspecialchars($member->username) ?></strong>
                                <span style="font-size:12px;color:var(--primary-blue);background:var(--blue-bg);padding:4px 10px;border-radius:6px;font-weight:600;"><?= htmlspecialchars($member->role ?? 'Member') ?></span>
                            </div>
                            
                            <div style="display:flex;align-items:center;gap:15px;margin-bottom:10px;">
                                <span style="font-size:13px;color:#666;font-weight:500;display:flex;align-items:center;gap:4px;"><i class="ph ph-list-bullets" style="font-size:14px;"></i> <?= $memberTotal ?> tasks</span>
                                <span style="font-size:13px;color:#16a34a;font-weight:500;display:flex;align-items:center;gap:4px;"><i class="ph ph-check" style="font-size:14px;"></i> <?= $memberCompleted ?> done</span>
                                <?php if($memberOverdue > 0): ?>
                                    <span style="font-size:13px;color:#ef4444;font-weight:600;display:flex;align-items:center;gap:4px;"><i class="ph ph-warning-circle" style="font-size:14px;"></i> <?= $memberOverdue ?> overdue</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($memberTotal > 0): ?>
                            <div style="background:#e5e7eb;height:8px;border-radius:6px;overflow:hidden;">
                                <div style="background:var(--primary-blue);height:100%;width:<?= $memberPct ?>%;"></div>
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
                    <span style="background:#ef4444;color:white;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px;"><i class="ph-bold ph-warning"></i></span>
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

        <?php
            $formatSkillLabel = static function ($skill) {
                $skill = trim((string)$skill);
                if ($skill === '') {
                    return '';
                }

                $normalized = strtolower(str_replace(['_', '-'], ' ', $skill));
                $normalized = preg_replace('/\s+/', ' ', trim($normalized));

                $special = [
                    'ai' => 'AI',
                    'api' => 'API',
                    'qa' => 'QA',
                    'ui' => 'UI',
                    'ux' => 'UX',
                    'ui ux' => 'UI/UX',
                    'ui/ux' => 'UI/UX',
                    'sql' => 'SQL',
                    'ml' => 'ML',
                    'github' => 'GitHub',
                    'devops' => 'DevOps',
                    'javascript' => 'JavaScript',
                    'typescript' => 'TypeScript',
                    'node js' => 'Node.js',
                    'react js' => 'React.js',
                    'next js' => 'Next.js'
                ];

                if (isset($special[$normalized])) {
                    return $special[$normalized];
                }

                return ucwords($normalized);
            };
        ?>

        <!-- Members List -->
        <div class="members-list">
            <?php if(empty($data['members'])): ?>
                <div class="card empty-card">
                    <div class="card-body">
                        <div class="empty-icon"><i class="ph ph-users" style="font-size:64px;color:#9ca3af;"></i></div>
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
                                        <span class="icon"><i class="ph ph-star"></i></span> Rating: <?= $member->user_rating ?? '0' ?>
                                    </span>
                                </div>
                            </div>

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
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;flex-wrap:wrap;gap:10px;">
                                    <h4 style="margin:0;color:#1a1a1a;font-size:16px;">Tasks for this Member</h4>
                                    <button class="btn btn-primary assign-task-btn" data-member-id="<?= $member->user_id ?>" data-member-name="<?= htmlspecialchars($member->username) ?>">
                                        Assign Task
                                    </button>
                                </div>

                                <?php 
                                $memberTasks = $data['taskModel']->getTasksByMember($data['projectId'], $member->user_id);
                                $totalTasks = count($memberTasks);
                                $todoCount = 0;
                                $inProgressCount = 0;
                                $doneCount = 0;

                                foreach ($memberTasks as $task) {
                                    $st = $task->status ?? 'todo';
                                    if ($st === 'todo' || $st === 'pending') {
                                        $todoCount++;
                                    } elseif ($st === 'in-progress' || $st === 'in_progress' || $st === 'on_hold') {
                                        $inProgressCount++;
                                    } elseif ($st === 'done' || $st === 'completed') {
                                        $doneCount++;
                                    }
                                }
                                ?>

                                <?php if ($totalTasks === 0): ?>
                                    <p style="color:#6b7280;font-size:14px;padding:14px;background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;">
                                        No tasks assigned yet.
                                    </p>
                                <?php else: ?>
                                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                                        <span style="background:#eef4f8;color:#355a72;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:600;">To-Do: <?= $todoCount ?></span>
                                        <span style="background:#eef4f8;color:#355a72;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:600;">In Progress: <?= $inProgressCount ?></span>
                                        <span style="background:#eef4f8;color:#355a72;padding:5px 10px;border-radius:999px;font-size:12px;font-weight:600;">Completed: <?= $doneCount ?></span>
                                    </div>

                                    <div style="overflow-x:auto;border:1px solid #e1eefb;border-radius:8px;">
                                        <table style="width:100%;border-collapse:collapse;font-size:13px;background:#fff;min-width:620px;">
                                            <thead>
                                                <tr style="background:#f7fbff;border-bottom:1px solid #e1eefb;">
                                                    <th style="text-align:left;padding:10px;color:#355a72;font-weight:700;">Task</th>
                                                    <th style="text-align:left;padding:10px;color:#355a72;font-weight:700;">Priority</th>
                                                    <th style="text-align:left;padding:10px;color:#355a72;font-weight:700;">Deadline</th>
                                                    <th style="text-align:left;padding:10px;color:#355a72;font-weight:700;">Buckx</th>
                                                    <th style="text-align:left;padding:10px;color:#355a72;font-weight:700;">Status</th>
                                                    <th style="text-align:left;padding:10px;color:#355a72;font-weight:700;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($memberTasks as $task): ?>
                                                    <?php
                                                        $taskTitle = $task->title ?? $task->task_name ?? 'Untitled';
                                                        $isOverdue = !empty($task->deadline) && strtotime($task->deadline) < time() && !in_array(($task->status ?? ''), ['done', 'completed']);
                                                        $priority = strtolower((string)($task->priority ?? 'low'));
                                                        if ($priority === 'high') {
                                                            $priorityStyle = 'background:#fee2e2;color:#991b1b;border:1px solid #fecaca;';
                                                        } elseif ($priority === 'medium') {
                                                            $priorityStyle = 'background:#fef3c7;color:#92400e;border:1px solid #fde68a;';
                                                        } else {
                                                            $priorityStyle = 'background:#e0f2fe;color:#0c4a6e;border:1px solid #bae6fd;';
                                                        }
                                                    ?>
                                                    <tr style="border-bottom:1px solid #edf2f7;">
                                                        <td style="padding:10px;color:#1f2937;font-weight:600;max-width:260px;">
                                                            <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($taskTitle) ?></div>
                                                        </td>
                                                        <td style="padding:10px;">
                                                            <span style="<?= $priorityStyle ?>padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;text-transform:capitalize;">
                                                                <?= htmlspecialchars($priority) ?>
                                                            </span>
                                                        </td>
                                                        <td style="padding:10px;color:<?= $isOverdue ? '#b91c1c' : '#4b5563' ?>;font-weight:<?= $isOverdue ? '600' : '400' ?>;">
                                                            <?= !empty($task->deadline) ? date('M d, Y', strtotime($task->deadline)) : '-' ?>
                                                        </td>
                                                        <td style="padding:10px;color:#1f2937;">
                                                            <?php 
                                                                $buckxAmount = !empty($task->buckx_allocated) ? (int)$task->buckx_allocated : 0;
                                                                echo $buckxAmount > 0 ? $buckxAmount : '-';
                                                            ?>
                                                        </td>
                                                        <td style="padding:10px;">
                                                            <select class="task-status-select" data-task-id="<?= $task->id ?>" style="width:130px;padding:5px 8px;border-radius:6px;border:1px solid #d1d5db;font-size:12px;background:#fff;">
                                                                <option value="todo" <?= (($task->status ?? '') === 'todo' || ($task->status ?? '') === 'pending') ? 'selected' : '' ?>>To-Do</option>
                                                                <option value="in-progress" <?= (($task->status ?? '') === 'in-progress' || ($task->status ?? '') === 'in_progress' || ($task->status ?? '') === 'on_hold') ? 'selected' : '' ?>>In Progress</option>
                                                                <option value="done" <?= (($task->status ?? '') === 'done' || ($task->status ?? '') === 'completed') ? 'selected' : '' ?>>Completed</option>
                                                            </select>
                                                        </td>
                                                        <td style="padding:10px;">
                                                            <button onclick="removeOrgTask(<?= $task->id ?>, this.closest('tr'))" title="Remove task" style="padding:5px 8px;background:#fff;border:1px solid #fca5a5;border-radius:6px;color:#dc2626;font-size:12px;cursor:pointer;">
                                                                <i class="ph ph-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Owner actions: Remove / Report / Give Feedback -->
                            <div class="member-actions" style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
                                <button class="btn btn-feedback give-feedback-btn" 
                                    data-user-id="<?= $member->user_id ?>" 
                                    data-user-name="<?= htmlspecialchars($member->username) ?>" 
                                    data-user-avatar="<?= !empty($member->profile_picture) ? URLROOT . '/' . $member->profile_picture : '' ?>" 
                                    data-context-type="project"
                                    data-context-id="<?= $data['projectId'] ?>"
                                    data-context-name="<?= htmlspecialchars($data['project']->name) ?>"
                                    onclick="openFeedbackModal(<?= $member->user_id ?>, '<?= addslashes($member->username) ?>', '<?= !empty($member->profile_picture) ? addslashes(URLROOT . '/' . $member->profile_picture) : '' ?>', 'project', <?= $data['projectId'] ?>, '<?= addslashes($data['project']->name) ?>')">
                                    <i class="ph ph-star" style="font-size:16px;"></i> Give Feedback
                                </button>
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
            <h2 style="margin:0;font-size:20px;">Assign New Task</h2>
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
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label style="display:block;font-weight:600;margin-bottom:5px;">Deadline</label>
                    <input type="date" name="due_date" id="modal_deadline" min="" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom:15px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;">BuckX Reward (Optional)</label>
                <input type="number" name="buckx_allocated" id="modal_buckx_allocated" min="0" step="0.01" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;" placeholder="Amount of BuckX to reward">
                <small style="display:block;margin-top:5px;color:#666;font-size:0.85rem;">Amount of BuckX to reward when task is completed. Leave empty or 0 for no reward.</small>
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
    
    // Debug: Test if feedback buttons exist
    console.log('URLROOT:', window.URLROOT);
    console.log('Feedback buttons on load:', document.querySelectorAll('.give-feedback-btn').length);

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
        document.getElementById('modal_buckx_allocated').value = '';
        
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('modal_deadline').min = today;
        
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
    
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating...';

    const projectId = document.getElementById('modal_project_id').value;
    const memberId  = document.getElementById('modal_assigned_to').value;
    const taskName  = document.getElementById('modal_task_name').value.trim();
    const description = document.getElementById('modal_description').value.trim();
    const priority  = document.getElementById('modal_priority').value;
    const dueDate   = document.getElementById('modal_deadline').value;
    const buckxAllocated = document.getElementById('modal_buckx_allocated').value.trim() || '0';

    if (!taskName) {
        alert('Task title is required');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Create Task';
        return;
    }

    const formData = new FormData();
    formData.append('project_id', projectId);
    formData.append('member_id', memberId);
    formData.append('task_name', taskName);
    formData.append('description', description);
    formData.append('priority', priority);
    formData.append('due_date', dueDate);
    formData.append('buckx_allocated', buckxAllocated);
    
    fetch(URLROOT + '/organization/assignTask', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (res.status === 401) {
            return res.json().then(data => {
                throw new Error(data.message || 'Your organization session has expired. Please sign in again.');
            });
        }
        if (!res.ok) {
            return res.text().then(txt => { throw new Error('Server returned ' + res.status + ': ' + txt.substring(0, 200)); });
        }
        const contentType = res.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            return res.text().then(txt => {
                const lower = txt.toLowerCase();
                if (lower.includes('/auth/signin') || lower.includes('sign in') || lower.includes('login')) {
                    throw new Error('Your organization session has expired. Please sign in again as the organization account.');
                }
                throw new Error('Expected JSON response but received: ' + txt.substring(0, 200));
            });
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message || 'Task assigned successfully');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to create task'));
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Task';
        }
    })
    .catch(err => {
        console.error('Task creation error:', err);
        alert('An error occurred while creating the task: ' + err.message);
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
        
        fetch(URLROOT + '/task/updateStatus', {
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

// Remove task (org only)
function removeOrgTask(taskId, cardEl) {
    if (!confirm('Remove this task? The member will be notified.')) return;

    // Visual feedback — dim card
    if (cardEl) { cardEl.style.opacity = '0.5'; cardEl.style.pointerEvents = 'none'; }

    fetch(URLROOT + '/organization/removeTask', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
        body: JSON.stringify({ task_id: taskId })
    })
    .then(r => {
        if (!r.ok) return r.text().then(t => { throw new Error(t.substring(0, 200)); });
        return r.json();
    })
    .then(data => {
        if (data.success) {
            // Animate removal
            if (cardEl) {
                cardEl.style.transition = 'all 0.3s ease';
                cardEl.style.transform  = 'scale(0.95)';
                cardEl.style.opacity    = '0';
                setTimeout(() => cardEl.remove(), 300);
            }
        } else {
            alert('Error: ' + (data.message || 'Failed to remove task'));
            if (cardEl) { cardEl.style.opacity = '1'; cardEl.style.pointerEvents = ''; }
        }
    })
    .catch(err => {
        console.error('Remove task error:', err);
        alert('An error occurred: ' + err.message);
        if (cardEl) { cardEl.style.opacity = '1'; cardEl.style.pointerEvents = ''; }
    });
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
