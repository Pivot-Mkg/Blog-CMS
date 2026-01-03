<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/home_hero.php';

$hero = get_home_hero();
$errors = [];

if (is_post()) {
    verify_csrf();
    $payload = [
        'title' => $_POST['hero_title'] ?? '',
        'subtitle' => $_POST['hero_subtitle'] ?? '',
        'cta_label' => $_POST['hero_cta_label'] ?? '',
        'cta_url' => $_POST['hero_cta_url'] ?? '',
        'image' => $_POST['hero_image'] ?? '',
        'image_alt' => $_POST['hero_image_alt'] ?? '',
    ];
    if (save_home_hero($payload)) {
        flash('success', 'Homepage hero updated.');
        redirect(BASE_URL . 'admin/home-hero.php');
    } else {
        $errors[] = 'Unable to save settings. Check file permissions.';
        $hero = array_merge($hero, $payload);
    }
}

$heroImageSrc = home_hero_resolve_image($hero['image'] ?? '');

$pageTitle = 'Homepage Hero';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="page-header">
    <div>
        <div class="breadcrumb"><a href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a> <span>/</span> Homepage Hero</div>
        <h1 class="page-title">Homepage Hero</h1>
        <p class="page-subtitle">Control the banner content on the public blog listing page.</p>
    </div>
    <div class="page-actions">
        <a class="btn btn-secondary btn-ghost" href="<?php echo BASE_URL; ?>admin/dashboard.php">Back</a>
        <button class="btn btn-primary" type="submit" form="hero-form">Save</button>
    </div>
</div>

<form id="hero-form" class="edit-form" method="post">
    <?php echo csrf_field(); ?>
    <?php if (!empty($errors)): ?>
        <div class="card form-alert form-alert--error">
            <?php echo e(implode(' ', $errors)); ?>
        </div>
    <?php endif; ?>
    <?php if ($flash = flash('success')): ?>
        <div class="card form-alert form-alert--success">
            <?php echo e($flash['message']); ?>
        </div>
    <?php endif; ?>

    <section class="card section-card">
        <div class="section-card__header">
            <div>
                <h2>Hero content</h2>
                <!-- <p class="section-hint">Independent of any blog post—shown on the blog listing page.</p> -->
            </div>
        </div>
        <div class="section-card__body form-grid form-grid--split">
            <div class="form-group">
                <label class="form-label">Title</label>
                <input type="text" name="hero_title" class="form-control form-control--lg" required value="<?php echo e($hero['title']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Subtitle</label>
                <textarea name="hero_subtitle" class="form-control" rows="3"><?php echo e($hero['subtitle']); ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">CTA label</label>
                <input type="text" name="hero_cta_label" class="form-control" placeholder="e.g., Browse posts" value="<?php echo e($hero['cta_label']); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">CTA URL</label>
                <input type="text" name="hero_cta_url" class="form-control" placeholder="/index.php or https://example.com" value="<?php echo e($hero['cta_url']); ?>">
                <small class="form-hint">Use a full URL or a path starting with "/".</small>
            </div>
        </div>
    </section>

    <section class="card section-card">
        <div class="section-card__header">
            <div>
                <h2>Hero background</h2>
                <!-- <p class="section-hint">Choose the banner image and alt text.</p> -->
            </div>
        </div>
        <div class="section-card__body media-grid">
            <div class="media-block">
                <div class="image-preview"><img id="hero-image-preview" alt="<?php echo e($hero['image_alt']); ?>" src="<?php echo e($heroImageSrc); ?>" class="is-visible"></div>
                <div class="form-group" style="display:none;">
                    <label class="form-label">Image URL</label>
                    <input type="text" id="hero-image" name="hero_image" class="form-control" value="<?php echo e($hero['image']); ?>">
                    <small class="form-hint">Paste a direct image URL or upload below.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Image alt text</label>
                    <input type="text" name="hero_image_alt" class="form-control" value="<?php echo e($hero['image_alt']); ?>">
                    <!-- <small class="form-hint">Used for accessibility.</small> -->
                </div>
                <button type="button" class="btn btn-secondary btn-ghost" id="hero-image-upload">
                    <i class="fa-solid fa-upload"></i> Upload image
                </button>
            </div>
        </div>
    </section>

    <div class="form-actions">
        <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>admin/dashboard.php">Cancel</a>
        <button type="submit" class="btn btn-primary">Save changes</button>
    </div>
</form>

<script>
(function() {
    const baseUrl = <?php echo json_encode(BASE_URL); ?>;
    const originBase = (() => {
        if (/^https?:\/\//i.test(baseUrl)) return baseUrl;
        return new URL(baseUrl, window.location.origin).href;
    })();
    const uploadBtn = document.getElementById('hero-image-upload');
    const imageInput = document.getElementById('hero-image');
    const preview = document.getElementById('hero-image-preview');

    const resolveUrl = (value) => {
        if (!value) return '';
        if (/^https?:\/\//i.test(value)) return value;
        if (value.startsWith(baseUrl) || value.startsWith(originBase)) return value;
        const normalizedBase = originBase.replace(/\/$/, '');
        return normalizedBase + '/' + value.replace(/^\//, '');
    };

    const updatePreview = () => {
        if (preview && imageInput && imageInput.value) {
            preview.src = resolveUrl(imageInput.value);
            preview.classList.add('is-visible');
        }
    };
    if (imageInput) {
        imageInput.addEventListener('input', updatePreview);
    }

    if (uploadBtn) {
        uploadBtn.addEventListener('click', () => {
            const picker = document.createElement('input');
            picker.type = 'file';
            picker.accept = 'image/*';
            picker.onchange = async () => {
                const file = picker.files && picker.files[0];
                if (!file) return;
                const formData = new FormData();
                formData.append('file', file);
                try {
                    const response = await fetch(`${originBase.replace(/\/$/, '')}/admin/media/upload_handler.php`, { method: 'POST', body: formData });
                    const data = await response.json();
                    if (!response.ok || !data.location) throw new Error(data.error || 'Upload failed');
                    if (data.location.startsWith(baseUrl)) {
                        imageInput.value = data.location.replace(baseUrl, '');
                    } else if (data.location.startsWith(originBase)) {
                        imageInput.value = data.location.replace(originBase, '');
                    } else {
                        imageInput.value = data.location;
                    }
                    updatePreview();
                } catch (err) {
                    alert(err.message || 'Upload failed');
                }
            };
            picker.click();
        });
    }

    updatePreview();
})();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
