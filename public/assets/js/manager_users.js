// Add User Modal Functions
function openAddUserModal() {
    document.getElementById('addUserModal').style.display = 'block';
}

function closeAddUserModal() {
    document.getElementById('addUserModal').style.display = 'none';
    document.getElementById('addUserForm').reset();
}

// Add User Form Submit
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const urlRoot = this.getAttribute('data-urlroot');
        
        fetch(`${urlRoot}/managerdashboard/addUser`, {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeAddUserModal();
                location.reload();
            } else {
                alert(data.message || 'Error adding user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding user');
        });
    });

    // Edit User Form Submit
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const urlRoot = this.getAttribute('data-urlroot');
        
        // Log the data being sent for debugging
        console.log('Sending data:', {
            user_id: formData.get('user_id'),
            name: formData.get('name'),
            email: formData.get('email'),
            role: formData.get('role'),
            password: formData.get('password') ? '***' : '(empty)'
        });
        
        fetch(`${urlRoot}/managerdashboard/updateUser`, {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeEditUserModal();
                location.reload();
            } else {
                alert(data.message || 'Error updating user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating user');
        });
    });
});

// Edit User Modal Functions
function openEditUserModal(userId, name, email, role) {
    console.log('Opening edit modal with:', { userId, name, email, role }); // Debug log
    
    document.getElementById('editUserId').value = userId;
    document.getElementById('editUserName').value = name;
    document.getElementById('editUserEmail').value = email;
    document.getElementById('editUserRole').value = role; // This will now match the database value
    document.getElementById('editUserPassword').value = '';
    document.getElementById('editUserModal').style.display = 'block';
}

function closeEditUserModal() {
    document.getElementById('editUserModal').style.display = 'none';
    document.getElementById('editUserForm').reset();
}

// Remove User Function
function removeUser(userId, userName) {
    if (confirm(`Are you sure you want to remove "${userName}"? This action cannot be undone.`)) {
        const urlRoot = document.querySelector('[data-urlroot]').getAttribute('data-urlroot');
        
        fetch(`${urlRoot}/managerdashboard/removeUser`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                document.querySelector(`tr[data-user-id="${userId}"]`).remove();
            } else {
                alert(data.message || 'Error removing user');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error removing user');
        });
    }
}

// Close modals when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addUserModal');
    const editModal = document.getElementById('editUserModal');
    
    if (event.target == addModal) {
        addModal.style.display = 'none';
    }
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
}