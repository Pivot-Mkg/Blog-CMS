<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// If already authenticated, go to dashboard; otherwise send to login.
if (isset($_SESSION['admin_id'])) {
    redirect(BASE_URL . 'admin/dashboard.php');
}

redirect(BASE_URL . 'admin/auth/login.php');
