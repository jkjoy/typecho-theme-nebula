<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="dark">
<head>
    <meta charset="<?php $this->options->charset(); ?>">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#070b14">
    <meta name="description" content="<?php $this->options->description(); ?>">
    <title><?php $this->archiveTitle([
        'category' => _t('分类 %s'),
        'search' => _t('搜索 %s'),
        'tag' => _t('标签 %s'),
        'author' => _t('%s 的文章')
    ], '', ' - '); ?><?php $this->options->title(); ?></title>
    <script>try{document.documentElement.dataset.theme=localStorage.getItem('nebula-theme')||'dark'}catch(e){}</script>
    <?php if (nebula_option('faviconUrl')): ?>
        <link rel="icon" href="<?php echo htmlspecialchars(nebula_option('faviconUrl'), ENT_QUOTES, 'UTF-8'); ?>">
    <?php else: ?>
        <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Cdefs%3E%3ClinearGradient id='g' x2='1' y2='1'%3E%3Cstop stop-color='%237c3aed'/%3E%3Cstop offset='.55' stop-color='%2306b6d4'/%3E%3Cstop offset='1' stop-color='%23f472b6'/%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath fill='url(%23g)' d='M32 2c2 13 6 20 12 24s12 5 18 6c-6 1-12 2-18 6S34 49 32 62c-2-13-6-20-12-24S8 33 2 32c6-1 12-2 18-6S30 15 32 2z'/%3E%3C/svg%3E">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(nebula_asset_url('assets/css/style.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <?php $this->header('generator=&template=&pingback=&xmlrpc=&wlw='); ?>
    <?php if (nebula_option('analyticsCode')) echo nebula_option('analyticsCode'); ?>
</head>
<body>
<a class="skip-link" href="#main-content">跳到正文</a>
<canvas id="bg-canvas" aria-hidden="true"></canvas>
<div class="aurora" aria-hidden="true"><span></span><span></span><span></span></div>

<header class="site-header">
    <div class="container nav-inner">
        <a class="brand" href="<?php $this->options->siteUrl(); ?>" aria-label="<?php $this->options->title(); ?>首页">
            <?php if (nebula_option('logoUrl')): ?>
                <img src="<?php echo htmlspecialchars(nebula_option('logoUrl'), ENT_QUOTES, 'UTF-8'); ?>" alt="">
            <?php else: ?>
                <span class="brand-mark" aria-hidden="true"></span>
            <?php endif; ?>
            <span class="brand-name grad-text"><?php $this->options->title(); ?></span>
        </a>

        <?php \Widget\Contents\Page\Rows::alloc()->to($navPages); ?>
        <nav class="site-nav" id="site-nav" aria-label="主导航">
            <a href="<?php $this->options->siteUrl(); ?>"<?php if ($this->is('index')): ?> class="active" aria-current="page"<?php endif; ?>>首页</a>
            <?php while ($navPages->next()): ?>
            <?php $navActive = $this->is('page', $navPages->slug) || ($navPages->slug === 'tags' && $this->is('tag')); ?>
            <a href="<?php echo htmlspecialchars((string) $navPages->permalink, ENT_QUOTES, 'UTF-8'); ?>"<?php if ($navActive): ?> class="active" aria-current="page"<?php endif; ?>><?php echo htmlspecialchars((string) $navPages->title, ENT_QUOTES, 'UTF-8'); ?></a>
            <?php endwhile; ?>
        </nav>

        <div class="nav-actions">
            <button class="icon-button" id="search-toggle" type="button" aria-label="打开搜索" aria-expanded="false" aria-controls="search-panel">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.8-3.8"></path></svg>
            </button>
            <button class="icon-button theme-toggle" id="theme-toggle" type="button" aria-label="切换明暗主题">
                <span class="theme-icon icon-moon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8z"></path></svg></span>
                <span class="theme-icon icon-sun"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path></svg></span>
            </button>
            <button class="icon-button menu-toggle" id="menu-toggle" type="button" aria-label="打开菜单" aria-expanded="false" aria-controls="site-nav"><span></span><span></span><span></span></button>
        </div>
    </div>
    <div class="search-panel" id="search-panel" hidden>
        <form class="container search-form" method="post" action="<?php $this->options->siteUrl(); ?>" role="search">
            <label class="sr-only" for="search-input">搜索文章</label>
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.8-3.8"></path></svg>
            <input id="search-input" type="search" name="s" placeholder="输入关键词搜索文章" autocomplete="off">
            <button type="submit">搜索</button>
        </form>
    </div>
</header>
