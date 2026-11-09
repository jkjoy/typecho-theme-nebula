<?php
/**
 * 瞬间动态
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$memos = nebula_memos(20, $this->request->get('memo-page', 1));
$escape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$sourceIcons = [
    'Web端' => '<rect x="3" y="4" width="18" height="12" rx="2"></rect><path d="M8 20h8m-4-4v4"></path>',
    '手机端' => '<rect x="6" y="2" width="12" height="20" rx="2"></rect><path d="M11 18h2"></path>',
    'API' => '<path d="m8 7-5 5 5 5m8-10 5 5-5 5M14 4l-4 16"></path>',
    'default' => '<circle cx="12" cy="12" r="9"></circle><path d="M3 12h18M12 3a18 18 0 0 1 0 18 18 18 0 0 1 0-18Z"></path>',
];
foreach (nebula_sns_services() as $service) {
    if (in_array($service['label'], ['QQ', '微信'], true)) {
        $sourceIcons[$service['label']] = '<path fill="currentColor" stroke="none" d="' . $service['path'] . '"></path>';
    }
}
$this->need('header.php');
?>
<main class="container page-memos" id="main-content">
    <header class="page-head reveal">
        <p class="page-kicker">MOMENTS IN ORBIT</p>
        <h1 class="grad-text"><?php $this->title(); ?></h1>
        <p>捕捉日常里的微光，收藏此刻的心情</p>
    </header>

    <section class="memos-layout reveal" data-memos aria-labelledby="memos-title">
        <h2 class="sr-only" id="memos-title">瞬间动态</h2>
        <?php if ($memos['available'] && !$memos['error']): ?>
            <div class="memos-toolbar">
                <span class="memos-toolbar-label"><span class="memos-live-dot" aria-hidden="true"></span>最近动态</span>
                <span class="memos-status" role="status">共 <?php echo $memos['total']; ?> 条动态</span>
            </div>
        <?php endif; ?>

        <?php if ($memos['error']): ?>
            <div class="memos-message"><h2>瞬间暂时无法读取</h2><p>请检查 Enhancement 插件是否已更新并正常启用，然后刷新页面重试。</p></div>
        <?php elseif (!$memos['available']): ?>
            <div class="memos-message"><h2>请先安装瞬间插件</h2><p>安装并启用 Enhancement 插件的瞬间功能后，即可在这里展示你发布的公开动态。</p><a class="btn-ghost" href="https://github.com/jkjoy/Enhancement" target="_blank" rel="noopener noreferrer" data-no-swup>获取 Enhancement 插件</a></div>
        <?php elseif (!$memos['items']): ?>
            <div class="memos-message"><h2>等待新的瞬间</h2><p>还没有公开动态，新的念头出现后会在这里与你见面。</p></div>
        <?php else: ?>
            <div class="memos-feed">
                <?php foreach ($memos['items'] as $index => $memo): ?>
                    <?php $published = $memo['created'] > 0 ? new \Typecho\Date($memo['created']) : null; ?>
                    <article class="memo-item" id="memo-<?php echo $memo['mid']; ?>" style="--d:<?php echo min($index * .04, .24); ?>s">
                        <a class="memo-date" href="#memo-<?php echo $memo['mid']; ?>" title="<?php echo $published ? $escape($published->format('Y-m-d H:i')) : '时间未知'; ?>">
                            <time<?php if ($published): ?> datetime="<?php echo $escape(gmdate('c', $memo['created'])); ?>"<?php endif; ?>><?php echo $published ? $escape($published->word()) : '时间未知'; ?></time>
                            <?php if ($published): ?><small><?php echo $escape($published->format('H:i')); ?></small><?php endif; ?>
                        </a>
                        <div class="memo-card">
                            <?php if ($memo['tags']): ?>
                                <div class="memo-tags">
                                    <?php foreach ($memo['tags'] as $tag): ?>
                                        <span class="memo-tag"><svg class="memo-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9h16M3 15h16M10 3 8 21M16 3l-2 18"></path></svg><span><?php echo $escape($tag); ?></span></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($memo['content'] !== ''): ?><div class="memo-content"><?php echo $memo['content']; ?></div><?php endif; ?>
                            <?php if ($memo['media']): ?>
                                <div class="memo-media" data-count="<?php echo min(count($memo['media']), 4); ?>">
                                    <?php foreach ($memo['media'] as $mediaIndex => $media): ?>
                                        <?php if (in_array($media['type'], ['PHOTO', 'IMAGE'], true)): ?>
                                            <a class="memo-photo" href="<?php echo $escape($media['url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="预览图片" data-no-swup><img src="<?php echo $escape($media['url']); ?>" alt="瞬间 <?php echo $memo['mid']; ?> 的第 <?php echo $mediaIndex + 1; ?> 张配图" loading="lazy"></a>
                                        <?php elseif ($media['type'] === 'VIDEO'): ?>
                                            <video src="<?php echo $escape($media['url']); ?>" controls preload="metadata"></video>
                                        <?php elseif ($media['type'] === 'AUDIO'): ?>
                                            <audio src="<?php echo $escape($media['url']); ?>" controls preload="metadata"></audio>
                                        <?php else: ?>
                                            <a class="memo-attachment" href="<?php echo $escape($media['url']); ?>" target="_blank" rel="noopener noreferrer" data-no-swup>查看附件</a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <footer class="memo-foot">
                                <?php if ($memo['location'] !== ''): ?>
                                    <?php if ($memo['location_url'] !== ''): ?>
                                        <a class="memo-location" href="<?php echo $escape($memo['location_url']); ?>" target="_blank" rel="noopener noreferrer" title="在地图中查看位置" data-no-swup>
                                    <?php else: ?>
                                        <span class="memo-location">
                                    <?php endif; ?>
                                        <svg class="memo-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z"></path><circle cx="12" cy="10" r="2.5"></circle></svg>
                                        <span><span class="sr-only">位置：</span><?php echo $escape($memo['location']); ?></span>
                                    <?php if ($memo['location_url'] !== ''): ?></a><?php else: ?></span><?php endif; ?>
                                <?php endif; ?>
                                <span class="memo-source"><svg class="memo-icon" viewBox="0 0 24 24" aria-hidden="true"><?php echo $sourceIcons[$memo['source']] ?? $sourceIcons['default']; ?></svg><span>来自 · <?php echo $escape($memo['source']); ?></span></span>
                            </footer>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$memos['error'] && $memos['pages'] > 1): ?>
            <nav class="memos-pager" aria-label="瞬间分页">
                <?php if ($memos['page'] > 1): ?><a class="btn-ghost" href="<?php echo $escape(nebula_memos_page_url($this->permalink, $memos['page'] - 1)); ?>" rel="prev">上一页</a><?php else: ?><span class="btn-ghost" aria-disabled="true">上一页</span><?php endif; ?>
                <span aria-current="page">第 <?php echo $memos['page']; ?> / <?php echo $memos['pages']; ?> 页</span>
                <?php if ($memos['page'] < $memos['pages']): ?><a class="btn-ghost" href="<?php echo $escape(nebula_memos_page_url($this->permalink, $memos['page'] + 1)); ?>" rel="next">下一页</a><?php else: ?><span class="btn-ghost" aria-disabled="true">下一页</span><?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>

    <dialog class="memo-lightbox" data-memo-lightbox aria-label="图片预览">
        <button class="memo-lightbox-close" type="button" data-lightbox-close aria-label="关闭图片预览">&times;</button>
        <button class="memo-lightbox-nav memo-lightbox-prev" type="button" data-lightbox-prev aria-label="上一张图片">&#8249;</button>
        <figure><img data-lightbox-image alt=""><figcaption data-lightbox-caption></figcaption></figure>
        <button class="memo-lightbox-nav memo-lightbox-next" type="button" data-lightbox-next aria-label="下一张图片">&#8250;</button>
    </dialog>
</main>
<?php $this->need('footer.php'); ?>
