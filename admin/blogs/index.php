<?php
require_once __DIR__ . '/../../includes/auth_check.php';

$pdo = get_pdo();
$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPageOptions = [5, 10, 20, 50];
$perPage = (int)($_GET['per_page'] ?? 10);
if (!in_array($perPage, $perPageOptions, true)) {
    $perPage = 10;
}
$offset = ($page - 1) * $perPage;

$conditions = ['is_deleted = 0'];
$params = [];
if ($search !== '') {
    $conditions[] = 'title LIKE :search';
    $params[':search'] = '%' . $search . '%';
}
$allowedStatuses = ['draft', 'published', 'scheduled'];
if (in_array($status, $allowedStatuses, true)) {
    $conditions[] = 'status = :status';
    $params[':status'] = $status;
}
$where = 'WHERE ' . implode(' AND ', $conditions);

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM blogs $where");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$orderBy = $sort === 'oldest' ? 'created_at ASC' : 'created_at DESC';
$listStmt = $pdo->prepare("SELECT id, title, slug, status, publish_date, updated_at FROM blogs $where ORDER BY $orderBy LIMIT :limit OFFSET :offset");
foreach ($params as $key => $value) {
    $listStmt->bindValue($key, $value);
}
$listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();
$blogs = $listStmt->fetchAll();

$pageTitle = 'Blogs';
require_once __DIR__ . '/../../includes/header.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;">
    <h1 style="margin:0;">Blogs</h1>
    <a href="<?php echo BASE_URL; ?>admin/blogs/create.php" class="btn btn-primary">New Blog</a>
</div>

<div class="card">
    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
        <input type="text" name="q" value="<?php echo e($search); ?>" class="form-control" placeholder="Search title" style="max-width:200px;">
        <select name="status" class="form-control" style="max-width:160px;">
            <option value="">All Statuses</option>
            <?php foreach ($allowedStatuses as $opt): ?>
                <option value="<?php echo $opt; ?>" <?php echo $status === $opt ? 'selected' : ''; ?>><?php echo ucfirst($opt); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="sort" class="form-control" style="max-width:160px;">
            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
            <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest</option>
        </select>
        <select name="per_page" class="form-control" style="max-width:140px;">
            <?php foreach ($perPageOptions as $opt): ?>
                <option value="<?php echo $opt; ?>" <?php echo $perPage === $opt ? 'selected' : ''; ?>><?php echo $opt; ?>/page</option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
    </form>

    <table class="table">
        <thead>
        <tr>
            <th>Title</th>
            <th>Status</th>
            <th>Publish Date</th>
            <th>Updated</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($blogs)): ?>
            <tr><td colspan="5">No blogs found.</td></tr>
        <?php else: ?>
            <?php foreach ($blogs as $blog): ?>
                <tr>
                    <td><?php echo e($blog['title']); ?></td>
                    <td><?php echo build_status_badge($blog['status']); ?></td>
                    <td><?php echo $blog['publish_date'] ? format_date($blog['publish_date']) : '-'; ?></td>
                    <td><?php echo format_date($blog['updated_at']); ?></td>
                    <td style="white-space:nowrap;">
                        <a class="btn btn-secondary btn-sm" href="<?php echo BASE_URL; ?>admin/blogs/edit.php?id=<?php echo $blog['id']; ?>">Edit</a>
                        <a class="btn btn-primary btn-sm" target="_blank" href="<?php echo BASE_URL; ?>public/blog.php?slug=<?php echo e($blog['slug']); ?>&preview=1">Preview</a>
                        <form method="post" action="<?php echo BASE_URL; ?>admin/blogs/delete.php" style="display:inline;" onsubmit="return confirm('Delete this blog?');">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="id" value="<?php echo $blog['id']; ?>">
                            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <?php echo pagination($total, $perPage, $page, BASE_URL . 'admin/blogs/index.php', ['q' => $search, 'status' => $status, 'sort' => $sort, 'per_page' => $perPage]); ?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
