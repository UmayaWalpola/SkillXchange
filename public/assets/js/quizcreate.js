// Quiz Create JavaScript - Pure Vanilla JS

// Quiz data structure
let quizData = {
    title: '',
    badge: '',
    duration: 30,
    description: '',
    questions: []
};

let editingQuestionIndex = -1;
const MAX_QUESTIONS = 20;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateQuestionCount();
});

// Add new question - Open modal
function addQuestion() {
    if (quizData.questions.length >= MAX_QUESTIONS) {
        showNotification('Maximum 20 questions allowed per quiz', 'error');
        return;
    }
    
    // Reset form
    document.getElementById('questionForm').reset();
    document.getElementById('editingQuestionIndex').value = '-1';
    document.getElementById('modalTitle').textContent = 'Add Question';
    editingQuestionIndex = -1;
    
    // Open modal
    document.getElementById('questionModal').style.display = 'block';
}

// Save question from modal
function saveQuestion(event) {
    event.preventDefault();
    
    const questionText = document.getElementById('questionText').value.trim();
    const options = [
        document.getElementById('option0').value.trim(),
        document.getElementById('option1').value.trim(),
        document.getElementById('option2').value.trim(),
        document.getElementById('option3').value.trim()
    ];
    
    const correctAnswerRadio = document.querySelector('input[name="correctAnswer"]:checked');
    const correctAnswer = correctAnswerRadio ? parseInt(correctAnswerRadio.value) : -1;
    
    // Validation
    if (!questionText) {
        showNotification('Question text is required', 'error');
        return;
    }
    
    if (options.some(opt => !opt)) {
        showNotification('All 4 options are required', 'error');
        return;
    }
    
    if (correctAnswer === -1) {
        showNotification('Please select the correct answer', 'error');
        return;
    }
    
    // Create question object
    const question = {
        question: questionText,
        options: options,
        correctAnswer: correctAnswer
    };
    
    // Check if editing or adding new
    const index = parseInt(document.getElementById('editingQuestionIndex').value);
    
    if (index >= 0) {
        // Update existing question
        quizData.questions[index] = question;
        showNotification('Question updated successfully', 'success');
    } else {
        // Add new question
        quizData.questions.push(question);
        showNotification('Question added successfully', 'success');
    }
    
    // Close modal and refresh display
    closeQuestionModal();
    renderQuestions();
    updateQuestionCount();
}

// Render all questions
function renderQuestions() {
    const container = document.getElementById('questionsContainer');
    const noQuestionsMsg = document.getElementById('noQuestionsMessage');
    
    if (quizData.questions.length === 0) {
        container.innerHTML = '';
        noQuestionsMsg.style.display = 'block';
        return;
    }
    
    noQuestionsMsg.style.display = 'none';
    container.innerHTML = '';
    
    quizData.questions.forEach((question, index) => {
        const questionCard = createQuestionCard(question, index);
        container.appendChild(questionCard);
    });
}

// Create question card element
function createQuestionCard(question, index) {
    const card = document.createElement('div');
    card.className = 'question-item';
    
    const optionLetters = ['A', 'B', 'C', 'D'];
const optionsHTML = question.options.map((option, i) => `
    <div class="option">
        <span class="option-letter">${optionLetters[i]}.</span>
        <span>${option}</span>
    </div>
`).join('');
    
    card.innerHTML = `
        <div class="question-header">
            <span class="question-number">Question ${index + 1}</span>
            <div class="question-actions">
                <button class="btn-icon" onclick="editQuestion(${index})" title="Edit">&#x270F;&#xFE0F;</button>
                <button class="btn-icon" onclick="deleteQuestion(${index})" title="Delete">&#x1F5D1;&#xFE0F;</button>
            </div>
        </div>
        <div class="question-text">${question.question}</div>
        <div class="question-options">
            ${optionsHTML}
        </div>
    `;
    
    return card;
}

// Edit question
function editQuestion(index) {
    const question = quizData.questions[index];
    
    // Populate form
    document.getElementById('questionText').value = question.question;
    document.getElementById('option0').value = question.options[0];
    document.getElementById('option1').value = question.options[1];
    document.getElementById('option2').value = question.options[2];
    document.getElementById('option3').value = question.options[3];
    document.getElementById(`correct${question.correctAnswer}`).checked = true;
    document.getElementById('editingQuestionIndex').value = index;
    document.getElementById('modalTitle').textContent = 'Edit Question';
    
    editingQuestionIndex = index;
    
    // Open modal
    document.getElementById('questionModal').style.display = 'block';
}

// Delete question
function deleteQuestion(index) {
    if (confirm('Are you sure you want to delete this question?')) {
        quizData.questions.splice(index, 1);
        renderQuestions();
        updateQuestionCount();
        showNotification('Question deleted successfully', 'success');
    }
}

// Update question count
function updateQuestionCount() {
    document.getElementById('questionCount').textContent = quizData.questions.length;
    
    // Disable/enable add button
    const addBtn = document.getElementById('addQuestionBtn');
    if (quizData.questions.length >= MAX_QUESTIONS) {
        addBtn.disabled = true;
        addBtn.style.opacity = '0.5';
        addBtn.style.cursor = 'not-allowed';
    } else {
        addBtn.disabled = false;
        addBtn.style.opacity = '1';
        addBtn.style.cursor = 'pointer';
    }
}

// Close question modal
function closeQuestionModal() {
    document.getElementById('questionModal').style.display = 'none';
    document.getElementById('questionForm').reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('questionModal');
    if (event.target === modal) {
        closeQuestionModal();
    }
}

// Get quiz basic info
function getQuizBasicInfo() {
    const title = document.getElementById('quizTitle').value.trim();
    const badge = document.getElementById('quizBadge').value;
    const duration = parseInt(document.getElementById('quizDuration').value) || 30;
    const description = document.getElementById('quizDescription').value.trim();
    
    return { title, badge, duration, description };
}

// Validate quiz data
function validateQuiz() {
    const { title, badge } = getQuizBasicInfo();
    
    // Clear previous errors
    document.getElementById('titleError').textContent = '';
    document.getElementById('badgeError').textContent = '';
    
    let isValid = true;
    
    if (!title) {
        document.getElementById('titleError').textContent = 'Quiz title is required';
        isValid = false;
    }
    
    if (!badge) {
        document.getElementById('badgeError').textContent = 'Difficulty level is required';
        isValid = false;
    }
    
    if (quizData.questions.length === 0) {
        showNotification('Please add at least one question', 'error');
        isValid = false;
    }
    
    return isValid;
}

// Save as Draft
function saveDraft() {
    if (!validateQuiz()) {
        return;
    }
    
    const basicInfo = getQuizBasicInfo();
    const fullQuizData = {
        ...basicInfo,
        questions: quizData.questions,
        status: 'draft'
    };
    
    // Save to backend (AJAX call)
    saveQuizToBackend(fullQuizData, 'Draft saved successfully! You can continue editing later.');
}

// Preview Quiz - NOW WORKS!
function previewQuiz() {
    if (!validateQuiz()) {
        return;
    }
    
    const basicInfo = getQuizBasicInfo();
    const fullQuizData = {
        ...basicInfo,
        questions: quizData.questions
    };
    
    // Create preview modal
    showPreviewModal(fullQuizData);
}

// Show Preview Modal - NEW FUNCTION
function showPreviewModal(quizData) {
    // Create preview modal HTML
    const previewHTML = `
        <div id="previewModal" class="modal" style="display: block;">
            <div class="modal-content modal-preview">
                <span class="close" onclick="closePreviewModal()">&times;</span>
                <h2 style="color: var(--primary-blue); margin-bottom: 10px;">${quizData.title}</h2>
                <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                    <span class="badge badge-${quizData.badge.toLowerCase()}">${quizData.badge}</span>
                    <span style="color: #666;">&#x23F1;&#xFE0F; ${quizData.duration} minutes</span>
                    <span style="color: #666;">&#x1F4DD; ${quizData.questions.length} questions</span>
                </div>
                ${quizData.description ? `<p style="color: #666; margin-bottom: 25px;">${quizData.description}</p>` : ''}
                
                <div style="max-height: 400px; overflow-y: auto; padding-right: 10px;">
                    ${quizData.questions.map((q, index) => `
                        <div style="background: var(--blue-bg); padding: 20px; border-radius: 10px; margin-bottom: 15px; border: 2px solid var(--accent-blue);">
                            <h3 style="color: var(--primary-blue); margin-bottom: 10px;">Question ${index + 1}</h3>
                            <p style="font-weight: 600; margin-bottom: 15px; color: var(--dark-bg);">${q.question}</p>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                ${q.options.map((opt, i) => `
                                    <div style="padding: 10px 15px; background: ${i === q.correctAnswer ? '#dcfce7' : 'white'}; 
                                         border-radius: 8px; border: 2px solid ${i === q.correctAnswer ? '#22c55e' : '#ddd'};">
                                        <strong>${['A', 'B', 'C', 'D'][i]}.</strong> ${opt}
                                        ${i === q.correctAnswer ? ' <span style="color: #22c55e;">✓ Correct Answer</span>' : ''}
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `).join('')}
                </div>
                
                <div style="margin-top: 25px; padding-top: 20px; border-top: 2px solid var(--blue-bg); display: flex; justify-content: flex-end;">
                    <button class="btn btn-primary" onclick="closePreviewModal()">Close Preview</button>
                </div>
            </div>
        </div>
    `;
    
    // Add to body
    const previewContainer = document.createElement('div');
    previewContainer.innerHTML = previewHTML;
    document.body.appendChild(previewContainer.firstElementChild);
}

// Close Preview Modal - NEW FUNCTION
function closePreviewModal() {
    const previewModal = document.getElementById('previewModal');
    if (previewModal) {
        previewModal.remove();
    }
}

// Publish Quiz
function publishQuiz() {
    if (!validateQuiz()) {
        return;
    }
    
    if (!confirm('Are you sure you want to publish this quiz? It will be available to users immediately.')) {
        return;
    }
    
    const basicInfo = getQuizBasicInfo();
    const fullQuizData = {
        ...basicInfo,
        questions: quizData.questions,
        status: 'active'
    };
    
    // Save to backend (AJAX call)
    saveQuizToBackend(fullQuizData, 'Quiz published successfully!');
}

// Save quiz to backend
function saveQuizToBackend(data, successMessage) {
    // Show loading
    showNotification('Saving quiz...', 'info');
    
    // AJAX call to backend
    fetch(`${URLROOT}/quizmanager/save`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification(successMessage || result.message, 'success');
            
            // Redirect after 2 seconds
            setTimeout(() => {
                window.location.href = `${URLROOT}/quizmanager`;
            }, 2000);
        } else {
            showNotification('Error: ' + (result.errors ? result.errors.join(', ') : 'Failed to save quiz'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Network error. Please try again.', 'error');
    });
}

// Show notification - FIXED TO CENTER
// Show notification - FIXED TO CENTER
function showNotification(message, type = 'success') {
    // Remove existing notification
    const existing = document.querySelector('.custom-notification');
    if (existing) {
        existing.remove();
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `custom-notification ${type}`;
    
    let icon;
    
    if (type === 'success') {
        icon = '✓';
    } else if (type === 'error') {
        icon = '✕';
    } else {
        icon = 'ℹ';
    }
    
    notification.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.7);
        padding: 25px 35px;
        border-radius: 12px;
        background: white;
        color: #12120D;
        border: 3px solid #658396;
        font-weight: 600;
        font-size: 16px;
        z-index: 100000;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        animation: popIn 0.3s ease forwards;
        max-width: 500px;
        text-align: center;
        min-width: 300px;
    `;
    
    notification.innerHTML = `
        <div style="font-size: 32px; margin-bottom: 10px; color: #658396;">${icon}</div>
        <div>${message}</div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'popOut 0.3s ease forwards';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add animation styles - UPDATED
if (!document.getElementById('notification-styles')) {
    const style = document.createElement('style');
    style.id = 'notification-styles';
    style.textContent = `
        @keyframes popIn {
            from { 
                transform: translate(-50%, -50%) scale(0.7);
                opacity: 0;
            }
            to { 
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }
        @keyframes popOut {
            from { 
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
            to { 
                transform: translate(-50%, -50%) scale(0.7);
                opacity: 0;
            }
        }
        
        .modal-preview {
            max-width: 800px !important;
            max-height: 90vh;
            overflow-y: auto;
        }
    `;
    document.head.appendChild(style);
}

