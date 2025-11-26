<?php
// Include database configuration
require_once '../app/config/config.php';

// Database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_task'])) {
            $stmt = $pdo->prepare("INSERT INTO project_tasks (project_id, task_name, description, assigned_to, status, priority, due_date, created_at) VALUES (?, ?, ?, ?, 'pending', ?, ?, NOW())");
            $stmt->execute([
                $_POST['project_id'] ?? 0,
                $_POST['task_name'],
                $_POST['description'],
                $_POST['assigned_to'] ?? null,
                $_POST['priority'] ?? 'medium',
                $_POST['due_date'] ?? null
            ]);
            header('Location: task_tracker.php');
            exit;
        } elseif (isset($_POST['update_status'])) {
            $stmt = $pdo->prepare("UPDATE project_tasks SET status = ? WHERE id = ?");
            $stmt->execute([$_POST['new_status'], $_POST['task_id']]);
            header('Location: task_tracker.php');
            exit;
        } elseif (isset($_POST['delete_task'])) {
            $stmt = $pdo->prepare("DELETE FROM project_tasks WHERE id = ?");
            $stmt->execute([$_POST['task_id']]);
            header('Location: task_tracker.php');
            exit;
        }
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch all tasks
$tasks = $pdo->query("
    SELECT 
        pt.*,
        p.name as project_name,
        u.username as assigned_user
    FROM project_tasks pt
    LEFT JOIN projects p ON pt.project_id = p.id
    LEFT JOIN users u ON pt.assigned_to = u.id
    ORDER BY pt.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch projects for dropdown
$projects = $pdo->query("SELECT id, name FROM projects ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Fetch users for assignment dropdown
$users = $pdo->query("SELECT id, username FROM users WHERE role = 'individual' ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Assignment & Tracker - SkillXchange</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 10px 40px rgba(0,0,0,0.2); 
        }
        h1 { 
            color: #333; 
            margin-bottom: 10px; 
            font-size: 32px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }
        .form-section {
            background: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
        }
        .form-section h2 {
            margin-bottom: 20px;
            color: #555;
            font-size: 20px;
        }
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group { 
            margin-bottom: 15px; 
        }
        label { 
            display: block; 
            margin-bottom: 5px; 
            font-weight: 600; 
            color: #555; 
            font-size: 13px;
        }
        input[type="text"],
        input[type="date"],
        select,
        textarea { 
            width: 100%; 
            padding: 10px 12px; 
            border: 1px solid #ddd; 
            border-radius: 6px; 
            font-size: 14px;
            font-family: inherit;
            transition: border 0.3s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        button { 
            padding: 12px 24px; 
            background: #667eea; 
            color: white; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-size: 14px; 
            font-weight: 600;
            transition: background 0.3s;
        }
        button:hover { 
            background: #5568d3; 
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        .task-list { 
            margin-top: 30px; 
        }
        .task-list h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .filters {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 8px 16px;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s;
        }
        .filter-btn:hover,
        .filter-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .task-item { 
            background: #fff; 
            padding: 20px; 
            margin-bottom: 15px; 
            border-radius: 8px; 
            border: 1px solid #e0e0e0;
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start;
            transition: box-shadow 0.3s;
        }
        .task-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .task-item.completed { 
            opacity: 0.7; 
            background: #f8f8f8;
        }
        .task-item.completed .task-title {
            text-decoration: line-through;
        }
        .task-info { 
            flex: 1; 
        }
        .task-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .task-title { 
            font-weight: bold; 
            font-size: 16px;
            color: #333;
        }
        .task-description {
            color: #666;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .task-meta { 
            font-size: 12px; 
            color: #666; 
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .task-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .task-actions { 
            display: flex; 
            gap: 8px; 
            align-items: flex-start;
        }
        .task-actions button,
        .task-actions select { 
            padding: 6px 12px; 
            font-size: 12px; 
        }
        .task-actions select {
            cursor: pointer;
        }
        .btn-delete { 
            background: #f44336; 
        }
        .btn-delete:hover { 
            background: #da190b; 
        }
        .no-tasks { 
            text-align: center; 
            color: #999; 
            padding: 40px; 
            background: #f9f9f9;
            border-radius: 8px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-in_progress { background: #cfe2ff; color: #084298; }
        .status-completed { background: #d1e7dd; color: #0f5132; }
        .status-on_hold { background: #f8d7da; color: #842029; }
        .priority-low { background: #d1e7dd; color: #0f5132; }
        .priority-medium { background: #fff3cd; color: #856404; }
        .priority-high { background: #f8d7da; color: #842029; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Task Assignment & Tracker</h1>
        <p class="subtitle">Manage and track all project tasks in one place</p>

        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= count($tasks) ?></div>
                <div class="stat-label">Total Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($tasks, fn($t) => $t['status'] === 'pending')) ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($tasks, fn($t) => $t['status'] === 'in_progress')) ?></div>
                <div class="stat-label">In Progress</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count(array_filter($tasks, fn($t) => $t['status'] === 'completed')) ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>

        <!-- Add Task Form -->
        <div class="form-section">
            <h2>➕ Add New Task</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="task_name">Task Name *</label>
                        <input type="text" id="task_name" name="task_name" required placeholder="Enter task name">
                    </div>
                    <div class="form-group">
                        <label for="project_id">Project</label>
                        <select id="project_id" name="project_id">
                            <option value="0">-- No Project --</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?= $project['id'] ?>"><?= htmlspecialchars($project['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Task description (optional)"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="assigned_to">Assign To</label>
                        <select id="assigned_to" name="assigned_to">
                            <option value="">-- Unassigned --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="priority">Priority</label>
                        <select id="priority" name="priority">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input type="date" id="due_date" name="due_date">
                    </div>
                </div>
                <button type="submit" name="add_task">Add Task</button>
            </form>
        </div>

        <!-- Task List -->
        <div class="task-list">
            <h2>📝 All Tasks</h2>
            
            <div class="filters">
                <button class="filter-btn active" onclick="filterTasks('all')">All</button>
                <button class="filter-btn" onclick="filterTasks('pending')">Pending</button>
                <button class="filter-btn" onclick="filterTasks('in_progress')">In Progress</button>
                <button class="filter-btn" onclick="filterTasks('completed')">Completed</button>
                <button class="filter-btn" onclick="filterTasks('on_hold')">On Hold</button>
            </div>

            <?php if (empty($tasks)): ?>
                <p class="no-tasks">📭 No tasks yet. Add your first task above!</p>
            <?php else: ?>
                <?php foreach ($tasks as $task): ?>
                    <div class="task-item <?= $task['status'] === 'completed' ? 'completed' : '' ?>" data-status="<?= $task['status'] ?>">
                        <div class="task-info">
                            <div class="task-header">
                                <div class="task-title"><?= htmlspecialchars($task['task_name']) ?></div>
                                <span class="badge status-<?= $task['status'] ?>"><?= ucfirst(str_replace('_', ' ', $task['status'])) ?></span>
                                <span class="badge priority-<?= $task['priority'] ?>"><?= ucfirst($task['priority']) ?></span>
                            </div>
                            <?php if ($task['description']): ?>
                                <div class="task-description"><?= htmlspecialchars($task['description']) ?></div>
                            <?php endif; ?>
                            <div class="task-meta">
                                <?php if ($task['project_name']): ?>
                                    <div class="task-meta-item">
                                        <strong>🎯 Project:</strong> <?= htmlspecialchars($task['project_name']) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="task-meta-item">
                                    <strong>👤 Assigned:</strong> <?= $task['assigned_user'] ? htmlspecialchars($task['assigned_user']) : 'Unassigned' ?>
                                </div>
                                <?php if ($task['due_date']): ?>
                                    <div class="task-meta-item">
                                        <strong>📅 Due:</strong> <?= date('M d, Y', strtotime($task['due_date'])) ?>
                                    </div>
                                <?php endif; ?>
                                <div class="task-meta-item">
                                    <strong>🕒 Created:</strong> <?= date('M d, Y', strtotime($task['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <div class="task-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                <select name="new_status" onchange="this.form.submit()">
                                    <option value="">Change Status...</option>
                                    <option value="pending" <?= $task['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="in_progress" <?= $task['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="completed" <?= $task['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="on_hold" <?= $task['status'] === 'on_hold' ? 'selected' : '' ?>>On Hold</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this task?')">
                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                <button type="submit" name="delete_task" class="btn-delete">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterTasks(status) {
            const tasks = document.querySelectorAll('.task-item');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter tasks
            tasks.forEach(task => {
                if (status === 'all' || task.dataset.status === status) {
                    task.style.display = 'flex';
                } else {
                    task.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
