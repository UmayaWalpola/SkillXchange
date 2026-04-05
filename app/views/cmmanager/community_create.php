<?php require_once '../app/views/layouts/header_user.php'; ?>
<?php require_once '../app/views/layouts/commanagersidebar.php'; ?>

<link rel="stylesheet" href="<?php echo URLROOT; ?>/assets/css/communitycreate.css">

<div class="dashboard-container">
    <div class="builder-container">

        <!-- Header -->
        <div class="builder-header">
            <a href="<?php echo URLROOT; ?>/community" class="btn-back">Back to Dashboard</a>
            <h1 class="builder-title">Create a Community</h1>
            <p class="builder-subtitle">Build your space and connect people around shared skills</p>
        </div>

        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step active">
                <div class="step-dot">1</div>
                <span class="step-label">Basics</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-dot">2</div>
                <span class="step-label">Rules</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-dot">3</div>
                <span class="step-label">Tags</span>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-dot">4</div>
                <span class="step-label">Publish</span>
            </div>
        </div>

        <!-- Section 1: Basic Info -->
        <div class="section-card">
            <div class="section-title-row">
                <div class="section-number">1</div>
                <div>
                    <div class="section-title">Community Information</div>
                </div>
            </div>

            <div class="form-group">
                <label for="communityName">Community Name</label>
                <input
                    type="text"
                    id="communityName"
                    placeholder="e.g. Web Development Enthusiasts"
                    maxlength="100"
                >
                <span class="error-text" id="nameError"></span>
            </div>

            <div class="form-group">
                <label>Privacy Setting</label>
                <div class="privacy-options">
                    <label class="privacy-option">
                        <input type="radio" name="privacy" value="public" checked>
                        <div class="privacy-card">
                            <div class="privacy-icon">🌐</div>
                            <div class="privacy-name">Public</div>
                            <div class="privacy-desc">Anyone can join</div>
                        </div>
                    </label>
                    <label class="privacy-option">
                        <input type="radio" name="privacy" value="private">
                        <div class="privacy-card">
                            <div class="privacy-icon">🔒</div>
                            <div class="privacy-name">Private</div>
                            <div class="privacy-desc">Approval required</div>
                        </div>
                    </label>
                </div>
                <!-- Hidden select kept for JS compatibility -->
                <select id="communityPrivacy" style="display:none">
                    <option value="public">Public</option>
                    <option value="private">Private</option>
                </select>
            </div>

            <div class="form-group">
                <label for="communityDescription">Description</label>
                <textarea
                    id="communityDescription"
                    placeholder="Describe your community — what it's about, who it's for, and what members can expect..."
                    rows="4"
                    maxlength="1000"
                ></textarea>
                <span class="error-text" id="descriptionError"></span>
                <span class="char-count"><span id="descCharCount">0</span> / 1000</span>
            </div>
        </div>

        <!-- Section 2: Rules -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-title-row" style="margin-bottom:0">
                    <div class="section-number">2</div>
                    <div>
                        <div class="section-title">Community Rules &nbsp;<span style="font-size:13px;font-weight:400;color:var(--text-3)" id="ruleCount">0 / 10</span></div>
                    </div>
                </div>
                <button class="btn-add-rule" onclick="addRule()" id="addRuleBtn">
                    + Add Rule
                </button>
            </div>

            <div id="rulesContainer"></div>

            <div id="noRulesMessage" class="rules-empty" style="display:block">
                <span>📋</span>
                No rules yet — add some guidelines to keep your community healthy
            </div>
        </div>

        <!-- Section 3: Tags -->
        <div class="section-card">
            <div class="section-title-row">
                <div class="section-number">3</div>
                <div>
                    <div class="section-title">Tags <span style="font-size:13px;font-weight:400;color:var(--text-3)">(optional)</span></div>
                </div>
            </div>
            <p class="section-subtitle">Help people discover your community with relevant keywords</p>

            <div class="form-group">
                <label for="tagInput">Add a tag</label>
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

        <!-- Action Bar -->
        <div class="action-bar">
            <div class="action-bar-left">
                <button class="btn btn-ghost" onclick="saveDraft()">Save Draft</button>
            </div>
            <button class="btn btn-secondary" onclick="previewCommunity()">Preview</button>
            <button class="btn btn-primary" onclick="publishCommunity()">
                Create Community →
            </button>
        </div>

    </div>
</div>

<!-- Rule Modal -->
<div id="ruleModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="ruleModalTitle">Add Rule</h2>
            <span class="close" onclick="closeRuleModal()">×</span>
        </div>

        <form id="ruleForm" onsubmit="saveRule(event)">
            <input type="hidden" id="editingRuleIndex" value="-1">

            <div class="form-group">
                <label for="ruleTitle">Rule Title</label>
                <input
                    type="text"
                    id="ruleTitle"
                    placeholder="e.g. Be respectful to others"
                    required
                    maxlength="100"
                >
            </div>

            <div class="form-group">
                <label for="ruleDescription">Rule Description</label>
                <textarea
                    id="ruleDescription"
                    placeholder="Explain what this rule means and why it matters..."
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

<script>
    const URLROOT = '<?php echo URLROOT; ?>';

    // Sync privacy radio buttons to hidden select
    document.querySelectorAll('input[name="privacy"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.getElementById('communityPrivacy').value = this.value;
        });
    });
</script>
<script src="<?php echo URLROOT; ?>/assets/js/communitycreate.js"></script>

</body>
</html>