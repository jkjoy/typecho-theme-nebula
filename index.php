<?php
/**
 * Nebula 是一款深空与极光风格的 Typecho 博客主题。
 *
 * @package Nebula
 * @author 老孙
 * @version 1.0.7
 * @link https://imsun.org
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>
<main class="container page-index" id="main-content">
    <section class="posts-section" id="posts">
        <div class="section-heading reveal">
            <div><p class="section-kicker">LATEST WRITING</p><h2 class="section-title">最新文章</h2></div>
        </div>

        <?php if ($this->have()): ?>
            <div class="post-grid">
                <?php $cardIndex = 0; while ($this->next()): $cover = nebula_post_cover($this); $cardIndex++; ?>
                    <article class="post-card reveal" style="--d:<?php echo min(($cardIndex % 3) * .08, .24); ?>s" itemscope itemtype="https://schema.org/BlogPosting">
                        <a class="post-cover cover-<?php echo (($cardIndex - 1) % 6) + 1; ?>" href="<?php $this->permalink(); ?>" tabindex="-1" aria-hidden="true">
                            <?php if ($cover['url']): ?><img src="<?php echo htmlspecialchars($cover['url'], ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy"><?php endif; ?>
                            <?php if (!$cover['url']): ?><span class="cover-label"><?php echo htmlspecialchars($cover['label'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                        </a>
                        <div class="post-body">
                            <div class="post-meta"><time datetime="<?php $this->date('c'); ?>"><?php $this->date('Y-m-d'); ?></time><span><?php $this->commentsNum('0 评论', '1 评论', '%d 评论'); ?></span></div>
                            <h3 itemprop="headline"><a href="<?php $this->permalink(); ?>" itemprop="url"><?php $this->title(); ?></a></h3>
                            <p class="post-excerpt"><?php echo htmlspecialchars(nebula_post_excerpt($this), ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="post-foot"><span class="post-tags"><?php $this->tags(' ', true, ''); ?></span></div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            <?php $this->pageNav('上一页', '下一页', 3, '…', ['wrapTag' => 'nav', 'wrapClass' => 'pager', 'itemTag' => 'span', 'textTag' => 'span', 'currentClass' => 'current']); ?>
        <?php else: ?>
            <div class="empty-state reveal"><span class="empty-mark" aria-hidden="true">✦</span><h2>星图尚未点亮</h2><p>这里还没有文章，第一篇内容发布后会出现在这里。</p></div>
        <?php endif; ?>
    </section>
</main>
<?php $this->need('footer.php'); ?>
