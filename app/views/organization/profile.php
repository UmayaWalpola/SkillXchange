<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="profile-container">
        
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar">
                <span id="orgInitial">
                    <?php 
                    // Get first letter of organization name from session or data
                    $orgName = isset($data['organization']->name) ? $data['organization']->name : $_SESSION['username'];
                    echo strtoupper(substr($orgName, 0, 1));
                    ?>
                </span>
            </div>
            <div class="profile-info">
                <h1 id="orgName"><?= htmlspecialchars($orgName) ?></h1>
                <p class="org-type">Organization Account</p>
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
                        <input type="text" id="inputOrgName" name="org_name" class="info-input" 
                               value="<?= isset($data['organization']->name) ? htmlspecialchars($data['organization']->name) : htmlspecialchars($_SESSION['username']) ?>" 
                               disabled>
                    </div>

                    <div class="info-item">
                        <label>Email</label>
                        <input type="email" id="inputEmail" name="email" class="info-input" 
                               value="<?= isset($data['organization']->email) ? htmlspecialchars($data['organization']->email) : '' ?>" 
                               disabled>
                    </div>

                    <div class="info-item">
                        <label>Phone</label>
                        <input type="tel" id="inputPhone" name="phone" class="info-input" 
                               value="<?= isset($data['organization']->phone) ? htmlspecialchars($data['organization']->phone) : '' ?>" 
                               disabled>
                    </div>

                    <div class="info-item">
                        <label>Website</label>
                        <input type="url" id="inputWebsite" name="website" class="info-input" 
                               value="<?= isset($data['organization']->website) ? htmlspecialchars($data['organization']->website) : '' ?>" 
                               disabled>
                    </div>

                    <div class="info-item full-width">
                        <label>Description</label>
                        <textarea id="inputDescription" name="description" class="info-textarea" rows="4" disabled><?= isset($data['organization']->description) ? htmlspecialchars($data['organization']->description) : '' ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="profile-section">
                <h2 class="section-title">Contact Information</h2>
                <div class="section-content">
                    <div class="info-item">
                        <label>Address</label>
                        <input type="text" id="inputAddress" name="address" class="info-input" 
                               value="<?= isset($data['organization']->address) ? htmlspecialchars($data['organization']->address) : '' ?>" 
                               disabled>
                    </div>

                    <div class="info-item">
                        <label>City</label>
                        <input type="text" id="inputCity" name="city" class="info-input" 
                               value="<?= isset($data['organization']->city) ? htmlspecialchars($data['organization']->city) : '' ?>" 
                               disabled>
                    </div>

                    <div class="info-item">
                        <label>Country</label>
                        <input type="text" id="inputCountry" name="country" class="info-input" 
                               value="<?= isset($data['organization']->country) ? htmlspecialchars($data['organization']->country) : '' ?>" 
                               disabled>
                    </div>

                    <div class="info-item">
                        <label>Postal Code</label>
                        <input type="text" id="inputPostal" name="postal_code" class="info-input" 
                               value="<?= isset($data['organization']->postal_code) ? htmlspecialchars($data['organization']->postal_code) : '' ?>" 
                               disabled>
                    </div>
                </div>
            </div>

            <!-- Social Links -->
            <div class="profile-section">
                <h2 class="section-title">Social Media</h2>
                <div class="section-content">
                    <div class="info-item">
                        <label>LinkedIn</label>
                        <input type="url" id="inputLinkedin" name="linkedin" class="info-input" 
                               value="<?= isset($data['organization']->linkedin) ? htmlspecialchars($data['organization']->linkedin) : '' ?>" 
                               disabled>
                    </div>

                    <div class="info-item">
                        <label>Twitter</label>
                        <input type="url" id="inputTwitter" name="twitter" class="info-input" 
                               value="<?= isset($data['organization']->twitter) ? htmlspecialchars($data['organization']->twitter) : '' ?>" 
                               disabled>
                    </div>

                    <div class="info-item">
                        <label>GitHub</label>
                        <input type="url" id="inputGithub" name="github" class="info-input" 
                               value="<?= isset($data['organization']->github) ? htmlspecialchars($data['organization']->github) : '' ?>" 
                               disabled>
                    </div>
                </div>
            </div>

            <!-- Statistics Section (Optional) -->
            <div class="profile-section">
                <h2 class="section-title">Quick Stats</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-value" id="totalProjects">
                            <?= isset($data['stats']->total_projects) ? $data['stats']->total_projects : '0' ?>
                        </div>
                        <div class="stat-label">Total Projects</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="activeProjects">
                            <?= isset($data['stats']->active_projects) ? $data['stats']->active_projects : '0' ?>
                        </div>
                        <div class="stat-label">Active Projects</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="totalApplications">
                            <?= isset($data['stats']->total_applications) ? $data['stats']->total_applications : '0' ?>
                        </div>
                        <div class="stat-label">Applications</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="totalMembers">
                            <?= isset($data['stats']->total_members) ? $data['stats']->total_members : '0' ?>
                        </div>
                        <div class="stat-label">Team Members</div>
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

<script>
// Store original values for cancel functionality
let originalValues = {};

function enableEdit() {
    const inputs = document.querySelectorAll('.info-input, .info-textarea');
    const editBtn = document.querySelector('.edit-btn');
    const actionButtons = document.getElementById('actionButtons');
    
    // Store original values
    inputs.forEach(input => {
        originalValues[input.id] = input.value;
        input.disabled = false;
    });
    
    editBtn.style.display = 'none';
    actionButtons.style.display = 'flex';
}

function cancelEdit() {
    const inputs = document.querySelectorAll('.info-input, .info-textarea');
    const editBtn = document.querySelector('.edit-btn');
    const actionButtons = document.getElementById('actionButtons');
    
    // Restore original values
    inputs.forEach(input => {
        input.value = originalValues[input.id];
        input.disabled = true;
    });
    
    editBtn.style.display = 'block';
    actionButtons.style.display = 'none';
}

function saveProfile() {
    const formData = new FormData();
    
    // Collect all input values
    const inputs = document.querySelectorAll('.info-input, .info-textarea');
    inputs.forEach(input => {
        if(input.name) {
            formData.append(input.name, input.value);
        }
    });
    
    // Send AJAX request
    fetch('<?= URLROOT ?>/organization/updateProfile', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Profile updated successfully!');
            
            // Disable inputs and hide action buttons
            const inputs = document.querySelectorAll('.info-input, .info-textarea');
            inputs.forEach(input => input.disabled = true);
            
            document.querySelector('.edit-btn').style.display = 'block';
            document.getElementById('actionButtons').style.display = 'none';
            
            // Update header name if changed
            const newOrgName = document.getElementById('inputOrgName').value;
            document.getElementById('orgName').textContent = newOrgName;
            document.getElementById('orgInitial').textContent = newOrgName.charAt(0).toUpperCase();
        } else {
            alert('Failed to update profile: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating profile');
    });
}

// Load statistics on page load
document.addEventListener('DOMContentLoaded', function() {
    fetch('<?= URLROOT ?>/organization/getStats')
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            document.getElementById('totalProjects').textContent = data.data.total_projects;
            document.getElementById('activeProjects').textContent = data.data.active_projects;
            document.getElementById('totalApplications').textContent = data.data.total_applications;
            document.getElementById('totalMembers').textContent = data.data.total_members;
        }
    })
    .catch(error => console.error('Error loading stats:', error));
});
</script>

<script src="<?= URLROOT ?>/assets/js/organizations.js" defer></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>