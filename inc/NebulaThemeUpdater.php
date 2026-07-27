<?php

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

final class NebulaThemeUpdater
{
    private const API_URL = 'https://api.github.com/repos/%s/releases/latest';
    private const RAW_URL = 'https://raw.githubusercontent.com/%s/%s/index.php';
    private const TAG_ZIP_URL = 'https://github.com/%s/archive/refs/tags/%s.zip';
    private const BRANCH_ZIP_URL = 'https://github.com/%s/archive/refs/heads/%s.zip';
    private const MAX_DOWNLOAD_BYTES = 33554432;
    private const MAX_EXTRACTED_BYTES = 67108864;
    private const MAX_FILES = 3000;

    private string $themeDirectory;
    private string $repository;
    private string $branch;

    public function __construct(string $themeDirectory, string $repository, string $branch = 'main')
    {
        $resolvedThemeDirectory = realpath($themeDirectory);
        if ($resolvedThemeDirectory === false || !is_dir($resolvedThemeDirectory)) {
            throw new RuntimeException('无法定位当前主题目录。');
        }

        if (!preg_match('#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repository)) {
            throw new InvalidArgumentException('GitHub 仓库地址无效。');
        }

        if (!preg_match('/^[A-Za-z0-9._\/-]+$/', $branch)) {
            throw new InvalidArgumentException('GitHub 分支名称无效。');
        }

        $this->themeDirectory = $resolvedThemeDirectory;
        $this->repository = $repository;
        $this->branch = $branch;
    }

    public function check(): array
    {
        $currentVersion = self::readThemeVersion($this->themeDirectory . DIRECTORY_SEPARATOR . 'index.php');
        $remote = $this->findRemoteVersion();

        return [
            'current_version' => $currentVersion,
            'latest_version' => $remote['version'],
            'update_available' => version_compare($remote['version'], $currentVersion, '>'),
            'source' => $remote['source'],
            'download_url' => $remote['download_url'],
        ];
    }

    public function update(): array
    {
        $check = $this->check();
        if (!$check['update_available']) {
            throw new RuntimeException('当前已经是最新版本，无需升级。');
        }

        $temporaryDirectory = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'nebula-theme-update-' . bin2hex(random_bytes(8));
        $archivePath = $temporaryDirectory . DIRECTORY_SEPARATOR . 'package.zip';
        $extractDirectory = $temporaryDirectory . DIRECTORY_SEPARATOR . 'extracted';
        $backupDirectory = $temporaryDirectory . DIRECTORY_SEPARATOR . 'backup';

        try {
            $this->makeDirectory($temporaryDirectory);
            $this->makeDirectory($extractDirectory);
            $this->makeDirectory($backupDirectory);
            $this->downloadArchive($check['download_url'], $archivePath);
            $packageDirectory = $this->extractArchive($archivePath, $extractDirectory);
            $packageVersion = $this->validatePackage($packageDirectory, $check['latest_version']);
            $files = $this->applyPackage($packageDirectory, $backupDirectory);

            return ['version' => $packageVersion, 'files' => $files];
        } finally {
            $this->removeTemporaryDirectory($temporaryDirectory);
        }
    }

    public static function readThemeVersion(string $indexFile): string
    {
        $contents = @file_get_contents($indexFile);
        if (!is_string($contents) || !preg_match('/@version\s+([^\s*]+)/i', $contents, $matches)) {
            throw new RuntimeException('无法读取主题版本号。');
        }

        $version = self::normalizeVersion($matches[1]);
        if ($version === '') {
            throw new RuntimeException('主题版本号格式无效。');
        }

        return $version;
    }

    private function findRemoteVersion(): array
    {
        $releaseResponse = $this->httpGet(sprintf(self::API_URL, $this->repository));
        if ($releaseResponse['status'] === 200) {
            $release = json_decode($releaseResponse['body'], true);
            $tag = is_array($release) ? trim((string) ($release['tag_name'] ?? '')) : '';
            $version = self::normalizeVersion($tag);
            if ($version !== '' && $tag !== '') {
                return [
                    'version' => $version,
                    'download_url' => sprintf(self::TAG_ZIP_URL, $this->repository, rawurlencode($tag)),
                    'source' => 'release',
                ];
            }
        }

        $rawUrl = sprintf(self::RAW_URL, $this->repository, rawurlencode($this->branch));
        $branchResponse = $this->httpGet($rawUrl, 'text/plain');
        if ($branchResponse['status'] === 200) {
            $version = self::versionFromContents($branchResponse['body']);
            if ($version !== '') {
                return [
                    'version' => $version,
                    'download_url' => sprintf(
                        self::BRANCH_ZIP_URL,
                        $this->repository,
                        str_replace('%2F', '/', rawurlencode($this->branch))
                    ),
                    'source' => 'branch',
                ];
            }
        }

        if ($releaseResponse['status'] === 403 || $branchResponse['status'] === 403) {
            throw new RuntimeException('GitHub API 请求受限，请稍后再试。');
        }

        throw new RuntimeException('GitHub 仓库尚未发布可用版本，请先推送主题文件或创建 Release。');
    }

    private function httpGet(string $url, string $accept = 'application/vnd.github+json'): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('服务器未启用 cURL，无法检查更新。');
        }

        $handle = curl_init($url);
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERAGENT => 'Nebula-Theme-Updater',
            CURLOPT_HTTPHEADER => ['Accept: ' . $accept],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        if (PHP_OS_FAMILY === 'Windows' && defined('CURLSSLOPT_NATIVE_CA')) {
            $options[CURLOPT_SSL_OPTIONS] = CURLSSLOPT_NATIVE_CA;
        }
        curl_setopt_array($handle, $options);

        $body = curl_exec($handle);
        $error = curl_error($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        if ($body === false) {
            throw new RuntimeException('连接 GitHub 失败：' . ($error ?: '未知网络错误'));
        }

        if (strlen($body) > 2097152) {
            throw new RuntimeException('GitHub 版本响应异常。');
        }

        return ['status' => $status, 'body' => $body];
    }

    private function downloadArchive(string $url, string $destination): void
    {
        $file = @fopen($destination, 'wb');
        if ($file === false) {
            throw new RuntimeException('无法创建升级临时文件。');
        }

        $handle = curl_init($url);
        $maxBytes = self::MAX_DOWNLOAD_BYTES;
        $options = [
            CURLOPT_FILE => $file,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_USERAGENT => 'Nebula-Theme-Updater',
            CURLOPT_HTTPHEADER => ['Accept: application/zip, application/octet-stream;q=0.9, */*;q=0.8'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_NOPROGRESS => false,
            CURLOPT_XFERINFOFUNCTION => static function ($resource, $downloadSize, $downloaded) use ($maxBytes) {
                return $downloadSize > $maxBytes || $downloaded > $maxBytes ? 1 : 0;
            },
        ];
        if (PHP_OS_FAMILY === 'Windows' && defined('CURLSSLOPT_NATIVE_CA')) {
            $options[CURLOPT_SSL_OPTIONS] = CURLSSLOPT_NATIVE_CA;
        }
        curl_setopt_array($handle, $options);

        $success = curl_exec($handle);
        $error = curl_error($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);
        fclose($file);

        if (!$success || $status < 200 || $status >= 300) {
            throw new RuntimeException('下载主题升级包失败：' . ($error ?: 'HTTP ' . $status));
        }

        $size = @filesize($destination);
        if (!$size || $size > self::MAX_DOWNLOAD_BYTES) {
            throw new RuntimeException('主题升级包大小异常。');
        }

        $signature = @file_get_contents($destination, false, null, 0, 4);
        if (!is_string($signature) || !in_array($signature, ["PK\x03\x04", "PK\x05\x06", "PK\x07\x08"], true)) {
            throw new RuntimeException('GitHub 未返回有效的 ZIP 升级包。');
        }
    }

    private function extractArchive(string $archivePath, string $extractDirectory): string
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('服务器未启用 ZipArchive，无法安装更新。');
        }

        $zip = new ZipArchive();
        if ($zip->open($archivePath) !== true) {
            throw new RuntimeException('无法打开主题升级包。');
        }

        try {
            if ($zip->numFiles < 1 || $zip->numFiles > self::MAX_FILES) {
                throw new RuntimeException('主题升级包文件数量异常。');
            }

            $totalSize = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                $name = is_array($stat) ? (string) ($stat['name'] ?? '') : '';
                $this->assertSafeRelativePath($name);
                $totalSize += (int) ($stat['size'] ?? 0);

                if ($totalSize > self::MAX_EXTRACTED_BYTES) {
                    throw new RuntimeException('主题升级包解压后过大。');
                }

                $operatingSystem = 0;
                $attributes = 0;
                if ($zip->getExternalAttributesIndex($index, $operatingSystem, $attributes)) {
                    $fileType = ($attributes >> 16) & 0170000;
                    if ($fileType === 0120000) {
                        throw new RuntimeException('主题升级包包含不允许的符号链接。');
                    }
                }
            }

            if (!$zip->extractTo($extractDirectory)) {
                throw new RuntimeException('无法解压主题升级包。');
            }
        } finally {
            $zip->close();
        }

        $candidates = [$extractDirectory];
        foreach (glob($extractDirectory . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [] as $directory) {
            $candidates[] = $directory;
        }

        $valid = array_values(array_filter($candidates, static function ($directory) {
            return is_file($directory . DIRECTORY_SEPARATOR . 'index.php')
                && is_file($directory . DIRECTORY_SEPARATOR . 'functions.php');
        }));

        if (count($valid) !== 1) {
            throw new RuntimeException('升级包中未找到唯一的主题目录。');
        }

        return $valid[0];
    }

    private function validatePackage(string $packageDirectory, string $expectedVersion): string
    {
        $indexFile = $packageDirectory . DIRECTORY_SEPARATOR . 'index.php';
        $indexContents = @file_get_contents($indexFile);
        if (!is_string($indexContents) || !preg_match('/@package\s+Nebula\b/i', $indexContents)) {
            throw new RuntimeException('升级包不是有效的 Nebula 主题。');
        }

        $packageVersion = self::readThemeVersion($indexFile);
        if (version_compare($packageVersion, $expectedVersion, '!=')) {
            throw new RuntimeException('升级包版本与 GitHub 版本不一致。');
        }

        $localVersion = self::readThemeVersion($this->themeDirectory . DIRECTORY_SEPARATOR . 'index.php');
        if (!version_compare($packageVersion, $localVersion, '>')) {
            throw new RuntimeException('升级包版本不高于当前主题。');
        }

        return $packageVersion;
    }

    private function applyPackage(string $packageDirectory, string $backupDirectory): int
    {
        if (!is_writable($this->themeDirectory)) {
            throw new RuntimeException('主题目录不可写，无法覆盖升级。');
        }

        $files = $this->packageFiles($packageDirectory);
        $newFiles = [];
        $backups = [];

        foreach ($files as $relativePath => $source) {
            $destination = $this->themeDirectory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $destinationDirectory = dirname($destination);
            $this->makeDirectory($destinationDirectory);

            if (is_dir($destination)) {
                throw new RuntimeException('主题目录存在文件类型冲突：' . $relativePath);
            }

            if (file_exists($destination)) {
                if (is_link($destination) || !is_file($destination) || !is_writable($destination)) {
                    throw new RuntimeException('主题文件不可覆盖：' . $relativePath);
                }

                $backup = $backupDirectory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
                $this->makeDirectory(dirname($backup));
                if (!copy($destination, $backup)) {
                    throw new RuntimeException('无法备份主题文件：' . $relativePath);
                }
                $backups[$destination] = $backup;
            } else {
                $newFiles[] = $destination;
            }
        }

        try {
            foreach ($files as $relativePath => $source) {
                $destination = $this->themeDirectory . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
                if (!copy($source, $destination)) {
                    throw new RuntimeException('覆盖主题文件失败：' . $relativePath);
                }
            }
        } catch (Throwable $error) {
            foreach ($backups as $destination => $backup) {
                @copy($backup, $destination);
            }
            foreach ($newFiles as $destination) {
                if (is_file($destination)) {
                    @unlink($destination);
                }
            }
            throw $error;
        }

        return count($files);
    }

    private function packageFiles(string $packageDirectory): array
    {
        $files = [];
        $totalSize = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($packageDirectory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isLink()) {
                throw new RuntimeException('主题升级包包含不允许的符号链接。');
            }
            if (!$file->isFile()) {
                continue;
            }

            $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($packageDirectory) + 1));
            $this->assertSafeRelativePath($relativePath);
            $totalSize += $file->getSize();
            if (count($files) >= self::MAX_FILES || $totalSize > self::MAX_EXTRACTED_BYTES) {
                throw new RuntimeException('主题升级包内容过多。');
            }
            $files[$relativePath] = $file->getPathname();
        }

        if (!$files) {
            throw new RuntimeException('主题升级包不包含可更新文件。');
        }

        return $files;
    }

    private function assertSafeRelativePath(string $path): void
    {
        $path = str_replace('\\', '/', $path);
        if ($path === '' || str_contains($path, "\0") || str_starts_with($path, '/')
            || preg_match('/^[A-Za-z]:/', $path)) {
            throw new RuntimeException('主题升级包包含无效路径。');
        }

        foreach (explode('/', trim($path, '/')) as $segment) {
            if ($segment === '..') {
                throw new RuntimeException('主题升级包包含越界路径。');
            }
        }
    }

    private function makeDirectory(string $directory): void
    {
        if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('无法创建升级目录。');
        }
    }

    private function removeTemporaryDirectory(string $directory): void
    {
        $temporaryRoot = realpath(sys_get_temp_dir());
        $resolved = realpath($directory);
        if ($temporaryRoot === false || $resolved === false
            || !str_starts_with($resolved, $temporaryRoot . DIRECTORY_SEPARATOR)
            || !str_starts_with(basename($resolved), 'nebula-theme-update-')) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($resolved, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isLink() || $item->isFile()) {
                @unlink($item->getPathname());
            } else {
                @rmdir($item->getPathname());
            }
        }
        @rmdir($resolved);
    }

    private static function versionFromContents(string $contents): string
    {
        return preg_match('/@version\s+([^\s*]+)/i', $contents, $matches)
            ? self::normalizeVersion($matches[1])
            : '';
    }

    private static function normalizeVersion(string $version): string
    {
        return preg_match('/\d+(?:\.\d+)+(?:[-+][0-9A-Za-z.-]+)?/', trim($version), $matches)
            ? $matches[0]
            : '';
    }
}
