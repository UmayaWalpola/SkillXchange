<?php 

include __DIR__ . '/layouts/header.php'; 
?>

<main class="site-main">
  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-container" style="max-width:1400px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:2.5rem;align-items:center;padding:120px 2rem 60px;">
      <div class="hero-content">
        <h1 class="fade-in">SkillXchange</h1>
        <div class="hero-tagline fade-in animate-delay-1" style="font-size:1.2rem;color:var(--primary-blue);margin-top:1rem;">
          Teach. Learn. Collaborate.<br>Build skills that actually matter.
        </div>
        <div class="organization-cta fade-in animate-delay-2" style="margin-top:1.5rem;">
          <p class="organization-question" style="color:var(--primary-blue);">Are you an organization looking for the right skills for your projects?</p>
          <a class="btn-primary" href="<?= URLROOT ?>/auth/register?type=organization">Join as an Organization</a>
        </div>
      </div>

      <div class="hero-graphic fade-in animate-delay-3" style="height:360px;border-radius:16px;display:flex;align-items:center;justify-content:center;color:var(--white);font-weight:700;">
      <img src="<?php echo URLROOT; ?>/assets/images/herographic.svg" alt="Learning illustration">
      </div>
    </div>
  </section>
 
  <!-- Stats Section -->
  <section class="stats-section" id="stats">
    <div class="stats-container">
      <h2 class="section-title fade-in">Not Just Another Platform</h2>
      <div class="stats-grid">
        <div class="stat-card fade-in animate-delay-1" data-count="20">
          <div class="stat-icon">
            <img src="<?php echo URLROOT; ?>/assets/images/puzzle.svg" alt="Skills Icon">
          </div>
          <div class="stat-number">0</div>
          <div class="stat-label">Skills</div>
        </div>
        <div class="stat-card fade-in animate-delay-2" data-count="500">
          <div class="stat-icon">
            <img src="<?php echo URLROOT; ?>/assets/images/users.svg" alt="Users Icon">
          </div>
          <div class="stat-number">0</div>
          <div class="stat-label">Students Engaged</div>
        </div>
        <div class="stat-card fade-in animate-delay-3" data-count="10">
          <div class="stat-icon">
            <img src="<?php echo URLROOT; ?>/assets/images/building.svg" alt="Org Icon">
          </div>
          <div class="stat-number">0</div>
          <div class="stat-label">Partner Organizations</div>
        </div>
        <div class="stat-card fade-in animate-delay-4" data-count="100">
          <div class="stat-icon">
            <img src="<?php echo URLROOT; ?>/assets/images/project.svg" alt="Project Icon">
          </div>
          <div class="stat-number">0</div>
          <div class="stat-label">Projects</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Skills Section -->
  <section class="skills-section" id="explore">
    <div class="skills-container">
      <h2 class="skills-title fade-in">Explore Skills</h2>
      <p class="skills-subtitle fade-in animate-delay-1">Discover and master in-demand skills through hands-on collaboration</p>

      <div id="skillsGrid" class="skills-grid">
        <div class="skill-card fade-in animate-delay-1" data-skill="webdev">
          <div class="skill-icon">💻</div>
          <h3 class="skill-name">Web Development</h3>
          <p class="skill-description">Build modern, responsive websites and web applications using the latest technologies and frameworks.</p>
          <div class="skill-stats">
            <span class="skill-learners">150+ learners</span>
          </div>
        </div>

        <div class="skill-card fade-in animate-delay-2" data-skill="uidesign">
          <div class="skill-icon">🎨</div>
          <h3 class="skill-name">UI/UX Design</h3>
          <p class="skill-description">Create intuitive and beautiful user interfaces that enhance user experience and engagement.</p>
          <div class="skill-stats">
            <span class="skill-learners">120+ learners</span>
          </div>
        </div>

        <div class="skill-card fade-in animate-delay-3" data-skill="ai">
          <div class="skill-icon">🤖</div>
          <h3 class="skill-name">Artificial Intelligence</h3>
          <p class="skill-description">Explore machine learning, deep learning, and AI applications in real-world scenarios.</p>
          <div class="skill-stats">
            <span class="skill-learners">200+ learners</span>
          </div>
        </div>

        <div class="skill-card fade-in animate-delay-1" data-skill="mobile">
          <div class="skill-icon">📱</div>
          <h3 class="skill-name">Mobile Development</h3>
          <p class="skill-description">Develop native and cross-platform mobile applications for iOS and Android devices.</p>
          <div class="skill-stats">
            <span class="skill-learners">80+ learners</span>
          </div>
        </div>
      </div>

      <div class="explore-more-container">
        <button id="loadMoreSkillsBtn" type="button" class="explore-more-btn">More</button>
        <span id="skillsLoading" style="display:none; margin-left: 1rem; color: var(--primary-blue);">Loading...</span>
      </div>
    </div>
  </section>

  <script>
    (function() {
      const URLROOT = '<?= URLROOT ?>';
      let skillsOffset = 4; // First 4 skills already shown
      
      const loadMoreBtn = document.getElementById('loadMoreSkillsBtn');
      const skillsGrid = document.getElementById('skillsGrid');
      const loadingSpan = document.getElementById('skillsLoading');
      
      if (!loadMoreBtn || !skillsGrid) return;
      
      loadMoreBtn.addEventListener('click', function() {
        // Disable button and show loading
        loadMoreBtn.disabled = true;
        loadingSpan.style.display = 'inline';
        
        // Fetch more skills
        fetch(URLROOT + '/skills/loadMore?offset=' + skillsOffset)
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              // Append new skills to grid
              skillsGrid.insertAdjacentHTML('beforeend', data.html);
              
              // Update offset
              skillsOffset = data.nextOffset;
              
              // Trigger fade-in animation for new cards
              const newCards = skillsGrid.querySelectorAll('.skill-card.fade-in');
              newCards.forEach(card => {
                card.style.animationPlayState = 'running';
              });
              
              // Check if there are more skills
              if (!data.hasMore) {
                loadMoreBtn.textContent = 'No more skills';
                loadMoreBtn.disabled = true;
              } else {
                loadMoreBtn.disabled = false;
              }
            } else {
              console.error('Error loading skills:', data.message || 'Unknown error');
              alert('Failed to load more skills. Please try again.');
              loadMoreBtn.disabled = false;
            }
          })
          .catch(error => {
            console.error('Error fetching skills:', error);
            alert('An error occurred while loading skills. Please try again.');
            loadMoreBtn.disabled = false;
          })
          .finally(() => {
            loadingSpan.style.display = 'none';
          });
      });
    })();
  </script>

  <!-- Process Section -->
  <section class="process-section" id="how">
    <div class="process-container">
      <h2 class="process-title fade-in">How SkillXchange works</h2>
      <div class="process-steps">
        <div class="process-step fade-in animate-delay-1">
          <div class="step-number">1</div>
          <h3 class="step-title">Create Your Skill Profile</h3>
          <p class="step-description">Tell us what you can teach and what you want to learn.</p>
        </div>
        <div class="process-step fade-in animate-delay-2">
          <div class="step-number">2</div>
          <h3 class="step-title">Get Skill Matched</h3>
          <p class="step-description">We connect you with peers who match your teaching and learning goals.</p>
        </div>
        <div class="process-step fade-in animate-delay-3">
          <div class="step-number">3</div>
          <h3 class="step-title">Join Real Projects</h3>
          <p class="step-description">Collaborate with others or organizations to apply your skills in real-life challenges.</p>
        </div>
      </div>
    </div>
  </section>
</main>

<?php include __DIR__ . '/layouts/footer.php'; ?>