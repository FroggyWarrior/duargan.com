<?php use App\Core\ViewHelper; ?>
<footer>
    <div class="footer-content">
        <a href="/">
            <img src="/img/logo.svg" alt="Duargan Logo" class="logo">
        </a>
        
        <?php echo ViewHelper::renderSocialMedia($social_media, 'footer-social'); ?>
        
        <p>This website is licensed under the <a href="https://www.gnu.org/licenses/gpl-3.0.html" target="_blank">GPL-3.0 License</a>.</p>
        <p>Source code available on <a href="https://github.com/Duargan/duargan.com" target="_blank">GitHub</a>.</p>
    </div>
</footer>

</body>
</html>