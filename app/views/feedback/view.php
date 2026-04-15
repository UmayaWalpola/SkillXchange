<?php require_once "../app/views/layouts/header_user.php"; ?>

<?php 
// Include appropriate sidebar based on user role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'organization') {
        require_once "../app/views/layouts/organization_sidebar.php";
    } else {
        require_once "../app/views/layouts/usersidebar.php";
    }
}
?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/feedback.css">

<main class="site-main">
    <div class="container" style="max-width:1200px;margin:0 auto;padding:20px;">
        <!-- Page Header -->
        <div class="page-header" style="margin-bottom:30px;">
            <h1 style="font-size:28px;font-weight:700;color:#1a1a1a;margin-bottom:10px;">My Feedback</h1>
            <p style="color:#666;font-size:16px;">View and filter all feedback you've received</p>
        </div>

        <!-- Stats Overview -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;">
            <div style="background:linear-gradient(135deg,var(--primary-blue),var(--accent-blue));border-radius:16px;padding:25px;color:white;">
                <div style="font-size:14px;opacity:0.9;margin-bottom:6px;">Average Rating</div>
                <div style="font-size:42px;font-weight:700;margin-bottom:5px;"><?= number_format($data['stats']->avg_rating ?? 0, 1) ?></div>
                <div style="font-size:13px;opacity:0.9;">out of 5.0</div>
            </div>
            <div style="background:white;border:2px solid #e1eefb;border-radius:16px;padding:25px;">
                <div style="font-size:14px;color:#666;margin-bottom:8px;">Total Feedback</div>
                <div style="font-size:42px;font-weight:700;color:#1a1a1a;margin-bottom:5px;"><?= $data['stats']->total_count ?? 0 ?></div>
                <div style="font-size:13px;color:#666;">received</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="feedback-filter-bar" style="background:white;border:2px solid #e1eefb;border-radius:16px;padding:25px;margin-bottom:30px;">
            <div style="display:flex;flex-wrap:wrap;gap:15px;align-items:flex-end;">
                <!-- Sort Dropdown -->
                <div style="flex:1;min-width:180px;">
                    <label style="display:block;font-weight:600;margin-bottom:8px;font-size:14px;color:#1a1a1a;">Sort By</label>
                    <select id="sortSelect" style="width:100%;padding:10px 12px;border:2px solid #e1eefb;border-radius:8px;font-size:14px;background:white;">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="highest">Highest Rating</option>
                        <option value="lowest">Lowest Rating</option>
                    </select>
                </div>

                <!-- Rating Filter -->
                <div style="flex:1;min-width:150px;">
                    <label style="display:block;font-weight:600;margin-bottom:8px;font-size:14px;color:#1a1a1a;">Rating</label>
                    <select id="ratingFilter" style="width:100%;padding:10px 12px;border:2px solid #e1eefb;border-radius:8px;font-size:14px;background:white;">
                        <option value="">All Ratings</option>
                        <option value="5">5 Stars</option>
                        <option value="4">4 Stars</option>
                        <option value="3">3 Stars</option>
                        <option value="2">2 Stars</option>
                        <option value="1">1 Star</option>
                    </select>
                </div>

                <!-- Tag Filter -->
                <div style="flex:1;min-width:180px;">
                    <label style="display:block;font-weight:600;margin-bottom:8px;font-size:14px;color:#1a1a1a;">Tag</label>
                    <select id="tagFilter" style="width:100%;padding:10px 12px;border:2px solid #e1eefb;border-radius:8px;font-size:14px;background:white;">
                        <option value="">All Tags</option>
                        <option value="communication">Communication</option>
                        <option value="quality">Quality</option>
                        <option value="ontime">On-time</option>
                        <option value="teamwork">Teamwork</option>
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <div>
                    <button id="clearFilters" style="padding:10px 20px;background:#f0f0f0;border:2px solid #e0e0e0;border-radius:8px;font-weight:600;cursor:pointer;font-size:14px;transition:all 0.2s;">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Feedback List -->
        <div id="feedbackContainer">
            <div style="text-align:center;padding:60px 20px;">
                <div style="font-size:48px;color:#e0e0e0;margin-bottom:15px;">
                    <i class="ph ph-star"></i>
                </div>
                <p style="color:#999;font-size:16px;">Loading feedback...</p>
            </div>
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" style="display:none;margin-top:30px;text-align:center;">
            <div style="display:inline-flex;gap:10px;align-items:center;">
                <button id="prevPage" style="padding:10px 20px;background:white;border:2px solid #e1eefb;border-radius:8px;font-weight:600;cursor:pointer;transition:all 0.2s;">
                    Previous
                </button>
                <span id="pageInfo" style="color:#666;font-size:14px;padding:0 15px;"></span>
                <button id="nextPage" style="padding:10px 20px;background:white;border:2px solid #e1eefb;border-radius:8px;font-weight:600;cursor:pointer;transition:all 0.2s;">
                    Next
                </button>
            </div>
        </div>
    </div>
</main>

<!-- Include Report Modal -->
<?php require_once "../app/views/feedback/report_modal.php"; ?>

<script>
    window.URLROOT = '<?= URLROOT ?>';
    window.CURRENT_USER_ID = <?= $data['userId'] ?>;
</script>
<script src="<?= URLROOT ?>/assets/js/feedback_filters.js"></script>
<script src="<?= URLROOT ?>/assets/js/feedback_report.js"></script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>