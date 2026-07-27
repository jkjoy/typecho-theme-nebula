<?php

$nebulaUpdateBufferLevel = ob_get_level();
ob_start();

function nebula_update_response(array $data, int $status = 200): void
{
    global $nebulaUpdateBufferLevel;

    while (ob_get_level() > $nebulaUpdateBufferLevel) {
        ob_end_clean();
    }

    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    define('__TYPECHO_ADMIN__', true);

    $rootDirectory = dirname(__DIR__, 3);
    if (!defined('__TYPECHO_ROOT_DIR__')) {
        require_once $rootDirectory . '/config.inc.php';
    }

    \Widget\Init::alloc();
    \Widget\User::alloc()->pass('administrator');
    \Widget\Security::alloc()->protect();

    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        throw new RuntimeException('更新接口只接受 POST 请求。');
    }

    require_once __DIR__ . '/inc/NebulaThemeUpdater.php';
    $updater = new NebulaThemeUpdater(__DIR__, 'jkjoy/typecho-theme-nebula', 'main');
    $action = (string) \Typecho\Request::getInstance()->get('action', '');

    if ($action === 'check') {
        $result = $updater->check();
        $result['success'] = true;
        $result['message'] = $result['update_available']
            ? '发现可用更新。'
            : '当前已经是最新版本。';
        nebula_update_response($result);
    }

    if ($action === 'update') {
        $result = $updater->update();
        nebula_update_response([
            'success' => true,
            'version' => $result['version'],
            'files' => $result['files'],
            'message' => '主题升级完成。',
        ]);
    }

    throw new RuntimeException('未知的更新操作。');
} catch (Throwable $error) {
    nebula_update_response([
        'success' => false,
        'message' => $error->getMessage(),
    ], 400);
}
