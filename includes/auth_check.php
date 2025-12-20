<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

if (!isset($_SESSION['admin_id'])) {
    flash('auth', 'Please log in to continue', 'error');
    redirect(BASE_URL . 'admin/auth/login.php');
}

$admin = current_admin(get_pdo());
if (!$admin) {
    session_destroy();
    redirect(BASE_URL . 'admin/auth/login.php');
}
