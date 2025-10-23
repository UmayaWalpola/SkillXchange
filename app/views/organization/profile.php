<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="profile-container">
        
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <span id="orgInitial">O</span>
            </div>
            <div class="profile-info">
                <h1 id="orgName">Techcorp Solutions</h1>
            </div>
            <button class="edit-btn" onclick="enableEdit()">Edit Profile</button>
        </div>

        <!-- Profile Body -->
        <div class="profile-body">
            <!-- About Section -->
            <div class="profile-section">
                <h2 class="section-title">About Organization</h2>
                <div class="section-content">
                    <div class="info-item">
                        <label>Organization Name</label>
                        <input type="text" id="inputOrgName" class="info-input" value="TechCorp Solutions" disabled>
                    </div>

                    <div class="info-item">
                        <label>Email</label>
                        <input type="email" id="inputEmail" class="info-input" value="info@techcorp.com" disabled>
                    </div>

                    <div class="info-item">
                        <label>Phone</label>
                        <input type="tel" id="inputPhone" class="info-input" value="+1 234 567 8900" disabled>
                    </div>

                    <div class="info-item">
                        <label>Website</label>
                        <input type="url" id="inputWebsite" class="info-input" value="www.techcorp.com" disabled>
                    </div>

                    <div class="info-item full-width">
                        <label>Description</label>
                        <textarea id="inputDescription" class="info-textarea" rows="4" disabled>Leading technology solutions provider specializing in innovative software development and digital transformation.</textarea>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="profile-section">
                <h2 class="section-title">Contact Information</h2>
                <div class="section-content">
                    <div class="info-item">
                        <label>Address</label>
                        <input type="text" id="inputAddress" class="info-input" value="123 Tech Street" disabled>
                    </div>

                    <div class="info-item">
                        <label>City</label>
                        <input type="text" id="inputCity" class="info-input" value="San Francisco" disabled>
                    </div>

                    <div class="info-item">
                        <label>Country</label>
                        <input type="text" id="inputCountry" class="info-input" value="United States" disabled>
                    </div>

                    <div class="info-item">
                        <label>Postal Code</label>
                        <input type="text" id="inputPostal" class="info-input" value="94102" disabled>
                    </div>
                </div>
            </div>

            <!-- Social Links -->
            <div class="profile-section">
                <h2 class="section-title">Social Media</h2>
                <div class="section-content">
                    <div class="info-item">
                        <label>LinkedIn</label>
                        <input type="url" id="inputLinkedin" class="info-input" value="linkedin.com/company/techcorp" disabled>
                    </div>

                    <div class="info-item">
                        <label>Twitter</label>
                        <input type="url" id="inputTwitter" class="info-input" value="twitter.com/techcorp" disabled>
                    </div>

                    <div class="info-item">
                        <label>GitHub</label>
                        <input type="url" id="inputGithub" class="info-input" value="github.com/techcorp" disabled>
                    </div>
                </div>
            </div>

            <!-- Save/Cancel Buttons -->
            <div class="action-buttons" id="actionButtons" style="display: none;">
                <button class="cancel-btn" onclick="cancelEdit()">Cancel</button>
                <button class="save-btn" onclick="saveProfile()">Save Changes</button>
            </div>
        </div>
    </div>
</main>

<script src="<?= URLROOT ?>/assets/js/organizations.js" defer></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>