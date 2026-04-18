<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/qmansidebar.php'; ?>
<link rel="stylesheet" href="<?= URLROOT; ?>/assets/css/quizcreate.css">

<?php
$quizData = is_array($quiz) ? $quiz : (is_object($quiz) ? (array)$quiz : []);
$quizTitle = htmlspecialchars($quizData['title'] ?? 'Untitled Quiz');
$quizDifficulty = htmlspecialchars($quizData['difficulty_level'] ?? ($quizData['difficulty'] ?? 'General'));
$quizReward = htmlspecialchars((int)($quizData['reward_amount'] ?? 0));
$quizDescription = nl2br(htmlspecialchars($quizData['description'] ?? 'No description provided.'));
$questionsData = is_array($questions) ? $questions : (is_object($questions) ? (array)$questions : []);
$questionsData = array_map(function($q) {
    return is_array($q) ? $q : (is_object($q) ? (array)$q : []);
}, $questionsData);
?>
<div class="dashboard-container">
    <div class="builder-container">
        <div class="builder-header">
            <a href="<?= URLROOT; ?>/quizmanager" class="btn-back">← Back to Dashboard</a>
            <h1 class="builder-title">Edit Quiz</h1>
            <p class="builder-subtitle">This quiz is available for review. Full editing is not supported yet.</p>
        </div>

        <div class="section-card">
            <h2 class="section-title">Quiz Details</h2>
            <div class="form-row" style="gap: 16px; flex-wrap: wrap; margin-bottom: 20px;">
                <div class="form-group" style="flex: 1; min-width: 220px;">
                    <label>Title</label>
                    <div class="preview-value"><?= $quizTitle; ?></div>
                </div>
                <div class="form-group" style="flex: 1; min-width: 220px;">
                    <label>Difficulty</label>
                    <div class="preview-value"><?= $quizDifficulty; ?></div>
                </div>
                <div class="form-group" style="flex: 1; min-width: 220px;">
                    <label>Reward</label>
                    <div class="preview-value"><?= $quizReward; ?> Buckx</div>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <p class="preview-value" style="white-space: pre-wrap;"><?= $quizDescription; ?></p>
            </div>
        </div>

        <div class="section-card">
            <h2 class="section-title">Quiz Questions</h2>
            <?php if (!empty($questionsData)): ?>
                <?php foreach ($questionsData as $index => $question): ?>
                    <div class="question-item" style="margin-bottom: 18px; padding: 18px; border: 1px solid #dde3ea; border-radius: 12px;">
                        <div class="question-header" style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                            <span class="question-number">Question <?= $index + 1; ?></span>
                            <span class="status-text" style="text-transform: capitalize; background: #eef2ff; padding: 6px 12px; border-radius: 999px; font-size: .85rem;">Order <?= htmlspecialchars($question['question_order'] ?? ($index + 1)); ?></span>
                        </div>
                        <div class="question-text" style="font-size: 1rem; font-weight: 600; margin-bottom: 14px; color: #1f2937;"><?= htmlspecialchars($question['question_text'] ?? ''); ?></div>
                        <div class="question-options" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <?php foreach (['option_a', 'option_b', 'option_c', 'option_d'] as $optKey): ?>
                                <?php if (!empty($question[$optKey])): ?>
                                    <?php $letter = strtoupper(substr($optKey, -1)); ?>
                                    <div style="background: <?= intval($question['correct_answer']) === array_search($optKey, ['option_a','option_b','option_c','option_d']) ? '#f0fdf4' : '#f8fafc'; ?>; padding: 12px 14px; border-radius: 10px; border: 1px solid <?= intval($question['correct_answer']) === array_search($optKey, ['option_a','option_b','option_c','option_d']) ? '#4ade80' : '#cbd5e1'; ?>;">
                                        <strong><?= $letter; ?>.</strong> <?= htmlspecialchars($question[$optKey]); ?>
                                        <?php if (intval($question['correct_answer']) === array_search($optKey, ['option_a','option_b','option_c','option_d'])): ?>
                                            <span style="color: #16a34a; font-weight: 600;">✔ Correct</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No questions available for this quiz.</p>
            <?php endif; ?>
        </div>

        <div class="action-bar" style="justify-content: flex-start; gap: 12px;">
            <a href="<?= URLROOT; ?>/quizmanager" class="btn btn-secondary">Back to Dashboard</a>
            <a href="<?= URLROOT; ?>/quizmanager/create" class="btn btn-primary">Create a New Quiz</a>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer_user.php'; ?>
