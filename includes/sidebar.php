<?php $currentPath = $_SERVER['PHP_SELF'] ?? ''; ?>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="brand">
            <span class="brand-dot"></span>
            <span class="brand-name">Pivot</span>
        </div>
        <div class="header-controls">
            <button type="button" id="sidebar-collapse" class="collapse-switch" aria-label="Toggle sidebar">
                <i class="collapse-icon fa-solid fa-arrow-left" data-open="fa-arrow-left" data-closed="fa-arrow-right"></i>
            </button>
            <button type="button" id="theme-toggle" class="theme-switch" aria-label="Toggle theme">
                <span class="switch-icon switch-icon--sun">☀</span>
                <span class="switch-icon switch-icon--moon">🌙</span>
                <span class="switch-handle"></span>
            </button>
        </div>
    </div>
    <div class="sidebar-nav">
        <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="<?php echo strpos($currentPath, 'dashboard.php') !== false ? 'active' : ''; ?>">
            <span class="nav-icon"><i class="fa-solid fa-house"></i></span><span class="nav-label">Dashboard</span>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/home-hero.php" class="<?php echo strpos($currentPath, 'home-hero.php') !== false ? 'active' : ''; ?>">
            <span class="nav-icon"><i class="fa-solid fa-image"></i></span><span class="nav-label">Homepage Hero</span>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/blogs/index.php" class="<?php echo strpos($currentPath, 'blogs') !== false ? 'active' : ''; ?>">
            <span class="nav-icon"><i class="fa-solid fa-file-pen"></i></span><span class="nav-label">Blogs</span>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/blogs/create.php" class="<?php echo strpos($currentPath, 'create.php') !== false ? 'active' : ''; ?>">
            <span class="nav-icon"><i class="fa-solid fa-plus"></i></span><span class="nav-label">Create Blog</span>
        </a>
        <a href="<?php echo BASE_URL; ?>admin/auth/logout.php">
            <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span><span class="nav-label">Logout</span>
        </a>
    </div>
</div>
