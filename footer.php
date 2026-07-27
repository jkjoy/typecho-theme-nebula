<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<footer class="site-footer">
    <div class="container footer-inner">
        <p>&copy; <?php echo date('Y'); ?> <a class="grad-text" href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a></p>
        <p>Powered by <a href="https://typecho.org" rel="noopener noreferrer">Typecho</a> · Theme <a href="https://github.com/jkjoy/typecho-theme-nebula" target="_blank" rel="noopener noreferrer">Nebula</a></p>
        <p><a href="<?php $this->options->feedUrl(); ?>">订阅 RSS</a></p>
        <?php if (nebula_option('footerText')): ?><div class="footer-extra"><?php echo nebula_option('footerText'); ?></div><?php endif; ?>
    </div>
</footer>
<button class="back-top" id="back-top" type="button" aria-label="回到顶部">
    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"></path></svg>
</button>
<script src="<?php echo htmlspecialchars(nebula_asset_url('assets/js/main.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php $this->footer(); ?>
</body>
</html>
