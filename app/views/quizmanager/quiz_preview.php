<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/qmansidebar.php'; ?>

<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/quizcreate.css">

<div class="dashboard-container">
    <div class="builder-container">
        <div class="builder-header">
            <a href="<?php echo URLROOT; ?>/quizmanager" class="btn-back">← Back to Dashboard</a>
            <h1 class="builder-title"><?= htmlspecialchars($data['quiz']['title'] ?? 'Quiz Preview') ?></h1>
            <p class="builder-subtitle">
                <?= htmlspecialchars($data['quiz']['category'] ?? 'General') ?> ·
                <?= htmlspecialchars($data['quiz']['difficulty_level'] ?? 'Intermediate') ?> ·
                <?= (int)($data['quiz']['duration'] ?? 0) ?> minutes
            </p>
        </div>

        <?php if (!empty($data['quiz']['description'])): ?>
            <div class="section-card">
                <h2 class="section-title">Description</h2>
                <p><?= nl2br(htmlspecialchars($data['quiz']['description'])) ?></p>
            </div>
        <?php endif; ?>

        <div class="section-card">
            <h2 class="section-title">Questions (<?= count($data['questions'] ?? []) ?>)</h2>

            <?php if (empty($data['questions'])): ?>
                <div class="no-questions">
                    <p>No questions have been added to this quiz.</p>
                </div>
            <?php else: ?>
                <?php foreach ($data['questions'] as $index => $question): ?>
                    <div class="question-item">
                        <div class="question-header">
                            <span class="question-number">Question <?= $index + 1 ?></span>
                        </div>
                        <div class="question-text"><?= htmlspecialchars($question['question_text'] ?? '') ?></div>
                        <div class="question-options">
                            <?php foreach (['a', 'b', 'c', 'd', 'e'] as $optionIndex => $letter): ?>
                                <?php $key = 'option_' . $letter; ?>
                                <?php if (!empty($question[$key])): ?>
                                    <div class="option" style="<?= ((int)($question['correct_answer'] ?? -1) === $optionIndex) ? 'background:#dcfce7;border-color:#22c55e;' : '' ?>">
                                        <span class="option-letter"><?= strtoupper($letter) ?>.</span>
                                        <span><?= htmlspecialchars($question[$key]) ?></span>
                                        <?php if ((int)($question['correct_answer'] ?? -1) === $optionIndex): ?>
                                            <span style="margin-left:auto;color:#166534;font-weight:700;">Correct</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="action-bar">
            <a class="btn btn-secondary" href="<?php echo URLROOT; ?>/quizmanager/edit/<?= (int)($data['quiz']['id'] ?? 0) ?>">Edit</a>
            <a class="btn btn-primary" href="<?php echo URLROOT; ?>/quizmanager">Done</a>
        </div>
    </div>
</div>

<?php require_once '../app/views/layouts/footer_user.php'; ?>
