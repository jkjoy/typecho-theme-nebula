<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $snsLinks = nebula_sns_links(); ?>
<nav class="sns-bar" aria-label="社交媒体与订阅">
    <div class="container sns-links">
        <?php foreach ($snsLinks as $sns): ?>
        <a class="sns-link" href="<?php echo htmlspecialchars($sns['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo htmlspecialchars($sns['label'], ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($sns['label'], ENT_QUOTES, 'UTF-8'); ?>">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?php echo $sns['path']; ?>"></path></svg>
        </a>
        <?php endforeach; ?>
        <a class="sns-link" href="<?php $this->options->feedUrl(); ?>" aria-label="订阅 RSS" title="订阅 RSS">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19.199 24C19.199 13.401 10.599 4.8 0 4.8V0c13.255 0 24 10.745 24 24h-4.801zM3.291 17.415a3.293 3.293 0 1 0 0 6.585 3.293 3.293 0 0 0 0-6.585zM15.909 24h-4.665c0-6.213-5.04-11.244-11.244-11.244V8.091C8.79 8.091 15.909 15.21 15.909 24z"></path></svg>
        </a>
    </div>
</nav>
<footer class="site-footer">
    <div class="container footer-inner">
        <p>&copy; <?php echo date('Y'); ?> <a class="grad-text" href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a></p>
        <p>Powered by <a href="https://typecho.org" rel="noopener noreferrer">Typecho</a> · Theme <a href="https://github.com/jkjoy/typecho-theme-nebula" target="_blank" rel="noopener noreferrer">Nebula</a></p>
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
