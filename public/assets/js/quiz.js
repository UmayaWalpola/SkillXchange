const quizDataElement = document.getElementById('quiz-data');
const quizzes = JSON.parse(quizDataElement.dataset.quizzes);
const urlRoot = quizDataElement.dataset.urlroot;

// Current filter state
let currentFilter = { 
    search: '', 
    category: 'All', 
    status: 'all' 
};

// DOM Elements
const searchInput = document.getElementById('searchInput');
const categoryFilters = document.getElementById('categoryFilters');
const quizGrid = document.getElementById('quizGrid');
const noResults = document.getElementById('noResults');

// Event Listeners
searchInput.addEventListener('input', (e) => {
    currentFilter.search = e.target.value;
    renderQuizzes();
});

categoryFilters.addEventListener('click', (e) => {
    if (e.target.classList.contains('filter-btn')) {
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        e.target.classList.add('active');
        currentFilter.category = e.target.dataset.category;
        renderQuizzes();
    }
});

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        e.target.classList.add('active');
        currentFilter.status = e.target.dataset.status;
        renderQuizzes();
    });
});

// Functions
async function toggleSave(quizId) {
    const quiz = quizzes.find(q => q.id === quizId);
    if (!quiz) return;
    
    const action = quiz.status === 'saved' ? 'unsave' : 'save';
    
    try {
        const response = await fetch(urlRoot + '/userdashboard/toggleSaveQuiz', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `quiz_id=${quizId}&action=${action}`
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update local state
            quiz.status = action === 'save' ? 'saved' : 'not_started';
            renderQuizzes();
            
            // Show success message (optional)
            showNotification(result.message, 'success');
        } else {
            showNotification(result.message || 'Failed to update quiz', 'error');
        }
    } catch (error) {
        console.error('Error toggling save:', error);
        showNotification('An error occurred', 'error');
    }
}

function startQuiz(quizId) {
    window.location.href = urlRoot + '/userdashboard/takeQuiz/' + quizId;
}

function getStatusText(status) {
    switch (status) {
        case 'completed': return 'Completed';
        case 'saved': return 'Saved for Later';
        default: return 'Not Started';
    }
}

function getStatusIcon(status) {
    switch (status) {
        case 'completed': return '✓';
        case 'saved': return '🔖';
        default: return '○';
    }
}

function createQuizCard(quiz) {
    const difficultyClass = `difficulty-${quiz.difficulty.toLowerCase()}`;
    const statusClass = `status-${quiz.status.replace('_', '-')}`;
    const rewardAmount = Number.isFinite(Number(quiz.rewardAmount)) ? Number(quiz.rewardAmount) : 0;
    
    return `
        <div class="quiz-card">
            ${quiz.isPremium ? '<div class="premium-badge">👑 Premium</div>' : ''}
            <div class="quiz-card-header">
                <div class="quiz-info">
                    <h3>${quiz.title}</h3>
                    <div class="quiz-meta">
                        <span class="badge ${difficultyClass}">${quiz.difficulty}</span>
                        <span class="quiz-questions">${quiz.questionCount} Questions</span>
                        ${quiz.timeLimit ? `<span class="quiz-time">⏱️ ${quiz.timeLimit} min</span>` : ''}
                        <span class="quiz-reward">Reward: ${rewardAmount} Buckx</span>
                    </div>
                </div>
            </div>
            <p class="quiz-description">${quiz.description}</p>
            ${quiz.badge ? `
                <div class="quiz-badge-preview">
                    <span class="badge-icon">${quiz.badge.icon}</span>
                    <span class="badge-text">Earn: ${quiz.badge.name}</span>
                </div>
            ` : ''}
            ${quiz.lastScore !== null ? `
                <div class="quiz-last-score">
                    Last Score: <strong>${quiz.lastScore}%</strong>
                </div>
            ` : ''}
            <div class="quiz-actions">
                <div class="status-badge ${statusClass}">
                    ${getStatusIcon(quiz.status)} ${getStatusText(quiz.status)}
                </div>
                <div class="quiz-actions-buttons">
                    <button class="btn btn-secondary" onclick="toggleSave(${quiz.id})">
                        ${quiz.status === 'saved' ? '✓ Saved' : '🔖 Save'}
                    </button>
                    ${quiz.status !== 'completed' ? 
                        `<button class="btn btn-primary" onclick="startQuiz(${quiz.id})">Start Quiz</button>` :
                        `<button class="btn btn-success" onclick="startQuiz(${quiz.id})">Retake</button>`}
                </div>
            </div>
        </div>
    `;
}

function filterQuizzes() {
    return quizzes.filter(quiz => {
        const matchesSearch = quiz.title.toLowerCase().includes(currentFilter.search.toLowerCase()) || 
                            quiz.description.toLowerCase().includes(currentFilter.search.toLowerCase());
        const matchesCategory = currentFilter.category === 'All' || quiz.category === currentFilter.category;
        const matchesStatus = currentFilter.status === 'all' || 
                            (currentFilter.status === 'completed' && quiz.status === 'completed') || 
                            (currentFilter.status === 'saved' && quiz.status === 'saved');
        return matchesSearch && matchesCategory && matchesStatus;
    });
}

function renderQuizzes() {
    const filteredQuizzes = filterQuizzes();
    if (filteredQuizzes.length === 0) {
        quizGrid.style.display = 'none';
        noResults.style.display = 'block';
    } else {
        quizGrid.style.display = 'grid';
        noResults.style.display = 'none';
        quizGrid.innerHTML = filteredQuizzes.map(quiz => createQuizCard(quiz)).join('');
    }
}

function showNotification(message, type = 'info') {
    // Simple notification system
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 9999;
        animation: slideIn 0.3s ease-out;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Initialize
renderQuizzes();

// Make functions globally accessible
window.toggleSave = toggleSave;
window.startQuiz = startQuiz;
