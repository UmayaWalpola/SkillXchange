document.getElementById('confirmTransferForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = this.querySelector('.btn-confirm');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Processing...';
    
    const formData = new FormData(this);
    
    fetch('<?= URLROOT ?>/wallet/processTransfer', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.href = '<?= URLROOT ?>/wallet';
        } else {
            alert(data.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});