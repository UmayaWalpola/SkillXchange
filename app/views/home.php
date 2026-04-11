<?php include __DIR__ . '/layouts/header.php'; ?>

<?php
if (!isset($stats)) {
    $stats = isset($data['stats']) ? $data['stats'] : [];
}
$stat_users  = isset($stats['users'])         ? (int)$stats['users']         : 0;
$stat_orgs   = isset($stats['organizations']) ? (int)$stats['organizations'] : 0;
$stat_proj   = isset($stats['projects'])      ? (int)$stats['projects']      : 0;
$stat_skills = isset($stats['skills'])        ? (int)$stats['skills']        : 0;
?>

<main class="site-main" style="padding-top:0;">

  <!-- ===== HERO ===== -->
  <section class="hero">
    <div class="hero-container">

      <div class="hero-content fade-in">
        <h1>SkillXchange</h1>
        <p class="hero-tagline">Teach. Learn. Collaborate.<br>Build skills that actually matter.</p>

        <div class="hero-cta-row">
          <a class="hero-btn-solid" href="<?= URLROOT ?>/auth/register?type=organization">
            <span class="hero-btn-main">Join as an Organization</span>
            <span class="hero-btn-sub">For teams &amp; companies</span>
          </a>
          <a class="hero-btn-outline" href="<?= URLROOT ?>/auth/register?type=individual">
            <span class="hero-btn-main">Join as an Individual</span>
            <span class="hero-btn-sub">Level up your tech skills</span>
          </a>
        </div>
      </div>

      <div class="hero-graphic fade-in animate-delay-2">
        <img src="<?= URLROOT ?>/assets/images/herographic.svg" alt="Learning illustration">
      </div>

    </div>

    <div class="scroll-indicator" onclick="document.getElementById('stats').scrollIntoView({behavior:'smooth'})">
      <span>Scroll down</span>
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
      </svg>
    </div>
  </section>

  <!-- ===== STATS ===== -->
  <section class="stats-section" id="stats">
    <div class="stats-container">
      <h2 class="section-title fade-in">Not Just Another Platform</h2>
      <div class="stats-grid">

        <div class="stat-card fade-in animate-delay-1">
          <div class="stat-icon"><img src="<?= URLROOT ?>/assets/images/puzzle.svg" alt="Skills"></div>
          <div class="stat-number" id="count-skills"><?= $stat_skills ?></div>
          <div class="stat-label">Skills</div>
        </div>

        <div class="stat-card fade-in animate-delay-2">
          <div class="stat-icon"><img src="<?= URLROOT ?>/assets/images/users.svg" alt="Users"></div>
          <div class="stat-number" id="count-users"><?= $stat_users ?></div>
          <div class="stat-label">Students Engaged</div>
        </div>

        <div class="stat-card fade-in animate-delay-3">
          <div class="stat-icon"><img src="<?= URLROOT ?>/assets/images/building.svg" alt="Orgs"></div>
          <div class="stat-number" id="count-orgs"><?= $stat_orgs ?></div>
          <div class="stat-label">Partner Organizations</div>
        </div>

        <div class="stat-card fade-in animate-delay-4">
          <div class="stat-icon"><img src="<?= URLROOT ?>/assets/images/project.svg" alt="Projects"></div>
          <div class="stat-number" id="count-projects"><?= $stat_proj ?></div>
          <div class="stat-label">Projects</div>
        </div>

      </div>
    </div>
  </section>

  <!-- ===== EXPLORE SKILLS ===== -->
  <section class="skills-section" id="explore">
    <div class="skills-container">
      <h2 class="skills-title fade-in">Explore Skills</h2>
      <p class="skills-subtitle fade-in animate-delay-1">Discover and master in-demand skills through hands-on collaboration</p>

      <div id="skillsGrid" class="skills-grid">

        <div class="skill-card fade-in animate-delay-1">
          <div class="skill-icon-wrap ic-orange"><i class="ph ph-globe"></i></div>
          <h3 class="skill-name">Web Development</h3>
          <p class="skill-description">Build modern, responsive websites and web applications using the latest technologies.</p>
        </div>

        <div class="skill-card fade-in animate-delay-2">
          <div class="skill-icon-wrap ic-blue"><i class="ph ph-layout"></i></div>
          <h3 class="skill-name">Frontend Frameworks</h3>
          <p class="skill-description">Master React, Vue, and Angular to build fast, interactive user interfaces.</p>
        </div>

        <div class="skill-card fade-in animate-delay-3">
          <div class="skill-icon-wrap ic-green"><i class="ph ph-terminal"></i></div>
          <h3 class="skill-name">Backend Development</h3>
          <p class="skill-description">Design and build robust server-side logic, APIs, and application backends.</p>
        </div>

        <div class="skill-card fade-in animate-delay-4">
          <div class="skill-icon-wrap ic-purple"><i class="ph ph-database"></i></div>
          <h3 class="skill-name">Database Management</h3>
          <p class="skill-description">Work with relational and non-relational databases to store and manage data efficiently.</p>
        </div>

        <div class="skill-card fade-in animate-delay-1 skill-hidden" style="display:none;">
          <div class="skill-icon-wrap ic-pink"><i class="ph ph-device-mobile"></i></div>
          <h3 class="skill-name">Mobile App Development</h3>
          <p class="skill-description">Develop native and cross-platform mobile apps for iOS and Android.</p>
        </div>

        <div class="skill-card fade-in animate-delay-2 skill-hidden" style="display:none;">
          <div class="skill-icon-wrap ic-teal"><i class="ph ph-cloud"></i></div>
          <h3 class="skill-name">Cloud Computing</h3>
          <p class="skill-description">Deploy and scale applications using AWS, GCP, and Azure cloud platforms.</p>
        </div>

        <div class="skill-card fade-in animate-delay-3 skill-hidden" style="display:none;">
          <div class="skill-icon-wrap ic-indigo"><i class="ph ph-chart-bar"></i></div>
          <h3 class="skill-name">Data Analysis & Visualization</h3>
          <p class="skill-description">Turn raw data into meaningful insights using modern analytics tools.</p>
        </div>

        <div class="skill-card fade-in animate-delay-4 skill-hidden" style="display:none;">
          <div class="skill-icon-wrap ic-red"><i class="ph ph-shield-check"></i></div>
          <h3 class="skill-name">Cybersecurity</h3>
          <p class="skill-description">Learn to protect systems, networks, and data from modern cyber threats.</p>
        </div>

        <div class="skill-card fade-in animate-delay-1 skill-hidden" style="display:none;">
          <div class="skill-icon-wrap ic-cyan"><i class="ph ph-git-branch"></i></div>
          <h3 class="skill-name">DevOps</h3>
          <p class="skill-description">Bridge development and operations with CI/CD pipelines and automation tools.</p>
        </div>

        <div class="skill-card fade-in animate-delay-2 skill-hidden" style="display:none;">
          <div class="skill-icon-wrap ic-lime"><i class="ph ph-git-merge"></i></div>
          <h3 class="skill-name">GitHub and Git</h3>
          <p class="skill-description">Master version control and collaborative workflows using Git and GitHub.</p>
        </div>

        <div class="skill-card fade-in animate-delay-3 skill-hidden" style="display:none;">
          <div class="skill-icon-wrap ic-amber"><i class="ph ph-robot"></i></div>
          <h3 class="skill-name">AI and ML</h3>
          <p class="skill-description">Explore machine learning algorithms and build intelligent AI-powered applications.</p>
        </div>

        <div class="skill-card fade-in animate-delay-4 skill-hidden" style="display:none;">
          <div class="skill-icon-wrap ic-rose"><i class="ph ph-megaphone"></i></div>
          <h3 class="skill-name">Digital Marketing</h3>
          <p class="skill-description">Drive growth through SEO, social media, content strategy, and data-driven campaigns.</p>
        </div>

        <div class="skill-card fade-in animate-delay-1 skill-hidden" style="display:none;">
          <div class="skill-icon-wrap ic-sky"><i class="ph ph-atom"></i></div>
          <h3 class="skill-name">Data Science</h3>
          <p class="skill-description">Apply statistical methods and machine learning to solve real-world data challenges.</p>
        </div>

      </div>

      <div class="explore-more-container">
        <button id="loadMoreSkillsBtn" type="button" class="explore-more-btn">Show More</button>
      </div>
    </div>
  </section>

  <!-- ===== HOW IT WORKS ===== -->
  <section class="process-section" id="how">
    <div class="process-container">
      <h2 class="process-title fade-in">How SkillXchange Works</h2>
      <div class="process-steps">

        <div class="process-step fade-in animate-delay-1">
          <div class="step-number">1</div>
          <h3 class="step-title">Create Your Profile</h3>
          <p class="step-description">Set up your profile with the skills you can teach and the ones you want to learn.</p>
        </div>

        <div class="process-step fade-in animate-delay-2">
          <div class="step-number">2</div>
          <h3 class="step-title">Get Matched</h3>
          <p class="step-description">We connect you with peers who match your teaching and learning goals — skill swap made simple.</p>
        </div>

        <div class="process-step fade-in animate-delay-3">
          <div class="step-number">3</div>
          <h3 class="step-title">Join Real-World Projects</h3>
          <p class="step-description">Collaborate with organizations on real projects and take quizzes to validate your skills and earn rewards.</p>
        </div>

        <div class="process-step fade-in animate-delay-4">
          <div class="step-number">4</div>
          <h3 class="step-title">Grow with the Community</h3>
          <p class="step-description">Share thoughts, ask questions, and gain insights from a community of learners and builders.</p>
        </div>

      </div>
    </div>
  </section>

</main>

<script>
  if (!document.querySelector('script[src*="phosphor"]')) {
    var s = document.createElement('script');
    s.src = 'https://unpkg.com/@phosphor-icons/web';
    document.head.appendChild(s);
  }

  (function () {
    var btn = document.getElementById('loadMoreSkillsBtn');
    var hidden = document.querySelectorAll('.skill-hidden');
    var showing = false;
    if (!btn) return;
    btn.addEventListener('click', function () {
      showing = !showing;
      for (var i = 0; i < hidden.length; i++) hidden[i].style.display = showing ? '' : 'none';
      btn.textContent = showing ? 'Show Less' : 'Show More';
    });
  })();

  (function () {
    var els = document.querySelectorAll('.fade-in');
    function check() {
      els.forEach(function(el) {
        if (el.getBoundingClientRect().top < window.innerHeight - 60)
          el.style.animationPlayState = 'running';
      });
    }
    window.addEventListener('scroll', check);
    check();
  })();

  (function () {
    var counters = [
      { id: 'count-skills',   target: <?= $stat_skills ?> },
      { id: 'count-users',    target: <?= $stat_users ?>  },
      { id: 'count-orgs',     target: <?= $stat_orgs ?>   },
      { id: 'count-projects', target: <?= $stat_proj ?>   }
    ];
    var done = false;
    function animate(el, target) {
      var step = Math.max(1, Math.ceil(target / 60));
      var cur = 0;
      var t = setInterval(function() {
        cur = Math.min(cur + step, target);
        el.textContent = cur;
        if (cur >= target) clearInterval(t);
      }, 24);
    }
    function run() {
      if (done) return;
      var sec = document.getElementById('stats');
      if (!sec) return;
      if (sec.getBoundingClientRect().top < window.innerHeight) {
        done = true;
        counters.forEach(function(c) {
          var el = document.getElementById(c.id);
          if (el && c.target > 0) { el.textContent = '0'; animate(el, c.target); }
        });
      }
    }
    window.addEventListener('scroll', run);
    setTimeout(run, 400);
  })();
</script>

<?php include __DIR__ . '/layouts/footer.php'; ?>