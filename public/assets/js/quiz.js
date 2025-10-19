// Get data from HTML data attributes
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
function toggleSave(quizId) {
    const quiz = quizzes.find(q => q.id === quizId);
    if (quiz) {
        quiz.status = quiz.status === 'saved' ? 'not_started' : 'saved';
        renderQuizzes();
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

function createQuizCard(quiz) {
    const difficultyClass = `difficulty-${quiz.difficulty.toLowerCase()}`;
    const statusClass = `status-${quiz.status.replace('_', '-')}`;
    
    return `
        <div class="quiz-card">
            ${quiz.isPremium ? '<div class="premium-badge">Premium</div>' : ''}
            <div class="quiz-card-header">
                <div class="quiz-info">
                    <h3>${quiz.title}</h3>
                    <span class="badge ${difficultyClass}">${quiz.difficulty}</span>
                </div>
            </div>
            <p class="quiz-description">${quiz.description}</p>
            <div class="quiz-actions">
                <div class="status-badge ${statusClass}">
                    ${getStatusText(quiz.status)}
                </div>
                <div class="quiz-actions-buttons">
                    <button class="btn btn-secondary" onclick="toggleSave(${quiz.id})">
                        ${quiz.status === 'saved' ? '✓ Saved' : 'Save'}
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

// Initialize
renderQuizzes();