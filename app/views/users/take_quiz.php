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
                <button class="back-btn" onclick="window.location.href='<?= URLROOT ?>/userdashboard/quiz'">
                    ← Back to Quizzes
                </button>
                <h1><?= $data['quiz']['title'] ?></h1>
                <p><?= $data['quiz']['description'] ?></p>
                <div class="quiz-meta">
                    <span class="badge difficulty-<?= strtolower($data['quiz']['difficulty']) ?>">
                        <?= $data['quiz']['difficulty'] ?>
                    </span>
                    <span class="quiz-info-item">
                        📝 <?= count($data['quiz']['questions']) ?> Questions
                    </span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress-section">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p class="progress-text">
                    Question <span id="currentQuestion">1</span> of <span id="totalQuestions"><?= count($data['quiz']['questions']) ?></span>
                </p>
            </div>

            <!-- Quiz Content -->
            <div class="quiz-content" id="quizContent">
                <!-- Questions will be loaded here by JavaScript -->
            </div>

            <!-- Quiz Navigation -->
            <div class="quiz-navigation">
                <button class="btn btn-secondary" id="prevBtn" disabled>Previous</button>
                <button class="btn btn-primary" id="nextBtn">Next</button>
                <button class="btn btn-success" id="submitBtn" style="display: none;">Submit Quiz</button>
            </div>

            <!-- Results Section (hidden initially) -->
            <div class="results-section" id="resultsSection" style="display: none;">
                <div class="results-card">
                    <h2>Quiz Complete! 🎉</h2>
                    <div class="score-display">
                        <div class="score-circle">
                            <span id="scorePercentage">0</span>%
                        </div>
                        <p id="scoreText"></p>
                    </div>
                    <div class="results-actions">
                        <button class="btn btn-primary" onclick="location.reload()">Retake Quiz</button>
                        <button class="btn btn-secondary" onclick="window.location.href='/userdashboard/quiz'">Back to Quizzes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

<script>
// Pass PHP quiz data to JavaScript
const quizData = <?= json_encode($data['quiz']) ?>;
</script>
<script src="<?= URLROOT ?>/assets/js/take_quiz.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>