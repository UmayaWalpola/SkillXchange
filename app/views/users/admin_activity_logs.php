<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/adminsidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/global.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/profile.css">
<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/admin.css">

<main class="site-main">
<div class="dashboard-container">
	<div class="dashboard-main">
		<div class="admin-content">
			<!-- Header -->
			<div class="profile-header">
				<div class="profile-info">
					<div class="profile-avatar">AD</div>
					<div class="profile-details">
						<h1>Activity Logs</h1>
						<p class="profile-bio">Monitor user activity and admin actions</p>
					</div>
				</div>
			</div>

			<!-- Flash Messages -->
			<?php if (isset($_SESSION['success'])): ?>
				<div class="alert alert-success">
					<?= $_SESSION['success'] ?>
					<?php unset($_SESSION['success']); ?>
				</div>
			<?php endif; ?>
            
			<?php if (isset($_SESSION['error'])): ?>
				<div class="alert alert-error">
					<?= $_SESSION['error'] ?>
					<?php unset($_SESSION['error']); ?>
				</div>
			<?php endif; ?>

			<!-- User Activity Logs -->
			<section class="admin-section">
				<div class="section-header">
					<h2 class="section-title">User Activity</h2>
				</div>

				<div class="table-container">
					<table class="admin-table">
						<thead>
							<tr>
								<th>User</th>
								<th>Activity Type</th>
								<th>Description</th>
								<th>Date</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($data['user_activities'])): ?>
								<?php foreach ($data['user_activities'] as $activity): ?>
									<tr>
										<td>
											<div>
												<div><?= htmlspecialchars($activity->username ?? 'Unknown') ?></div>
												<div class="text-muted" style="font-size: 0.85rem;"><?= htmlspecialchars($activity->email ?? '-') ?></div>
											</div>
										</td>
										<td><?= htmlspecialchars($activity->activity_type ?? '-') ?></td>
										<td><?= htmlspecialchars($activity->description ?? '-') ?></td>
										<td>
											<?= !empty($activity->created_at) ? date('M d, Y H:i', strtotime($activity->created_at)) : '-' ?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="4" class="text-center">No user activity found</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</section>

			<!-- Admin Actions -->
			<section class="admin-section">
				<div class="section-header">
					<h2 class="section-title">Admin Actions</h2>
				</div>

				<div class="table-container">
					<table class="admin-table">
						<thead>
							<tr>
								<th>Admin</th>
								<th>Action</th>
								<th>Target User</th>
								<th>Description</th>
								<th>Date</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($data['admin_actions'])): ?>
								<?php foreach ($data['admin_actions'] as $action): ?>
									<tr>
										<td><?= htmlspecialchars($action->admin_username ?? 'Admin') ?></td>
										<td><?= htmlspecialchars(str_replace('_', ' ', $action->action_type ?? 'action')) ?></td>
										<td><?= htmlspecialchars($action->target_username ?? '-') ?></td>
										<td><?= htmlspecialchars($action->description ?? '-') ?></td>
										<td>
											<?= !empty($action->created_at) ? date('M d, Y H:i', strtotime($action->created_at)) : '-' ?>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="5" class="text-center">No admin actions found</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</section>
		</div>
	</div>
</div>
</main>
