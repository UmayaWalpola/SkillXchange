const projects = [
  {
    id: 1,
    title: "AI Chatbot Assistant",
    description: "Develop a chatbot that can handle user queries using AI and NLP techniques.",
    category: "Web Development",
    categoryClass: "web",
    icon: "🤖",
    status: "active",
    overview:
      "This project aims to create an AI-driven chatbot that can understand and respond to user questions intelligently using NLP models like Dialogflow or GPT-based frameworks.",
    goals: [
      "Build NLP model integration",
      "Design a conversational UI",
      "Test chatbot responses",
      "Deploy on web platform",
    ],
    skills: ["JavaScript", "Node.js", "Dialogflow", "NLP", "UX Design"],
    progress: 70,
    creator: "Ayesha Rahman",
    totalMembers: 5,
    createdDate: "2025-09-10",
    deadline: "2025-11-30",
    lead: {
      name: "Ayesha Rahman",
      role: "AI Developer",
      avatar: "👩‍💻",
    },
    members: [
      { name: "Imran Khan", role: "Frontend Dev", avatar: "👨‍💻" },
      { name: "Sara Ali", role: "Backend Dev", avatar: "🧑‍💻" },
      { name: "Lahiru Perera", role: "UX Designer", avatar: "🎨" },
    ],
    resources: [
      { name: "Dialogflow Docs", icon: "📘" },
      { name: "TensorFlow JS", icon: "🧠" },
      { name: "UI Prototype", icon: "🎨" },
    ],
  },
  {
    id: 2,
    title: "SkillXchange Mobile App",
    description: "Create a cross-platform app to help users exchange and learn skills collaboratively.",
    category: "Mobile App",
    categoryClass: "mobile",
    icon: "📱",
    status: "in-progress",
    overview:
      "SkillXchange app allows users to post their skills, find partners to exchange knowledge, and collaborate on micro-projects using a community-driven model.",
    goals: [
      "Design UI using Flutter",
      "Integrate Firebase authentication",
      "Implement skill matching algorithm",
      "Publish to Play Store",
    ],
    skills: ["Flutter", "Firebase", "UX Design", "Dart", "REST API"],
    progress: 45,
    creator: "Namal Perera",
    totalMembers: 6,
    createdDate: "2025-08-15",
    deadline: "2025-12-15",
    lead: {
      name: "Namal Perera",
      role: "Mobile App Lead",
      avatar: "👨‍💻",
    },
    members: [
      { name: "Hassan Rafi", role: "Backend Dev", avatar: "🧑‍💻" },
      { name: "Mithila Fernando", role: "UI Designer", avatar: "🎨" },
      { name: "Pasan Jayasuriya", role: "Tester", avatar: "🧪" },
    ],
    resources: [
      { name: "Flutter Docs", icon: "📘" },
      { name: "Firebase Setup Guide", icon: "🔥" },
      { name: "UI Kit Figma", icon: "🎨" },
    ],
  },
  {
    id: 3,
    title: "Data Visualization Dashboard",
    description: "A web dashboard to visualize company analytics with real-time charts.",
    category: "Data Science",
    categoryClass: "data",
    icon: "📊",
    status: "completed",
    overview:
      "The project delivers a responsive data analytics dashboard with interactive visualizations powered by Chart.js and D3.js. It helps businesses make data-driven decisions quickly.",
    goals: [
      "Integrate Chart.js and D3.js",
      "Implement backend with Node.js",
      "Add user authentication",
      "Host on cloud",
    ],
    skills: ["D3.js", "Chart.js", "Node.js", "Express", "HTML/CSS"],
    progress: 100,
    creator: "Ravindu Silva",
    totalMembers: 4,
    createdDate: "2025-06-01",
    deadline: "2025-08-30",
    lead: {
      name: "Ravindu Silva",
      role: "Data Engineer",
      avatar: "👨‍💻",
    },
    members: [
      { name: "Maya Fernando", role: "UI Dev", avatar: "👩‍🎨" },
      { name: "Kavindu Perera", role: "Backend Dev", avatar: "🧑‍💻" },
    ],
    resources: [
      { name: "Chart.js Docs", icon: "📈" },
      { name: "D3.js Guide", icon: "📊" },
      { name: "Cloud Hosting", icon: "☁️" },
    ],
  },
  {
    id: 4,
    title: "UX Design System",
    description: "Develop a unified design system for SkillXchange components.",
    category: "UI/UX Design",
    categoryClass: "design",
    icon: "🎨",
    status: "active",
    overview:
      "A project focused on building a reusable design system with typography, color schemes, and UI components for SkillXchange’s web and mobile platforms.",
    goals: [
      "Research design standards",
      "Create Figma component library",
      "Ensure accessibility compliance",
      "Deliver design documentation",
    ],
    skills: ["Figma", "UI Design", "Accessibility", "Branding"],
    progress: 60,
    creator: "Dilani Madushani",
    totalMembers: 3,
    createdDate: "2025-09-20",
    deadline: "2025-12-01",
    lead: {
      name: "Dilani Madushani",
      role: "UX Designer",
      avatar: "👩‍🎨",
    },
    members: [
      { name: "Sahan Wijesinghe", role: "UI Dev", avatar: "👨‍💻" },
      { name: "Naduni Jayasekara", role: "Brand Designer", avatar: "🎨" },
    ],
    resources: [
      { name: "Figma Library", icon: "🎨" },
      { name: "Design Guidelines", icon: "📘" },
      { name: "Accessibility Checklist", icon: "✅" },
    ],
  },
];

/* --- JS FUNCTIONS --- */
let currentFilter = 'all';

function renderProjects() {
  const container = document.getElementById('projectsContainer');
  container.innerHTML = '';

  let filtered = projects;
  if (currentFilter !== 'all') {
    filtered = projects.filter(p => p.status === currentFilter);
  }

  filtered.forEach(project => {
    const card = document.createElement('div');
    card.className = 'project-card';
    card.innerHTML = `
      <div class="project-banner ${project.categoryClass}">${project.icon}</div>
      <span class="status-badge ${project.status}">${project.status.toUpperCase()}</span>
      <div class="project-content">
        <h3 class="project-title">${project.title}</h3>
        <p class="project-description">${project.description}</p>
        <div class="project-meta">
          <span class="project-category">${project.category}</span>
          <span>by ${project.creator}</span>
        </div>
        <div class="project-skills">
          ${project.skills.slice(0, 3).map(s => `<span class="skill-tag">${s}</span>`).join('')}
        </div>
        <div class="project-footer">
          <span class="members-count">${project.totalMembers} Members</span>
          <button class="view-details-btn" onclick="showDetail(${project.id})">View Details</button>
        </div>
      </div>
    `;
    container.appendChild(card);
  });
}

function filterProjects(filter) {
  currentFilter = filter;
  document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
  event.target.classList.add('active');
  renderProjects();
}

function showDetail(id) {
  const project = projects.find(p => p.id === id);
  document.getElementById('projectHeader').innerHTML = `
    <h1>${project.title}</h1>
    <div class="project-header-meta">
      <span>📅 Created: ${project.createdDate}</span>
      <span>⏰ Deadline: ${project.deadline}</span>
      <span>📁 ${project.category}</span>
    </div>
  `;

  document.getElementById('projectOverview').textContent = project.overview;
  document.getElementById('goalsList').innerHTML = project.goals.map(g => `<li>${g}</li>`).join('');
  document.getElementById('skillsList').innerHTML = project.skills.map(s => `<span class="skill-tag">${s}</span>`).join('');
  document.getElementById('progressFill').style.width = project.progress + '%';
  document.getElementById('progressText').textContent = `${project.progress}% Complete`;

  document.getElementById('leadInfo').innerHTML = `
    <div class="avatar">${project.lead.avatar}</div>
    <div class="lead-details">
      <h4>${project.lead.name}</h4>
      <p>${project.lead.role}</p>
    </div>
  `;

  document.getElementById('teamList').innerHTML = project.members
    .map(
      m => `
    <div class="team-member">
      <div class="small-avatar">${m.avatar}</div>
      <div>
        <div style="color: #1a1a1a; font-size: 0.9rem; font-weight: 600;">${m.name}</div>
        <div style="color: #999; font-size: 0.75rem;">${m.role}</div>
      </div>
    </div>`
    )
    .join('');

  document.getElementById('resourcesList').innerHTML = project.resources
    .map(
      r => `
    <li>
      <span class="resource-icon">${r.icon}</span>
      <a href="#" class="resource-link">${r.name}</a>
    </li>`
    )
    .join('');

  document.getElementById('createdDate').textContent = project.createdDate;
  document.getElementById('deadlineDate').textContent = project.deadline;

  document.getElementById('projectsListPage').style.display = 'none';
  document.getElementById('projectDetailPage').classList.add('show');
}

function goBack() {
  document.getElementById('projectsListPage').style.display = 'block';
  document.getElementById('projectDetailPage').classList.remove('show');
}

function joinProject() {
  alert('Successfully joined the project!');
}

document.addEventListener('DOMContentLoaded', renderProjects);

document.getElementById('searchProjects').addEventListener('keyup', e => {
  const search = e.target.value.toLowerCase();
  document.querySelectorAll('.project-card').forEach(card => {
    const title = card.querySelector('.project-title').textContent.toLowerCase();
    card.style.display = title.includes(search) ? 'block' : 'none';
  });
});
