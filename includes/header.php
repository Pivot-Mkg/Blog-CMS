<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) : 'Admin Dashboard'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
    <script>
        (function() {
            const stored = localStorage.getItem('admin-theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = stored || (prefersDark ? 'dark' : 'light');
            if (theme === 'dark') {
                document.documentElement.classList.add('theme-dark');
            } else {
                document.documentElement.classList.remove('theme-dark');
            }
            const savedCollapse = localStorage.getItem('sidebar-collapsed');
            if (savedCollapse === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>
</head>
<body>
<div class="wrapper">
    <?php if (!isset($hideSidebar) || !$hideSidebar): ?>
        <?php require __DIR__ . '/sidebar.php'; ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])): ?>
        <div class="flash-container" id="flash-container">
            <?php foreach ($_SESSION['flash'] as $flashKey => $flashData): ?>
                <?php $flashType = $flashData['type'] ?? 'success'; ?>
                <div class="card flash flash-<?php echo e($flashType); ?>">
                    <div class="flash__content"><?php echo e($flashData['message']); ?></div>
                    <button class="flash__close" type="button" aria-label="Dismiss notification">&times;</button>
                </div>
            <?php endforeach; unset($_SESSION['flash']); ?>
        </div>
        <script>
            document.addEventListener('click', function (event) {
                if (event.target.classList.contains('flash__close')) {
                    const toast = event.target.closest('.flash');
                    if (toast) {
                        toast.remove();
                    }
                }
            });
        </script>
    <?php endif; ?>
    <div class="main-content">
