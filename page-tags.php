<?php
/**
 * 标签云
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
\Widget\Metas\Tag\Cloud::alloc('ignoreZeroCount=1&limit=0&sort=count&desc=1')->to($tags);
?>
<main class="container page-tags" id="main-content">
    <header class="page-head reveal">
        <p class="page-kicker">A CONSTELLATION OF IDEAS</p>
        <h1 class="grad-text">标签星图</h1>
        <p>每一枚标签，都是内容宇宙中的一颗星</p>
    </header>
    <div class="tag-cloud reveal">
        <?php $hasTags = false; while ($tags->next()): $hasTags = true; $level = max(1, min(5, (int) ceil(log(max(1, (int) $tags->count) + 1, 2)))); ?>
            <a class="tag-item tg-<?php echo $level; ?>" href="<?php $tags->permalink(); ?>">#<?php $tags->name(); ?><span class="cnt">×<?php $tags->count(); ?></span></a>
        <?php endwhile; ?>
        <?php if (!$hasTags): ?><div class="empty-state"><span class="empty-mark" aria-hidden="true">✦</span><h2>暂无标签</h2><p>给文章添加标签后，它们会在这里组成星图。</p></div><?php endif; ?>
    </div>
</main>
<?php $this->need('footer.php'); ?>
