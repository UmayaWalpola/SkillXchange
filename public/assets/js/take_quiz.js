// Wrap everything in an IIFE to avoid global scope pollution
(function() {
    'use strict';
    
    // Check if already initialized
    if (window.quizInitialized) {
        console.warn('Quiz already initialized, skipping...');
        return;
    }
    window.quizInitialized = true;

    // Get quiz data from HTML data attributes
    const quizDataElement = document.getElementById('quiz-data');

    if (!quizDataElement) {
        console.error('Quiz data element not found!');
        alert('Error: Quiz data not loaded. Please refresh the page.');
        return;
    }

    if (!quizDataElement.dataset.quiz) {
        console.error('Quiz data attribute is missing!');
        alert('Error: Quiz data not found. Please go back and try again.');
        return;
    }

    let quizData, urlRoot;

    try {
        quizData = JSON.parse(quizDataElement.dataset.quiz);
        urlRoot = quizDataElement.dataset.urlroot;
        console.log('✅ Quiz data loaded successfully:', quizData);
        console.log('📝 Total questions:', quizData.questions.length);
    } catch (error) {
        console.error('Error parsing quiz data:', error);
        alert('Error loading quiz. Please go back and try again.');
        return;
    }

    let currentQuestionIndex = 0;
    let userAnswers = [];
    let quizCompleted = false;
    let startTime = Date.now();
    let timerInterval = null;

    // DOM Elements
    const quizContent = document.getElementById('quizContent');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressFill = document.getElementById('progressFill');
    const currentQuestionEl = document.getElementById('currentQuestion');
    const totalQuestionsEl = document.getElementById('totalQuestions');
    const resultsSection = document.getElementById('resultsSection');
    const timerDisplay = document.getElementById('timerDisplay');

    // Initialize
    if (totalQuestionsEl) {
        totalQuestionsEl.textContent = quizData.questions.length;
    }

    // Start timer if time limit exists
    if (quizData.timeLimit) {
        startTimer(quizData.timeLimit * 60); // Convert minutes to seconds
    }

    function startTimer(seconds) {
        let remaining = seconds;
        
        updateTimerDisplay(remaining);
        
        timerInterval = setInterval(() => {
            remaining--;
            updateTimerDisplay(remaining);
            
            if (remaining <= 0) {
                clearInterval(timerInterval);
                autoSubmitQuiz();
            }
        }, 1000);
    }

    function updateTimerDisplay(seconds) {
        if (!timerDisplay) return;
        
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        timerDisplay.textContent = `${minutes}:${secs.toString().padStart(2, '0')}`;
        
        // Change color when time is running out
        if (seconds <= 60) {
            timerDisplay.style.color = '#ef4444';
        } else if (seconds <= 300) {
            timerDisplay.style.color = '#f59e0b';
        }
    }

    function renderQuestion() {
        const question = quizData.questions[currentQuestionIndex];
        
        quizContent.innerHTML = `
            <div class="question-card">
                <div class="question-header">
                    <span class="question-number">Question ${currentQuestionIndex + 1} of ${quizData.questions.length}</span>
                </div>
                <h2 class="question-text">${question.question}</h2>
                <div class="options-list">
                    ${question.options.map((option, index) => `
                        <div class="option-item ${userAnswers[currentQuestionIndex] === index ? 'selected' : ''}" 
                             data-option-index="${index}">
                            <div class="option-radio ${userAnswers[currentQuestionIndex] === index ? 'checked' : ''}">
                                ${userAnswers[currentQuestionIndex] === index ? '●' : ''}
                            </div>
                            <span class="option-letter">${String.fromCharCode(65 + index)}</span>
                            <span class="option-text">${option}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        // Add click handlers to options
        document.querySelectorAll('.option-item').forEach(item => {
            item.addEventListener('click', function() {
                const optionIndex = parseInt(this.dataset.optionIndex);
                selectOption(optionIndex);
            });
        });
        
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
        if (progressFill) {
            progressFill.style.width = `${progress}%`;
        }
        if (currentQuestionEl) {
            currentQuestionEl.textContent = currentQuestionIndex + 1;
        }
        
        // Update answered questions indicator
        const answeredCount = userAnswers.filter(a => a !== undefined).length;
        const progressText = document.getElementById('progressText');
        if (progressText) {
            progressText.textContent = `${answeredCount} of ${quizData.questions.length} answered`;
        }
    }

    function updateNavigation() {
        if (prevBtn) {
            prevBtn.disabled = currentQuestionIndex === 0;
        }
        
        if (currentQuestionIndex === quizData.questions.length - 1) {
            if (nextBtn) nextBtn.style.display = 'none';
            if (submitBtn) submitBtn.style.display = 'block';
        } else {
            if (nextBtn) nextBtn.style.display = 'block';
            if (submitBtn) submitBtn.style.display = 'none';
        }
    }

    function nextQuestion() {
        if (currentQuestionIndex < quizData.questions.length - 1) {
            currentQuestionIndex++;
            renderQuestion();
            window.scrollTo(0, 0);
        }
    }

    function previousQuestion() {
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            renderQuestion();
            window.scrollTo(0, 0);
        }
    }

    async function submitQuiz() {
        // Check if all questions are answered
        const unansweredQuestions = [];
        for (let i = 0; i < quizData.questions.length; i++) {
            if (userAnswers[i] === undefined) {
                unansweredQuestions.push(i + 1);
            }
        }
        
        if (unansweredQuestions.length > 0) {
            const message = `Please answer all questions before submitting.\n\nUnanswered questions: ${unansweredQuestions.join(', ')}`;
            alert(message);
            return;
        }
        
        // Show loading state
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
        }
        
        // Stop timer
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        
        const timeTaken = Math.floor((Date.now() - startTime) / 1000);
        
        try {
            const response = await fetch(urlRoot + '/userdashboard/submitQuiz', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `quiz_id=${quizData.id}&answers=${JSON.stringify(userAnswers)}&time_taken=${timeTaken}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                quizCompleted = true;
                displayResults(result);
            } else {
                alert(result.message || 'Failed to submit quiz');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Quiz';
                }
            }
        } catch (error) {
            console.error('Error submitting quiz:', error);
            alert('An error occurred while submitting the quiz');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Quiz';
            }
        }
    }

    function autoSubmitQuiz() {
        alert('Time is up! Your quiz will be submitted automatically.');
        submitQuiz();
    }

    function displayResults(result) {
        // Hide quiz content
        const quizContentEl = document.querySelector('.quiz-content');
        const quizNav = document.querySelector('.quiz-navigation');
        const progressSection = document.querySelector('.progress-section');
        
        if (quizContentEl) quizContentEl.style.display = 'none';
        if (quizNav) quizNav.style.display = 'none';
        if (progressSection) progressSection.style.display = 'none';
        
        const percentage = result.score;
        const passed = result.passed;
        const scoreClass = passed ? 'pass' : 'fail';
        
        let badgeHTML = '';
        if (result.badgeEarned) {
            badgeHTML = `
                <div class="badge-earned">
                    <div class="badge-icon-large">${result.badgeEarned.icon}</div>
                    <h3>🎉 Badge Earned!</h3>
                    <p class="badge-name">${result.badgeEarned.name}</p>
                    <p class="badge-description">${result.badgeEarned.description}</p>
                </div>
            `;
        }
        
        if (resultsSection) {
            resultsSection.innerHTML = `
                <div class="results-content">
                    <div class="score-circle ${scoreClass}">
                        <div class="score-percentage">${percentage}%</div>
                        <div class="score-label">${passed ? 'Passed!' : 'Failed'}</div>
                    </div>
                    
                    <div class="score-details">
                        <h2>${passed ? '🎉 Congratulations!' : '😔 Keep Trying!'}</h2>
                        <p class="score-text">
                            You got <strong>${result.correctAnswers}</strong> out of 
                            <strong>${result.totalQuestions}</strong> questions correct!
                        </p>
                        <p class="passing-score">Passing score: ${quizData.passingScore}%</p>
                    </div>
                    
                    ${badgeHTML}
                    
                    <div class="results-actions">
                        <a href="${urlRoot}/userdashboard/quiz" class="btn btn-secondary">Back to Quizzes</a>
                        <button class="btn-retake btn btn-primary">Retake Quiz</button>
                    </div>
                </div>
            `;
            
            // Add retake handler
            const retakeBtn = resultsSection.querySelector('.btn-retake');
            if (retakeBtn) {
                retakeBtn.addEventListener('click', () => location.reload());
            }
            
            resultsSection.style.display = 'block';
        }
        
        window.scrollTo(0, 0);
    }

    // Event Listeners
    if (prevBtn) {
        prevBtn.addEventListener('click', previousQuestion);
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', nextQuestion);
    }
    if (submitBtn) {
        submitBtn.addEventListener('click', submitQuiz);
    }

    // Warn before leaving page
    window.addEventListener('beforeunload', (e) => {
        if (!quizCompleted) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });

    // Initialize first question
    console.log('🚀 Initializing quiz...');
    renderQuestion();

})();