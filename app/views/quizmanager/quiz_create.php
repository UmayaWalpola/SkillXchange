<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/qmansidebar.php'; ?>

<!-- Link CSS -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/quizcreate.css">

<div class="dashboard-container">
    <div class="builder-container">
        <!-- Header -->
        <div class="builder-header">
            <a href="<?php echo URLROOT; ?>/quizmanager" class="btn-back">← Back to Dashboard</a>
            <h1 class="builder-title"><?= htmlspecialchars($data['title'] ?? 'Create New Quiz') ?></h1>
            <p class="builder-subtitle">Build your quiz step by step (Max 20 questions)</p>
        </div>

        <!-- Quiz Basic Information -->
        <div class="section-card">
            <h2 class="section-title">Quiz Information</h2>
            
            <div class="form-group">
                <label for="quizTitle">Quiz Title *</label>
                <input 
                    type="text" 
                    id="quizTitle" 
                    placeholder="Enter quiz title"
                    maxlength="200"
                    value="<?= htmlspecialchars($data['quiz']['title'] ?? '') ?>"
                >
                <span class="error-text" id="titleError"></span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="quizBadge">Difficulty Level *</label>
                    <select id="quizBadge">
                        <option value="">Select difficulty</option>
                        <?php $selectedDifficulty = $data['quiz']['difficulty_level'] ?? ''; ?>
                        <option value="Beginner" <?= $selectedDifficulty === 'Beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="Intermediate" <?= $selectedDifficulty === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="Expert" <?= $selectedDifficulty === 'Expert' ? 'selected' : '' ?>>Expert</option>
                    </select>
                    <span class="error-text" id="badgeError"></span>
                </div>

                <div class="form-group">
                    <label for="quizDuration">Duration (minutes) *</label>
                    <input 
                        type="number" 
                        id="quizDuration" 
                        placeholder="30"
                        min="1"
                        max="180"
                        value="<?= htmlspecialchars($data['quiz']['duration'] ?? 30) ?>"
                    >
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="quizSkill">Skill *</label>
                    <select id="quizSkill">
                        <option value="">Select skill</option>
                        <?php foreach (($data['available_skills'] ?? []) as $skill): ?>
                            <?php $skillName = $skill->skill_name ?? ''; ?>
                            <option value="<?= htmlspecialchars($skillName) ?>" <?= (($data['quiz']['category'] ?? '') === $skillName) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($skill->skill_name ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="error-text" id="skillError"></span>
                </div>

                <div class="form-group">
                    <label for="badgeToAward">Badge to Award (Optional)</label>
                    <select id="badgeToAward">
                        <option value="">No badge</option>
                        <?php if (!empty($data['available_badges'])): ?>
                            <?php foreach ($data['available_badges'] as $b): ?>
                                <option value="<?= htmlspecialchars($b->id); ?>" <?= ((string)($data['quiz']['badge_id'] ?? '') === (string)$b->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b->icon); ?> <?= htmlspecialchars($b->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="buckxReward">BuckX Reward</label>
                <input
                    type="number"
                    id="buckxReward"
                    min="0"
                    step="1"
                    placeholder="0"
                    value="<?= htmlspecialchars($data['quiz']['reward_amount'] ?? 0) ?>"
                >
            </div>

            <div class="form-group">
                <label for="quizDescription">Description</label>
                <textarea 
                    id="quizDescription" 
                    placeholder="Brief description of the quiz (optional)"
                    rows="3"
                    maxlength="500"
                ><?= htmlspecialchars($data['quiz']['description'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Questions Section -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">Questions (<span id="questionCount">0</span>/20)</h2>
                <button class="btn btn-primary" onclick="addQuestion()" id="addQuestionBtn">
                    + Add Question
                </button>
            </div>

            <div id="questionsContainer">
                <!-- Questions will be added here dynamically -->
            </div>

            <div id="noQuestionsMessage" class="no-questions">
                <p>No questions added yet. Click "Add Question" to start building your quiz.</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-bar">
            <button class="btn btn-secondary" onclick="saveDraft()">
                Save as Draft
            </button>
            <button class="btn btn-secondary" onclick="previewQuiz()">
                Preview
            </button>
            <button class="btn btn-primary" onclick="publishQuiz()">
                <?= (($data['mode'] ?? 'create') === 'edit') ? 'Update Quiz' : 'Publish Quiz' ?>
            </button>
        </div>
    </div>
</div>

<!-- Question Modal -->
<div id="questionModal" class="modal">
    <div class="modal-content modal-large">
        <span class="close" onclick="closeQuestionModal()">&times;</span>
        <h2 id="modalTitle">Add Question</h2>
        
        <form id="questionForm" onsubmit="saveQuestion(event)">
            <input type="hidden" id="editingQuestionIndex" value="-1">
            
            <div class="form-group">
                <label for="questionText">Question *</label>
                <textarea 
                    id="questionText" 
                    placeholder="Enter your question here"
                    rows="3"
                    required
                    maxlength="500"
                ></textarea>
            </div>

            <div class="form-group">
                <label>Answer Options * (Select the correct answer)</label>
                
                <div class="option-group">
                    <input type="radio" name="correctAnswer" value="0" id="correct0" required>
                    <input 
                        type="text" 
                        id="option0" 
                        placeholder="Option A"
                        required
                        maxlength="200"
                    >
                </div>

                <div class="option-group">
                    <input type="radio" name="correctAnswer" value="1" id="correct1" required>
                    <input 
                        type="text" 
                        id="option1" 
                        placeholder="Option B"
                        required
                        maxlength="200"
                    >
                </div>

                <div class="option-group">
                    <input type="radio" name="correctAnswer" value="2" id="correct2" required>
                    <input 
                        type="text" 
                        id="option2" 
                        placeholder="Option C"
                        required
                        maxlength="200"
                    >
                </div>

                <div class="option-group">
                    <input type="radio" name="correctAnswer" value="3" id="correct3" required>
                    <input 
                        type="text" 
                        id="option3" 
                        placeholder="Option D"
                        required
                        maxlength="200"
                    >
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeQuestionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Question</button>
            </div>
        </form>
    </div>
</div>

<!-- Link JavaScript -->
<script>
    // Pass URLROOT to JavaScript
    const URLROOT = '<?php echo URLROOT; ?>';
    window.quizManagerMode = '<?= htmlspecialchars($data['mode'] ?? 'create') ?>';
    window.quizSaveUrl = '<?= URLROOT ?>/quizmanager/<?= (($data['mode'] ?? 'create') === 'edit') ? 'update/' . (int)($data['quiz']['id'] ?? 0) : 'save' ?>';
    window.initialQuizData = <?= json_encode([
        'title' => $data['quiz']['title'] ?? '',
        'badge' => $data['quiz']['difficulty_level'] ?? '',
        'duration' => isset($data['quiz']['duration']) ? (int)$data['quiz']['duration'] : 30,
        'description' => $data['quiz']['description'] ?? '',
        'category' => $data['quiz']['category'] ?? '',
        'badgeId' => isset($data['quiz']['badge_id']) ? (string)$data['quiz']['badge_id'] : '',
        'rewardAmount' => isset($data['quiz']['reward_amount']) ? (int)$data['quiz']['reward_amount'] : 0,
        'questions' => array_map(function($q) {
            $question = (array)$q;
            $options = [];
            foreach (['a', 'b', 'c', 'd', 'e'] as $letter) {
                $key = 'option_' . $letter;
                if (isset($question[$key]) && $question[$key] !== '') {
                    $options[] = $question[$key];
                }
            }
            return [
                'question' => $question['question_text'] ?? '',
                'options' => $options,
                'correct' => isset($question['correct_answer']) ? (int)$question['correct_answer'] : 0
            ];
        }, $data['questions'] ?? [])
    ]) ?>;
</script>
<script src="<?php echo URLROOT; ?>/assets/js/quizcreate.js"></script>

</body>
</html>
