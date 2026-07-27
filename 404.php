<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; $this->need('header.php'); ?>
<main class="container error-page" id="main-content">
    <div class="empty-state reveal"><strong class="error-code">404</strong><h1>这颗星暂时不在航线上</h1><p>页面可能已被移动，或从未存在。</p><a class="btn" href="<?php $this->options->siteUrl(); ?>">返回首页</a></div>
</main>
<?php $this->need('footer.php'); ?>
