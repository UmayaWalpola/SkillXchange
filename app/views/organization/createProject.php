<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="form-container">
        
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1><?= isset($data['project']) ? 'Edit Project' : 'Create New Project' ?></h1>
                <p>Fill in the details to create a new project for your organization</p>
            </div>
        </div>

        <!-- Project Form -->
        <form id="projectForm" method="POST" action="<?= URLROOT ?>/organization/<?= isset($data['project']) ? 'updateProject' : 'createProject' ?>" class="project-form">
            
            <?php if(isset($data['project'])): ?>
                <input type="hidden" name="project_id" value="<?= $data['project']->id ?>">
            <?php endif; ?>

            <!-- Basic Information Section -->
            <div class="form-section">
                <h2 class="form-section-title">Basic Information</h2>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label for="projectName">Project Name *</label>
                        <input 
                            type="text" 
                            id="projectName" 
                            name="name" 
                            class="form-input" 
                            placeholder="Enter project name"
                            value="<?= isset($data['project']) ? htmlspecialchars($data['project']->name) : '' ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">Select a category</option>
                            <option value="web" <?= (isset($data['project']) && $data['project']->category == 'web') ? 'selected' : '' ?>>Web Development</option>
                            <option value="mobile" <?= (isset($data['project']) && $data['project']->category == 'mobile') ? 'selected' : '' ?>>Mobile Development</option>
                            <option value="data" <?= (isset($data['project']) && $data['project']->category == 'data') ? 'selected' : '' ?>>Data Science</option>
                            <option value="design" <?= (isset($data['project']) && $data['project']->category == 'design') ? 'selected' : '' ?>>Design</option>
                            <option value="other" <?= (isset($data['project']) && $data['project']->category == 'other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="active" <?= (isset($data['project']) && $data['project']->status == 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="in-progress" <?= (isset($data['project']) && $data['project']->status == 'in-progress') ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= (isset($data['project']) && $data['project']->status == 'completed') ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Description *</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            class="form-textarea" 
                            rows="5" 
                            placeholder="Describe your project in detail..."
                            required><?= isset($data['project']) ? htmlspecialchars($data['project']->description) : '' ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Project Details Section -->
            <div class="form-section">
                <h2 class="form-section-title">Project Details</h2>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="maxMembers">Maximum Members *</label>
                        <input 
                            type="number" 
                            id="maxMembers" 
                            name="max_members" 
                            class="form-input" 
                            min="1" 
                            max="50"
                            placeholder="e.g., 5"
                            value="<?= isset($data['project']) ? $data['project']->max_members : '5' ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="startDate">Start Date</label>
                        <input 
                            type="date" 
                            id="startDate" 
                            name="start_date" 
                            class="form-input"
                            value="<?= isset($data['project']) ? $data['project']->start_date : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="endDate">End Date</label>
                        <input 
                            type="date" 
                            id="endDate" 
                            name="end_date" 
                            class="form-input"
                            value="<?= isset($data['project']) ? $data['project']->end_date : '' ?>">
                    </div>

                    <div class="form-group full-width">
                        <label for="requiredSkills">Required Skills *</label>
                        <input 
                            type="text" 
                            id="requiredSkills" 
                            name="required_skills" 
                            class="form-input" 
                            placeholder="e.g., PHP, JavaScript, React, MySQL (comma-separated)"
                            value="<?= isset($data['project']) ? htmlspecialchars($data['project']->required_skills) : '' ?>"
                            required>
                        <small class="form-hint">Separate skills with commas</small>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="window.history.back()">Cancel</button>
                <button type="submit" class="btn-submit">
                    <?= isset($data['project']) ? 'Update Project' : 'Create Project' ?>
                </button>
            </div>

        </form>
    </div>
</main>

<script src="<?= URLROOT ?>/assets/js/organizations.js" defer></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>