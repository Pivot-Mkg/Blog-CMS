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
<div style="display:flex;justify-content:space-between;align-items:center;">
    <h1 style="margin:0;">Create Blog</h1>
    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>admin/blogs/index.php">Back</a>
</div>

<div class="card">
    <?php if (!empty($errors)): ?>
        <div class="card" style="border-left:4px solid #dc2626; background:#fef2f2;">
            <?php echo e(implode(' ', $errors)); ?>
        </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required value="<?php echo e($data['title']); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" placeholder="auto-generated if empty" value="<?php echo e($data['slug']); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Excerpt</label>
            <textarea name="excerpt" class="form-control" rows="3"><?php echo e($data['excerpt']); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Content</label>
            <div id="content-editor" style="height:400px;"></div>
            <textarea id="content" name="content" class="form-control" style="display:none;"><?php echo e($data['content']); ?></textarea>
        </div>
        <div class="form-group" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px;">
            <div>
                <label class="form-label">Banner Image</label>
                <label class="file-upload">
                    <i class="fa-solid fa-image"></i>
                    <span class="file-text">
                        <span class="file-title">Upload banner</span>
                        <span class="file-hint">JPG, PNG, WEBP up to 2MB</span>
                    </span>
                    <input type="file" name="banner_image" accept="image/*">
                </label>
                <small class="form-text">If left blank a default banner will be used.</small>
                <div class="image-preview"><img id="banner-preview" alt="Banner preview"></div>
            </div>
            <div>
                <label class="form-label">Inside Image</label>
                <label class="file-upload">
                    <i class="fa-solid fa-photo-film"></i>
                    <span class="file-text">
                        <span class="file-title">Upload inside image</span>
                        <span class="file-hint">JPG, PNG, WEBP up to 2MB</span>
                    </span>
                    <input type="file" name="featured_image" accept="image/*">
                </label>
                <div class="image-preview"><img id="inside-preview" alt="Inside image preview"></div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Status</label>
            <select name="status" class="form-control" required>
                <option value="draft" <?php echo $data['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="published" <?php echo $data['status'] === 'published' ? 'selected' : ''; ?>>Publish now</option>
                <option value="scheduled" <?php echo $data['status'] === 'scheduled' ? 'selected' : ''; ?>>Schedule</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Publish Date (for scheduled)</label>
            <input type="datetime-local" name="publish_date" class="form-control" value="<?php echo e($data['publish_date']); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Author Name</label>
            <input type="text" name="author_name" class="form-control" value="<?php echo e($data['author_name']); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Template</label>
            <div class="template-grid">
                <label class="template-card">
                    <input type="radio" name="template" value="standard" <?php echo $data['template'] === 'standard' ? 'checked' : ''; ?>>
                    <div class="template-card__body">
                        <div class="template-card__preview" style="background-image: linear-gradient(160deg, #eef2ff, #e5e7eb);"></div>
                        <div class="template-card__label">
                            <span class="template-card__title">Standard</span>
                            <span class="template-card__pill">Classic</span>
                        </div>
                    </div>
                </label>
                <label class="template-card">
                    <input type="radio" name="template" value="feature" <?php echo $data['template'] === 'feature' ? 'checked' : ''; ?>>
                    <div class="template-card__body">
                        <div class="template-card__preview" style="background-image: linear-gradient(160deg, #0b1220, #1e2a42), url('<?php echo BASE_URL; ?>assets/images/default-banner.svg'); background-blend-mode: overlay;"></div>
                        <div class="template-card__label">
                            <span class="template-card__title">Feature</span>
                            <span class="template-card__pill">Wide hero</span>
                        </div>
                    </div>
                </label>
            </div>
            <small class="form-text">Choose the layout used on the public page.</small>
        </div>
        <h3>SEO</h3>
        <div class="form-group">
            <label class="form-label">Meta Title</label>
            <input type="text" name="meta_title" class="form-control" value="<?php echo e($data['meta_title']); ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Meta Description</label>
            <textarea name="meta_description" class="form-control" rows="2"><?php echo e($data['meta_description']); ?></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Meta Keywords (comma separated)</label>
            <input type="text" name="meta_keywords" class="form-control" value="<?php echo e($data['meta_keywords']); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
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
