<?php
require_once __DIR__ . '/../config/config.php';

function redirect(string $path): void
{
    if (preg_match('#^https?://#i', $path)) {
        header('Location: ' . $path);
        exit;
    }

    if ($path === '') {
        header('Location: ' . BASE_URL);
        exit;
    }

    if (str_starts_with($path, BASE_URL)) {
        header('Location: ' . $path);
        exit;
    }

    if ($path[0] === '/') {
        $path = ltrim($path, '/');
    }

    header('Location: ' . BASE_URL . $path);
    exit;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function flash(string $key, ?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
        return null;
    }
    if (isset($_SESSION['flash'][$key])) {
        $data = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $data;
    }
    return null;
}

function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim($text, '-');
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = strtolower($text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    return $text ?: uniqid('post-');
}

function generate_unique_slug(PDO $pdo, string $title, ?int $excludeId = null): string
{
    $base = slugify($title);
    $slug = $base;
    $i = 1;
    while (true) {
        $sql = 'SELECT id FROM blogs WHERE slug = :slug';
        $params = [':slug' => $slug];
        if ($excludeId) {
            $sql .= ' AND id != :id';
            $params[':id'] = $excludeId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $existing = $stmt->fetch();
        if (!$existing) {
            return $slug;
        }
        $slug = $base . '-' . (++$i);
    }
}

function sanitize_html(string $html): string
{
    $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
    $html = preg_replace('#on\w+\s*=\s*("|\').*?("|\')#i', '', $html);
    return $html;
}

function ensure_upload_directory(): void
{
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }
}

function format_date(?string $date): string
{
    if (!$date) {
        return '-';
    }
    return date('M d, Y H:i', strtotime($date));
}

function current_admin(PDO $pdo): ?array
{
    if (!isset($_SESSION['admin_id'])) {
        return null;
    }
    static $admin = null;
    if ($admin !== null) {
        return $admin;
    }
    $stmt = $pdo->prepare('SELECT id, name, email, last_login, created_at FROM admins WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    return $admin ?: null;
}

function build_status_badge(string $status): string
{
    $class = 'status-draft';
    if ($status === 'published') {
        $class = 'status-published';
    } elseif ($status === 'scheduled') {
        $class = 'status-scheduled';
    }
    return '<span class="status-badge ' . $class . '">' . e(ucfirst($status)) . '</span>';
}

function pagination(int $total, int $perPage, int $currentPage, string $baseUrl, array $params = []): string
{
    $pages = (int)ceil($total / $perPage);
    if ($pages <= 1) {
        return '';
    }
    $html = '<div class="pagination">';
    for ($i = 1; $i <= $pages; $i++) {
        $params['page'] = $i;
        $query = http_build_query($params);
        $active = $i === $currentPage ? 'style="font-weight:bold;"' : '';
        $html .= '<a ' . $active . ' href="' . e($baseUrl . '?' . $query) . '">' . $i . '</a> ';
    }
    $html .= '</div>';
    return $html;
}

function upload_image(array $file, PDO $pdo): ?array
{
    ensure_upload_directory();
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return null;
    }
    $allowedTypes = ALLOWED_IMAGE_TYPES;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedTypes, true)) {
        return null;
    }
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return null;
    }
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safeName = uniqid('img_', true) . '.' . strtolower($extension);
    $destination = UPLOAD_DIR . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }

    $relativePath = 'uploads/blogs/' . $safeName;
    $stmt = $pdo->prepare('INSERT INTO media (file_name, file_path, file_type, uploaded_at) VALUES (:name, :path, :type, NOW())');
    $stmt->execute([
        ':name' => $safeName,
        ':path' => $relativePath,
        ':type' => $mime,
    ]);

    return [
        'name' => $safeName,
        'path' => $relativePath,
        'url' => UPLOAD_URL . $safeName,
        'type' => $mime,
    ];
}
