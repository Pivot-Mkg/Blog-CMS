<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/csrf.php';

if (isset($_SESSION['admin_id'])) {
    redirect(BASE_URL . 'admin/dashboard.php');
}

$errors = [];
if (is_post()) {
    verify_csrf();
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $errors[] = 'Email and password are required.';
    } else {
        $pdo = get_pdo();
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $pdo->prepare('UPDATE admins SET last_login = NOW() WHERE id = :id')->execute([':id' => $admin['id']]);
            redirect(BASE_URL . 'admin/dashboard.php');
        } else {
            $errors[] = 'Invalid credentials.';
        }
    }
}

$pageTitle = 'Admin Login';
$hideSidebar = true;
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="auth-page">
    <div class="auth-shell">
        <div class="auth-hero">
            <div class="auth-hero__content">
                <p class="eyebrow">Welcome back</p>
                <h1>Convert your ideas into successful business.</h1>
            </div>
        </div>
        <div class="auth-form">
            <div class="auth-logo">✺</div>
            <h2>Get Started</h2>
            <p class="auth-subtitle">Welcome back — let’s get you signed in</p>
            <?php if (!empty($errors)): ?>
                <div class="card flash flash-error" style="margin-bottom:12px;"><?php echo e(implode(' ', $errors)); ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <?php echo csrf_field(); ?>
                <div class="form-group">
                    <label class="form-label">Your email</label>
                    <input type="email" name="email" class="form-control" required placeholder="you@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary auth-submit">Login</button>
            </form>
            <p class="auth-footer-text">Don’t have an account? <a href="<?php echo BASE_URL; ?>admin/auth/login.php">Request access</a></p>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
