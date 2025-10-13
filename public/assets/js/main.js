// main.js - Complete JavaScript functionality for SkillXchange homepage
document.addEventListener('DOMContentLoaded', () => {
  initFadeInObserver();
  initCountersObserver();
  initCardEffects();
  handleHeaderScroll();
  bindAnchorSmoothScroll();
});

/* ===== FADE-IN ANIMATIONS ===== */
function initFadeInObserver() {
  const items = document.querySelectorAll('.fade-in');
  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.animationPlayState = 'running';
        observer.unobserve(entry.target);
      }
    });
  }, { 
    threshold: 0.12,
    rootMargin: '0px 0px -50px 0px'
  });
  
  items.forEach(item => {
    item.style.animationPlayState = 'paused';
    observer.observe(item);
  });
}

/* ===== ANIMATED COUNTERS ===== */
function initCountersObserver() {
  const cards = document.querySelectorAll('.stat-card');
  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        startCounter(entry.target);
        observer.unobserve(entry.target);
      }
    });
  }, { 
    threshold: 0.25,
    rootMargin: '0px 0px -50px 0px'
  });

  cards.forEach(card => observer.observe(card));
}

function startCounter(card) {
  const numberEl = card.querySelector('.stat-number');
  const target = parseInt(card.dataset.count || numberEl.textContent || '0', 10);
  
  if (isNaN(target) || target <= 0) { 
    numberEl.textContent = numberEl.textContent; 
    return; 
  }

  let current = 0;
  const duration = 2000; // 2 seconds
  const steps = 60; // approximately 60fps
  const increment = target / steps;
  const stepTime = duration / steps;

  function step() {
    current += increment;
    if (current < target) {
      numberEl.textContent = Math.ceil(current) + '+';
      setTimeout(step, stepTime);
    } else {
      numberEl.textContent = target + '+';
    }
  }
  
  step();
}

/* ===== INTERACTIVE CARD EFFECTS ===== */
function initCardEffects() {
  // Skill cards with 3D tilt and click effects
  document.querySelectorAll('.skill-card').forEach(card => {
    // 3D tilt effect on mouse move
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      
      const rotateX = (y - centerY) / 12;
      const rotateY = (centerX - x) / 12;
      
      card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
    });
    
    // Reset transform on mouse leave
    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
    });
    
    // Click effect with pulse animation
    card.addEventListener('click', () => {
      card.style.animation = 'pulse 0.28s ease';
      setTimeout(() => card.style.animation = '', 280);
      
      // Get skill data for potential routing/modal opening
      const skillType = card.dataset.skill;
      console.log(`Clicked skill: ${skillType}`);
      
      // Placeholder for skill detail modal or navigation
      // openSkillModal(skillType) or window.location.href = `/skills/${skillType}`
    });
    
    // Enhanced hover effect for skill icon
    const skillIcon = card.querySelector('.skill-icon');
    if (skillIcon) {
      card.addEventListener('mouseenter', () => {
        skillIcon.style.transform = 'scale(1.1) rotate(5deg)';
      });
      
      card.addEventListener('mouseleave', () => {
        skillIcon.style.transform = '';
      });
    }
  });

  // Stats cards with subtle 3D effects
  document.querySelectorAll('.stat-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      
      const rotateX = (y - centerY) / 14;
      const rotateY = (centerX - x) / 14;
      
      card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    });
    
    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
    });
  });

  // Process steps with 3D tilt
  document.querySelectorAll('.process-step').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      
      const rotateX = (y - centerY) / 14;
      const rotateY = (centerX - x) / 14;
      
      card.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-5px)`;
    });
    
    card.addEventListener('mouseleave', () => {
      card.style.transform = '';
    });
  });
}

/* ===== HEADER SCROLL BEHAVIOR ===== */
/* ===== INLINE HEADER FUNCTIONALITY ===== */
function handleHeaderScroll() {
    const header = document.getElementById('header');
    
    if (!header) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }, { passive: true });
}


/* ===== SMOOTH SCROLL FOR ANCHOR LINKS ===== */
function bindAnchorSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      
      // Skip empty anchors
      if (!href || href === '#') return;
      
      e.preventDefault();
      
      const target = document.querySelector(href);
      if (target) {
        const headerHeight = document.querySelector('.header')?.offsetHeight || 80;
        const targetPosition = target.offsetTop - headerHeight - 20;
        
        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });
      }
    });
  });
}

/* ===== UTILITY FUNCTIONS ===== */

// Throttle function for performance optimization
function throttle(func, limit) {
  let inThrottle;
  return function() {
    const args = arguments;
    const context = this;
    if (!inThrottle) {
      func.apply(context, args);
      inThrottle = true;
      setTimeout(() => inThrottle = false, limit);
    }
  }
}

// Debounce function for input handling
function debounce(func, wait, immediate) {
  let timeout;
  return function() {
    const context = this;
    const args = arguments;
    const later = function() {
      timeout = null;
      if (!immediate) func.apply(context, args);
    };
    const callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func.apply(context, args);
  };
}

/* ===== ADDITIONAL INTERACTIVE FEATURES ===== */

// Button hover effects enhancement
document.querySelectorAll('.btn-primary, .btn-secondary, .explore-more-btn').forEach(btn => {
  btn.addEventListener('mouseenter', function() {
    this.style.transform = 'translateY(-4px) scale(1.05)';
  });
  
  btn.addEventListener('mouseleave', function() {
    this.style.transform = '';
  });
});

// Logo hover effect
const logo = document.querySelector('.logo');
if (logo) {
  logo.addEventListener('mouseenter', () => {
    logo.style.transform = 'scale(1.05)';
  });
  
  logo.addEventListener('mouseleave', () => {
    logo.style.transform = '';
  });
}

// Add loading state management
window.addEventListener('load', () => {
  document.body.classList.add('loaded');
  
  // Trigger any final animations or setup
  setTimeout(() => {
    document.querySelectorAll('.fade-in').forEach(el => {
      if (!el.style.animationPlayState || el.style.animationPlayState === 'paused') {
        el.style.animationPlayState = 'running';
      }
    });
  }, 100);
});

// Handle resize events for responsive behavior
window.addEventListener('resize', throttle(() => {
  // Reset any transforms that might not work well on different screen sizes
  document.querySelectorAll('.skill-card, .stat-card, .process-step').forEach(card => {
    card.style.transform = '';
  });
}, 250));

// Keyboard navigation support
document.addEventListener('keydown', (e) => {
  // ESC key to close any open modals or reset states
  if (e.key === 'Escape') {
    document.querySelectorAll('.skill-card, .stat-card, .process-step').forEach(card => {
      card.style.transform = '';
      card.style.animation = '';
    });
  }
});

/* ===== CONSOLE BRANDING ===== */
console.log('%c🚀 SkillXchange Platform Loaded', 'color: #388C2B; font-size: 16px; font-weight: bold;');
console.log('%cReady to connect skills and build the future!', 'color: #4CAF50; font-size: 12px;');