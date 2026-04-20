<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/matches.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        <div class="matches-page">

            <div class="page-header">
                <h1>Your Matches</h1>
                <p>Discover people to learn with and teach</p>

                <div class="match-summary">
                    <span class="summary-item">
                        <strong><?= isset($data['matchStats']['total_count']) ? $data['matchStats']['total_count'] : count($data['allMatches'] ?? []); ?></strong> Total Matches
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

            <?php if (!empty($data['pendingRequests'])): ?>
            <div class="connection-requests-section">
                <h2 class="section-title">
                    Connection Requests
                    <span class="badge-count"><?= count($data['pendingRequests']); ?></span>
                </h2>

                <div class="requests-list">
                    <?php foreach ($data['pendingRequests'] as $request): ?>
                        <?php
                            $requestAvatar = $request['sender_avatar'] ?? '';
                            $requestHasImage = is_string($requestAvatar) && strpos($requestAvatar, 'uploads/') === 0;
                        ?>
                        <div class="request-card">
                            <div class="request-avatar">
                                <?php if ($requestHasImage): ?>
                                    <img
                                        src="<?= URLROOT ?>/<?= htmlspecialchars($requestAvatar); ?>"
                                        alt="<?= htmlspecialchars($request['sender_name']); ?> profile picture"
                                        class="avatar-image"
                                    >
                                <?php else: ?>
                                    <?= htmlspecialchars($requestAvatar ?: strtoupper(substr($request['sender_name'] ?? 'U', 0, 2))); ?>
                                <?php endif; ?>
                            </div>

                            <div class="request-info">
                                <h3 class="request-name"><?= htmlspecialchars($request['sender_name']); ?></h3>

                                <p class="request-skills">
                                    <?php if (!empty($request['skill_offered'])): ?>
                                        <span class="skill-badge offer">
                                            Offers: <?= htmlspecialchars(ucwords(str_replace('-', ' ', $request['skill_offered']))); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($request['skill_wanted'])): ?>
                                        <span class="skill-badge want">
                                            Wants: <?= htmlspecialchars(ucwords(str_replace('-', ' ', $request['skill_wanted']))); ?>
                                        </span>
                                    <?php endif; ?>
                                </p>

                                <p class="request-time"><?= htmlspecialchars($request['time_ago']); ?></p>
                            </div>

                            <div class="request-actions">
                                <button class="btn-accept" onclick="handleRequest(<?= (int)$request['exchange_id']; ?>, 'accept')">Accept</button>
                                <button class="btn-reject" onclick="handleRequest(<?= (int)$request['exchange_id']; ?>, 'reject')">Reject</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="matches-filters">
                <div class="filter-group">
                    <label for="match-tier-filter">Match Type:</label>
                    <select id="match-tier-filter" class="filter-select">
                        <option value="all">All Matches</option>
                        <option value="mutual">Mutual Matches</option>
                        <option value="multi">Multi-Skill Matches</option>
                        <option value="single">Single Skill Matches</option>
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

            <?php if (!empty($data['allMatches'])): ?>
            <div class="match-tier-section" data-tier="all">
                <h2 class="tier-title">
                    ALL MATCHES
                    <span class="tier-count">(<?= count($data['allMatches']); ?> found)</span>
                </h2>
                <p class="tier-description">Browse all your potential learning and teaching partners</p>

                <div class="matches-grid">
                    <?php foreach ($data['allMatches'] as $match): ?>
                        <?php
                            $iTeach = !empty($match['i_teach']) && is_array($match['i_teach']) ? $match['i_teach'] : [];
                            $theyTeach = !empty($match['they_teach']) && is_array($match['they_teach']) ? $match['they_teach'] : [];
                            $matchType = $match['match_type'] ?? 'single';
                            $matchAvatar = $match['avatar'] ?? '';
                            $matchHasImage = is_string($matchAvatar) && strpos($matchAvatar, 'uploads/') === 0;
                            $chatSkill = '';
                            $chatDir = '';

                            if ($matchType === 'mutual') {
                                $chatSkill = $iTeach[0]['name'] ?? '';
                                $chatDir = $theyTeach[0]['name'] ?? '';
                            } elseif (!empty($iTeach)) {
                                $chatSkill = $iTeach[0]['name'] ?? '';
                                $chatDir = 'teacher';
                            } else {
                                $chatSkill = $theyTeach[0]['name'] ?? '';
                                $chatDir = 'learner';
                            }

                            $allSkillNames = array_merge(
                                array_column($iTeach, 'name'),
                                array_column($theyTeach, 'name')
                            );
                        ?>

                        <div class="match-card <?= htmlspecialchars(($match['match_type'] ?? 'single') . '-match'); ?>"
                             data-tier="<?= htmlspecialchars($match['match_type'] ?? 'single'); ?>"
                             data-match-type="<?= htmlspecialchars($match['match_type'] ?? 'single'); ?>"
                             data-skills="<?= htmlspecialchars(json_encode($allSkillNames)); ?>">

                            <div class="match-header">
                                <div class="match-avatar">
                                    <?php if ($matchHasImage): ?>
                                        <img
                                            src="<?= URLROOT ?>/<?= htmlspecialchars($matchAvatar); ?>"
                                            alt="<?= htmlspecialchars($match['name']); ?> profile picture"
                                            class="avatar-image"
                                        >
                                    <?php else: ?>
                                        <?= htmlspecialchars($matchAvatar ?: '??'); ?>
                                    <?php endif; ?>
                                </div>

                                <div class="match-basic-info">
                                    <h3 class="match-name" onclick="viewProfile(<?= (int)$match['id']; ?>, '<?= htmlspecialchars($matchType, ENT_QUOTES); ?>', '<?= htmlspecialchars($chatSkill, ENT_QUOTES); ?>', '<?= htmlspecialchars($chatDir, ENT_QUOTES); ?>')">
                                        <?= htmlspecialchars($match['name']); ?>
                                    </h3>

                                    <span class="match-type-badge <?= htmlspecialchars(($match['match_type'] ?? 'single') . '-badge'); ?>">
                                        <?= htmlspecialchars($match['match_type_label'] ?? 'Match'); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="match-skills-section">
                                <?php if (!empty($iTeach)): ?>
                                <div class="skill-direction">
                                    <div class="direction-header">
                                        <span class="direction-label">You Teach</span>
                                    </div>

                                    <?php foreach ($iTeach as $skill): ?>
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

                                <?php if (!empty($theyTeach)): ?>
                                <div class="skill-direction">
                                    <div class="direction-header">
                                        <span class="direction-label">You Learn</span>
                                    </div>

                                    <?php foreach ($theyTeach as $skill): ?>
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
                                    <?= !empty($match['total_skills']) ? (int)$match['total_skills'] : count($allSkillNames); ?>
                                    skill<?= ((!empty($match['total_skills']) ? (int)$match['total_skills'] : count($allSkillNames)) !== 1) ? 's' : ''; ?> matched
                                </span>

                                <?php if (isset($match['connection_status']) && $match['connection_status'] === 'pending'): ?>
                                    <span class="badge badge-warning">⏳ Pending</span>

                                <?php elseif (isset($match['connection_status']) && $match['connection_status'] === 'connected'): ?>
                                    <div class="connected-actions">
                                        <span class="badge badge-success">✓ Connected</span>
                                        <button type="button"
                                                class="btn-chat"
                                                onclick="openSkillChat(<?= (int)$match['id']; ?>, '<?= htmlspecialchars($matchType, ENT_QUOTES); ?>', '<?= htmlspecialchars($chatSkill, ENT_QUOTES); ?>', '<?= htmlspecialchars($chatDir, ENT_QUOTES); ?>')">
                                            Go to Session
                                        </button>
                                    </div>

                                <?php else: ?>
                                    <button type="button"
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
            <?php else: ?>
            <div class="no-matches-state">
                <div class="no-matches-icon"></div>
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
