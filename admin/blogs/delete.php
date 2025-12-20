<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/csrf.php';

if (!is_post()) {
    redirect(BASE_URL . 'admin/blogs/index.php');
}

verify_csrf();
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    flash('error', 'Invalid request.', 'error');
    redirect(BASE_URL . 'admin/blogs/index.php');
}

$pdo = get_pdo();
$stmt = $pdo->prepare('UPDATE blogs SET is_deleted = 1, updated_at = NOW() WHERE id = :id');
$stmt->execute([':id' => $id]);
flash('success', 'Blog deleted.');
redirect(BASE_URL . 'admin/blogs/index.php');
