document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.confirm-action').forEach(function(btn){
        btn.addEventListener('click', function(e){
            var msg = btn.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });
});
