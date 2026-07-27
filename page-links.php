<?php
/**
 * 友情链接
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
$links = nebula_friend_links();
$logoUrl = nebula_option('logoUrl');
?>
<main class="container page-links" id="main-content">
    <header class="page-head reveal">
        <p class="page-kicker">FRIENDS ACROSS THE WEB</p>
        <h1 class="grad-text"><?php $this->title(); ?></h1>
        <p>海内存知己，天涯若比邻</p>
    </header>
    <?php if (trim((string) $this->text) !== ''): ?><div class="page-intro article-content reveal"><?php $this->content(); ?></div><?php endif; ?>
    <?php if ($links): ?>
        <div class="link-grid">
            <?php foreach ($links as $index => $link): ?>
                <a class="link-card reveal" href="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" style="--d:<?php echo min(($index % 4) * .07, .21); ?>s">
                    <span class="link-avatar av-<?php echo ($index % 6) + 1; ?>">
                        <?php if ($link['avatar']): ?><img src="<?php echo htmlspecialchars($link['avatar'], ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy"><?php else: ?><?php echo htmlspecialchars(mb_substr($link['name'], 0, 1), ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                    </span>
                    <span class="link-info"><strong class="name"><?php echo htmlspecialchars($link['name'], ENT_QUOTES, 'UTF-8'); ?></strong><span class="desc"><?php echo htmlspecialchars($link['description'] ?: '去拜访这位朋友', ENT_QUOTES, 'UTF-8'); ?></span></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state reveal"><span class="empty-mark" aria-hidden="true">✦</span><h2>友链正在汇聚</h2><p>在主题设置中按格式添加友链后，会显示在这里。</p></div>
    <?php endif; ?>
    <section class="apply-card reveal">
        <p class="section-kicker">LINK EXCHANGE</p>
        <h2>交换友链</h2>
        <p>欢迎内容持续更新、可稳定访问并已启用 HTTPS 的独立站点。请在任意文章下留言，附上站点名称、链接与一句简介。</p>
        <div class="site-info">
            <span>名称</span><strong><?php $this->options->title(); ?></strong>
            <span>链接</span><strong><?php $this->options->siteUrl(); ?></strong>
            <?php if ($logoUrl): ?><span>Logo</span><strong><?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?></strong><?php endif; ?>
            <span>描述</span><strong><?php $this->options->description(); ?></strong>
        </div>
    </section>
</main>
<?php $this->need('footer.php'); ?>
