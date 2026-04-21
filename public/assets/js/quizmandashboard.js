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
    
    console.log('🔄 Loading quizzes from:', urlRoot + '/quizmanager/getQuizzes');
    
    const response = await fetch(urlRoot + '/quizmanager/getQuizzes', {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    });
    
    console.log('📡 Response status:', response.status);
    
    if (!response.ok) {
      throw new Error('Failed to fetch quizzes: ' + response.statusText);
    }
    
    const data = await response.json();
    console.log('📊 Data received:', data);
    
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
      console.log('✅ Loaded', quizzes.length, 'quizzes');
    } else {
      console.warn('⚠️ No quizzes in response or success=false');
      quizzes = [];
    }
    
    renderQuizTable();
  } catch (error) {
    console.error('❌ Error loading quizzes:', error);
    const tbody = document.getElementById('quizTableBody');
    if (tbody) {
      tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #ef4444;">Error loading quizzes. <a href="javascript:location.reload()" style="color: #658396; text-decoration: underline;">Click to refresh</a></td></tr>';
    }
  }
}

// Render quiz table
function renderQuizTable(filteredQuizzes = null) {
  const quizzesToShow = filteredQuizzes || quizzes;
  const tbody = document.getElementById('quizTableBody');
  tbody.innerHTML = '';

  if (quizzesToShow.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px;">No quizzes found.</td></tr>';
    return;
  }

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

function viewQuiz(id) { 
  window.location.href = `${window.URLROOT}/quizmanager/preview/${id}`;
}

function editQuiz(id) { 
  window.location.href = `${window.URLROOT}/quizmanager/edit/${id}`;
}

async function activateQuiz(id) {
  await updateQuizStatus(id, 'active');
}

async function pauseQuiz(id) {
  await updateQuizStatus(id, 'paused');
}

async function updateQuizStatus(id, status) {
  try {
    const response = await fetch(`${window.URLROOT}/quizmanager/updateStatus/${id}/${status}`, {
      method: 'POST',
      headers: { 'Accept': 'application/json' }
    });
    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Failed to update quiz status');
    }

    const quiz = quizzes.find(q => q.id === id);
    if (quiz) quiz.status = status;
    renderQuizTable();
  } catch (error) {
    console.error('Status update failed:', error);
    alert(error.message || 'Failed to update quiz status');
  }
}

async function deleteQuiz(id) {
  if (!confirm('Are you sure you want to delete this quiz?')) return;

  try {
    const response = await fetch(`${window.URLROOT}/quizmanager/delete/${id}`, {
      method: 'POST',
      headers: { 'Accept': 'application/json' }
    });
    const result = await response.json();

    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Failed to delete quiz');
    }

    quizzes = quizzes.filter(q => q.id !== id);
    renderQuizTable();
  } catch (error) {
    console.error('Delete failed:', error);
    alert(error.message || 'Failed to delete quiz');
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
