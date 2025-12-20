<?php
/**
 * Global configuration for the Blog Management System.
 * Adjust DB_* constants to match your MySQL credentials.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'blog_cms');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

$baseUrl = getenv('BASE_URL');
if (!$baseUrl) {
    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') : '';
    $projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..') ?: ''), '/');

    if ($documentRoot !== '' && $projectRoot !== '' && str_starts_with($projectRoot, $documentRoot)) {
        $relative = substr($projectRoot, strlen($documentRoot));
        $relative = $relative === '' ? '/' : '/' . ltrim($relative, '/');
        $baseUrl = rtrim($relative, '/') . '/';
    } else {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $scriptDir = str_replace('\\', '/', dirname($scriptName));
        $scriptDir = $scriptDir === '/' ? '/' : rtrim($scriptDir, '/') . '/';
        $baseUrl = $scriptDir;
    }
}
define('BASE_URL', rtrim($baseUrl, '/') . '/');
$uploadDir = __DIR__ . '/../uploads/blogs/';
define('UPLOAD_DIR', rtrim($uploadDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
define('UPLOAD_URL', BASE_URL . 'uploads/blogs/');
define('DEFAULT_BANNER_IMAGE_PATH', 'assets/images/default-banner.svg');

// Security settings
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
