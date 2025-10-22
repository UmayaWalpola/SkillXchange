// Quiz data
let quizzes = [
    {
        id: 1,
        title: 'Programming Fundamentals',
        description: 'Explore core programming concepts using Python, JavaScript, or Java—covering variables, loops, functions, OOP, and error handling.',
        category: 'Programming',
        difficulty: 'Beginner',
        status: 'not_started'
    },
    {
        id: 2,
        title: 'Frontend Development',
        description: 'Dive into HTML, CSS, and JavaScript fundamentals, plus component-based frameworks like React or Vue and state management tools.',
        category: 'Frontend',
        difficulty: 'Advanced',
        status: 'completed'
    },
    {
        id: 3,
        title: 'System Design & Architecture',
        description: 'Understand microservices, caching, scalability, and architectural trade-offs like the CAP theorem.',
        category: 'System',
        difficulty: 'Intermediate',
        status: 'saved'
    },
    {
        id: 4,
        title: 'UI/UX Design',
        description: 'Master wireframing, user flows, accessibility, responsive layouts, and palette-driven design systems.',
        category: 'UI/UX',
        difficulty: 'Intermediate',
        status: 'not_started'
    },
    {
        id: 5,
        title: 'Database Design & Management',
        description: 'Learn SQL and NoSQL fundamentals, normalization, indexing, ER modeling, and query optimization.',
        category: 'Database',
        difficulty: 'Advanced',
        status: 'saved',
        isPremium: true
    },
    {
        id: 6,
        title: 'Cybersecurity Basics',
        description: 'Cover authentication, secure coding, encryption, and role-based access control with threat modeling.',
        category: 'Cyber',
        difficulty: 'Beginner',
        status: 'completed'
    },
    {
        id: 7,
        title: 'DevOps & Deployment',
        description: 'Build CI/CD pipelines, work with Docker, and deploy to cloud platforms like Vercel, Netlify, or AWS.',
        category: 'Devops',
        difficulty: 'Advanced',
        status: 'not_started'
    },
    {
        id: 8,
        title: 'Version Control & Collaboration',
        description: 'Master Git workflows, branching strategies, pull requests, and collaborative code reviews.',
        category: 'Version Control',
        difficulty: 'Intermediate',
        status: 'not_started'
    }
];

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
    const quiz = quizzes.find(q => q.id === quizId);
    if (quiz) {
        quiz.status = 'completed';
        renderQuizzes();
        alert(`Starting quiz: ${quiz.title}`);
    }
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