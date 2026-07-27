<?php
/**
 * 分类目录
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
\Widget\Metas\Category\Rows::alloc()->to($categories);
?>
<main class="container page-categories" id="main-content">
    <header class="page-head reveal">
        <p class="page-kicker">BROWSE BY ORBIT</p>
        <h1 class="grad-text">内容分类</h1>
        <p>沿着不同轨道，探索感兴趣的主题</p>
    </header>
    <div class="category-grid">
        <?php $hasCategories = false; $index = 0; while ($categories->next()): $hasCategories = true; $index++; ?>
            <a class="category-card reveal" href="<?php $categories->permalink(); ?>" style="--d:<?php echo min(($index % 4) * .07, .21); ?>s">
                <span class="category-index" aria-hidden="true"><?php echo str_pad((string) $index, 2, '0', STR_PAD_LEFT); ?></span>
                <span class="category-info"><strong><?php $categories->name(); ?></strong><small><?php echo (int) $categories->count; ?> 篇文章</small></span>
                <span class="category-arrow" aria-hidden="true">→</span>
            </a>
        <?php endwhile; ?>
        <?php if (!$hasCategories): ?><div class="empty-state"><span class="empty-mark" aria-hidden="true">✦</span><h2>暂无分类</h2><p>创建文章分类后，它们会显示在这里。</p></div><?php endif; ?>
    </div>
</main>
<?php $this->need('footer.php'); ?>
