<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/adminsidebar.php'; ?>

<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/communitycreate.css">

<div class="create-container">
    <div class="create-header">
        <h1 class="create-title">Edit Community</h1>
        <a href="<?php echo URLROOT; ?>/community" class="btn btn-secondary">
            ← Back to Dashboard
        </a>
    </div>

    <form id="editCommunityForm" class="community-form">
        <input type="hidden" id="communityId" value="<?php echo $data['community']->id; ?>">
        
        <!-- Community Name -->
        <div class="form-group">
            <label for="communityName">Community Name *</label>
            <input 
                type="text" 
                id="communityName" 
                name="name" 
                class="form-input"
                value="<?php echo htmlspecialchars($data['community']->name); ?>"
                placeholder="Enter community name"
                required
            >
            <span class="error-message" id="nameError"></span>
        </div>

        <!-- Category -->
        <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" name="category" class="form-input" required>
                <option value="">Select Category</option>
                <option value="technology" <?php echo $data['community']->category === 'technology' ? 'selected' : ''; ?>>Technology</option>
                <option value="education" <?php echo $data['community']->category === 'education' ? 'selected' : ''; ?>>Education</option>
                <option value="health" <?php echo $data['community']->category === 'health' ? 'selected' : ''; ?>>Health</option>
                <option value="lifestyle" <?php echo $data['community']->category === 'lifestyle' ? 'selected' : ''; ?>>Lifestyle</option>
                <option value="business" <?php echo $data['community']->category === 'business' ? 'selected' : ''; ?>>Business</option>
            </select>
            <span class="error-message" id="categoryError"></span>
        </div>

        <!-- Description -->
        <div class="form-group">
            <label for="description">Description *</label>
            <textarea 
                id="description" 
                name="description" 
                class="form-input"
                rows="5"
                placeholder="Describe your community..."
                required
            ><?php echo htmlspecialchars($data['community']->description); ?></textarea>
            <span class="error-message" id="descriptionError"></span>
        </div>

        <!-- Privacy -->
        <div class="form-group">
            <label>Privacy Setting</label>
            <div class="radio-group">
                <label class="radio-label">
                    <input 
                        type="radio" 
                        name="privacy" 
                        value="public" 
                        <?php echo $data['community']->privacy === 'public' ? 'checked' : ''; ?>
                    >
                    <span>Public - Anyone can join</span>
                </label>
                <label class="radio-label">
                    <input 
                        type="radio" 
                        name="privacy" 
                        value="private"
                        <?php echo $data['community']->privacy === 'private' ? 'checked' : ''; ?>
                    >
                    <span>Private - Requires approval</span>
                </label>
            </div>
        </div>

        <!-- Status -->
        <div class="form-group">
            <label>Status</label>
            <div class="radio-group">
                <label class="radio-label">
                    <input 
                        type="radio" 
                        name="status" 
                        value="active"
                        <?php echo $data['community']->status === 'active' ? 'checked' : ''; ?>
                    >
                    <span>Active</span>
                </label>
                <label class="radio-label">
                    <input 
                        type="radio" 
                        name="status" 
                        value="inactive"
                        <?php echo $data['community']->status === 'inactive' ? 'checked' : ''; ?>
                    >
                    <span>Inactive</span>
                </label>
            </div>
        </div>

        <!-- Community Rules (Optional) -->
        <div class="form-group">
            <label for="rules">Community Rules (Optional)</label>
            <div id="rulesContainer">
                <?php 
                $rules = json_decode($data['community']->rules, true);
                if (!empty($rules)) {
                    foreach ($rules as $rule) {
                        echo '<div class="rule-item">
                                <input type="text" class="form-input rule-input" value="' . htmlspecialchars($rule) . '" placeholder="Enter a rule">
                                <button type="button" class="btn-remove" onclick="removeRule(this)">×</button>
                              </div>';
                    }
                }
                ?>
            </div>
            <button type="button" class="btn btn-secondary" onclick="addRule()">+ Add Rule</button>
        </div>

        <!-- Tags (Optional) -->
        <div class="form-group">
            <label for="tags">Tags (Optional)</label>
            <input 
                type="text" 
                id="tags" 
                name="tags" 
                class="form-input"
                value="<?php 
                    $tags = json_decode($data['community']->tags, true);
                    echo is_array($tags) ? implode(', ', $tags) : '';
                ?>"
                placeholder="e.g., programming, web development, JavaScript"
            >
            <small class="form-help">Separate tags with commas</small>
        </div>

        <!-- Error Display -->
        <div id="formErrors" class="alert alert-error" style="display: none;"></div>

        <!-- Submit Button -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="submitBtn">
                Update Community
            </button>
            <a href="<?php echo URLROOT; ?>/community" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
    const URLROOT = '<?php echo URLROOT; ?>';
    const communityId = <?php echo $data['community']->id; ?>;
</script>
<script src="<?php echo URLROOT; ?>/assets/js/communityedit.js"></script>

<?php require_once '../app/views/layouts/footer_user.php'; ?>