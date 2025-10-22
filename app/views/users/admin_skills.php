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
                <h1>Skills Management</h1>
                <p class="admin-subtitle">Monitor and manage skills across the platform</p>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">💡</div>
                    <div class="stat-info">
                        <span class="stat-number">127</span>
                        <span class="stat-label">Total Skills</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍🏫</div>
                    <div class="stat-info">
                        <span class="stat-number">458</span>
                        <span class="stat-label">Total Teachers</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-info">
                        <span class="stat-number">612</span>
                        <span class="stat-label">Total Learners</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🔥</div>
                    <div class="stat-info">
                        <span class="stat-number">23</span>
                        <span class="stat-label">Trending Skills</span>
                    </div>
                </div>
            </div>

            <!-- Skills Categories -->
            <section class="admin-section">
                <div class="section-header">
                    <h2 class="section-title">Popular Skill Categories</h2>
                    <button class="btn-primary">Add Category</button>
                </div>
                
                <div class="categories-grid">
                    <div class="category-card">
                        <div class="category-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">💻</div>
                        <h3>Technology</h3>
                        <p class="category-count">34 Skills</p>
                        <div class="category-stats">
                            <span>142 Teachers</span>
                            <span>189 Learners</span>
                        </div>
                    </div>
                    
                    <div class="category-card">
                        <div class="category-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">🎨</div>
                        <h3>Creative Arts</h3>
                        <p class="category-count">28 Skills</p>
                        <div class="category-stats">
                            <span>98 Teachers</span>
                            <span>145 Learners</span>
                        </div>
                    </div>
                    
                    <div class="category-card">
                        <div class="category-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">🌍</div>
                        <h3>Languages</h3>
                        <p class="category-count">22 Skills</p>
                        <div class="category-stats">
                            <span>87 Teachers</span>
                            <span>156 Learners</span>
                        </div>
                    </div>
                    
                    <div class="category-card">
                        <div class="category-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">🎵</div>
                        <h3>Music</h3>
                        <p class="category-count">18 Skills</p>
                        <div class="category-stats">
                            <span>65 Teachers</span>
                            <span>92 Learners</span>
                        </div>
                    </div>
                    
                    <div class="category-card">
                        <div class="category-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">💼</div>
                        <h3>Business</h3>
                        <p class="category-count">15 Skills</p>
                        <div class="category-stats">
                            <span>43 Teachers</span>
                            <span>78 Learners</span>
                        </div>
                    </div>
                    
                    <div class="category-card">
                        <div class="category-icon" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">🏃</div>
                        <h3>Sports & Fitness</h3>
                        <p class="category-count">10 Skills</p>
                        <div class="category-stats">
                            <span>23 Teachers</span>
                            <span>52 Learners</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Top Skills Table -->
            <section class="admin-section">
                <div class="section-header">
                    <h2 class="section-title">Top Skills by Popularity</h2>
                    <div class="filter-options">
                        <select class="filter-select">
                            <option value="all">All Categories</option>
                            <option value="tech">Technology</option>
                            <option value="arts">Creative Arts</option>
                            <option value="lang">Languages</option>
                        </select>
                        <select class="filter-select">
                            <option value="popular">Most Popular</option>
                            <option value="teachers">Most Teachers</option>
                            <option value="learners">Most Learners</option>
                        </select>
                    </div>
                </div>
                
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Skill Name</th>
                                <th>Category</th>
                                <th>Teachers</th>
                                <th>Learners</th>
                                <th>Match Rate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="rank-badge rank-1">1</span></td>
                                <td>
                                    <div class="skill-cell">
                                        <span class="skill-icon">🐍</span>
                                        <strong>Python Programming</strong>
                                    </div>
                                </td>
                                <td><span class="category-badge tech">Technology</span></td>
                                <td>45</td>
                                <td>78</td>
                                <td><span class="match-rate high">87%</span></td>
                                <td>
                                    <button class="action-btn btn-view">View</button>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><span class="rank-badge rank-2">2</span></td>
                                <td>
                                    <div class="skill-cell">
                                        <span class="skill-icon">🌐</span>
                                        <strong>Spanish Language</strong>
                                    </div>
                                </td>
                                <td><span class="category-badge lang">Languages</span></td>
                                <td>38</td>
                                <td>65</td>
                                <td><span class="match-rate high">82%</span></td>
                                <td>
                                    <button class="action-btn btn-view">View</button>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><span class="rank-badge rank-3">3</span></td>
                                <td>
                                    <div class="skill-cell">
                                        <span class="skill-icon">🎸</span>
                                        <strong>Guitar Playing</strong>
                                    </div>
                                </td>
                                <td><span class="category-badge music">Music</span></td>
                                <td>32</td>
                                <td>54</td>
                                <td><span class="match-rate high">79%</span></td>
                                <td>
                                    <button class="action-btn btn-view">View</button>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><span class="rank-badge">4</span></td>
                                <td>
                                    <div class="skill-cell">
                                        <span class="skill-icon">🎨</span>
                                        <strong>Graphic Design</strong>
                                    </div>
                                </td>
                                <td><span class="category-badge arts">Creative Arts</span></td>
                                <td>29</td>
                                <td>48</td>
                                <td><span class="match-rate medium">71%</span></td>
                                <td>
                                    <button class="action-btn btn-view">View</button>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><span class="rank-badge">5</span></td>
                                <td>
                                    <div class="skill-cell">
                                        <span class="skill-icon">📸</span>
                                        <strong>Photography</strong>
                                    </div>
                                </td>
                                <td><span class="category-badge arts">Creative Arts</span></td>
                                <td>27</td>
                                <td>42</td>
                                <td><span class="match-rate medium">68%</span></td>
                                <td>
                                    <button class="action-btn btn-view">View</button>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><span class="rank-badge">6</span></td>
                                <td>
                                    <div class="skill-cell">
                                        <span class="skill-icon">💻</span>
                                        <strong>Web Development</strong>
                                    </div>
                                </td>
                                <td><span class="category-badge tech">Technology</span></td>
                                <td>24</td>
                                <td>39</td>
                                <td><span class="match-rate medium">65%</span></td>
                                <td>
                                    <button class="action-btn btn-view">View</button>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><span class="rank-badge">7</span></td>
                                <td>
                                    <div class="skill-cell">
                                        <span class="skill-icon">🧘</span>
                                        <strong>Yoga & Meditation</strong>
                                    </div>
                                </td>
                                <td><span class="category-badge fitness">Sports & Fitness</span></td>
                                <td>21</td>
                                <td>35</td>
                                <td><span class="match-rate medium">62%</span></td>
                                <td>
                                    <button class="action-btn btn-view">View</button>
                                </td>
                            </tr>
                            
                            <tr>
                                <td><span class="rank-badge">8</span></td>
                                <td>
                                    <div class="skill-cell">
                                        <span class="skill-icon">📝</span>
                                        <strong>Content Writing</strong>
                                    </div>
                                </td>
                                <td><span class="category-badge business">Business</span></td>
                                <td>19</td>
                                <td>31</td>
                                <td><span class="match-rate low">58%</span></td>
                                <td>
                                    <button class="action-btn btn-view">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
</main>

<style>
/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.category-card {
    background: var(--white-bg);
    border: 2px solid var(--blue-bg);
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(101, 131, 150, 0.15);
    border-color: var(--accent-blue);
}

.category-icon {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    margin: 0 auto 1rem;
}

.category-card h3 {
    font-size: 1.3rem;
    color: var(--dark-bg);
    margin-bottom: 0.5rem;
}

.category-count {
    color: var(--primary-blue);
    font-weight: 600;
    font-size: 0.95rem;
    margin-bottom: 1rem;
}

.category-stats {
    display: flex;
    justify-content: space-around;
    padding-top: 1rem;
    border-top: 2px solid var(--blue-bg);
}

.category-stats span {
    font-size: 0.85rem;
    color: var(--dark-bg);
    opacity: 0.7;
}

/* Skills Table Specific */
.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    border-radius: 8px;
    font-weight: bold;
    font-size: 1.1rem;
    background: var(--blue-bg);
    color: var(--primary-blue);
}

.rank-badge.rank-1 {
    background: linear-gradient(135deg, #ffd700, #ffed4e);
    color: var(--dark-bg);
}

.rank-badge.rank-2 {
    background: linear-gradient(135deg, #c0c0c0, #e8e8e8);
    color: var(--dark-bg);
}

.rank-badge.rank-3 {
    background: linear-gradient(135deg, #cd7f32, #e8a87c);
    color: var(--white-bg);
}

.skill-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.skill-icon {
    font-size: 1.8rem;
}

.category-badge {
    padding: 0.4rem 0.9rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.category-badge.tech {
    background: #dbeafe;
    color: #1e40af;
}

.category-badge.arts {
    background: #fce7f3;
    color: #be185d;
}

.category-badge.lang {
    background: #dcfce7;
    color: #166534;
}

.category-badge.music {
    background: #fef3c7;
    color: #92400e;
}

.category-badge.business {
    background: #e0e7ff;
    color: #4338ca;
}

.category-badge.fitness {
    background: #ecfdf5;
    color: #065f46;
}

.match-rate {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

.match-rate.high {
    background: #dcfce7;
    color: #22c55e;
}

.match-rate.medium {
    background: #fef3c7;
    color: #f59e0b;
}

.match-rate.low {
    background: #fee2e2;
    color: #ef4444;
}

@media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script src="<?= URLROOT ?>/assets/js/admin.js" defer></script>
<?php require_once "../app/views/layouts/footer.php"; ?>