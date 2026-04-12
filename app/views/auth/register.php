<?php if (!empty($data['errors'])): ?>
    <div class="error-messages">
        <?php foreach($data['errors'] as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/global.css">
<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/auth.css">

<div class="login-container">
    <div class="login-card">
        <!-- Left side: logo/image -->
        <div class="login-left">
            <img src="<?= URLROOT ?>/assets/images/logo-new.png" alt="Skill Exchange Logo">
        </div>

        <!-- Right side: forms -->
        <div class="login-right">
            <h2>Welcome!</h2>
            <p>LinkUp, SkillUp</p>
            <h3>Join Now</h3>

            <!-- Tabs -->
            <div class="signup-tabs">
                <button class="tab-btn active" data-tab="organization">Organization</button>
                <button class="tab-btn" data-tab="individual">Individual</button>
            </div>

            <!-- Forms -->
            <div class="form-container">
                <!-- Organization Form -->
                <form id="organization" class="active" enctype="multipart/form-data" method="POST" action="<?php echo URLROOT; ?>/auth/registerOrganization">
                    <p class="quote">Are you an organization?</p>
                    <label for="org-name">Organization Name</label>
                    <input type="text" id="org-name" name="org-name" required />

                    <label for="org-email">Email</label>
                    <input type="email" id="org-email" name="org-email" required />

                    <label for="org-password">Password</label>
                    <input type="password" id="org-password" name="org-password" required />

                    <label for="org-password-confirm">Confirm Password</label>
                    <input type="password" id="org-password-confirm" name="org-password-confirm" required />

                    <label for="org-cert">Upload Certified CoC (proof)</label>
                    <input type="file" id="org-cert" name="org-cert" accept=".pdf,.jpg,.png" required />

                    <button type="submit">Sign Up</button>
                </form>

                <!-- Individual Form -->
                <form id="individual" enctype="multipart/form-data" method="POST" action="<?php echo URLROOT; ?>/auth/registerIndividual">
                    <p class="quote">Are you an individual ready to teach, learn skills, and join exciting projects? Take quizzes and connect with others!</p>
                    <label for="ind-fullname">Full Name</label>
                    <input type="text" id="ind-fullname" name="ind-fullname" required />

                    <label for="ind-email">Email</label>
                    <input type="email" id="ind-email" name="ind-email" required />

                    <label for="ind-password">Password</label>
                    <input type="password" id="ind-password" name="ind-password" required />

                    <label for="ind-password-confirm">Confirm Password</label>
                    <input type="password" id="ind-password-confirm" name="ind-password-confirm" required />

                    <button type="submit">Sign Up</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Tab switching & input effects -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const forms = document.querySelectorAll('.form-container form');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            // Toggle active tab
            tabButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Show only the corresponding form
            forms.forEach(f => f.classList.remove('active'));
            const targetForm = document.getElementById(btn.dataset.tab);
            if (targetForm) targetForm.classList.add('active');
        });
    });

    // Input focus/hover effects
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.style.transform = 'translateY(-2px)';
                input.style.boxShadow = '0 0 0 4px rgba(101,131,150,0.1), 0 4px 12px rgba(0,0,0,0.12)';
            });
            input.addEventListener('blur', () => {
                input.style.transform = 'translateY(0)';
                input.style.boxShadow = '0 2px 4px rgba(0,0,0,0.08)';
            });
            input.addEventListener('mouseenter', () => {
                if (document.activeElement !== input) {
                    input.style.transform = 'translateY(-1px)';
                    input.style.boxShadow = '0 4px 8px rgba(0,0,0,0.08)';
                }
            });
            input.addEventListener('mouseleave', () => {
                if (document.activeElement !== input) {
                    input.style.transform = 'translateY(0)';
                    input.style.boxShadow = '0 2px 4px rgba(0,0,0,0.08)';
                }
            });
        });
    });
});
</script>



