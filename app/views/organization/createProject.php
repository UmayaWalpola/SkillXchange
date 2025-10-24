<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/organization_sidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/organizations.css">

<main class="site-main">
    <div class="form-container">
        
        <!-- Form Header -->
        <div class="form-header">
            <button class="back-btn" onclick="window.location.href='<?= URLROOT ?>/organization/projects'">
                ← Back to Projects
            </button>
            <h1><?= isset($data['project']) ? 'Edit Project' : 'Create New Project' ?></h1>
            <p>Fill in the details below to <?= isset($data['project']) ? 'update your' : 'create a new' ?> project</p>
        </div>

        <!-- Error Messages -->
        <?php if(!empty($data['errors'])): ?>
            <div class="alert alert-error">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach($data['errors'] as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Project Form -->
        <form method="POST" action="<?= isset($data['project']) ? URLROOT . '/organization/editProject/' . $data['project']->id : URLROOT . '/organization/createProject' ?>" class="project-form">
            
            <!-- Basic Information -->
            <div class="form-section">
                <h2 class="section-title">Basic Information</h2>
                
                <div class="form-group">
                    <label for="name">Project Name <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-input" 
                        placeholder="Enter project name"
                        value="<?= isset($data['project']) ? htmlspecialchars($data['project']->name) : '' ?>"
                        required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select id="category" name="category" class="form-select" required>
                            <option value="">Select category</option>
                            <option value="web" <?= (isset($data['project']) && $data['project']->category == 'web') ? 'selected' : '' ?>>Web Development</option>
                            <option value="mobile" <?= (isset($data['project']) && $data['project']->category == 'mobile') ? 'selected' : '' ?>>Mobile Development</option>
                            <option value="data" <?= (isset($data['project']) && $data['project']->category == 'data') ? 'selected' : '' ?>>Data Science</option>
                            <option value="design" <?= (isset($data['project']) && $data['project']->category == 'design') ? 'selected' : '' ?>>Design</option>
                            <option value="other" <?= (isset($data['project']) && $data['project']->category == 'other') ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="active" <?= (isset($data['project']) && $data['project']->status == 'active') ? 'selected' : '' ?>>Active</option>
                            <option value="in-progress" <?= (isset($data['project']) && $data['project']->status == 'in-progress') ? 'selected' : '' ?>>In Progress</option>
                            <option value="completed" <?= (isset($data['project']) && $data['project']->status == 'completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= (isset($data['project']) && $data['project']->status == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-textarea" 
                        rows="5" 
                        placeholder="Describe your project in detail..."
                        required><?= isset($data['project']) ? htmlspecialchars($data['project']->description) : '' ?></textarea>
                    <small class="form-hint">Provide a clear description of what the project aims to achieve</small>
                </div>
            </div>

            <!-- Team & Skills -->
            <div class="form-section">
                <h2 class="section-title">Team & Skills</h2>
                
                <div class="form-group">
                    <label for="max_members">Maximum Team Members <span class="required">*</span></label>
                    <input 
                        type="number" 
                        id="max_members" 
                        name="max_members" 
                        class="form-input" 
                        min="1" 
                        max="50"
                        placeholder="e.g., 5"
                        value="<?= isset($data['project']) ? $data['project']->max_members : '5' ?>"
                        required>
                    <small class="form-hint">Maximum number of team members for this project</small>
                </div>

                <div class="form-group">
                    <label for="required_skills">Required Skills <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="required_skills" 
                        name="required_skills" 
                        class="form-input" 
                        placeholder="e.g., JavaScript, React, Node.js, MongoDB"
                        value="<?= isset($data['project']) ? htmlspecialchars($data['project']->required_skills) : '' ?>"
                        required>
                    <small class="form-hint">Separate skills with commas</small>
                </div>
            </div>

            <!-- Timeline -->
            <div class="form-section">
                <h2 class="section-title">Project Timeline</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input 
                            type="date" 
                            id="start_date" 
                            name="start_date" 
                            class="form-input"
                            value="<?= isset($data['project']) ? $data['project']->start_date : '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input 
                            type="date" 
                            id="end_date" 
                            name="end_date" 
                            class="form-input"
                            value="<?= isset($data['project']) ? $data['project']->end_date : '' ?>">
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="window.location.href='<?= URLROOT ?>/organization/projects'">
                    Cancel
                </button>
                <button type="submit" class="btn-submit">
                    <?= isset($data['project']) ? '💾 Update Project' : '✨ Create Project' ?>
                </button>
            </div>
        </form>
    </div>
</main>

<style>
.form-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.form-header {
    margin-bottom: 30px;
}

.back-btn {
    background: none;
    border: none;
    color: #6366f1;
    font-size: 14px;
    cursor: pointer;
    padding: 8px 0;
    margin-bottom: 10px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.back-btn:hover {
    color: #4f46e5;
}

.form-header h1 {
    color: #1a1a1a;
    margin-bottom: 8px;
}

.form-header p {
    color: #666;
    font-size: 14px;
}

.alert {
    padding: 15px 20px;
    margin-bottom: 25px;
    border-radius: 8px;
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert ul {
    margin: 10px 0 0 20px;
}

.alert li {
    margin: 5px 0;
}

.project-form {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 30px;
}

.form-section {
    margin-bottom: 35px;
    padding-bottom: 35px;
    border-bottom: 1px solid #e5e7eb;
}

.form-section:last-of-type {
    border-bottom: none;
    margin-bottom: 25px;
}

.section-title {
    color: #1a1a1a;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-group {
    margin-bottom: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group label {
    display: block;
    color: #374151;
    font-weight: 500;
    margin-bottom: 8px;
    font-size: 14px;
}

.required {
    color: #dc3545;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    color: #1a1a1a;
    transition: all 0.2s;
    font-family: inherit;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-textarea {
    resize: vertical;
    min-height: 120px;
}

.form-hint {
    display: block;
    color: #6b7280;
    font-size: 12px;
    margin-top: 6px;
}

.form-actions {
    display: flex;
    gap: 15px;
    justify-content: flex-end;
    padding-top: 20px;
}

.btn-cancel,
.btn-submit {
    padding: 12px 30px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-cancel {
    background-color: #f3f4f6;
    color: #374151;
}

.btn-cancel:hover {
    background-color: #e5e7eb;
}

.btn-submit {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .btn-cancel,
    .btn-submit {
        width: 100%;
    }
}
</style>

<script>
// Get today's date in YYYY-MM-DD format
const today = new Date().toISOString().split('T')[0];

// Set minimum date for both date inputs to today
document.getElementById('start_date').setAttribute('min', today);
document.getElementById('end_date').setAttribute('min', today);

// Validate start date - cannot be in the past
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = this.value;
    const endDate = document.getElementById('end_date').value;
    
    // Check if start date is in the past
    if (startDate < today) {
        alert('Start date cannot be in the past');
        this.value = '';
        return;
    }
    
    // Check if end date is before start date
    if (endDate && endDate < startDate) {
        alert('End date cannot be before start date');
        document.getElementById('end_date').value = '';
    }
    
    // Update minimum end date to start date
    document.getElementById('end_date').setAttribute('min', startDate);
});

// Validate end date - cannot be in the past or before start date
document.getElementById('end_date').addEventListener('change', function() {
    const startDate = document.getElementById('start_date').value;
    const endDate = this.value;
    
    // Check if end date is in the past
    if (endDate < today) {
        alert('End date cannot be in the past');
        this.value = '';
        return;
    }
    
    // Check if end date is before start date
    if (startDate && endDate < startDate) {
        alert('End date cannot be before start date');
        this.value = '';
    }
});

// Form submission validation
document.querySelector('.project-form').addEventListener('submit', function(e) {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    // Validate start date if provided
    if (startDate && startDate < today) {
        e.preventDefault();
        alert('Start date cannot be in the past');
        return false;
    }
    
    // Validate end date if provided
    if (endDate && endDate < today) {
        e.preventDefault();
        alert('End date cannot be in the past');
        return false;
    }
    
    // Validate end date is after start date
    if (startDate && endDate && endDate < startDate) {
        e.preventDefault();
        alert('End date cannot be before start date');
        return false;
    }
});
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>