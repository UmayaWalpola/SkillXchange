// FIXED take_quiz.js - Correct quiz submission
(function() {
    'use strict';
    
    if (window.quizInitialized) {
        console.warn('Quiz already initialized');
        return;
    }
    window.quizInitialized = true;

    const quizDataElement = document.getElementById('quiz-data');
    if (!quizDataElement || !quizDataElement.dataset.quiz) {
        console.error('Quiz data not found!');
        alert('Error: Quiz data not loaded');
        return;
    }

    let quizData, urlRoot;
    try {
        quizData = JSON.parse(quizDataElement.dataset.quiz);
        urlRoot = quizDataElement.dataset.urlroot;
        console.log('✅ Quiz loaded:', quizData);
    } catch (error) {
        console.error('Parse error:', error);
        alert('Error loading quiz data');
        return;
    }

    let currentQuestionIndex = 0;
    let userAnswers = [];
    let quizCompleted = false;
    let startTime = Date.now();
    let timerInterval = null;

    const quizContent = document.getElementById('quizContent');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressFill = document.getElementById('progressFill');
    const currentQuestionEl = document.getElementById('currentQuestion');
    const totalQuestionsEl = document.getElementById('totalQuestions');
    const resultsSection = document.getElementById('resultsSection');
    const timerDisplay = document.getElementById('timerDisplay');

    if (totalQuestionsEl) {
        totalQuestionsEl.textContent = quizData.questions.length;
    }

    if (quizData.timeLimit) {
        startTimer(quizData.timeLimit * 60);
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
        // Check all questions answered
        const unanswered = [];
        for (let i = 0; i < quizData.questions.length; i++) {
            if (userAnswers[i] === undefined) {
                unanswered.push(i + 1);
            }
        }
        
        if (unanswered.length > 0) {
            alert(`Please answer all questions.\n\nUnanswered: ${unanswered.join(', ')}`);
            return;
        }
        
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
        }
        
        if (timerInterval) {
            clearInterval(timerInterval);
        }
        
        const timeTaken = Math.floor((Date.now() - startTime) / 1000);
        
        // FIXED: Format answers correctly with question IDs
        const formattedAnswers = quizData.questions.map((q, index) => ({
            question_id: q.id,
            quiz_id: quizData.id,
            selected_answer: userAnswers[index]
        }));
        
        // Submit using form-urlencoded (legacy/earlier submission style)
        const payload = new URLSearchParams();
        payload.set('attempt_id', quizData.attempt_id);
        payload.set('quiz_id', quizData.id);
        payload.set('answers', JSON.stringify(formattedAnswers));
        payload.set('time_taken', String(timeTaken));

        console.log('📤 Submitting (form):', Object.fromEntries(payload.entries()));
        
        try {
            const response = await fetch(urlRoot + '/userdashboard/submitQuiz', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: payload.toString()
            });

            // Some server-side errors return HTML; read text first and parse safely
            const raw = await response.text();
            let result;
            try {
                result = JSON.parse(raw);
            } catch (e) {
                console.error('Non-JSON response from submitQuiz:', raw);
                throw new Error('Server returned non-JSON response');
            }
            console.log('📥 Response:', result);
            
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
            console.error('❌ Submit error:', error);
            alert(error.message || 'Error submitting quiz. Please try again.');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Quiz';
            }
        }
    }

    function autoSubmitQuiz() {
        alert('Time is up! Submitting automatically...');
        submitQuiz();
    }

    function displayResults(result) {
        const quizContentEl = document.querySelector('.quiz-content');
        const quizNav = document.querySelector('.quiz-navigation');
        const progressSection = document.querySelector('.progress-section');
        
        if (quizContentEl) quizContentEl.style.display = 'none';
        if (quizNav) quizNav.style.display = 'none';
        if (progressSection) progressSection.style.display = 'none';
        
        const percentage = result.score;
        const passed = result.passed || percentage >= 70;
        const scoreClass = passed ? 'pass' : 'fail';
        
        let badgeHTML = '';
        if (result.badgeEarned && result.badgeEarned.name) {
            badgeHTML = `
                <div class="badge-earned">
                    <div class="badge-icon-large">${result.badgeEarned.icon || '🏆'}</div>
                    <h3>🎉 Badge Earned!</h3>
                    <p class="badge-name">${result.badgeEarned.name}</p>
                    <p class="badge-description">${result.badgeEarned.description || 'Congratulations!'}</p>
                </div>
            `;
        }
        
        if (resultsSection) {
            let rewardHTML = '';
            if (result.reward && result.reward > 0) {
                const msg = result.rewardMessage || `You received ${result.reward} Buckx`;
                rewardHTML = `<div class="reward-banner" style="background: #ecf8f8; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; font-weight:600; color:#0b7285;">${msg}</div>`;
            }

            resultsSection.innerHTML = `
                <div class="results-content">
                    ${rewardHTML}
                    <div class="score-circle ${scoreClass}">
                        <div class="score-percentage">${percentage.toFixed(1)}%</div>
                        <div class="score-label">${passed ? 'Passed!' : 'Failed'}</div>
                    </div>
                    
                    <div class="score-details">
                        <h2>${passed ? '🎉 Congratulations!' : '😔 Keep Trying!'}</h2>
                        <p class="score-text">
                            You got <strong>${result.correct}</strong> out of 
                            <strong>${result.total}</strong> questions correct!
                        </p>
                        <p class="passing-score">Passing score: 70%</p>
                    </div>
                    
                    ${badgeHTML}
                    
                    <div class="results-actions">
                        <a href="${urlRoot}/userdashboard/quiz" class="btn btn-secondary">Back to Quizzes</a>
                        <button class="btn-retake btn btn-primary" onclick="location.reload()">Retake Quiz</button>
                    </div>
                </div>
            `;

            resultsSection.style.display = 'block';
        }
        
        window.scrollTo(0, 0);
    }

    if (prevBtn) prevBtn.addEventListener('click', previousQuestion);
    if (nextBtn) nextBtn.addEventListener('click', nextQuestion);
    if (submitBtn) submitBtn.addEventListener('click', submitQuiz);

    window.addEventListener('beforeunload', (e) => {
        if (!quizCompleted) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    console.log('🚀 Quiz initialized');
    renderQuestion();

})();