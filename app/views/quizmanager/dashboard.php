<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/qmansidebar.php'; ?>

<!-- Link CSS -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/quizmandashboard.css">

<div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1 class="dashboard-title">Quiz Manager Dashboard</h1>
        <a href="<?php echo URLROOT; ?>/quizmanager/create" class="btn btn-primary create-btn">
            + Create New Quiz
        </a>
    </div>

    <!-- Quiz Table -->
    <div class="quiz-table-container">
        <div class="table-header">
            <h2 class="table-title">Quiz Management</h2>
            <select class="filter-select" id="statusFilter" onchange="filterQuizzes()">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="paused">Paused</option>
                <option value="draft">Draft</option>
            </select>
        </div>

        <table class="quiz-table">
            <thead>
                <tr>
                    <th>Quiz Details</th>
                    <th>Status</th>
                    <th>Participants</th>
                    <th>Questions</th>
                    <th>Duration</th>
                    <th>Avg Score</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="quizTableBody"></tbody>
        </table>
    </div>
</div>

<!-- Link JavaScript -->
<script src="<?php echo URLROOT; ?>/assets/js/quizmandashboard.js"></script>

<?php require_once '../app/views/layouts/footer_user.php'; ?>