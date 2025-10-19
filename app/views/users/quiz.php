<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/usersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/quiz.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        <div class="quiz-container">
            <div class="quiz-header">
                <h1>Your tech journey starts here</h1>
                <p>Take a quiz, earn badges, level up</p>
            </div>

            <div class="search-section">
                <div class="search-bar">
                    <input type="text" id="searchInput" class="search-input" placeholder="Search quizzes...">
                </div>

                <div class="filters" id="categoryFilters">
                    <button class="filter-btn active" data-category="All">All</button>
                    <button class="filter-btn" data-category="Programming">Programming Fundamentals</button>
                    <button class="filter-btn" data-category="UI/UX">UI/UX Design</button>
                    <button class="filter-btn" data-category="Frontend">Frontend Development</button>
                    <button class="filter-btn" data-category="Backend">Backend Development</button>
                    <button class="filter-btn" data-category="Database">Database Design & Management</button>
                    <button class="filter-btn" data-category="System">System Design & Architecture</button>
                    <button class="filter-btn" data-category="Cyber">Cybersecurity</button>
                    <button class="filter-btn" data-category="Version Control">Version Control & Collaboration</button>
                    <button class="filter-btn" data-category="Devops">DevOps & Deployment</button>
                </div>

                <div class="toggle-tabs">
                    <button class="tab-btn active" data-status="all">All Quizzes</button>
                    <button class="tab-btn" data-status="completed">Completed</button>
                    <button class="tab-btn" data-status="saved">Saved for Later</button>
                </div>
            </div>

            <div class="quiz-grid" id="quizGrid">
                <!-- Quiz cards will be populated by JavaScript -->
            </div>

            <div class="no-results" id="noResults" style="display: none;">
                <h3>No quizzes found</h3>
                <p>Try adjusting your search terms or filters</p>
            </div>
        </div>
    </div>
</div>
</main>

<!-- Pass data to JavaScript using data attributes -->
<div id="quiz-data" 
     data-quizzes='<?= json_encode($data['quizzes']) ?>'
     data-urlroot='<?= URLROOT ?>' 
     style="display: none;">
</div>

<script src="<?= URLROOT ?>/assets/js/quiz.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>