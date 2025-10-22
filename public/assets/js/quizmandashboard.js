// Quiz Manager Dashboard JavaScript

// Sample quiz data
let quizzes = [
  {
    id: 1,
    title: "JavaScript Fundamentals",
    badge: "Advanced",
    status: "active",
    participants: 85,
    totalQuestions: 20,
    duration: 30,
    createdDate: "2024-03-15",
    averageScore: 78.5
  },
  {
    id: 2,
    title: "React Advanced Concepts",
    badge: "Intermediate",
    status: "active",
    participants: 52,
    totalQuestions: 15,
    duration: 45,
    createdDate: "2024-03-18",
    averageScore: 82.1
  },
  {
    id: 3,
    title: "Database Design Principles",
    badge: "Beginer",
    status: "paused",
    participants: 73,
    totalQuestions: 25,
    duration: 40,
    createdDate: "2024-03-10",
    averageScore: 71.3
  },
  {
    id: 4,
    title: "Python Data Structures",
    badge: "Advanced",
    status: "draft",
    participants: 0,
    totalQuestions: 18,
    duration: 35,
    createdDate: "2024-03-20",
    averageScore: 0
  }
];

// Initialize dashboard
function initDashboard() {
  renderQuizTable();
}

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