<?php
require_once __DIR__ . '/../includes/auth_check.php';

$pdo = get_pdo();
$totalBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs WHERE is_deleted = 0")->fetchColumn();
$draftBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs WHERE is_deleted = 0 AND status = 'draft'")->fetchColumn();
$publishedBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs WHERE is_deleted = 0 AND status = 'published'")->fetchColumn();
$scheduledBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs WHERE is_deleted = 0 AND status = 'scheduled'")->fetchColumn();

$recentStmt = $pdo->prepare("SELECT id, title, slug, status, updated_at FROM blogs WHERE is_deleted = 0 ORDER BY updated_at DESC LIMIT 5");
$recentStmt->execute();
$recentBlogs = $recentStmt->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="main-shell">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px;">
        <div>
            <p style="margin:0;color:var(--muted);font-weight:600;">Hello, <?php echo e($admin['name'] ?? 'Admin'); ?> 👋</p>
            <h1 style="margin:6px 0 0;">Your blog pulse</h1>
        </div>
        <a href="<?php echo BASE_URL; ?>admin/blogs/create.php" class="btn btn-primary">Create blog</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo $totalBlogs; ?></div>
            <div class="stat-label">Total Blogs</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $draftBlogs; ?></div>
            <div class="stat-label">Drafts</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $publishedBlogs; ?></div>
            <div class="stat-label">Published</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $scheduledBlogs; ?></div>
            <div class="stat-label">Scheduled</div>
        </div>
    </div>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;">Recently Edited</h3>
            <a href="<?php echo BASE_URL; ?>admin/blogs/create.php" class="btn btn-secondary btn-sm">New Blog</a>
        </div>
        <table class="table" style="margin-top:15px;">
            <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Last Updated</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($recentBlogs)): ?>
                <tr><td colspan="4">No blogs yet.</td></tr>
            <?php else: ?>
                <?php foreach ($recentBlogs as $blog): ?>
                    <tr>
                        <td><?php echo e($blog['title']); ?></td>
                        <td><?php echo build_status_badge($blog['status']); ?></td>
                        <td><?php echo format_date($blog['updated_at']); ?></td>
                        <td><a href="<?php echo BASE_URL; ?>admin/blogs/edit.php?id=<?php echo $blog['id']; ?>" class="btn btn-secondary btn-sm">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
