<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/matches.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        
        <div class="matches-page">
            <!-- Page Header -->
            <div class="page-header">
                <h1>Your Matches</h1>
                <p>Discover people to learn with and teach</p>
                <div class="match-summary">
                    <span class="summary-item">
                        <strong><?= isset($data['matchStats']['total_count']) ? $data['matchStats']['total_count'] : 0; ?></strong> Total Matches
                    </span>
                    <span class="summary-divider">•</span>
                    <span class="summary-item mutual">
                         <?= isset($data['matchStats']['mutual_count']) ? $data['matchStats']['mutual_count'] : 0; ?> Mutual
                    </span>
                    <span class="summary-divider">•</span>
                    <span class="summary-item multi">
                         <?= isset($data['matchStats']['multi_count']) ? $data['matchStats']['multi_count'] : 0; ?> Multi-Skill
                    </span>
                    <span class="summary-divider">•</span>
                    <span class="summary-item single">
                         <?= isset($data['matchStats']['single_count']) ? $data['matchStats']['single_count'] : 0; ?> Single
                    </span>
                </div>
            </div>

            <!-- CONNECTION REQUESTS SECTION -->
            <?php if (!empty($data['pendingRequests'])): ?>
            <div class="connection-requests-section">
                <h2 class="section-title">
                    Connection Requests
                    <span class="badge-count"><?= count($data['pendingRequests']); ?></span>
                </h2>
                
                <div class="requests-list">
                    <?php foreach ($data['pendingRequests'] as $request): ?>
                        <div class="request-card">
                            <div class="request-avatar">
                                <?= htmlspecialchars($request['sender_avatar']); ?>
                            </div>
                            <div class="request-info">
                                <h3 class="request-name"><?= htmlspecialchars($request['sender_name']); ?></h3>
                                <p class="request-skills">
                                    <?php if ($request['skill_offered']): ?>
                                        <span class="skill-badge offer">Offers: <?= htmlspecialchars(ucwords(str_replace('-', ' ', $request['skill_offered']))); ?></span>
                                    <?php endif; ?>
                                    <?php if ($request['skill_wanted']): ?>
                                        <span class="skill-badge want">Wants: <?= htmlspecialchars(ucwords(str_replace('-', ' ', $request['skill_wanted']))); ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="request-time"><?= htmlspecialchars($request['time_ago']); ?></p>
                            </div>
                            <div class="request-actions">
                                <button class="btn-accept" onclick="handleRequest(<?= $request['exchange_id']; ?>, 'accept')">
                                    Accept
                                </button>
                                <button class="btn-reject" onclick="handleRequest(<?= $request['exchange_id']; ?>, 'reject')">
                                    Reject
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filters Section -->
            <div class="matches-filters">
                <div class="filter-group">
                    <label for="match-tier-filter">Match Type:</label>
                    <select id="match-tier-filter" class="filter-select">
                        <option value="all">All Matches</option>
                        <option value="mutual"> Mutual Matches</option>
                        <option value="multi"> Multi-Skill</option>
                        <option value="single"> Single Skill</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="skill-filter">Filter by Skill:</label>
                    <select id="skill-filter" class="filter-select">
                        <option value="all">All Skills</option>
                        <optgroup label="Skills I Teach">
                            <?php if (!empty($data['userSkills']['teaches'])): ?>
                                <?php foreach ($data['userSkills']['teaches'] as $skill): ?>
                                    <option value="<?= htmlspecialchars($skill['name']); ?>">
                                        <?= htmlspecialchars($skill['display']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </optgroup>
                        <optgroup label="Skills I Want to Learn">
                            <?php if (!empty($data['userSkills']['learns'])): ?>
                                <?php foreach ($data['userSkills']['learns'] as $skill): ?>
                                    <option value="<?= htmlspecialchars($skill['name']); ?>">
                                        <?= htmlspecialchars($skill['display']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </optgroup>
                    </select>
                </div>

                <button class="btn-clear-filters" onclick="clearFilters()">Clear Filters</button>
            </div>

            <!-- MUTUAL MATCHES -->
            <?php if (!empty($data['mutual'])): ?>
            <div class="match-tier-section" data-tier="mutual">
                <h2 class="tier-title">
                     MUTUAL MATCHES
                    <span class="tier-count">(<?= count($data['mutual']); ?> found)</span>
                </h2>
                <p class="tier-description">You can both teach AND learn from each other - the best connections!</p>
                
                <div class="matches-grid">
                    <?php foreach ($data['mutual'] as $match): ?>
                        <div class="match-card mutual-match" 
                             data-tier="mutual"
                             data-skills="<?= htmlspecialchars(json_encode(array_merge(
                                 array_column($match['i_teach'], 'name'),
                                 array_column($match['they_teach'], 'name')
                             ))); ?>">
                            <div class="match-badge-overlay">Mutual</div>
                            <div class="match-header">
                                <div class="match-avatar">
                                    <?= htmlspecialchars($match['avatar']); ?>
                                </div>
                                <div class="match-basic-info">
                                    <h3 class="match-name" onclick="viewProfile(<?= $match['id']; ?>)">
                                        <?= htmlspecialchars($match['name']); ?>
                                    </h3>
                                    <span class="match-type-badge mutual-badge">⚡ Mutual Exchange</span>
                                </div>
                            </div>
                            
                            <div class="match-skills-section">
                                <?php if (!empty($match['i_teach'])): ?>
                                <div class="skill-direction">
                                    <div class="direction-header">
                                        <span class="direction-label">You Teach</span>
                                    </div>
                                    <?php foreach ($match['i_teach'] as $skill): ?>
                                        <div class="skill-item teach">
                                            <span class="skill-name"><?= htmlspecialchars($skill['display_name']); ?></span>
                                            <div class="skill-levels">
                                                <span class="level-badge your-level"><?= htmlspecialchars($skill['my_level']); ?></span>
                                                <span class="level-arrow">→</span>
                                                <span class="level-badge their-level"><?= htmlspecialchars($skill['their_level']); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($match['they_teach'])): ?>
                                <div class="skill-direction">
                                    <div class="direction-header">
                                        <span class="direction-label">You Learn</span>
                                    </div>
                                    <?php foreach ($match['they_teach'] as $skill): ?>
                                        <div class="skill-item learn">
                                            <span class="skill-name"><?= htmlspecialchars($skill['display_name']); ?></span>
                                            <div class="skill-levels">
                                                <span class="level-badge their-level"><?= htmlspecialchars($skill['their_level']); ?></span>
                                                <span class="level-arrow">→</span>
                                                <span class="level-badge your-level"><?= htmlspecialchars($skill['my_level']); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="match-footer">
                                <span class="compatibility-score">
                                    <?= $match['total_skills']; ?> skill<?= $match['total_skills'] > 1 ? 's' : ''; ?> matched
                                </span>
                                
                                <?php if (isset($match['connection_status']) && $match['connection_status'] === 'pending'): ?>
    <span class="badge badge-warning"> Pending</span>

<?php elseif (isset($match['connection_status']) && $match['connection_status'] === 'connected'): ?>
    <div class="connected-actions">
        <span class="badge badge-success">✓ Connected</span>
        <button
            type="button"
            class="btn-chat"
            onclick="openSkillChat(<?= (int)$match['id']; ?>)">
            Go to Chat
        </button>
    </div>

<?php else: ?>
    <button
        type="button"
        class="btn-connect"
        onclick="return connectWithUser(<?= (int)$match['id']; ?>, '<?= htmlspecialchars($match['name'], ENT_QUOTES); ?>', event)">
        Connect
    </button>
<?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            

            <!-- MULTI-SKILL MATCHES -->
            <?php if (!empty($data['multi'])): ?>
            <div class="match-tier-section" data-tier="multi">
                <h2 class="tier-title">
                    ⭐ MULTI-SKILL MATCHES
                    <span class="tier-count">(<?= count($data['multi']); ?> found)</span>
                </h2>
                <p class="tier-description">Multiple skills in common - great learning potential</p>
                
                <div class="matches-grid">
                    <?php foreach ($data['multi'] as $match): ?>
                        <div class="match-card multi-match" 
                             data-tier="multi"
                             data-skills="<?= htmlspecialchars(json_encode(array_merge(
                                 array_column($match['i_teach'], 'name'),
                                 array_column($match['they_teach'], 'name')
                             ))); ?>">
                            <div class="match-badge-overlay">⭐ Multi</div>
                            <div class="match-header">
                                <div class="match-avatar">
                                    <?= htmlspecialchars($match['avatar']); ?>
                                </div>
                                <div class="match-basic-info">
                                    <h3 class="match-name" onclick="viewProfile(<?= $match['id']; ?>)">
                                        <?= htmlspecialchars($match['name']); ?>
                                    </h3>
                                </div>
                            </div>
                            
                            <div class="match-skills-section">
                                <?php if (!empty($match['i_teach'])): ?>
                                <div class="skill-direction">
                                    <div class="direction-header">
                                        <span class="direction-label">You Teach</span>
                                    </div>
                                    <?php foreach ($match['i_teach'] as $skill): ?>
                                        <div class="skill-item teach">
                                            <span class="skill-name"><?= htmlspecialchars($skill['display_name']); ?></span>
                                            <div class="skill-levels">
                                                <span class="level-badge your-level"><?= htmlspecialchars($skill['my_level']); ?></span>
                                                <span class="level-arrow">→</span>
                                                <span class="level-badge their-level"><?= htmlspecialchars($skill['their_level']); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($match['they_teach'])): ?>
                                <div class="skill-direction">
                                    <div class="direction-header">
                                        <span class="direction-label">You Learn</span>
                                    </div>
                                    <?php foreach ($match['they_teach'] as $skill): ?>
                                        <div class="skill-item learn">
                                            <span class="skill-name"><?= htmlspecialchars($skill['display_name']); ?></span>
                                            <div class="skill-levels">
                                                <span class="level-badge their-level"><?= htmlspecialchars($skill['their_level']); ?></span>
                                                <span class="level-arrow">→</span>
                                                <span class="level-badge your-level"><?= htmlspecialchars($skill['my_level']); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="match-footer">
                                <span class="compatibility-score">
                                    <?= $match['total_skills']; ?> skills matched
                                </span>
                                
                                <?php if (isset($match['connection_status']) && $match['connection_status'] === 'pending'): ?>
    <span class="badge badge-warning"> Pending</span>

<?php elseif (isset($match['connection_status']) && $match['connection_status'] === 'connected'): ?>
    <div class="connected-actions">
        <span class="badge badge-success">✓ Connected</span>
        <button
            type="button"
            class="btn-chat"
            onclick="openSkillChat(<?= (int)$match['id']; ?>)">
            Go to Chat
        </button>
    </div>

<?php else: ?>
    <button
        type="button"
        class="btn-connect"
        onclick="return connectWithUser(<?= (int)$match['id']; ?>, '<?= htmlspecialchars($match['name'], ENT_QUOTES); ?>', event)">
        Connect
    </button>
<?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- SINGLE SKILL MATCHES -->
            <?php if (!empty($data['single'])): ?>
            <div class="match-tier-section" data-tier="single">
                <h2 class="tier-title">
                    ✓ MATCHES
                    <span class="tier-count">(<?= count($data['single']); ?> found)</span>
                </h2>
                <p class="tier-description">One skill to learn or teach</p>
                
                <div class="matches-grid">
                    <?php foreach ($data['single'] as $match): ?>
                        <div class="match-card single-match" 
                             data-tier="single"
                             data-skills="<?= htmlspecialchars(json_encode(array_merge(
                                 array_column($match['i_teach'], 'name'),
                                 array_column($match['they_teach'], 'name')
                             ))); ?>">
                            <div class="match-header">
                                <div class="match-avatar">
                                    <?= htmlspecialchars($match['avatar']); ?>
                                </div>
                                <div class="match-basic-info">
                                    <h3 class="match-name" onclick="viewProfile(<?= $match['id']; ?>)">
                                        <?= htmlspecialchars($match['name']); ?>
                                    </h3>
                                </div>
                            </div>
                            
                            <div class="match-skills-section">
                                <?php if (!empty($match['i_teach'])): ?>
                                <div class="skill-direction">
                                    <div class="direction-header">
                                        <span class="direction-label">You Teach</span>
                                    </div>
                                    <?php foreach ($match['i_teach'] as $skill): ?>
                                        <div class="skill-item teach">
                                            <span class="skill-name"><?= htmlspecialchars($skill['display_name']); ?></span>
                                            <div class="skill-levels">
                                                <span class="level-badge your-level"><?= htmlspecialchars($skill['my_level']); ?></span>
                                                <span class="level-arrow">→</span>
                                                <span class="level-badge their-level"><?= htmlspecialchars($skill['their_level']); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($match['they_teach'])): ?>
                                <div class="skill-direction">
                                    <div class="direction-header">
                                        <span class="direction-label">You Learn</span>
                                    </div>
                                    <?php foreach ($match['they_teach'] as $skill): ?>
                                        <div class="skill-item learn">
                                            <span class="skill-name"><?= htmlspecialchars($skill['display_name']); ?></span>
                                            <div class="skill-levels">
                                                <span class="level-badge their-level"><?= htmlspecialchars($skill['their_level']); ?></span>
                                                <span class="level-arrow">→</span>
                                                <span class="level-badge your-level"><?= htmlspecialchars($skill['my_level']); ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="match-footer">
                                <span class="compatibility-score">
                                    1 skill matched
                                </span>
                                
                               <?php if (isset($match['connection_status']) && $match['connection_status'] === 'pending'): ?>
    <span class="badge badge-warning"> Pending</span>

<?php elseif (isset($match['connection_status']) && $match['connection_status'] === 'connected'): ?>
    <div class="connected-actions">
        <span class="badge badge-success">✓ Connected</span>
        <button
            type="button"
            class="btn-chat"
            onclick="openSkillChat(<?= (int)$match['id']; ?>)">
            Go to Chat
        </button>
    </div>

<?php else: ?>
    <button
        type="button"
        class="btn-connect"
        onclick="return connectWithUser(<?= (int)$match['id']; ?>, '<?= htmlspecialchars($match['name'], ENT_QUOTES); ?>', event)">
        Connect
    </button>
<?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- NO MATCHES MESSAGE -->
            <?php if (empty($data['mutual']) && empty($data['multi']) && empty($data['single'])): ?>
            <div class="no-matches-state">
                <div class="no-matches-icon">🔍</div>
                <h2>No matches found yet</h2>
                <p>Try adding more skills to your profile to find compatible learning partners!</p>
                <a href="<?= URLROOT ?>/users/profile" class="btn-primary">Update Your Skills</a>
            </div>
            <?php endif; ?>

        </div>

    </div>
</div>
</main>

<script>
    const URLROOT = '<?= URLROOT ?>';
</script>
<script src="<?= URLROOT ?>/assets/js/matches.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>