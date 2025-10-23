<footer class="footer-minimal" style="margin-left: 250px !important; width: calc(100% - 250px) !important;">
    <div class="footer-minimal-container">
        <!-- Copy same footer content from footer.php -->
        <div class="footer-minimal-top">
            <div class="footer-contact">
                <a href="mailto:info@skillxchange.com" class="footer-email">info@skillxchange.com</a>
                <a href="tel:+94112345678" class="footer-phone">+94 11 234 5678</a>
            </div>

            <div class="footer-logo-center">
                <a href="<?= URLROOT ?>" class="footer-logo-link">
                    <img src="<?= URLROOT; ?>/assets/images/logo-new.png" alt="SkillXchange Logo" class="logo-image">
                </a>
            </div>

            <div class="footer-social">
                <a href="#" class="social-icon">
                    <svg width="24" height="24" fill="currentColor"><use href="#facebook-icon"/></svg>
                </a>
                <a href="#" class="social-icon">
                    <svg width="24" height="24" fill="currentColor"><use href="#twitter-icon"/></svg>
                </a>
                <a href="#" class="social-icon">
                    <svg width="24" height="24" fill="currentColor"><use href="#linkedin-icon"/></svg>
                </a>
            </div>
        </div>

        <div class="footer-minimal-bottom">
            <p class="footer-copyright">
                © 2024 <a href="<?= URLROOT ?>" class="footer-link-highlight">SkillXchange</a>. All rights reserved.
            </p>
            <div class="footer-legal-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>