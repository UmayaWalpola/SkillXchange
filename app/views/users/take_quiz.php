<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/take_quiz.css">


<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        <div class="take-quiz-container">
        <!-- Quiz Header -->
        <div class="quiz-header-section">
            <button onclick="window.location.href='<?= URLROOT ?>/userdashboard/quiz'" class="back-btn">
                ← Back to Quizzes
            </button>
            <h1><?= $data['quiz']['title'] ?></h1>
            <p><?= $data['quiz']['description'] ?></p>
            
            <div class="quiz-meta-info">
                <span class="badge difficulty-<?= strtolower($data['quiz']['difficulty']) ?>">
                    <?= $data['quiz']['difficulty'] ?>
                </span>
                <span class="quiz-info-item"><i class="ph ph-notepad"></i> <?= $data['quiz']['questionCount'] ?> Questions</span>
                <?php if ($data['quiz']['timeLimit']): ?>
                    <span class="quiz-info-item">⏱️ <?= $data['quiz']['timeLimit'] ?> minutes</span>
                <?php endif; ?>
            </div>
            
            <?php if ($data['quiz']['badge']): ?>
            <div class="quiz-badge-info">
                <span class="badge-icon"><?= $data['quiz']['badge']['icon'] ?></span>
                <span>Complete this quiz to earn: <strong><?= $data['quiz']['badge']['name'] ?></strong></span>
            </div>
            <?php endif; ?>
            
            <?php if ($data['quiz']['timeLimit']): ?>
            <div class="quiz-timer">
                <span>Time Remaining:</span>
                <span id="timerDisplay" class="timer-display">--:--</span>
            </div>
            <?php endif; ?>
        </div>

        <!-- Progress Section -->
        <div class="progress-section">
            <div class="progress-bar">
                <div id="progressFill" class="progress-fill"></div>
            </div>
            <div class="progress-text">
                <span>Question <span id="currentQuestion">1</span> of <span id="totalQuestions">-</span></span>
                <span id="progressText">0 of <?= $data['quiz']['questionCount'] ?> answered</span>
            </div>
        </div>

        <!-- Quiz Content -->
        <div id="quizContent" class="quiz-content">
            <div style="text-align: center; padding: 40px 0; color: #6b7280;">
                <p>Loading questions...</p>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="quiz-navigation">
            <button id="prevBtn" class="btn btn-secondary">← Previous</button>
            <button id="nextBtn" class="btn btn-primary">Next →</button>
            <button id="submitBtn" class="btn btn-success" style="display: none;">Submit Quiz</button>
        </div>

        <!-- Results Section -->
        <div id="resultsSection" class="results-section"></div>
    </div>
</div>
</div>
</main>

<!-- Hidden data element -->
<div id="quiz-data" 
     data-quiz='<?= htmlspecialchars(json_encode($data['quiz']), ENT_QUOTES, 'UTF-8') ?>'
     data-urlroot="<?= URLROOT ?>"
     style="display: none;">
</div>

<script>
// Pass PHP quiz data to JavaScript
const quizData = <?= json_encode($data['quiz']) ?>;
</script>
<script src="<?= URLROOT ?>/assets/js/take_quiz.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
