<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';

if (isset($_SESSION['admin_id'])) {
    redirect(BASE_URL . 'admin/dashboard.php');
}

$errors = [];
$success = false;
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$company = trim($_POST['company'] ?? '');
$message = trim($_POST['message'] ?? '');

if (is_post()) {
    verify_csrf();

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if ($message === '') {
        $errors[] = 'Please include a short note about your request.';
    }

    if (empty($errors)) {
        $logLine = sprintf(
            "[%s] %s | %s | %s | %s\n",
            date('Y-m-d H:i:s'),
            $name,
            $email,
            $company ?: 'No company',
            str_replace(["\r", "\n"], ' ', $message)
        );
        $logPath = __DIR__ . '/../../database/access-requests.log';
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        @file_put_contents($logPath, $logLine, FILE_APPEND | LOCK_EX);
        $success = true;
        $name = $email = $company = $message = '';
    }
}

$pageTitle = 'Request Access';
$hideSidebar = true;
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="auth-page">
    <div class="auth-shell">
        <div class="auth-hero">
            <div class="auth-hero__content">
                <p class="eyebrow">Access request</p>
                <h1>Need an account? Tell us a bit about you.</h1>
            </div>
        </div>
        <div class="auth-form">
            <div class="auth-logo">
                <img src="https://pivotmkg.com/assets/images/Final_logo.png" alt="Pivot Marketing Logo">
            </div>
            <h2>Request access</h2>
            <p class="auth-subtitle">Share your details and the team will follow up.</p>
            <?php if (!empty($errors)): ?>
                <div class="card flash flash-error" style="margin-bottom:12px;"><?php echo e(implode(' ', $errors)); ?></div>
            <?php elseif ($success): ?>
                <div class="card flash flash-success" style="margin-bottom:12px;">Request received. We’ll email you soon.</div>
            <?php endif; ?>
            <form method="post" action="">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required value="<?php echo e($name); ?>" placeholder="Your name">
                </div>
                <div class="form-grid form-grid--split">
                    <div class="form-group">
                        <label class="form-label">Work email</label>
                        <input type="email" name="email" class="form-control" required value="<?php echo e($email); ?>" placeholder="you@company.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Company (optional)</label>
                        <input type="text" name="company" class="form-control" value="<?php echo e($company); ?>" placeholder="Company or team name">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">What do you need?</label>
                    <textarea name="message" class="form-control" rows="3" required placeholder="Role, access needs, or any notes"><?php echo e($message); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary auth-submit">Submit request</button>
            </form>
            <p class="auth-footer-text">Already have an account? <a href="<?php echo BASE_URL; ?>admin/auth/login.php">Return to login</a></p>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
