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

    foreach (nebula_sns_services() as $service) {
        $fields[] = new \Typecho\Widget\Helper\Form\Element\Text(
            $service['option'],
            null,
            null,
            _t($service['label'] . ' 链接'),
            _t('填写完整的 http 或 https 地址；留空则不显示该图标。')
        );
    }

    nebula_add_update_panel($form);

    foreach ($fields as $field) {
        $form->addInput($field);
    }
}

function nebula_sns_services()
{
    return [
        ['option' => 'snsX', 'label' => 'X', 'path' => 'M14.234 10.162 22.977 0h-2.072l-7.591 8.824L7.251 0H.258l9.168 13.343L.258 24H2.33l8.016-9.318L16.749 24h6.993zm-2.837 3.299-.929-1.329L3.076 1.56h3.182l5.965 8.532.929 1.329 7.754 11.09h-3.182z'],
        ['option' => 'snsTelegram', 'label' => 'Telegram', 'path' => 'M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z'],
        ['option' => 'snsWhatsApp', 'label' => 'WhatsApp', 'path' => 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z'],
        ['option' => 'snsWeChat', 'label' => '微信', 'path' => 'M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 0 1 .213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 0 0 .167-.054l1.903-1.114a.864.864 0 0 1 .717-.098 10.16 10.16 0 0 0 2.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178A1.17 1.17 0 0 1 4.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 0 1-1.162 1.178 1.17 1.17 0 0 1-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 0 1 .598.082l1.584.926a.272.272 0 0 0 .14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 0 1-.023-.156.49.49 0 0 1 .201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-6.656-6.088V8.89c-.135-.01-.27-.027-.407-.03zm-2.53 3.274c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.97-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 0 1-.969.983.976.976 0 0 1-.969-.983c0-.542.434-.982.969-.982z'],
        ['option' => 'snsQQ', 'label' => 'QQ', 'path' => 'M21.395 15.035a40 40 0 0 0-.803-2.264l-1.079-2.695c.001-.032.014-.562.014-.836C19.526 4.632 17.351 0 12 0S4.474 4.632 4.474 9.241c0 .274.013.804.014.836l-1.08 2.695a39 39 0 0 0-.802 2.264c-1.021 3.283-.69 4.643-.438 4.673.54.065 2.103-2.472 2.103-2.472 0 1.469.756 3.387 2.394 4.771-.612.188-1.363.479-1.845.835-.434.32-.379.646-.301.778.343.578 5.883.369 7.482.189 1.6.18 7.14.389 7.483-.189.078-.132.132-.458-.301-.778-.483-.356-1.233-.646-1.846-.836 1.637-1.384 2.393-3.302 2.393-4.771 0 0 1.563 2.537 2.103 2.472.251-.03.581-1.39-.438-4.673'],
        ['option' => 'snsFacebook', 'label' => 'Facebook', 'path' => 'M9.101 23.691v-7.98H6.627v-3.667h2.474v-1.58c0-4.085 1.848-5.978 5.858-5.978.401 0 .955.042 1.468.103a8.68 8.68 0 0 1 1.141.195v3.325a8.623 8.623 0 0 0-.653-.036 26.805 26.805 0 0 0-.733-.009c-.707 0-1.259.096-1.675.309a1.686 1.686 0 0 0-.679.622c-.258.42-.374.995-.374 1.752v1.297h3.919l-.386 2.103-.287 1.564h-3.246v8.245C19.396 23.238 24 18.179 24 12.044c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.628 3.874 10.35 9.101 11.647Z'],
        ['option' => 'snsBilibili', 'label' => '哔哩哔哩', 'path' => 'M17.813 4.653h.854c1.51.054 2.769.578 3.773 1.574 1.004.995 1.524 2.249 1.56 3.76v7.36c-.036 1.51-.556 2.769-1.56 3.773s-2.262 1.524-3.773 1.56H5.333c-1.51-.036-2.769-.556-3.773-1.56S.036 18.858 0 17.347v-7.36c.036-1.511.556-2.765 1.56-3.76 1.004-.996 2.262-1.52 3.773-1.574h.774l-1.174-1.12a1.234 1.234 0 0 1-.373-.906c0-.356.124-.658.373-.907l.027-.027c.267-.249.573-.373.92-.373.347 0 .653.124.92.373L9.653 4.44c.071.071.134.142.187.213h4.267a.836.836 0 0 1 .16-.213l2.853-2.747c.267-.249.573-.373.92-.373.347 0 .662.151.929.4.267.249.391.551.391.907 0 .355-.124.657-.373.906zM5.333 7.24c-.746.018-1.373.276-1.88.773-.506.498-.769 1.13-.786 1.894v7.52c.017.764.28 1.395.786 1.893.507.498 1.134.756 1.88.773h13.334c.746-.017 1.373-.275 1.88-.773.506-.498.769-1.129.786-1.893v-7.52c-.017-.765-.28-1.396-.786-1.894-.507-.497-1.134-.755-1.88-.773zM8 11.107c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c0-.373.129-.689.386-.947.258-.257.574-.386.947-.386zm8 0c.373 0 .684.124.933.373.25.249.383.569.4.96v1.173c-.017.391-.15.711-.4.96-.249.25-.56.374-.933.374s-.684-.125-.933-.374c-.25-.249-.383-.569-.4-.96V12.44c.017-.391.15-.711.4-.96.249-.249.56-.373.933-.373Z'],
        ['option' => 'snsMastodon', 'label' => 'Mastodon', 'path' => 'M23.268 5.313c-.35-2.578-2.617-4.61-5.304-5.004C17.51.242 15.792 0 11.813 0h-.03c-3.98 0-4.835.242-5.288.309C3.882.692 1.496 2.518.917 5.127.64 6.412.61 7.837.661 9.143c.074 1.874.088 3.745.26 5.611.118 1.24.325 2.47.62 3.68.55 2.237 2.777 4.098 4.96 4.857 2.336.792 4.849.923 7.256.38.265-.061.527-.132.786-.213.585-.184 1.27-.39 1.774-.753a.057.057 0 0 0 .023-.043v-1.809a.052.052 0 0 0-.02-.041.053.053 0 0 0-.046-.01 20.282 20.282 0 0 1-4.709.545c-2.73 0-3.463-1.284-3.674-1.818a5.593 5.593 0 0 1-.319-1.433.053.053 0 0 1 .066-.054c1.517.363 3.072.546 4.632.546.376 0 .75 0 1.125-.01 1.57-.044 3.224-.124 4.768-.422.038-.008.077-.015.11-.024 2.435-.464 4.753-1.92 4.989-5.604.008-.145.03-1.52.03-1.67.002-.512.167-3.63-.024-5.545zm-3.748 9.195h-2.561V8.29c0-1.309-.55-1.976-1.67-1.976-1.23 0-1.846.79-1.846 2.35v3.403h-2.546V8.663c0-1.56-.617-2.35-1.848-2.35-1.112 0-1.668.668-1.67 1.977v6.218H4.822V8.102c0-1.31.337-2.35 1.011-3.12.696-.77 1.608-1.164 2.74-1.164 1.311 0 2.302.5 2.962 1.498l.638 1.06.638-1.06c.66-.999 1.65-1.498 2.96-1.498 1.13 0 2.043.395 2.74 1.164.675.77 1.012 1.81 1.012 3.12z'],
    ];
}

function nebula_sns_links()
{
    $links = [];

    foreach (nebula_sns_services() as $service) {
        $url = nebula_option($service['option']);
        if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $url)) {
            $service['url'] = $url;
            $links[] = $service;
        }
    }

    return $links;
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
    $styleUrl = nebula_asset_url('assets/css/admin-update.css');
    $scriptUrl = nebula_asset_url('assets/js/admin-update.js');
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

function nebula_asset_url($path)
{
    $options = \Widget\Options::alloc();
    $themeUrl = rtrim((string) $options->themeUrl, '/') . '/';
    $url = \Typecho\Common::url(ltrim((string) $path, '/'), $themeUrl);
    $separator = strpos($url, '?') === false ? '?' : '&';

    return $url . $separator . 'v=' . rawurlencode(nebula_theme_version());
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
        _t('仅在没有封面图片时显示；留空时使用文章的第一个分类。')
    );

    $layout->addItem($cover);
    $layout->addItem($coverLabel);
}

function themePostFields($layout)
{
    $isSticky = new \Typecho\Widget\Helper\Form\Element\Radio(
        'isSticky',
        ['0' => _t('否'), '1' => _t('是')],
        '0',
        _t('是否置顶'),
        _t('选择“是”后文章将在首页优先显示，并将后续文章依次向后顺延。')
    );
    $summary = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'summary',
        null,
        null,
        _t('文章摘要'),
        _t('用于文章列表，并显示在文章详情正文之前；留空时列表自动从正文生成摘要。')
    );

    $layout->addItem($isSticky);
    $layout->addItem($summary);
}

function themeInit($archive)
{
    static $stickyQueryRegistered = false;

    if (!$stickyQueryRegistered && ($archive->is('index') || $archive->is('category'))) {
        \Widget\Archive::pluginHandle()->query = 'nebula_query_sticky_posts';
        $stickyQueryRegistered = true;
    }
}

function nebula_query_sticky_posts($archive, $select)
{
    if ($archive->is('index') || $archive->is('category')) {
        $select->join(
            'table.fields AS nebula_sticky',
            "table.contents.cid = nebula_sticky.cid AND nebula_sticky.name = 'isSticky' AND nebula_sticky.str_value = '1'",
            \Typecho\Db::LEFT_JOIN
        );
        $select->cleanAttribute('order')
            ->order('nebula_sticky.str_value', \Typecho\Db::SORT_DESC)
            ->order('table.contents.created', \Typecho\Db::SORT_DESC);
    }

    \Typecho\Db::get()->fetchAll($select, [$archive, 'push']);
}

function nebula_option($name, $default = '')
{
    $options = \Widget\Options::alloc();
    $value = isset($options->{$name}) ? trim((string) $options->{$name}) : '';
    return $value !== '' ? $value : $default;
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

function nebula_post_is_sticky($archive)
{
    return isset($archive->fields)
        && isset($archive->fields->isSticky)
        && (string) $archive->fields->isSticky === '1';
}

function nebula_raw_post_text($archive)
{
    static $textCache = [];

    $text = is_object($archive) ? (string) $archive->text : '';

    // Listing widgets may contain an excerpt instead of the complete source.
    // Read the stored article by cid so images after <!--more--> are available.
    $cid = is_object($archive) ? (int) $archive->cid : 0;
    if ($cid > 0 && array_key_exists($cid, $textCache)) {
        return $textCache[$cid];
    }
    if ($cid > 0) {
        $database = \Typecho\Db::get();
        $row = $database->fetchRow(
            $database->select('text')
                ->from('table.contents')
                ->where('cid = ?', $cid)
                ->limit(1)
        );
        if (isset($row['text']) && $row['text'] !== '') {
            $text = (string) $row['text'];
        }
        $textCache[$cid] = $text;
    }

    return $text;
}

function nebula_raw_post_html($archive)
{
    $text = nebula_raw_post_text($archive);
    if ($text === '') {
        return '';
    }

    // Allow an UTF-8 BOM or whitespace before Typecho's Markdown marker.
    if (preg_match('/^(?:\xEF\xBB\xBF)?\s*<!--markdown-->/i', $text, $matches)) {
        $markdown = substr($text, strlen($matches[0]));
        if (class_exists('\\Utils\\Markdown')) {
            return \Utils\Markdown::convert($markdown);
        }
    }

    return $text;
}

function nebula_first_content_image($archive)
{
    // Reading $archive->content executes content plugins. Some shortcode plugins
    // reuse the current Archive widget and can corrupt the listing iterator.
    $rawContent = nebula_raw_post_text($archive);
    $content = nebula_raw_post_html($archive);
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
                foreach (['data-src', 'data-original', 'data-lazy-src', 'src'] as $attribute) {
                    $imageUrl = trim((string) $image->getAttribute($attribute));
                    if ($imageUrl !== '') {
                        break;
                    }
                }
            }
        }
    } elseif (preg_match('/<img[^>]+(?:data-src|data-original|data-lazy-src|src)=["\']([^"\']+)["\']/i', $content, $matches)) {
        $imageUrl = trim($matches[1]);
    }

    // This also works when a Markdown converter is unavailable or a plugin
    // changes the rendered article content on listing pages.
    if ($imageUrl === '' && preg_match('/!\[[^\]]*\]\(\s*<?([^>\s)]+)>?(?:\s+["\'][^"\']*["\'])?\s*\)/u', $rawContent, $matches)) {
        $imageUrl = trim($matches[1]);
    }

    $imageUrl = html_entity_decode($imageUrl, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    if ($imageUrl === '' || preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i', $imageUrl)) {
        return $imageUrl;
    }

    return \Typecho\Common::url($imageUrl, (string) \Widget\Options::alloc()->siteUrl);
}

function nebula_post_excerpt($archive, $length = 100, $trim = '…')
{
    $summary = nebula_post_summary($archive);
    if ($summary !== '') {
        $summary = html_entity_decode(strip_tags($summary), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $summary = trim((string) preg_replace('/\s+/u', ' ', $summary));

        return \Typecho\Common::subStr($summary, 0, (int) $length, (string) $trim);
    }

    $content = nebula_raw_post_html($archive);
    if ($content === '') {
        return '';
    }

    [$content] = explode('<!--more-->', $content, 2);
    $content = preg_replace('/\[(?:article|github)\b[^\]]*\]/i', '', $content);
    $content = preg_replace('#</?(?:address|article|aside|blockquote|br|div|dl|dt|dd|figcaption|figure|footer|h[1-6]|header|hr|li|main|nav|ol|p|pre|section|table|tr|ul)\b[^>]*>#i', ' ', $content);
    $content = html_entity_decode(strip_tags((string) $content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $content = trim((string) preg_replace('/\s+/u', ' ', $content));

    return \Typecho\Common::subStr($content, 0, (int) $length, (string) $trim);
}

function nebula_post_summary($archive)
{
    if (!isset($archive->fields) || !isset($archive->fields->summary)) {
        return '';
    }

    return trim((string) $archive->fields->summary);
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
    static $commentAuthors = [];

    $commentId = (int) $comments->coid;
    $parentId = (int) $comments->parent;
    $authorName = trim(html_entity_decode(strip_tags((string) $comments->author), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $replyToAuthor = $parentId > 0 ? ($commentAuthors[$parentId] ?? '') : '';
    if ($commentId > 0 && $authorName !== '') {
        $commentAuthors[$commentId] = $authorName;
    }

    $isAuthor = (int) $comments->authorId > 0 && (int) $comments->authorId === (int) $comments->ownerId;
    $commentClass = ($comments->levels > 0 ? ' comment-child' : '') . ($isAuthor ? ' comment-by-author' : '');
    $replyLabel = _t('回复 %s', strip_tags((string) $comments->author));
    ?>
    <li id="li-<?php $comments->theId(); ?>" class="comment-item<?php echo $commentClass; ?>">
        <div id="<?php $comments->theId(); ?>" class="comment-entry">
            <div class="comment-row">
                <div class="comment-avatar">
                    <span aria-hidden="true"><?php echo htmlspecialchars(mb_substr(strip_tags((string) $comments->author), 0, 1), ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php $comments->gravatar(48, 'mp', false); ?>
                    <span class="comment-avatar-reply"><?php $comments->reply('<span class="sr-only">' . htmlspecialchars($replyLabel, ENT_QUOTES, 'UTF-8') . '</span>'); ?></span>
                </div>
                <div class="comment-head">
                    <strong><?php $comments->author(); ?></strong>
                    <time datetime="<?php $comments->date('c'); ?>"><?php $comments->date('Y-m-d H:i'); ?></time>
                </div>
            </div>
            <div class="comment-main">
                <div class="comment-content"><?php if ($replyToAuthor !== ''): ?><span class="comment-reply-to">@<?php echo htmlspecialchars($replyToAuthor, ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?><?php $comments->content(); ?></div>
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
