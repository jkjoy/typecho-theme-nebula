<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$slug = (string) $this->slug;
$special = ['archives', 'categories', 'tags', 'links'];
if (in_array($slug, $special, true)) {
    $this->need('page-' . $slug . '.php');
    return;
}
$this->need('header.php');
?>
<main class="container article-shell page-article" id="main-content">
    <article class="article" itemscope itemtype="https://schema.org/Article">
        <header class="article-head reveal">
            <p class="page-kicker">PAGE</p>
            <h1 itemprop="headline"><?php $this->title(); ?></h1>
        </header>
        <div class="article-content reveal" itemprop="articleBody"><?php $this->content(); ?></div>
    </article>
    <?php $this->need('comments.php'); ?>
</main>
<?php $this->need('footer.php'); ?>
