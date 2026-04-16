// Quiz Manager Dashboard JavaScript

let quizzes = [];

// Initialize dashboard
function initDashboard() {
  loadQuizzesFromBackend();
}

// Load quizzes from backend
async function loadQuizzesFromBackend() {
  try {
    // Get URLROOT from data attribute if available
    const urlRoot = window.URLROOT || document.querySelector('.dashboard-container')?.dataset?.urlroot || '/SkillXchange';
    
    const response = await fetch(urlRoot + '/quizmanager/getQuizzes', {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    });
    
    if (!response.ok) {
      throw new Error('Failed to fetch quizzes: ' + response.statusText);
    }
    
    const data = await response.json();
    
    if (data.success && Array.isArray(data.quizzes)) {
      quizzes = data.quizzes.map(quiz => ({
        id: quiz.id,
        title: quiz.title || 'Untitled Quiz',
        badge: quiz.difficulty_level || 'General',
        status: quiz.status || 'draft',
        participants: parseInt(quiz.participant_count) || 0,
        totalQuestions: parseInt(quiz.total_questions) || 0,
        duration: parseInt(quiz.duration) || 0,
        createdDate: quiz.created_at || new Date().toISOString().split('T')[0],
        averageScore: parseFloat(quiz.avg_score) || 0
      }));
    } else {\n      quizzes = [];\n    }\n    \n    renderQuizTable();\n  } catch (error) {\n    console.error('Error loading quizzes:', error);\n    // Show error message but still initialize with empty quizzes\n    const tbody = document.getElementById('quizTableBody');\n    if (tbody) {\n      tbody.innerHTML = '<tr><td colspan=\"7\" style=\"text-align: center; padding: 20px; color: #ef4444;\">Error loading quizzes. Please refresh the page.</td></tr>';\n    }\n  }\n}

// Render quiz table
function renderQuizTable(filteredQuizzes = null) {
  const quizzesToShow = filteredQuizzes || quizzes;
  const tbody = document.getElementById('quizTableBody');
  tbody.innerHTML = '';

  quizzesToShow.forEach(quiz => {
    const row = document.createElement('tr');
    
    // Fix badge name for global.css (Beginer -> beginner)
    const badgeClass = quiz.badge.toLowerCase() === 'beginer' ? 'beginner' : quiz.badge.toLowerCase();
    
    row.innerHTML = `
      <td>
        <div class="quiz-title">${quiz.title}</div>
        <span class="badge badge-${badgeClass} quiz-badge">${quiz.badge}</span>
        <div class="quiz-meta">Created: ${new Date(quiz.createdDate).toLocaleDateString()}</div>
      </td>
      <td><span class="status-text">${quiz.status}</span></td>
      <td><div class="participants-count">${quiz.participants}</div></td>
      <td>${quiz.totalQuestions} questions</td>
      <td>${quiz.duration} mins</td>
      <td><div class="score-display">${quiz.averageScore > 0 ? quiz.averageScore + '%' : '-'}</div></td>
      <td>
        <div class="action-buttons">
          <button class="action-btn btn-view" onclick="viewQuiz(${quiz.id})">View</button>
          <button class="action-btn btn-edit" onclick="editQuiz(${quiz.id})">Edit</button>
          ${quiz.status === 'active' 
            ? `<button class="action-btn btn-pause" onclick="pauseQuiz(${quiz.id})">Pause</button>`
            : `<button class="action-btn btn-play" onclick="activateQuiz(${quiz.id})">Activate</button>`
          }
          <button class="action-btn btn-delete" onclick="deleteQuiz(${quiz.id})">Delete</button>
        </div>
      </td>
    `;
    tbody.appendChild(row);
  });
}

// Filter quizzes
function filterQuizzes() {
  const filter = document.getElementById('statusFilter').value;
  if (filter === 'all') {
    renderQuizTable();
  } else {
    const filtered = quizzes.filter(quiz => quiz.status === filter);
    renderQuizTable(filtered);
  }
}

// Quiz management functions (placeholders for future)
function viewQuiz(id) { 
  alert(`Viewing quiz ${id}`); 
  // Future: window.location.href = `${URLROOT}/quizmanager/view/${id}`;
}

function editQuiz(id) { 
  alert(`Editing quiz ${id}`); 
  // Future: window.location.href = `${URLROOT}/quizmanager/edit/${id}`;
}

function activateQuiz(id) {
  const quiz = quizzes.find(q => q.id === id);
  quiz.status = 'active'; 
  renderQuizTable();
  // Future: Make AJAX call to backend
}

function pauseQuiz(id) {
  const quiz = quizzes.find(q => q.id === id);
  quiz.status = 'paused'; 
  renderQuizTable();
  // Future: Make AJAX call to backend
}

function deleteQuiz(id) {
  if(confirm('Are you sure you want to delete this quiz?')) {
    quizzes = quizzes.filter(q => q.id !== id); 
    renderQuizTable();
    // Future: Make AJAX call to backend
  }
}

// Modal functions
function openCreateModal() { 
  document.getElementById('createModal').style.display = 'block'; 
}

function closeCreateModal() { 
  document.getElementById('createModal').style.display = 'none'; 
}

// Close modal when clicking outside
window.onclick = function(event) {
  const modal = document.getElementById('createModal');
  if (event.target == modal) {
    modal.style.display = 'none';
  }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', initDashboard);