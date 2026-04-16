document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------------------
    // ORGANIZATION APPLICATIONS - CONFIRM ACCEPT / REJECT
    // Keeps the existing confirmation before a status-changing action.
    // -------------------------------------------------------------------------
    document.querySelectorAll('.confirm-action').forEach(function (button) {
        button.addEventListener('click', function (event) {
            var message = button.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // -------------------------------------------------------------------------
    // ORGANIZATION APPLICATIONS - MORE DETAILS TOGGLE
    // Expands and collapses extra applicant details inside each compact card.
    // -------------------------------------------------------------------------
    document.querySelectorAll('[data-app-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var targetId = button.getAttribute('data-target');
            var detailsPanel = targetId ? document.getElementById(targetId) : null;
            if (!detailsPanel) {
                return;
            }

            var isOpening = detailsPanel.hasAttribute('hidden');
            if (isOpening) {
                detailsPanel.removeAttribute('hidden');
            } else {
                detailsPanel.setAttribute('hidden', 'hidden');
            }

            button.setAttribute('aria-expanded', isOpening ? 'true' : 'false');

            var labelNode = button.querySelector('[data-toggle-label]');
            var openLabel = button.getAttribute('data-open-label') || 'More Details';
            var closeLabel = button.getAttribute('data-close-label') || 'Hide Details';
            if (labelNode) {
                labelNode.textContent = isOpening ? closeLabel : openLabel;
            }

            var iconNode = button.querySelector('i');
            if (iconNode) {
                iconNode.classList.toggle('ph-caret-down', !isOpening);
                iconNode.classList.toggle('ph-caret-up', isOpening);
            }
        });
    });
});
