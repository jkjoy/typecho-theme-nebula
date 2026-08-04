<?php
/**
 * 说说动态
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>
<main class="container page-memos" id="main-content">
    <header class="page-head reveal">
        <p class="page-kicker">MOMENTS IN ORBIT</p>
        <h1 class="grad-text"><?php $this->title(); ?></h1>
        <p>捕捉日常里的微光，收藏此刻的心情</p>
    </header>

    <section class="memos-layout reveal" data-memos-endpoint="/api/v1/memos" data-memos-limit="20" aria-labelledby="memos-title">
        <h2 class="sr-only" id="memos-title">说说动态</h2>
        <div class="memos-toolbar">
            <span class="memos-toolbar-label"><span class="memos-live-dot" aria-hidden="true"></span>最近动态</span>
            <span class="memos-status" data-memos-status role="status" aria-live="polite">正在接收信号...</span>
        </div>

        <div class="memos-feed is-loading" data-memos-list aria-busy="true">
            <?php for ($skeleton = 0; $skeleton < 3; $skeleton++): ?>
            <article class="memo-item memo-skeleton" aria-hidden="true">
                <div class="memo-date"><span></span><small></small></div>
                <div class="memo-card"><span class="skeleton-line skeleton-line-short"></span><span class="skeleton-line"></span><span class="skeleton-line skeleton-line-medium"></span></div>
            </article>
            <?php endfor; ?>
        </div>

        <noscript><div class="memos-message"><h2>需要启用 JavaScript</h2><p>说说内容通过接口动态加载，请启用 JavaScript 后查看。</p></div></noscript>

        <nav class="memos-pager" data-memos-pager aria-label="说说分页" hidden>
            <button class="btn-ghost" type="button" data-memos-prev>上一页</button>
            <span data-memos-page>第 1 页</span>
            <button class="btn-ghost" type="button" data-memos-next>下一页</button>
        </nav>
    </section>
</main>

<dialog class="memo-lightbox" data-memo-lightbox aria-label="图片预览">
    <button class="memo-lightbox-close" type="button" data-lightbox-close aria-label="关闭图片预览">&times;</button>
    <button class="memo-lightbox-nav memo-lightbox-prev" type="button" data-lightbox-prev aria-label="上一张图片">&#8249;</button>
    <figure><img data-lightbox-image src="" alt=""><figcaption data-lightbox-caption></figcaption></figure>
    <button class="memo-lightbox-nav memo-lightbox-next" type="button" data-lightbox-next aria-label="下一张图片">&#8250;</button>
</dialog>

<script src="<?php echo htmlspecialchars(nebula_asset_url('assets/js/memos.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php $this->need('footer.php'); ?>
