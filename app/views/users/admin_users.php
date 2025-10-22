<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/adminsidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/admin.css">

<main class="site-main">
<div class="dashboard-container">
    <div class="dashboard-main">
        <div class="admin-content">
            <!-- Header -->
            <div class="admin-header">
                <h1>User Management</h1>
                <p class="admin-subtitle">Manage and monitor all platform users</p>
            </div>

            <!-- Search and Filter Bar -->
            <div class="admin-section">
                <div class="filter-bar">
                    <div class="search-box">
                        <input type="text" placeholder="Search users by name or email..." class="search-input">
                        <button class="btn-primary">Search</button>
                    </div>
                    <div class="filter-options">
                        <select class="filter-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                        </select>
                        <select class="filter-select">
                            <option value="">Sort By</option>
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="name">Name (A-Z)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <section class="admin-section">
                <div class="section-header">
                    <h2 class="section-title">All Users (243)</h2>
                    <button class="btn-primary">Export Data</button>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Join Date</th>
                                <th>Skills</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- User 1 -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">JD</div>
                                        <span>John Doe</span>
                                    </div>
                                </td>
                                <td>john.doe@email.com</td>
                                <td>Jan 15, 2025</td>
                                <td>
                                    <span class="skill-badge">Python</span>
                                    <span class="skill-badge">Design</span>
                                </td>
                                <td><span class="badge badge-success">Active</span></td>
                                <td>
                                    <a href="#" class="action-btn btn-view">View</a>
                                    <button class="action-btn btn-suspend">Suspend</button>
                                </td>
                            </tr>
                            
                            <!-- User 2 -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar" style="background: #ef4444;">SA</div>
                                        <span>Sarah Anderson</span>
                                    </div>
                                </td>
                                <td>sarah.a@email.com</td>
                                <td>Jan 12, 2025</td>
                                <td>
                                    <span class="skill-badge">JavaScript</span>
                                    <span class="skill-badge">+2</span>
                                </td>
                                <td><span class="badge badge-success">Active</span></td>
                                <td>
                                    <a href="#" class="action-btn btn-view">View</a>
                                    <button class="action-btn btn-suspend">Suspend</button>
                                </td>
                            </tr>
                            
                            <!-- User 3 -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar" style="background: #f59e0b;">MC</div>
                                        <span>Michael Chen</span>
                                    </div>
                                </td>
                                <td>m.chen@email.com</td>
                                <td>Jan 10, 2025</td>
                                <td>
                                    <span class="skill-badge">Marketing</span>
                                </td>
                                <td><span class="badge badge-warning">Suspended</span></td>
                                <td>
                                    <a href="#" class="action-btn btn-view">View</a>
                                    <button class="action-btn btn-activate">Activate</button>
                                </td>
                            </tr>
                            
                            <!-- User 4 -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar" style="background: #8b5cf6;">EP</div>
                                        <span>Emma Parker</span>
                                    </div>
                                </td>
                                <td>emma.parker@email.com</td>
                                <td>Jan 08, 2025</td>
                                <td>
                                    <span class="skill-badge">Writing</span>
                                    <span class="skill-badge">SEO</span>
                                </td>
                                <td><span class="badge badge-success">Active</span></td>
                                <td>
                                    <a href="#" class="action-btn btn-view">View</a>
                                    <button class="action-btn btn-suspend">Suspend</button>
                                </td>
                            </tr>
                            
                            <!-- User 5 -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar" style="background: #10b981;">DW</div>
                                        <span>David Wilson</span>
                                    </div>
                                </td>
                                <td>d.wilson@email.com</td>
                                <td>Jan 05, 2025</td>
                                <td>
                                    <span class="skill-badge">Photography</span>
                                    <span class="skill-badge">+1</span>
                                </td>
                                <td><span class="badge badge-success">Active</span></td>
                                <td>
                                    <a href="#" class="action-btn btn-view">View</a>
                                    <button class="action-btn btn-suspend">Suspend</button>
                                </td>
                            </tr>
                            
                            <!-- User 6 -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar" style="background: #ec4899;">LT</div>
                                        <span>Lisa Thompson</span>
                                    </div>
                                </td>
                                <td>lisa.t@email.com</td>
                                <td>Jan 03, 2025</td>
                                <td>
                                    <span class="skill-badge">Yoga</span>
                                    <span class="skill-badge">Meditation</span>
                                </td>
                                <td><span class="badge badge-success">Active</span></td>
                                <td>
                                    <a href="#" class="action-btn btn-view">View</a>
                                    <button class="action-btn btn-suspend">Suspend</button>
                                </td>
                            </tr>
                            
                            <!-- User 7 -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar" style="background: #06b6d4;">RG</div>
                                        <span>Robert Garcia</span>
                                    </div>
                                </td>
                                <td>r.garcia@email.com</td>
                                <td>Dec 28, 2024</td>
                                <td>
                                    <span class="skill-badge">Guitar</span>
                                </td>
                                <td><span class="badge badge-success">Active</span></td>
                                <td>
                                    <a href="#" class="action-btn btn-view">View</a>
                                    <button class="action-btn btn-suspend">Suspend</button>
                                </td>
                            </tr>
                            
                            <!-- User 8 -->
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar" style="background: #f97316;">AM</div>
                                        <span>Amanda Martinez</span>
                                    </div>
                                </td>
                                <td>amanda.m@email.com</td>
                                <td>Dec 25, 2024</td>
                                <td>
                                    <span class="skill-badge">Spanish</span>
                                    <span class="skill-badge">+3</span>
                                </td>
                                <td><span class="badge badge-success">Active</span></td>
                                <td>
                                    <a href="#" class="action-btn btn-view">View</a>
                                    <button class="action-btn btn-suspend">Suspend</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <button class="pagination-btn" disabled>Previous</button>
                    <div class="pagination-numbers">
                        <button class="pagination-number active">1</button>
                        <button class="pagination-number">2</button>
                        <button class="pagination-number">3</button>
                        <span>...</span>
                        <button class="pagination-number">15</button>
                    </div>
                    <button class="pagination-btn">Next</button>
                </div>
            </section>
        </div>
    </div>
</div>
</main>

<style>
/* Additional styles for user management */
.filter-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-box {
    display: flex;
    gap: 0.5rem;
    flex: 1;
    min-width: 300px;
}

.search-input {
    flex: 1;
    padding: 0.75rem 1rem;
    border: 2px solid var(--primary-blue);
    border-radius: 8px;
    font-size: 0.95rem;
    background: var(--blue-bg);
    transition: all 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: var(--accent-blue);
    box-shadow: 0 0 0 3px rgba(156, 199, 223, 0.2);
}

.filter-options {
    display: flex;
    gap: 0.5rem;
}

.filter-select {
    padding: 0.75rem 1rem;
    border: 2px solid var(--primary-blue);
    border-radius: 8px;
    font-size: 0.95rem;
    background: var(--white-bg);
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-select:focus {
    outline: none;
    border-color: var(--accent-blue);
}

.skill-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: var(--blue-bg);
    color: var(--primary-blue);
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-right: 0.3rem;
}

.badge {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-success {
    background: #dcfce7;
    color: #22c55e;
}

.badge-warning {
    background: #fef3c7;
    color: #f59e0b;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--blue-bg);
}

.pagination-btn {
    padding: 0.5rem 1rem;
    background: var(--primary-blue);
    color: var(--white-bg);
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled) {
    background: var(--accent-blue);
    color: var(--dark-bg);
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-numbers {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.pagination-number {
    width: 40px;
    height: 40px;
    border: 2px solid var(--primary-blue);
    background: var(--white-bg);
    color: var(--dark-bg);
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.pagination-number:hover {
    background: var(--blue-bg);
}

.pagination-number.active {
    background: var(--primary-blue);
    color: var(--white-bg);
}

@media (max-width: 768px) {
    .filter-bar {
        flex-direction: column;
    }
    
    .search-box {
        width: 100%;
        min-width: auto;
    }
    
    .filter-options {
        width: 100%;
        flex-wrap: wrap;
    }
    
    .filter-select {
        flex: 1;
        min-width: 150px;
    }
}
</style>

<script src="<?= URLROOT ?>/assets/js/admin.js" defer></script>
<?php require_once "../app/views/layouts/footer.php"; ?>