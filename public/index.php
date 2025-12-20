<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = get_pdo();
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;
$offset = ($page - 1) * $perPage;

$where = "WHERE is_deleted = 0 AND (status = 'published' OR (status = 'scheduled' AND publish_date <= NOW()))";
$total = (int)$pdo->query("SELECT COUNT(*) FROM blogs $where")->fetchColumn();

$stmt = $pdo->prepare("SELECT title, slug, excerpt, featured_image, author_name, publish_date FROM blogs $where ORDER BY publish_date DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$blogs = $stmt->fetchAll();
$highlight = $blogs[0] ?? null;
$heroImage = $highlight && $highlight['featured_image'] ? $highlight['featured_image'] : 'assets/images/default-banner.svg';

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
<header class="hero hero-blog" style="background-image: linear-gradient(135deg, rgba(15,23,42,0.65), rgba(37,99,235,0.35)), url('<?php echo BASE_URL . e($heroImage); ?>');">
    <div class="hero-content container">
        <p class="eyebrow">Featured</p>
        <h1><?php echo e($highlight['title'] ?? 'Blogs'); ?></h1>
        <p class="hero-subtitle">
            <?php echo e($highlight['excerpt'] ?? 'Insights, releases, and stories from our team.'); ?>
        </p>
        <?php if ($highlight): ?>
            <a class="btn-read-more hero-cta" href="<?php echo BASE_URL; ?>public/blog.php?slug=<?php echo e($highlight['slug']); ?>">Read article</a>
        <?php endif; ?>
    </div>
</header>
<div class="container">
    <?php if (empty($blogs)): ?>
        <p>No posts yet.</p>
    <?php else: ?>
        <div class="section-heading">
            <h3>Recent blog posts</h3>
            <p class="section-subtitle">Browse the latest drops from the team.</p>
        </div>
        <div class="blog-grid">
            <?php foreach ($blogs as $blog): ?>
                <?php $cover = $blog['featured_image'] ?: 'assets/images/default-banner.svg'; ?>
                <article class="blog-card">
                    <a class="blog-card__image" href="<?php echo BASE_URL; ?>public/blog.php?slug=<?php echo e($blog['slug']); ?>" style="background-image: linear-gradient(180deg, rgba(15,23,42,0.08), rgba(15,23,42,0.35)), url('<?php echo BASE_URL . e($cover); ?>');"></a>
                    <div class="blog-card__body">
                        <div class="meta">By <?php echo e($blog['author_name']); ?> &mdash; <?php echo format_date($blog['publish_date']); ?></div>
                        <h2><a href="<?php echo BASE_URL; ?>public/blog.php?slug=<?php echo e($blog['slug']); ?>"><?php echo e($blog['title']); ?></a></h2>
                        <p class="excerpt"><?php echo e($blog['excerpt']); ?></p>
                        <div class="blog-card__footer">
                            <a class="btn-read-more" href="<?php echo BASE_URL; ?>public/blog.php?slug=<?php echo e($blog['slug']); ?>">Read more</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php echo pagination($total, $perPage, $page, BASE_URL . 'public/index.php'); ?>
</div>
</body>
</html>
