<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

function themeConfig($form)
{
    $fields = [
        new \Typecho\Widget\Helper\Form\Element\Text(
            'logoUrl',
            null,
            null,
            _t('Logo 地址'),
            _t('留空时显示站点名称。建议使用透明背景图片。')
        ),
        new \Typecho\Widget\Helper\Form\Element\Text(
            'faviconUrl',
            null,
            null,
            _t('Favicon 地址'),
            _t('浏览器标签页图标地址，留空使用主题内置星云图标。')
        ),
        new \Typecho\Widget\Helper\Form\Element\Textarea(
            'friendLinks',
            null,
            null,
            _t('友链'),
            _t('每行一条：名称|链接|描述|头像地址，头像地址可省略。')
        ),
        new \Typecho\Widget\Helper\Form\Element\Text(
            'footerText',
            null,
            null,
            _t('页脚附加文字'),
            _t('可填写备案号或版权说明，支持基础 HTML。')
        ),
        new \Typecho\Widget\Helper\Form\Element\Textarea(
            'analyticsCode',
            null,
            null,
            _t('统计代码'),
            _t('在  head  前原样输出，请只填写可信来源的统计脚本。')
        ),
    ];

    nebula_add_update_panel($form);

    foreach ($fields as $field) {
        $form->addInput($field);
    }
}

function nebula_theme_version()
{
    $index = @file_get_contents(__DIR__ . '/index.php');
    return is_string($index) && preg_match('/@version\s+([^\s*]+)/i', $index, $matches)
        ? trim($matches[1])
        : '0.0.0';
}

function nebula_add_update_panel($form)
{
    $options = \Widget\Options::alloc();
    $themeUrl = rtrim((string) $options->themeUrl, '/') . '/';
    $endpoint = \Typecho\Common::url('nebula-update.php', $themeUrl);
    $endpoint = \Widget\Security::alloc()->getTokenUrl($endpoint);
    $styleUrl = \Typecho\Common::url('assets/css/admin-update.css', $themeUrl);
    $scriptUrl = \Typecho\Common::url('assets/js/admin-update.js', $themeUrl);
    $escape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

    $panel = new \Typecho\Widget\Helper\Layout('div');
    $panel->html(
        '<link rel="stylesheet" href="' . $escape($styleUrl) . '">' .
        '<section class="nebula-update-panel" data-endpoint="' . $escape($endpoint) . '">' .
            '<div class="nebula-update-head">' .
                '<div><h3>主题更新</h3><p>当前版本 <code data-current-version>' . $escape(nebula_theme_version()) . '</code></p></div>' .
                '<span class="nebula-update-source">GitHub</span>' .
            '</div>' .
            '<div class="nebula-update-actions">' .
                '<button class="btn" type="button" data-check-update>检查更新</button>' .
                '<button class="btn primary" type="button" data-install-update hidden>立即升级</button>' .
            '</div>' .
            '<p class="nebula-update-status" data-update-status aria-live="polite">手动检查 GitHub 上的最新版本。</p>' .
        '</section>' .
        '<script src="' . $escape($scriptUrl) . '"></script>'
    );
    $form->addItem($panel);
}

function themeFields($layout)
{
    $cover = new \Typecho\Widget\Helper\Form\Element\Text(
        'cover',
        null,
        null,
        _t('文章封面'),
        _t('文章列表卡片使用的图片 URL；留空时自动使用正文第一张图片，正文无图时显示主题渐变封面。')
    );
    $coverLabel = new \Typecho\Widget\Helper\Form\Element\Text(
        'coverLabel',
        null,
        null,
        _t('封面标签'),
        _t('留空时显示文章的第一个分类。')
    );

    $layout->addItem($cover);
    $layout->addItem($coverLabel);
}

function nebula_option($name, $default = '')
{
    $options = \Widget\Options::alloc();
    $value = isset($options->{$name}) ? trim((string) $options->{$name}) : '';
    return $value !== '' ? $value : $default;
}

function nebula_page_url($slug)
{
    static $urls = null;

    if ($urls === null) {
        $urls = [];
        \Widget\Contents\Page\Rows::alloc()->to($pages);
        while ($pages->next()) {
            $urls[(string) $pages->slug] = (string) $pages->permalink;
        }
    }

    if (isset($urls[$slug])) {
        return $urls[$slug];
    }

    $options = \Widget\Options::alloc();
    return rtrim((string) $options->siteUrl, '/') . '/' . rawurlencode($slug) . '/';
}

function nebula_site_stats()
{
    $stat = \Widget\Stat::alloc();
    $database = \Typecho\Db::get();
    $firstPost = $database->fetchRow(
        $database->select(['MIN(created)' => 'created'])
            ->from('table.contents')
            ->where('type = ?', 'post')
            ->where('status = ?', 'publish')
            ->where('created <= ?', time())
    );
    $firstPostTime = isset($firstPost['created']) ? (int) $firstPost['created'] : 0;

    return [
        'posts' => (int) $stat->publishedPostsNum,
        'comments' => (int) $stat->publishedCommentsNum,
        'categories' => (int) $stat->categoriesNum,
        'days' => $firstPostTime > 0 ? max(1, (int) floor((time() - $firstPostTime) / 86400) + 1) : 0,
    ];
}

function nebula_post_cover($archive)
{
    $cover = '';
    $label = '';

    if (isset($archive->fields)) {
        $cover = isset($archive->fields->cover) ? trim((string) $archive->fields->cover) : '';
        $label = isset($archive->fields->coverLabel) ? trim((string) $archive->fields->coverLabel) : '';
    }

    if ($cover === '') {
        $cover = nebula_first_content_image($archive);
    }

    if ($label === '') {
        ob_start();
        $archive->category(' / ', false, 'UNCATEGORIZED');
        $label = trim(strip_tags(ob_get_clean()));
    }

    return ['url' => $cover, 'label' => $label ?: 'NEBULA'];
}

function nebula_first_content_image($archive)
{
    $content = isset($archive->content) ? (string) $archive->content : '';
    if ($content === '') {
        return '';
    }

    $imageUrl = '';
    if (class_exists('DOMDocument')) {
        $previousErrors = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"><div>' . $content . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousErrors);

        if ($loaded) {
            $images = $document->getElementsByTagName('img');
            if ($images->length > 0) {
                $image = $images->item(0);
                $imageUrl = trim((string) ($image->getAttribute('src') ?: $image->getAttribute('data-src')));
            }
        }
    } elseif (preg_match('/<img[^>]+(?:src|data-src)=["\']([^"\']+)["\']/i', $content, $matches)) {
        $imageUrl = trim($matches[1]);
    }

    if ($imageUrl === '' || preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $imageUrl)) {
        return $imageUrl;
    }

    return \Typecho\Common::url($imageUrl, (string) \Widget\Options::alloc()->siteUrl);
}

function nebula_friend_links()
{
    $links = [];

    try {
        $database = \Typecho\Db::get();
        $rows = $database->fetchAll(
            $database->select('name', 'url', 'description', 'image')
                ->from('table.links')
                ->where('state = ?', 1)
                ->order('order', \Typecho\Db::SORT_ASC)
        );

        foreach ($rows as $row) {
            $url = trim((string) ($row['url'] ?? ''));
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }

            $links[] = [
                'name' => trim((string) ($row['name'] ?? '')),
                'url' => $url,
                'description' => trim((string) ($row['description'] ?? '')),
                'avatar' => trim((string) ($row['image'] ?? '')),
            ];
        }
    } catch (\Throwable $error) {
        // The Links plugin table is optional; theme settings remain the fallback.
    }

    if ($links) {
        return $links;
    }

    $raw = nebula_option('friendLinks');

    foreach (preg_split('/\r\n|\r|\n/', $raw) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }

        $parts = array_map('trim', explode('|', $line, 4));
        if (count($parts) < 2 || !filter_var($parts[1], FILTER_VALIDATE_URL)) {
            continue;
        }

        $links[] = [
            'name' => $parts[0],
            'url' => $parts[1],
            'description' => $parts[2] ?? '',
            'avatar' => $parts[3] ?? '',
        ];
    }

    return $links;
}

function threadedComments($comments, $options)
{
    $commentClass = $comments->levels > 0 ? ' comment-child' : '';
    ?>
    <li id="li-<?php $comments->theId(); ?>" class="comment-item<?php echo $commentClass; ?>">
        <div id="<?php $comments->theId(); ?>" class="comment-row">
            <div class="comment-avatar"><span aria-hidden="true"><?php echo htmlspecialchars(mb_substr(strip_tags((string) $comments->author), 0, 1), ENT_QUOTES, 'UTF-8'); ?></span><?php $comments->gravatar(48, 'mp', false); ?></div>
            <div class="comment-main">
                <div class="comment-head">
                    <strong><?php $comments->author(); ?></strong>
                    <time datetime="<?php $comments->date('c'); ?>"><?php $comments->date('Y-m-d H:i'); ?></time>
                </div>
                <div class="comment-content"><?php $comments->content(); ?></div>
                <div class="comment-actions"><?php $comments->reply(_t('回复')); ?></div>
            </div>
        </div>
        <?php if ($comments->children): ?>
            <ul class="comment-children">
                <?php $comments->threadedComments(); ?>
            </ul>
        <?php endif; ?>
    </li>
    <?php
}
