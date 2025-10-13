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
          <a class="btn-primary" href="/organizations.php">Join as an Organization</a>
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

      <div class="skills-grid">
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
        <a class="explore-more-btn" href="/skills.php">More</a>
      </div>
    </div>
  </section>

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