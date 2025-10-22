let currentQuestionIndex = 0;
let userAnswers = [];
let quizCompleted = false;

const quizContent = document.getElementById('quizContent');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const submitBtn = document.getElementById('submitBtn');
const progressFill = document.getElementById('progressFill');
const currentQuestionEl = document.getElementById('currentQuestion');
const resultsSection = document.getElementById('resultsSection');

function renderQuestion() {
    const question = quizData.questions[currentQuestionIndex];
    const isAnswered = userAnswers[currentQuestionIndex] !== undefined;
    
    quizContent.innerHTML = `
        <div class="question-card">
            <h2 class="question-text">${question.question}</h2>
            <div class="options-list">
                ${question.options.map((option, index) => `
                    <div class="option-item ${userAnswers[currentQuestionIndex] === index ? 'selected' : ''}" 
                         onclick="selectOption(${index})">
                        <span class="option-letter">${String.fromCharCode(65 + index)}</span>
                        <span class="option-text">${option}</span>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    
    updateProgress();
    updateNavigation();
}

function selectOption(optionIndex) {
    if (quizCompleted) return;
    
    userAnswers[currentQuestionIndex] = optionIndex;
    renderQuestion();
}

function updateProgress() {
    const progress = ((currentQuestionIndex + 1) / quizData.questions.length) * 100;
    progressFill.style.width = `${progress}%`;
    currentQuestionEl.textContent = currentQuestionIndex + 1;
}

function updateNavigation() {
    prevBtn.disabled = currentQuestionIndex === 0;
    
    if (currentQuestionIndex === quizData.questions.length - 1) {
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'block';
    } else {
        nextBtn.style.display = 'block';
        submitBtn.style.display = 'none';
    }
}

function nextQuestion() {
    if (currentQuestionIndex < quizData.questions.length - 1) {
        currentQuestionIndex++;
        renderQuestion();
    }
}

function previousQuestion() {
    if (currentQuestionIndex > 0) {
        currentQuestionIndex--;
        renderQuestion();
    }
}

function submitQuiz() {
    if (userAnswers.length < quizData.questions.length) {
        alert('Please answer all questions before submitting');
        return;
    }
    
    quizCompleted = true;
    calculateResults();
}

function calculateResults() {
    let correctAnswers = 0;
    
    quizData.questions.forEach((question, index) => {
        if (userAnswers[index] === question.correct) {
            correctAnswers++;
        }
    });
    
    const percentage = Math.round((correctAnswers / quizData.questions.length) * 100);
    const scoreText = `You got ${correctAnswers} out of ${quizData.questions.length} questions correct!`;
    
    document.getElementById('scorePercentage').textContent = percentage;
    document.getElementById('scoreText').textContent = scoreText;
    
    document.querySelector('.quiz-content').style.display = 'none';
    document.querySelector('.quiz-navigation').style.display = 'none';
    document.querySelector('.progress-section').style.display = 'none';
    resultsSection.style.display = 'block';
}

// Event Listeners
prevBtn.addEventListener('click', previousQuestion);
nextBtn.addEventListener('click', nextQuestion);
submitBtn.addEventListener('click', submitQuiz);

// Initialize
renderQuestion();