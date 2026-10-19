<?php
/**
 * 朋友文章
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$friendPosts = nebula_friend_posts(20, $this->request->get('friend_page', 1));
$this->need('header.php');
?>
<main class="container page-friends" id="main-content">
    <header class="page-head reveal">
        <p class="page-kicker">STORIES FROM FRIENDS</p>
        <h1 class="grad-text"><?php $this->title(); ?></h1>
        <p>循着订阅，读一读朋友们的新文章</p>
    </header>
    <?php if (trim((string) $this->text) !== ''): ?><div class="page-intro article-content reveal"><?php $this->content(); ?></div><?php endif; ?>

    <section class="friend-posts-layout" aria-labelledby="friend-posts-heading">
        <?php if ($friendPosts['enabled']): ?>
            <div class="friend-posts-toolbar reveal">
                <h2 id="friend-posts-heading"><span class="memos-live-dot" aria-hidden="true"></span>最近文章 <span class="friend-posts-count"><?php echo $friendPosts['total']; ?> 篇</span></h2>
                <p>RSS 自动更新<?php if ($friendPosts['updated'] > 0): ?> · <time datetime="<?php echo gmdate('c', $friendPosts['updated']); ?>" title="最近更新：<?php echo (new \Typecho\Date($friendPosts['updated']))->format('Y-m-d H:i'); ?>"><?php echo (new \Typecho\Date($friendPosts['updated']))->format('m-d H:i'); ?></time><?php endif; ?></p>
            </div>
        <?php else: ?>
            <h2 id="friend-posts-heading" class="sr-only">朋友文章</h2>
        <?php endif; ?>

        <?php if (!$friendPosts['enabled']): ?>
            <div class="empty-state reveal"><span class="empty-mark" aria-hidden="true">✦</span><h2>朋友文章尚未开启</h2><p>开启后，这里会通过 RSS 自动汇聚朋友们的最新文章。</p></div>
        <?php elseif (!$friendPosts['items']): ?>
            <div class="empty-state reveal"><span class="empty-mark" aria-hidden="true">✦</span><h2>等待朋友们的新文章</h2><p>这里通过 RSS 自动更新，收录后就能看到朋友们的最近文章。</p></div>
        <?php else: ?>
            <div class="friend-posts-feed">
                <?php foreach ($friendPosts['items'] as $index => $item): ?>
                    <?php $friend = $item['friend']; $published = (int) $item['published']; ?>
                    <article class="friend-post-card reveal" style="--d:<?php echo ($index % 3) * .04; ?>s">
                        <div class="friend-post-meta">
                            <a class="friend-post-source" href="<?php echo htmlspecialchars($friend['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-no-swup>
                                <span class="friend-post-avatar av-<?php echo ($index % 6) + 1; ?>" aria-hidden="true">
                                    <span><?php echo htmlspecialchars(mb_substr($friend['name'], 0, 1), ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if ($friend['image'] !== ''): ?><img src="<?php echo htmlspecialchars($friend['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" referrerpolicy="no-referrer"><?php endif; ?>
                                </span>
                                <span class="friend-post-site"><?php echo htmlspecialchars($friend['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </a>
                            <?php if ($published > 0): ?><time datetime="<?php echo gmdate('c', $published); ?>"><?php echo (new \Typecho\Date($published))->format('Y-m-d'); ?></time><?php else: ?><span class="friend-post-undated">发布时间未提供</span><?php endif; ?>
                        </div>
                        <h3><a href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" data-no-swup><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?><span class="friend-post-arrow" aria-hidden="true">↗</span></a></h3>
                        <?php if ($item['summary'] !== ''): ?><p class="friend-post-summary"><?php echo htmlspecialchars(\Typecho\Common::subStr($item['summary'], 0, 180, '…'), ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
                        <?php if ($item['author'] !== '' && $item['author'] !== $friend['name']): ?><p class="friend-post-author">作者 · <?php echo htmlspecialchars($item['author'], ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($friendPosts['pages'] > 1): ?>
            <nav class="friend-posts-pager reveal" aria-label="朋友文章分页">
                <?php if ($friendPosts['page'] > 1): ?><a class="btn-ghost" href="<?php echo htmlspecialchars(nebula_friend_posts_page_url($this->permalink, $friendPosts['page'] - 1), ENT_QUOTES, 'UTF-8'); ?>" rel="prev">上一页</a><?php else: ?><span class="btn-ghost" aria-disabled="true">上一页</span><?php endif; ?>
                <span class="friend-posts-page" aria-current="page">第 <?php echo $friendPosts['page']; ?> / <?php echo $friendPosts['pages']; ?> 页</span>
                <?php if ($friendPosts['page'] < $friendPosts['pages']): ?><a class="btn-ghost" href="<?php echo htmlspecialchars(nebula_friend_posts_page_url($this->permalink, $friendPosts['page'] + 1), ENT_QUOTES, 'UTF-8'); ?>" rel="next">下一页</a><?php else: ?><span class="btn-ghost" aria-disabled="true">下一页</span><?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>
</main>
<?php $this->need('footer.php'); ?>
