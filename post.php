<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; $this->need('header.php'); ?>
<main class="container article-shell" id="main-content">
    <article class="article" itemscope itemtype="https://schema.org/BlogPosting">
        <header class="article-head reveal">
            <h1 itemprop="headline"><?php $this->title(); ?></h1>
            <div class="article-meta">
                <span><?php $this->author(); ?></span>
                <time datetime="<?php $this->date('c'); ?>" itemprop="datePublished"><?php $this->date('Y-m-d'); ?></time>
                <span class="article-category"><?php $this->category(' / ', true, '未分类'); ?></span>
                <span><?php $this->commentsNum('暂无评论', '1 条评论', '%d 条评论'); ?></span>
            </div>
        </header>
        <?php $summary = nebula_post_summary($this); if ($summary !== ''): ?>
            <aside class="article-summary reveal" aria-label="文章摘要" itemprop="abstract">
                <span>摘要</span>
                <p><?php echo nl2br(htmlspecialchars($summary, ENT_QUOTES, 'UTF-8'), false); ?></p>
            </aside>
        <?php endif; ?>
        <div class="article-content reveal" itemprop="articleBody"><?php $this->content(); ?></div>
        <footer class="article-footer">
            <div class="article-tags"><?php $this->tags(' ', true, '<em>暂无标签</em>'); ?></div>
        </footer>
    </article>

    <nav class="post-near" aria-label="相邻文章">
        <div><span>上一篇</span><?php $this->thePrev('%s', '已经是最早一篇'); ?></div>
        <div><span>下一篇</span><?php $this->theNext('%s', '已经是最新一篇'); ?></div>
    </nav>
    <?php $this->need('comments.php'); ?>
</main>
<?php $this->need('footer.php'); ?>
