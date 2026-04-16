<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/projects.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/discover.css">

<?php
    $memberProjectsList = isset($memberProjects) ? $memberProjects : [];
    $allProjectsList    = isset($projects) ? $projects : [];
    $memberProjectIds   = isset($memberProjectIds) ? $memberProjectIds : [];
    $appStatus          = isset($applicationStatusByProject) ? $applicationStatusByProject : [];
    $userTeachSkillsList = isset($userTeachSkills) ? $userTeachSkills : [];

    // Keep project cards within the existing SkillXchange blue palette.
    $gradients = [
        'linear-gradient(135deg,var(--primary-blue) 0%,var(--accent-blue) 100%)',
        'linear-gradient(135deg,#4f6b7d 0%,var(--primary-blue) 100%)',
        'linear-gradient(135deg,#6f93a8 0%,var(--accent-blue) 100%)',
        'linear-gradient(135deg,#7f9fb0 0%,#d5eaf6 100%)',
        'linear-gradient(135deg,#5c7d8f 0%,#aacdde 100%)',
        'linear-gradient(135deg,#708f9f 0%,#c6dfec 100%)',
    ];
    function getGradient($id, $gradients) {
        return $gradients[$id % count($gradients)];
    }
    function statusIcon($status) {
        switch (strtolower(str_replace('_','-',$status))) {
            case 'active':       return '🟢';
            case 'in-progress':  return '🟡';
            case 'completed':    return '🔵';
            default:             return '⚪';
        }
    }
    function statusLabel($status) {
        return ucfirst(str_replace(['-','_'],' ',$status));
    }
?>

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">

        <!-- ═══════════════════════════════════════════════════════════
             PAGE HEADER — matches existing projects-header style
        ════════════════════════════════════════════════════════════ -->
        <div class="disc-page-header">
            <div class="disc-page-header__text">
                <h1>Projects</h1>
                <p>Your active memberships & all available projects — discover and join from one place.</p>
            </div>
            <a href="<?= URLROOT ?>/userdashboard/projects" class="disc-btn-outline" style="text-decoration:none;">
                <i class="ph ph-layout"></i> Dashboard
            </a>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             SECTION 1 — YOUR PROJECTS (MEMBER)
        ════════════════════════════════════════════════════════════ -->
        <section class="disc-section" id="yourProjectsSection">
            <div class="disc-section-label">
                <span class="disc-section-label__dot dot-green"></span>
                <span>You're a Member</span>
                <span class="disc-count-pill"><?= count($memberProjectsList) ?></span>
            </div>

            <?php if (!empty($memberProjectsList)): ?>
            <div class="disc-member-grid">
                <?php foreach ($memberProjectsList as $idx => $proj): ?>
                <?php
                    if (is_array($proj)) $proj = (object)$proj;
                    $pid      = (int)($proj->id ?? 0);
                    $stClass  = strtolower(str_replace('_','-',$proj->status ?? 'active'));
                    $skills   = array_filter(array_map('trim', explode(',', (string)($proj->required_skills ?? ''))));
                    $g        = getGradient($pid, $gradients);
                    $joinDate = !empty($proj->joined_at) ? date('M Y', strtotime($proj->joined_at)) : 'N/A';
                    $role     = $proj->role ?? 'Member';
                ?>
                <a href="<?= URLROOT ?>/project/detail/<?= $pid ?>" class="disc-member-card" style="text-decoration:none;" data-idx="<?= $idx ?>">
                    <div class="disc-member-card__banner" style="background: <?= $g ?>;">
                        <i class="ph ph-briefcase"></i>
                        <span class="disc-member-card__status <?= $stClass ?>">
                            <?= statusIcon($proj->status ?? 'active') ?> <?= statusLabel($proj->status ?? 'active') ?>
                        </span>
                        <span class="disc-member-card__role-badge"><?= htmlspecialchars($role) ?></span>
                    </div>
                    <div class="disc-member-card__body">
                        <h3 class="disc-member-card__title"><?= htmlspecialchars($proj->name ?? 'Project') ?></h3>
                        <p class="disc-member-card__desc">
                            <?= htmlspecialchars(substr((string)($proj->description ?? ''), 0, 100)) ?><?= strlen((string)($proj->description ?? '')) > 100 ? '…' : '' ?>
                        </p>
                        <div class="disc-member-card__meta">
                            <span><i class="ph ph-users"></i> <?= (int)($proj->current_members ?? 0) ?>/<?= (int)($proj->max_members ?? 0) ?> Members</span>
                            <span><i class="ph ph-calendar-check"></i> Joined <?= $joinDate ?></span>
                        </div>
                        <div class="disc-member-card__cta">
                            Open Project <i class="ph ph-arrow-right"></i>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="disc-empty-state disc-empty-state--sm">
                <div class="disc-empty-state__icon">
                    <i class="ph ph-users-three"></i>
                </div>
                <p>You haven't joined any projects yet. Browse below and apply!</p>
            </div>
            <?php endif; ?>
        </section>

        <!-- ═══════════════════════════════════════════════════════════
             DIVIDER
        ════════════════════════════════════════════════════════════ -->
        <div class="disc-divider">
            <span class="disc-divider__line"></span>
            <span class="disc-divider__label"><i class="ph ph-magnifying-glass"></i> Explore All Projects</span>
            <span class="disc-divider__line"></span>
        </div>

        <!-- ═══════════════════════════════════════════════════════════
             SECTION 2 — ALL PROJECTS (DISCOVER)
        ════════════════════════════════════════════════════════════ -->
        <section class="disc-section" id="allProjectsSection">
            <div class="disc-section-label">
                <span class="disc-section-label__dot dot-blue"></span>
                <span>Suggested Projects</span>
                <span class="disc-count-pill"><?= count($allProjectsList) ?></span>
            </div>

            <!-- Search + Filters -->
            <div class="disc-filters">
                <div class="disc-search-wrap">
                    <i class="ph ph-magnifying-glass disc-search-icon"></i>
                    <input type="text" id="discSearchInput" placeholder="Search projects by name or keyword…" class="disc-search-input" autocomplete="off">
                </div>
                <div class="disc-filter-pills" id="discFilterPills">
                    <button class="disc-pill active" data-filter="all">All</button>
                    <button class="disc-pill" data-filter="active">Active</button>
                    <button class="disc-pill" data-filter="in-progress">In Progress</button>
                    <button class="disc-pill" data-filter="completed">Completed</button>
                </div>
            </div>

            <!-- Project Mini-Cards Grid -->
            <?php if (!empty($allProjectsList)): ?>
            <div class="disc-all-grid" id="discAllGrid">
                <?php foreach ($allProjectsList as $idx => $project): ?>
                <?php
                    if (is_array($project)) $project = (object)$project;
                    $pid         = (int)($project->id ?? 0);
                    $stClass     = strtolower(str_replace('_','-',$project->status ?? 'active'));
                    $skills      = array_filter(array_map('trim', explode(',', (string)($project->required_skills ?? ''))));
                    $matchedSkills = isset($project->matched_skills) && is_array($project->matched_skills) ? $project->matched_skills : [];
                    $organizationName = trim((string)($project->organization_name ?? ''));
                    $isMember    = !empty($memberProjectIds[$pid]);
                    $appStat     = $appStatus[$pid] ?? null;
                    $g           = getGradient($pid, $gradients);
                    $spotsLeft   = max(0, (int)($project->max_members ?? 0) - (int)($project->current_members ?? 0));
                    $isFull      = $spotsLeft <= 0 && (int)($project->max_members ?? 0) > 0;
                ?>
                <div class="disc-mini-card" data-status="<?= $stClass ?>"
                     data-name="<?= htmlspecialchars(strtolower($project->name ?? '')) ?>"
                     data-desc="<?= htmlspecialchars(strtolower(substr((string)($project->description ?? ''),0,200))) ?>"
                     data-org="<?= htmlspecialchars(strtolower($organizationName)) ?>">

                    <!-- Colour strip top -->
                    <div class="disc-mini-card__strip" style="background: <?= $g ?>;"></div>

                    <div class="disc-mini-card__body">
                        <div class="disc-mini-card__top-row">
                            <span class="disc-mini-status <?= $stClass ?>"><?= statusIcon($project->status ?? 'active') ?> <?= statusLabel($project->status ?? 'active') ?></span>
                            <?php if ($isMember): ?>
                            <span class="disc-mini-you-badge"><i class="ph ph-check-circle"></i> Joined</span>
                            <?php endif; ?>
                        </div>

                        <h4 class="disc-mini-card__title"><?= htmlspecialchars($project->name ?? 'Project') ?></h4>
                        <?php if ($organizationName !== ''): ?>
                        <div class="disc-mini-card__org">
                            <i class="ph ph-buildings"></i>
                            <span><?= htmlspecialchars($organizationName) ?></span>
                        </div>
                        <?php endif; ?>
                        <p class="disc-mini-card__desc">
                            <?= htmlspecialchars(substr((string)($project->description ?? ''), 0, 80)) ?><?= strlen((string)($project->description ?? '')) > 80 ? '…' : '' ?>
                        </p>

                        <?php if (!empty($skills)): ?>
                        <div class="disc-mini-skills">
                            <?php foreach (array_slice($skills,0,2) as $sk): ?>
                            <span class="disc-mini-skill"><?= htmlspecialchars($sk) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($skills) > 2): ?><span class="disc-mini-skill disc-mini-skill--more">+<?= count($skills)-2 ?></span><?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($matchedSkills)): ?>
                        <div style="margin-top:8px;">
                            <span style="display:inline-block;background:#e8f3fb;color:#355a72;border:1px solid rgba(101,131,150,0.28);padding:3px 8px;border-radius:999px;font-size:11px;font-weight:700;">
                                Matched: <?= htmlspecialchars(implode(', ', array_slice($matchedSkills, 0, 2))) ?><?= count($matchedSkills) > 2 ? ' +' . (count($matchedSkills) - 2) : '' ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <div class="disc-mini-card__meta">
                            <span><i class="ph ph-users"></i> <?= (int)($project->current_members ?? 0) ?>/<?= (int)($project->max_members ?? 0) ?></span>
                            <?php if (!$isMember && !$isFull): ?>
                            <span class="disc-spots-left"><?= $spotsLeft ?> spot<?= $spotsLeft !== 1 ? 's' : '' ?> left</span>
                            <?php elseif ($isFull && !$isMember): ?>
                            <span class="disc-spots-full">Full</span>
                            <?php endif; ?>
                        </div>

                        <div class="disc-mini-card__footer">
                            <?php if ($isMember): ?>
                                <a href="<?= URLROOT ?>/project/detail/<?= $pid ?>" class="disc-mini-btn disc-mini-btn--open" style="text-decoration:none;">
                                    <i class="ph ph-arrow-square-out"></i> Open
                                </a>
                            <?php elseif ($appStat === 'pending'): ?>
                                <span class="disc-mini-btn disc-mini-btn--pending"><i class="ph ph-clock"></i> Pending</span>
                            <?php elseif ($appStat === 'accepted'): ?>
                                <span class="disc-mini-btn disc-mini-btn--accepted"><i class="ph ph-check"></i> Accepted</span>
                            <?php elseif ($appStat === 'rejected'): ?>
                                <a href="<?= URLROOT ?>/project/detail/<?= $pid ?>" class="disc-mini-btn disc-mini-btn--view" style="text-decoration:none;">
                                    <i class="ph ph-arrow-right"></i> Re-apply
                                </a>
                            <?php elseif ($isFull): ?>
                                <span class="disc-mini-btn disc-mini-btn--full"><i class="ph ph-lock"></i> Full</span>
                            <?php else: ?>
                                <a href="<?= URLROOT ?>/project/detail/<?= $pid ?>" class="disc-mini-btn disc-mini-btn--join" style="text-decoration:none;">
                                    <i class="ph ph-plus-circle"></i> Join
                                </a>
                            <?php endif; ?>
                            <a href="<?= URLROOT ?>/project/detail/<?= $pid ?>" class="disc-mini-btn disc-mini-btn--view" style="text-decoration:none;">
                                Details <i class="ph ph-caret-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- No results state (shown by JS) -->
            <div class="disc-empty-state" id="discNoResults" style="display:none;">
                <div class="disc-empty-state__icon"><i class="ph ph-binoculars"></i></div>
                <p>No projects match your search.</p>
            </div>

            <?php else: ?>
            <div class="disc-empty-state">
                <div class="disc-empty-state__icon"><i class="ph ph-folder-open"></i></div>
                <?php if (empty($userTeachSkillsList)): ?>
                    <p>Add your teach skills in profile to get suggested projects.</p>
                <?php else: ?>
                    <p>No projects currently match your teach skills.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </section>

    </div><!-- /dashboard-main -->
</div><!-- /dashboard-container -->
</main>

<script>
(function () {
    // Filter pills
    const pills   = document.querySelectorAll('.disc-pill');
    const cards   = document.querySelectorAll('.disc-mini-card');
    const search  = document.getElementById('discSearchInput');
    const noRes   = document.getElementById('discNoResults');

    let activeFilter = 'all';
    let searchTerm   = '';

    function applyFilters() {
        let visible = 0;
        cards.forEach(card => {
            const statusMatch = activeFilter === 'all' || card.dataset.status === activeFilter;
            const textMatch   = !searchTerm ||
                card.dataset.name.includes(searchTerm) ||
                card.dataset.desc.includes(searchTerm) ||
                (card.dataset.org || '').includes(searchTerm);
            const show = statusMatch && textMatch;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        if (noRes) noRes.style.display = visible === 0 ? 'flex' : 'none';
    }

    pills.forEach(pill => {
        pill.addEventListener('click', () => {
            pills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            activeFilter = pill.dataset.filter;
            applyFilters();
        });
    });

    if (search) {
        search.addEventListener('input', () => {
            searchTerm = search.value.toLowerCase().trim();
            applyFilters();
        });
    }

    // Staggered entrance for mini cards
    cards.forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 60 + i * 35);
    });

    // Staggered entrance for member cards
    document.querySelectorAll('.disc-member-card').forEach((card, i) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(24px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 80 + i * 80);
    });
})();
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>
