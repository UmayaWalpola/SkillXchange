// Profile page functionality

function showProjects(type) {
    // Hide all project grids
    const completedProjects = document.getElementById('completed-projects');
    const progressProjects = document.getElementById('progress-projects');
    
    // Remove active class from all toggle buttons
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected project type and activate corresponding button
    if (type === 'completed') {
        completedProjects.style.display = 'grid';
        progressProjects.style.display = 'none';
        document.querySelector('.toggle-btn').classList.add('active');
    } else {
        completedProjects.style.display = 'none';
        progressProjects.style.display = 'grid';
        document.querySelectorAll('.toggle-btn')[1].classList.add('active');
    }
}

// Add fade-in animations when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in class to profile sections
    const sections = document.querySelectorAll('.profile-section');
    sections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(30px)';
        section.style.transition = 'all 0.6s ease';
        
        setTimeout(() => {
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
        }, 100 * (index + 1));
    });
    
    // Add hover effects to stats
    const statItems = document.querySelectorAll('.stat-item');
    statItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Add hover effects to skill tags
    const skillTags = document.querySelectorAll('.skill-tag');
    skillTags.forEach(tag => {
        tag.addEventListener('click', function() {
            // Add click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'translateY(-2px)';
            }, 150);
        });
    });
    
    // Add hover effects to project cards
    const projectCards = document.querySelectorAll('.project-card');
    projectCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Add hover effects to badge items
    const badgeItems = document.querySelectorAll('.badge-item');
    badgeItems.forEach(badge => {
        badge.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.02)';
        });
        
        badge.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Add hover effects to feedback items
    const feedbackItems = document.querySelectorAll('.feedback-item');
    feedbackItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Add hover effects to activity items
    const activityItems = document.querySelectorAll('.activity-item');
    activityItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });
    
    // Add click effect to edit profile button
    const editBtn = document.querySelector('.edit-profile-btn');
    if (editBtn) {
        editBtn.addEventListener('click', function() {
            // Add click animation
            this.style.transform = 'translateY(-2px) scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'translateY(-2px) scale(1)';
            }, 150);
            
            // You can add edit profile functionality here
            console.log('Edit profile clicked');
        });
    }
    
    // Add smooth scrolling for view all buttons
    const viewAllBtns = document.querySelectorAll('.view-all-btn');
    viewAllBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            // Add click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
            
            // You can add navigation functionality here
            console.log('View all clicked for:', this.textContent);
        });
    });
});

// Function to animate counter numbers (optional enhancement)
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    const animationDuration = 2000; // 2 seconds
    
    counters.forEach(counter => {
        const target = parseInt(counter.textContent);
        const increment = target / (animationDuration / 16); // 60fps
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            counter.textContent = Math.floor(current);
        }, 16);
    });
}

// Function to show loading state (useful for AJAX calls)
function showLoading(element) {
    const originalText = element.textContent;
    element.textContent = 'Loading...';
    element.style.opacity = '0.6';
    element.style.pointerEvents = 'none';
    
    return function hideLoading() {
        element.textContent = originalText;
        element.style.opacity = '1';
        element.style.pointerEvents = 'auto';
    };
}

// Function to show notifications (can be used for profile updates)
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 10000;
        transform: translateX(100%);
        transition: all 0.3s ease;
        ${type === 'success' ? 'background: #4caf50;' : ''}
        ${type === 'error' ? 'background: #f44336;' : ''}
        ${type === 'info' ? 'background: #2196f3;' : ''}
    `;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Slide in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Slide out and remove
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Export functions for use in other files (if using modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showProjects,
        animateCounters,
        showLoading,
        showNotification
    };
}

// JavaScript for sidebar navigation
function setActiveItem(clickedItem) {
    // Remove active class from all items
    document.querySelectorAll('.sidebar-item').forEach(item => {
        item.classList.remove('active');
    });

    // Add active class to clicked item
    clickedItem.classList.add('active');
}

// Add click event listeners to all sidebar items
document.querySelectorAll('.sidebar-item').forEach(item => {
    item.addEventListener('click', function(e) {
        // Don't prevent default - let the link work!
        setActiveItem(this);
        
        // Optional: You can add loading state here
        console.log('Navigating to:', this.querySelector('span:last-child').textContent);
    });
});
