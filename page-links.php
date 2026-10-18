<?php
/**
 * 友情链接
 *
 * @package custom
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
$links = nebula_friend_links();
$application = nebula_friend_link_application();
$applicationSubmitted = $application && (string) $this->request->get('enhancement_submitted') === '1';
$logoUrl = nebula_option('logoUrl');
?>
<main class="container page-links" id="main-content">
    <header class="page-head reveal">
        <p class="page-kicker">FRIENDS ACROSS THE WEB</p>
        <h1 class="grad-text"><?php $this->title(); ?></h1>
        <p>海内存知己，天涯若比邻</p>
    </header>
    <?php if (trim((string) $this->text) !== ''): ?><div class="page-intro article-content reveal"><?php $this->content(); ?></div><?php endif; ?>
    <?php if ($links): ?>
        <div class="link-grid">
            <?php foreach ($links as $index => $link): ?>
                <a class="link-card reveal" href="<?php echo htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" style="--d:<?php echo ($index % 4) * .04; ?>s">
                    <span class="link-avatar av-<?php echo ($index % 6) + 1; ?>">
                        <?php if ($link['avatar']): ?><img src="<?php echo htmlspecialchars($link['avatar'], ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy"><?php else: ?><?php echo htmlspecialchars(mb_substr($link['name'], 0, 1), ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                    </span>
                    <span class="link-info"><strong class="name"><?php echo htmlspecialchars($link['name'], ENT_QUOTES, 'UTF-8'); ?></strong><span class="desc"><?php echo htmlspecialchars($link['description'] ?: '去拜访这位朋友', ENT_QUOTES, 'UTF-8'); ?></span></span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state reveal"><span class="empty-mark" aria-hidden="true">✦</span><h2>友链正在汇聚</h2><p>期待与你的站点相遇。</p></div>
    <?php endif; ?>
    <section class="apply-card reveal">
        <p class="section-kicker">LINK EXCHANGE</p>
        <h2>交换友链</h2>
        <?php if ($application): ?>
            <p>欢迎内容持续更新、可稳定访问并已启用 HTTPS 的独立站点。填写下方申请表，提交后会在审核通过后展示。</p>
        <?php else: ?>
            <p>欢迎内容持续更新、可稳定访问并已启用 HTTPS 的独立站点。请在任意文章下留言，附上站点名称、链接与一句简介。</p>
        <?php endif; ?>
        <div class="site-info">
            <span>名称</span><strong><?php $this->options->title(); ?></strong>
            <span>链接</span><strong><?php $this->options->siteUrl(); ?></strong>
            <?php if ($logoUrl): ?><span>Logo</span><strong><?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?></strong><?php endif; ?>
            <span>描述</span><strong><?php $this->options->description(); ?></strong>
        </div>
        <?php if ($application): ?>
            <form class="link-apply-form" data-link-apply-form data-turnstile-required="<?php echo $application['turnstile_required'] ? 'true' : 'false'; ?>" action="<?php echo htmlspecialchars($application['action'], ENT_QUOTES, 'UTF-8'); ?>" method="post" data-no-swup>
                <input type="hidden" name="do" value="submit">
                <div hidden aria-hidden="true"><input type="text" name="homepage" value="" tabindex="-1" autocomplete="off"></div>
                <div class="link-apply-fields">
                    <label for="link-apply-name"><span>站点名称 <em>必填</em></span><input id="link-apply-name" name="name" type="text" maxlength="50" autocomplete="organization" required></label>
                    <label for="link-apply-url"><span>站点链接 <em>必填</em></span><input id="link-apply-url" name="url" type="url" maxlength="200" pattern="https?://.+" placeholder="https://" autocomplete="url" required></label>
                    <label for="link-apply-email"><span>联系邮箱 <em>选填</em></span><input id="link-apply-email" name="email" type="email" maxlength="50" autocomplete="email" aria-describedby="link-apply-email-hint"><small id="link-apply-email-hint">仅用于联系，不会公开展示。</small></label>
                    <label for="link-apply-image"><span>站点图标 <em>选填</em></span><input id="link-apply-image" name="image" type="url" maxlength="200" pattern="https?://.+" placeholder="https://" autocomplete="off"></label>
                    <label class="link-apply-description" for="link-apply-description"><span>一句简介 <em>选填</em></span><textarea id="link-apply-description" name="description" maxlength="200" rows="3" placeholder="介绍一下你的站点，最多 200 字"></textarea></label>
                </div>
                <?php if ($application['turnstile_required']): ?>
                    <div class="link-apply-challenge" data-sitekey="<?php echo htmlspecialchars($application['turnstile_site_key'], ENT_QUOTES, 'UTF-8'); ?>"></div>
                    <noscript><p class="link-apply-note">请启用 JavaScript，完成人机验证后提交申请。</p></noscript>
                <?php endif; ?>
                <div class="link-apply-actions"><button class="btn" type="submit" data-link-apply-submit>提交申请 →</button><span>审核通过后展示</span></div>
                <p class="link-apply-status" data-link-apply-status role="status" aria-live="polite"<?php if ($applicationSubmitted): ?> data-state="success"<?php else: ?> hidden<?php endif; ?>><?php if ($applicationSubmitted): ?>提交成功，等待审核。<?php endif; ?></p>
            </form>
        <?php endif; ?>
    </section>
</main>
<?php $this->need('footer.php'); ?>
