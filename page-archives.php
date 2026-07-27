<?php
/**
 * 文章归档
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
$stats = nebula_site_stats();
\Widget\Contents\Post\Recent::alloc('pageSize=10000')->to($archivePosts);
?>
<main class="container page-archives" id="main-content">
    <header class="page-head reveal">
        <p class="page-kicker">ALL STORIES, ONE TIMELINE</p>
        <h1 class="grad-text">文章归档</h1>
        <p>沿着时间的轨迹，回看每一次记录</p>
    </header>
    <section class="stats reveal" aria-label="站点统计">
        <div class="stat"><strong class="num"><?php echo $stats['posts']; ?></strong><span class="label">总文章</span></div>
        <div class="stat"><strong class="num"><?php echo $stats['categories']; ?></strong><span class="label">分类</span></div>
        <div class="stat"><strong class="num"><?php echo $stats['comments']; ?></strong><span class="label">评论</span></div>
        <div class="stat"><strong class="num"><?php echo $stats['days']; ?></strong><span class="label">建站天数</span></div>
    </section>
    <div class="timeline">
        <?php $year = null; $hasPosts = false; while ($archivePosts->next()): $hasPosts = true; $postYear = date('Y', (int) $archivePosts->created); ?>
            <?php if ($postYear !== $year): $year = $postYear; ?><h2 class="t-year reveal"><?php echo $year; ?></h2><?php endif; ?>
            <a class="t-item reveal" href="<?php $archivePosts->permalink(); ?>">
                <time class="t-date" datetime="<?php $archivePosts->date('c'); ?>"><?php $archivePosts->date('m-d'); ?></time>
                <span class="t-title"><?php $archivePosts->title(); ?></span>
                <span class="t-category"><?php $archivePosts->category(' / ', false, '未分类'); ?></span>
            </a>
        <?php endwhile; ?>
        <?php if (!$hasPosts): ?><div class="empty-state"><span class="empty-mark" aria-hidden="true">✦</span><h2>暂无文章</h2><p>发布第一篇文章后，时间线会在这里展开。</p></div><?php endif; ?>
    </div>
</main>
<?php $this->need('footer.php'); ?>
