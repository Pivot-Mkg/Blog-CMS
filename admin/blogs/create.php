<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/csrf.php';

$pdo = get_pdo();
$errors = [];
$data = [
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'status' => 'draft',
    'publish_date' => '',
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => '',
    'template' => 'standard',
    'banner_image' => '',
    'author_name' => $admin['name'] ?? 'Admin',
];

if (is_post()) {
    verify_csrf();
    $data['title'] = trim($_POST['title'] ?? '');
    $data['slug'] = trim($_POST['slug'] ?? '');
    $data['excerpt'] = trim($_POST['excerpt'] ?? '');
    $data['content'] = $_POST['content'] ?? '';
    $data['status'] = $_POST['status'] ?? 'draft';
    $data['publish_date'] = $_POST['publish_date'] ?? '';
    $data['meta_title'] = trim($_POST['meta_title'] ?? '');
    $data['meta_description'] = trim($_POST['meta_description'] ?? '');
    $data['meta_keywords'] = trim($_POST['meta_keywords'] ?? '');
    $data['author_name'] = trim($_POST['author_name'] ?? '');
    $data['template'] = in_array($_POST['template'] ?? 'standard', ['standard', 'feature'], true) ? $_POST['template'] : 'standard';

    if ($data['title'] === '') {
        $errors[] = 'Title is required.';
    }
    $allowedStatuses = ['draft', 'published', 'scheduled'];
    if (!in_array($data['status'], $allowedStatuses, true)) {
        $errors[] = 'Invalid status.';
    }

    $publishDate = null;
    if ($data['status'] === 'scheduled') {
        if (empty($data['publish_date'])) {
            $errors[] = 'Publish date required for scheduled blogs.';
        } else {
            $publishDate = date('Y-m-d H:i:s', strtotime($data['publish_date']));
        }
    } elseif ($data['status'] === 'published') {
        $publishDate = date('Y-m-d H:i:s');
    }

    $slugToUse = $data['slug'] !== '' ? slugify($data['slug']) : generate_unique_slug($pdo, $data['title']);
    $slugToUse = generate_unique_slug($pdo, $slugToUse);

    $bannerPath = null;
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = upload_image($_FILES['banner_image'], $pdo);
        if ($upload) {
            $bannerPath = $upload['path'];
        } else {
            $errors[] = 'Invalid banner image.';
        }
    }

    $featuredPath = null;
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = upload_image($_FILES['featured_image'], $pdo);
        if ($upload) {
            $featuredPath = $upload['path'];
        } else {
            $errors[] = 'Invalid featured image.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO blogs (title, slug, excerpt, content, featured_image, banner_image, template, status, publish_date, meta_title, meta_description, meta_keywords, author_name, created_at, updated_at, is_deleted) VALUES (:title, :slug, :excerpt, :content, :featured_image, :banner_image, :template, :status, :publish_date, :meta_title, :meta_description, :meta_keywords, :author_name, NOW(), NOW(), 0)');
        $stmt->execute([
            ':title' => $data['title'],
            ':slug' => $slugToUse,
            ':excerpt' => $data['excerpt'],
            ':content' => sanitize_html($data['content']),
            ':featured_image' => $featuredPath,
            ':banner_image' => $bannerPath,
            ':template' => $data['template'],
            ':status' => $data['status'],
            ':publish_date' => $publishDate,
            ':meta_title' => $data['meta_title'],
            ':meta_description' => $data['meta_description'],
            ':meta_keywords' => $data['meta_keywords'],
            ':author_name' => $data['author_name'] ?: 'Admin',
        ]);
        flash('success', 'Blog created successfully.');
        redirect(BASE_URL . 'admin/blogs/index.php');
    }
}

$pageTitle = 'Create Blog';
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="<?php echo BASE_URL; ?>admin/blogs/index.php">Blogs</a> <span>/</span> Create Blog</div>
        <h1 class="page-title">Create Blog</h1>
        <p class="page-subtitle">Draft a new blog post, media, layout, and SEO just like the edit experience.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-secondary btn-ghost" href="<?php echo BASE_URL; ?>admin/blogs/index.php">Back</a>
        <button class="btn btn-primary" type="submit" form="create-form">Create</button>
    </div>
</div>

<form id="create-form" class="edit-form" method="post" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    <?php if (!empty($errors)): ?>
        <div class="card form-alert form-alert--error">
            <?php echo e(implode(' ', $errors)); ?>
        </div>
    <?php endif; ?>

    <section class="card section-card">
        <div class="section-card__header">
            <div>
                <h2>Content</h2>
                <p class="section-hint">Core content fields shown on the blog page.</p>
            </div>
        </div>
        <div class="section-card__body">
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control form-control--lg" required value="<?php echo e($data['title']); ?>">
            </div>
            <div class="form-grid form-grid--split">
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" name="slug" class="form-control" placeholder="auto-generated if empty" value="<?php echo e($data['slug']); ?>">
                    <small class="form-hint">Used in URL, editable.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Excerpt</label>
                    <textarea name="excerpt" class="form-control" rows="3"><?php echo e($data['excerpt']); ?></textarea>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Content</label>
                <div id="content-editor" class="editor-shell"></div>
                <textarea id="content" name="content" class="form-control" style="display:none;"><?php echo e($data['content']); ?></textarea>
            </div>
        </div>
    </section>

    <section class="card section-card">
        <div class="section-card__header">
            <div>
                <h2>Media</h2>
                <p class="section-hint">Add or replace the banner and inside images.</p>
            </div>
        </div>
        <div class="section-card__body media-grid">
            <div class="media-block">
                <div class="media-block__header">
                    <h3>Banner Image</h3>
                    <p class="form-hint">Hero banner shown at the top.</p>
                </div>
                <div class="image-preview"><img id="banner-preview" alt="Banner preview"></div>
                <label class="file-upload file-upload--buttonish">
                    <i class="fa-solid fa-image"></i>
                    <span class="file-text">
                        <span class="file-title">Upload banner</span>
                        <span class="file-hint">JPG, PNG, WEBP up to 2MB</span>
                    </span>
                    <input type="file" name="banner_image" accept="image/*">
                </label>
                <small class="form-text">If left blank a default banner will be used.</small>
            </div>
            <div class="media-block">
                <div class="media-block__header">
                    <h3>Inside Image</h3>
                    <p class="form-hint">Used inside the article body.</p>
                </div>
                <div class="image-preview"><img id="inside-preview" alt="Inside image preview"></div>
                <label class="file-upload file-upload--buttonish">
                    <i class="fa-solid fa-photo-film"></i>
                    <span class="file-text">
                        <span class="file-title">Upload inside image</span>
                        <span class="file-hint">JPG, PNG, WEBP up to 2MB</span>
                    </span>
                    <input type="file" name="featured_image" accept="image/*">
                </label>
            </div>
        </div>
    </section>

    <section class="card section-card">
        <div class="section-card__header">
            <div>
                <h2>Publishing &amp; Author</h2>
                <p class="section-hint">Control visibility, schedule, and author details.</p>
            </div>
        </div>
        <div class="section-card__body form-grid form-grid--split">
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control" required>
                    <option value="draft" <?php echo $data['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo $data['status'] === 'published' ? 'selected' : ''; ?>>Publish now</option>
                    <option value="scheduled" <?php echo $data['status'] === 'scheduled' ? 'selected' : ''; ?>>Schedule</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Publish date &amp; time</label>
                <input type="datetime-local" name="publish_date" class="form-control" value="<?php echo e($data['publish_date']); ?>">
                <small class="form-hint">Required when scheduling.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Author Name</label>
                <input type="text" name="author_name" class="form-control" value="<?php echo e($data['author_name']); ?>">
                <small class="form-hint">Shown as the blog author.</small>
            </div>
        </div>
    </section>

    <section class="card section-card">
        <div class="section-card__header">
            <div>
                <h2>Layout template</h2>
                <p class="section-hint">Choose how this post appears on the public page.</p>
            </div>
        </div>
        <div class="section-card__body">
            <div class="template-grid template-grid--tight">
                <label class="template-card template-card--stacked">
                    <input type="radio" name="template" value="standard" <?php echo $data['template'] === 'standard' ? 'checked' : ''; ?>>
                    <div class="template-card__body">
                        <div class="template-card__preview" style="background-image: linear-gradient(160deg, #eef2ff, #e5e7eb);"></div>
                        <div class="template-card__label">
                            <div>
                                <span class="template-card__title">Standard</span>
                                <span class="template-card__description">Classic blog layout with hero and body.</span>
                            </div>
                            <span class="template-card__pill">Classic</span>
                        </div>
                    </div>
                </label>
                <label class="template-card template-card--stacked">
                    <input type="radio" name="template" value="feature" <?php echo $data['template'] === 'feature' ? 'checked' : ''; ?>>
                    <div class="template-card__body">
                        <div class="template-card__preview" style="background-image: linear-gradient(160deg, #0b1220, #1e2a42), url('<?php echo BASE_URL; ?>assets/images/default-banner.svg'); background-blend-mode: overlay;"></div>
                        <div class="template-card__label">
                            <div>
                                <span class="template-card__title">Feature</span>
                                <span class="template-card__description">Wide hero banner for standout stories.</span>
                            </div>
                            <span class="template-card__pill">Wide hero</span>
                        </div>
                    </div>
                </label>
            </div>
            <small class="form-text">Controls how this blog appears on the public page.</small>
        </div>
    </section>

    <?php
    $seoPreviewTitle = $data['meta_title'] ?: ($data['title'] ?: 'Your title here');
    $seoPreviewUrl = BASE_URL . 'public/blog.php?slug=' . ($data['slug'] ?: 'your-slug');
    $seoPreviewDesc = $data['meta_description'] ?: $data['excerpt'];
    ?>
    <section class="card section-card">
        <div class="section-card__header">
            <div>
                <h2>SEO</h2>
                <p class="section-hint">Set metadata for search and sharing.</p>
            </div>
        </div>
        <div class="section-card__body">
            <div class="form-group">
                <label class="form-label">Meta Title</label>
                <input type="text" name="meta_title" class="form-control" value="<?php echo e($data['meta_title']); ?>">
                <small class="form-hint">Recommended up to 60 characters.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Description</label>
                <textarea name="meta_description" class="form-control" rows="2"><?php echo e($data['meta_description']); ?></textarea>
                <small class="form-hint">Shown in search results.</small>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Keywords</label>
                <input type="text" name="meta_keywords" class="form-control" value="<?php echo e($data['meta_keywords']); ?>">
                <small class="form-hint">Comma-separated, optional.</small>
            </div>
            <div class="seo-preview">
                <div class="seo-preview__title"><?php echo e($seoPreviewTitle); ?></div>
                <div class="seo-preview__url"><?php echo e($seoPreviewUrl); ?></div>
                <?php if ($seoPreviewDesc): ?><div class="seo-preview__desc"><?php echo e($seoPreviewDesc); ?></div><?php endif; ?>
            </div>
        </div>
    </section>

    <div class="form-actions">
        <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>admin/blogs/index.php">Back</a>
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
</form>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
const quill = new Quill('#content-editor', {
    theme: 'snow',
    placeholder: 'Write your blog content here...',
    modules: {
        toolbar: [
            [{ header: [1, 2, 3, false] }, { size: ['small', false, 'large', 'huge'] }],
            ['bold', 'italic', 'underline', 'strike', { color: [] }],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['link', 'image', 'blockquote', 'code-block'],
            ['clean']
        ]
    }
});

const contentField = document.getElementById('content');
const initialContent = <?php echo json_encode($data['content']); ?>;

if (initialContent) {
    quill.root.innerHTML = initialContent;
    contentField.value = initialContent;
}

const form = document.querySelector('form');
const syncContent = () => {
    contentField.value = quill.root.innerHTML;
};

quill.on('text-change', syncContent);
if (form) {
    form.addEventListener('submit', syncContent);
}

const previewImage = (inputEl, imgEl) => {
    if (!inputEl || !imgEl) return;
    inputEl.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (file) {
            imgEl.src = URL.createObjectURL(file);
            imgEl.classList.add('is-visible');
        }
    });
};
previewImage(document.querySelector('input[name="banner_image"]'), document.getElementById('banner-preview'));
previewImage(document.querySelector('input[name="featured_image"]'), document.getElementById('inside-preview'));
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
