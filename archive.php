<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; $this->need('header.php'); ?>
<main class="container page-list" id="main-content">
    <header class="page-head reveal">
        <p class="page-kicker"><?php echo $this->is('search') ? 'SEARCH RESULTS' : 'EXPLORE THE ARCHIVE'; ?></p>
        <h1 class="grad-text"><?php $this->archiveTitle([
            'category' => _t('分类：%s'),
            'search' => _t('搜索：%s'),
            'tag' => _t('标签：%s'),
            'author' => _t('%s 的文章')
        ], '', ''); ?></h1>
        <p><?php echo $this->have() ? '在星云中找到这些内容' : '没有找到符合条件的内容'; ?></p>
    </header>

    <?php if ($this->have()): ?>
        <div class="post-grid list-grid">
            <?php $cardIndex = 0; while ($this->next()): $cover = nebula_post_cover($this); $cardIndex++; ?>
                <article class="post-card reveal" itemscope itemtype="https://schema.org/BlogPosting">
                    <a class="post-cover cover-<?php echo (($cardIndex - 1) % 6) + 1; ?>" href="<?php $this->permalink(); ?>" tabindex="-1" aria-hidden="true">
                        <?php if ($cover['url']): ?><img src="<?php echo htmlspecialchars($cover['url'], ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy"><?php endif; ?>
                        <span class="cover-label"><?php echo htmlspecialchars($cover['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                    <div class="post-body">
                        <div class="post-meta"><time datetime="<?php $this->date('c'); ?>"><?php $this->date('Y-m-d'); ?></time><span><?php $this->commentsNum('0 评论', '1 评论', '%d 评论'); ?></span></div>
                        <h2 itemprop="headline"><a href="<?php $this->permalink(); ?>" itemprop="url"><?php $this->title(); ?></a></h2>
                        <p class="post-excerpt"><?php $this->excerpt(100, '…'); ?></p>
                        <div class="post-foot"><span class="post-tags"><?php $this->tags(' ', true, ''); ?></span></div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php if (!$this->is('single')): ?>
            <?php $this->pageNav('上一页', '下一页', 3, '…', ['wrapTag' => 'nav', 'wrapClass' => 'pager', 'itemTag' => 'span', 'textTag' => 'span', 'currentClass' => 'current']); ?>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state reveal"><span class="empty-mark" aria-hidden="true">✦</span><h2>没有找到内容</h2><p>换一个关键词，或返回首页继续浏览。</p><a class="btn-ghost" href="<?php $this->options->siteUrl(); ?>">返回首页</a></div>
    <?php endif; ?>
</main>
<?php $this->need('footer.php'); ?>
