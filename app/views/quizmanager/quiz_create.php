<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/qmansidebar.php'; ?>

<!-- Link CSS -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/quizcreate.css">

<div class="dashboard-container">
    <div class="builder-container">
        <!-- Header -->
        <div class="builder-header">
            <a href="<?php echo URLROOT; ?>/quizmanager" class="btn-back">← Back to Dashboard</a>
            <h1 class="builder-title">Create New Quiz</h1>
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
                >
                <span class="error-text" id="titleError"></span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="quizBadge">Difficulty Level *</label>
                    <select id="quizBadge">
                        <option value="">Select difficulty</option>
                        <option value="Beginer">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Expert">Expert</option>
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
                        value="30"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="quizDescription">Description</label>
                <textarea 
                    id="quizDescription" 
                    placeholder="Brief description of the quiz (optional)"
                    rows="3"
                    maxlength="500"
                ></textarea>
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
                Publish Quiz
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
</script>
<script src="<?php echo URLROOT; ?>/assets/js/quizcreate.js"></script>

</body>
</html>