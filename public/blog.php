<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = get_pdo();
$slug = $_GET['slug'] ?? '';
$preview = isset($_GET['preview']) && !empty($_SESSION['admin_id']);

if (!$slug) {
    http_response_code(404);
    exit('Not found');
}

$conditions = 'slug = :slug AND is_deleted = 0';
if (!$preview) {
    $conditions .= " AND (status = 'published' OR (status = 'scheduled' AND publish_date <= NOW()))";
}

$stmt = $pdo->prepare("SELECT * FROM blogs WHERE $conditions LIMIT 1");
$stmt->execute([':slug' => $slug]);
$blog = $stmt->fetch();
if (!$blog) {
    http_response_code(404);
    ?><!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Not Found</title>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    </head>
    <body>
    <div class="container not-found">
        <h1>404</h1>
        <p>Blog post not found.</p>
        <a href="<?php echo BASE_URL; ?>index.php">Back to blog</a>
    </div>
    </body>
    </html><?php
    exit;
}

$metaTitle = $blog['meta_title'] ?: $blog['title'];
$metaDescription = $blog['meta_description'] ?: $blog['excerpt'];
$metaKeywords = $blog['meta_keywords'];
$bannerImage = $blog['banner_image'] ?: DEFAULT_BANNER_IMAGE_PATH;
$insideImage = $blog['featured_image'] ?? null;
$template = $blog['template'] ?? 'standard';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($metaTitle); ?></title>
    <?php if ($metaDescription): ?><meta name="description" content="<?php echo e($metaDescription); ?>"><?php endif; ?>
    <?php if ($metaKeywords): ?><meta name="keywords" content="<?php echo e($metaKeywords); ?>"><?php endif; ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/language-toggle.php'; ?>
<?php if ($template === 'feature'): ?>
    <div class="feature-layout">
        <header class="feature-hero" style="background-image: url('<?php echo BASE_URL . e($bannerImage); ?>');">
            <div class="feature-hero__overlay"></div>
            <div class="feature-hero__content container feature-container">
                <p class="eyebrow">Featured</p>
                <h1><?php echo e($blog['title']); ?></h1>
                <?php if (!empty($blog['excerpt'])): ?><p class="hero-subtitle"><?php echo e($blog['excerpt']); ?></p><?php endif; ?>
                <div class="feature-hero__meta">
                    <span>By <?php echo e($blog['author_name']); ?></span>
                    <span>&middot;</span>
                    <span><?php echo format_date($blog['publish_date']); ?></span>
                </div>
            </div>
        </header>
        <div class="feature-main container feature-container">
            <?php if ($insideImage): ?>
                <div class="feature-image">
                    <img src="<?php echo BASE_URL . e($insideImage); ?>" alt="<?php echo e($blog['title']); ?>">
                </div>
            <?php endif; ?>
            <div class="feature-grid">
                <aside class="feature-share">
                    <p class="meta-label">Share</p>
                    <ul>
                        <li><a href="#">Instagram</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Facebook</a></li>
                    </ul>
                </aside>
                <article class="feature-article content">
                    <?php echo $blog['content']; ?>
                </article>
                <aside class="feature-meta">
                    <div class="feature-card">
                        <p class="meta-label">Details</p>
                        <div class="meta-row"><span>Date</span><span><?php echo format_date($blog['publish_date']); ?></span></div>
                        <div class="meta-row"><span>Status</span><span><?php echo ucfirst($blog['status']); ?></span></div>
                    </div>
                    <div class="feature-card">
                        <p class="meta-label">Author</p>
                        <div class="author-chip">
                            <div class="author-avatar"><?php echo strtoupper(substr($blog['author_name'] ?? 'A', 0, 1)); ?></div>
                            <div>
                                <div class="author-name"><?php echo e($blog['author_name']); ?></div>
                                <div class="author-role">Contributor</div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
<?php else: ?>
    <header class="hero hero-post" style="background-image: linear-gradient(135deg, rgba(15,23,42,0.75), rgba(37,99,235,0.35)), url('<?php echo BASE_URL . e($bannerImage); ?>');">
        <div class="container hero-content">
            <p class="eyebrow">Blog</p>
            <h1><?php echo e($blog['title']); ?></h1>
            <div class="meta">By <?php echo e($blog['author_name']); ?> on <?php echo format_date($blog['publish_date']); ?></div>
        </div>
    </header>
    <div class="container article-container">
        <?php if ($insideImage): ?>
            <div class="inside-image">
                <img class="featured-image" src="<?php echo BASE_URL . e($insideImage); ?>" alt="<?php echo e($blog['title']); ?>">
            </div>
        <?php endif; ?>
        <div class="content"><?php echo $blog['content']; ?></div>
    </div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/site-footer.php'; ?>
</body>
</html>
