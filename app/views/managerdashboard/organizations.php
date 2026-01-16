<?php require_once "../app/views/layouts/header_user.php"; ?>
<?php require_once "../app/views/layouts/managersidebar.php"; ?>

<link rel="stylesheet" href="<?= URLROOT ?>/assets/css/manager_dashboard.css">

<main class="site-main">
    <div class="dashboard-container">
        <div class="dashboard-main">
            
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1>Organizations Management</h1>
                    <p>View and manage registered organizations</p>
                </div>
            </div>

            <!-- Organizations Table -->
            <div class="section-card">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Organization Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Address</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($data['organizations'])): ?>
                                <?php foreach($data['organizations'] as $org): ?>
                                    <tr data-org-id="<?= $org['id'] ?>">
                                        <td>
                                            <div class="org-info">
                                                <strong><?= htmlspecialchars($org['name']) ?></strong>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($org['email']) ?></td>
                                        <td><?= htmlspecialchars($org['phone']) ?></td>
                                        <td><?= htmlspecialchars($org['address']) ?></td>
                                        <td><?= date('M d, Y', strtotime($org['created_at'])) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-icon btn-view" title="View Details" onclick="viewOrganization(<?= $org['id'] ?>)">
                                                    View
                                                </button>
                                                <button class="btn-icon btn-delete" title="Remove Organization" onclick="removeOrganization(<?= $org['id'] ?>, '<?= htmlspecialchars($org['name']) ?>')">
                                                    Remove
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="no-data">No organizations found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- View Organization Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeViewModal()">&times;</span>
        <h2>Organization Details</h2>
        <div id="orgDetails" style="margin-top: 20px;">
            <!-- Details will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
function viewOrganization(orgId) {
    // Get organization data from the table row
<<<<<<< HEAD
    const row = document.querySelector(tr[data-org-id="${orgId}"]);
=======
    const row = document.querySelector(`tr[data-org-id="${orgId}"]`);
>>>>>>> origin/feature/manager
    const cells = row.getElementsByTagName('td');
    
    const orgDetails = `
        <div style="line-height: 2;">
            <p><strong>Organization Name:</strong> ${cells[0].innerText}</p>
            <p><strong>Email:</strong> ${cells[1].innerText}</p>
            <p><strong>Phone:</strong> ${cells[2].innerText}</p>
            <p><strong>Address:</strong> ${cells[3].innerText}</p>
            <p><strong>Registration Date:</strong> ${cells[4].innerText}</p>
        </div>
    `;
    
    document.getElementById('orgDetails').innerHTML = orgDetails;
    document.getElementById('viewModal').style.display = 'block';
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

function removeOrganization(orgId, orgName) {
<<<<<<< HEAD
    if (confirm(Are you sure you want to remove "${orgName}"? This action cannot be undone.)) {
=======
    if (confirm(`Are you sure you want to remove "${orgName}"? This action cannot be undone.`)) {
>>>>>>> origin/feature/manager
        // TODO: Implement actual AJAX call to remove organization
        fetch('<?= URLROOT ?>/managerdashboard/removeOrganization', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
<<<<<<< HEAD
            body: org_id=${orgId}
=======
            body: `org_id=${orgId}`
>>>>>>> origin/feature/manager
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Remove the row from table
<<<<<<< HEAD
                document.querySelector(tr[data-org-id="${orgId}"]).remove();
=======
                document.querySelector(`tr[data-org-id="${orgId}"]`).remove();
>>>>>>> origin/feature/manager
            } else {
                alert('Error removing organization');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing organization');
        });
    }
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('viewModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php require_once "../app/views/layouts/footer_user.php"; ?>