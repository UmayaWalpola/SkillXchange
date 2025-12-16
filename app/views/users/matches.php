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
                        <strong><?= $data['matchStats']['total_count']; ?></strong> Total Matches
                    </span>
                    <span class="summary-divider">•</span>
                    <span class="summary-item perfect">
                         <?= $data['matchStats']['perfect_count']; ?> Perfect
                    </span>
                    <span class="summary-divider">•</span>
                    <span class="summary-item great">
                         <?= $data['matchStats']['great_count']; ?> Great
                    </span>
                    <span class="summary-divider">•</span>
                    <span class="summary-item good">
                         <?= $data['matchStats']['good_count']; ?> Good
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
                    <label for="match-tier-filter">Match Quality:</label>
                    <select id="match-tier-filter" class="filter-select">
                        <option value="all">All Matches</option>
                        <option value="perfect"> Perfect (100%)</option>
                        <option value="great"> Great (75%)</option>
                        <option value="good"> Good (50%)</option>
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

            <!-- PERFECT MATCHES (100%) -->
            <?php if (!empty($data['perfectMatches'])): ?>
            <div class="match-tier-section" data-tier="perfect">
                <h2 class="tier-title">
                    PERFECT MATCHES (100%)
                    <span class="tier-count">(<?= count($data['perfectMatches']); ?> found)</span>
                </h2>
                <p class="tier-description">Mutual benefit - both can teach AND learn from each other</p>
                
                <div class="matches-grid">
                    <?php foreach ($data['perfectMatches'] as $match): ?>
                        <div class="match-card perfect-match" 
                             data-tier="perfect"
                             data-skills="<?= htmlspecialchars(json_encode(array_merge(
                                 array_column($match['i_teach'], 'name'),
                                 array_column($match['they_teach'], 'name')
                             ))); ?>">
                            <div class="match-badge-overlay">Perfect</div>
                            <div class="match-header">
                                <div class="match-avatar">
                                    <?= htmlspecialchars($match['avatar']); ?>
                                </div>
                                <div class="match-basic-info">
                                    <h3 class="match-name" onclick="viewProfile(<?= $match['id']; ?>)">
                                        <?= htmlspecialchars($match['name']); ?>
                                    </h3>
                                    <span class="match-type-badge mutual">⚡ Mutual Match</span>
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
    
    <?php if(isset($match['connection_status']) && $match['connection_status'] === 'pending'): ?>
        <span class="badge badge-warning">Request Pending</span>
    <?php elseif(isset($match['connection_status']) && $match['connection_status'] === 'connected'): ?>
        <span class="badge badge-success">✓ Connected</span>
    <?php else: ?>
        <button class="btn-connect" onclick="event.stopPropagation(); connectWithUser(<?= $match['id']; ?>, '<?= htmlspecialchars($match['name']); ?>')">
            Connect
        </button>
    <?php endif; ?>
</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- GREAT MATCHES (75%) -->
            <?php if (!empty($data['greatMatches'])): ?>
            <div class="match-tier-section" data-tier="great">
                <h2 class="tier-title">
                    GREAT MATCHES (75%)
                    <span class="tier-count">(<?= count($data['greatMatches']); ?> found)</span>
                </h2>
                <p class="tier-description">High compatibility - multiple skills or excellent proficiency match</p>
                
                <div class="matches-grid">
                    <?php foreach ($data['greatMatches'] as $match): ?>
                        <div class="match-card great-match" 
                             data-tier="great"
                             data-skills="<?= htmlspecialchars(json_encode(array_merge(
                                 array_column($match['i_teach'], 'name'),
                                 array_column($match['they_teach'], 'name')
                             ))); ?>">
                            <div class="match-badge-overlay"> Great</div>
                            <div class="match-header">
                                <div class="match-avatar">
                                    <?= htmlspecialchars($match['avatar']); ?>
                                </div>
                                <div class="match-basic-info">
                                    <h3 class="match-name" onclick="viewProfile(<?= $match['id']; ?>)">
                                        <?= htmlspecialchars($match['name']); ?>
                                    </h3>
                                    <?php if ($match['is_mutual']): ?>
                                        <span class="match-type-badge mutual">⚡ Mutual</span>
                                    <?php endif; ?>
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
    
    <?php if(isset($match['connection_status']) && $match['connection_status'] === 'pending'): ?>
        <span class="badge badge-warning">⏳ Request Pending</span>
    <?php elseif(isset($match['connection_status']) && $match['connection_status'] === 'connected'): ?>
        <span class="badge badge-success">✓ Connected</span>
    <?php else: ?>
        <button class="btn-connect" onclick="event.stopPropagation(); connectWithUser(<?= $match['id']; ?>, '<?= htmlspecialchars($match['name']); ?>')">
            Connect
        </button>
    <?php endif; ?>
</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- GOOD MATCHES (50%) -->
            <?php if (!empty($data['goodMatches'])): ?>
            <div class="match-tier-section" data-tier="good">
                <h2 class="tier-title">
                    GOOD MATCHES (50%)
                    <span class="tier-count">(<?= count($data['goodMatches']); ?> found)</span>
                </h2>
                <p class="tier-description">Solid connections - valuable learning opportunities</p>
                
                <div class="matches-grid">
                    <?php foreach ($data['goodMatches'] as $match): ?>
                        <div class="match-card good-match" 
                             data-tier="good"
                             data-skills="<?= htmlspecialchars(json_encode(array_merge(
                                 array_column($match['i_teach'], 'name'),
                                 array_column($match['they_teach'], 'name')
                             ))); ?>">
                            <div class="match-badge-overlay">Good</div>
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
         <?= $match['total_skills']; ?> skill<?= $match['total_skills'] > 1 ? 's' : ''; ?> matched
    </span>
    
    <?php if(isset($match['connection_status']) && $match['connection_status'] === 'pending'): ?>
        <span class="badge badge-warning"> Request Pending</span>
    <?php elseif(isset($match['connection_status']) && $match['connection_status'] === 'connected'): ?>
        <span class="badge badge-success">✓ Connected</span>
    <?php else: ?>
        <button class="btn-connect" onclick="event.stopPropagation(); connectWithUser(<?= $match['id']; ?>, '<?= htmlspecialchars($match['name']); ?>')">
            Connect
        </button>
    <?php endif; ?>
</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- No Matches State -->
            <?php if (empty($data['perfectMatches']) && empty($data['greatMatches']) && empty($data['goodMatches'])): ?>
            <div class="no-matches-state">
                <h2>No matches found yet</h2>
                <p>We're looking for people who match your skills!</p>
                <small>Check back later or update your profile to see more matches.</small>
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