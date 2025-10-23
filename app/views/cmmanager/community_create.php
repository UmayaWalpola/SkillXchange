<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/adminsidebar.php'; ?>

<!-- Link CSS -->
<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/communitycreate.css">

<div class="dashboard-container">
    <div class="builder-container">
        <!-- Header -->
        <div class="builder-header">
            <a href="<?php echo URLROOT; ?>/community" class="btn-back">← Back to Dashboard</a>
            <h1 class="builder-title">Create New Community</h1>
            <p class="builder-subtitle">Build your community and connect people</p>
        </div>

        <!-- Community Basic Information -->
        <div class="section-card">
            <h2 class="section-title">Community Information</h2>
            
            <div class="form-group">
                <label for="communityName">Community Name *</label>
                <input 
                    type="text" 
                    id="communityName" 
                    placeholder="Enter community name"
                    maxlength="100"
                >
                <span class="error-text" id="nameError"></span>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="communityCategory">Category *</label>
                    <select id="communityCategory">
                        <option value="">Select category</option>
                        <option value="technology">Technology</option>
                        <option value="education">Education</option>
                        <option value="health">Health & Fitness</option>
                        <option value="lifestyle">Lifestyle</option>
                        <option value="business">Business</option>
                        <option value="entertainment">Entertainment</option>
                        <option value="sports">Sports</option>
                        <option value="other">Other</option>
                    </select>
                    <span class="error-text" id="categoryError"></span>
                </div>

                <div class="form-group">
                    <label for="communityPrivacy">Privacy Setting *</label>
                    <select id="communityPrivacy">
                        <option value="public">Public - Anyone can join</option>
                        <option value="private">Private - Approval required</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="communityDescription">Description *</label>
                <textarea 
                    id="communityDescription" 
                    placeholder="Describe your community, its purpose, and what members can expect..."
                    rows="4"
                    maxlength="1000"
                ></textarea>
                <span class="error-text" id="descriptionError"></span>
                <span class="char-count"><span id="descCharCount">0</span>/1000</span>
            </div>
        </div>

        <!-- Community Rules & Guidelines -->
        <div class="section-card">
            <div class="section-header">
                <h2 class="section-title">Community Rules (<span id="ruleCount">0</span>/10)</h2>
                <button class="btn btn-secondary" onclick="addRule()" id="addRuleBtn">
                    + Add Rule
                </button>
            </div>

            <div id="rulesContainer">
                <!-- Rules will be added here dynamically -->
            </div>

            <div id="noRulesMessage" class="no-items">
                <p>No rules added yet. Click "Add Rule" to create community guidelines.</p>
            </div>
        </div>

        <!-- Community Tags -->
        <div class="section-card">
            <h2 class="section-title">Tags (Optional)</h2>
            <p class="section-subtitle">Add relevant tags to help users discover your community</p>
            
            <div class="form-group">
                <div class="tag-input-container">
                    <input 
                        type="text" 
                        id="tagInput" 
                        placeholder="Type a tag and press Enter"
                        maxlength="30"
                        onkeypress="handleTagInput(event)"
                    >
                </div>
                <div id="tagsContainer" class="tags-display"></div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-bar">
            <button class="btn btn-secondary" onclick="saveDraft()">
                Save as Draft
            </button>
            <button class="btn btn-secondary" onclick="previewCommunity()">
                Preview
            </button>
            <button class="btn btn-primary" onclick="publishCommunity()">
                Create Community
            </button>
        </div>
    </div>
</div>

<!-- Rule Modal -->
<div id="ruleModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeRuleModal()">&times;</span>
        <h2 id="ruleModalTitle">Add Rule</h2>
        
        <form id="ruleForm" onsubmit="saveRule(event)">
            <input type="hidden" id="editingRuleIndex" value="-1">
            
            <div class="form-group">
                <label for="ruleTitle">Rule Title *</label>
                <input 
                    type="text" 
                    id="ruleTitle" 
                    placeholder="e.g., Be respectful"
                    required
                    maxlength="100"
                >
            </div>

            <div class="form-group">
                <label for="ruleDescription">Rule Description *</label>
                <textarea 
                    id="ruleDescription" 
                    placeholder="Explain the rule in detail..."
                    rows="4"
                    required
                    maxlength="500"
                ></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="closeRuleModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Rule</button>
            </div>
        </form>
    </div>
</div>

<!-- Link JavaScript -->
<script>
    // Pass URLROOT to JavaScript
    const URLROOT = '<?php echo URLROOT; ?>';
</script>
<script src="<?php echo URLROOT; ?>/assets/js/communitycreate.js"></script>

</body>
</html>