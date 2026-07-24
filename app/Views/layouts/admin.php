<?php
/**
 * @var string $title
 * @var array  $menuTree
 */
$user      = service('auth')->user();
$uri       = uri_string();
$isActive  = static function (string $url) use ($uri): bool {
    $url = trim($url, '/');
    if ($url === 'admin') {
        return $uri === 'admin' || $uri === 'admin/';
    }
    return $url !== '' && str_starts_with($uri, $url);
};

/**
 * Recursively render menu items.
 *  - $depth 0 = top level (navbar), 1+ = inside a dropdown (submenu items).
 */
$renderMenu = static function (array $items, int $depth = 0) use (&$renderMenu, $isActive): void {
    foreach ($items as $item) {
        $hasChildren = ! empty($item['children']);
        $icon        = $item['icon'] ? '<i class="bi bi-' . esc($item['icon']) . ' me-' . ($depth === 0 ? '1' : '2') . '"></i>' : '';
        $title       = esc($item['title']);
        $href        = site_url($item['url']);
        $active      = $isActive($item['url']) ? 'active' : '';

        if ($depth === 0) {
            // ---- top-level item ----
            if ($hasChildren) {
                echo '<li class="nav-item dropdown">';
                echo '<a class="nav-link dropdown-toggle ' . $active . '" href="#" role="button" data-bs-toggle="dropdown">' . $icon . $title . '</a>';
                echo '<ul class="dropdown-menu">';
                $renderMenu($item['children'], $depth + 1);
                echo '</ul></li>';
            } else {
                echo '<li class="nav-item"><a class="nav-link ' . $active . '" href="' . $href . '">' . $icon . $title . '</a></li>';
            }
        } else {
            // ---- item inside a dropdown ----
            if ($hasChildren) {
                echo '<li class="dropdown-submenu">';
                echo '<a class="dropdown-item dropdown-toggle" href="' . $href . '" data-bs-toggle="dropdown">' . $icon . $title . '</a>';
                echo '<ul class="dropdown-menu">';
                $renderMenu($item['children'], $depth + 1);
                echo '</ul></li>';
            } else {
                echo '<li><a class="dropdown-item" href="' . $href . '">' . $icon . $title . '</a></li>';
            }
        }
    }
};
?>
<!DOCTYPE html>
<html lang="en"<?= setting('Theme.darkMode') ? ' data-bs-theme="dark"' : '' ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin') ?> &middot; Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?= $this->renderSection('styles') ?>
    <?php
        // Resolve theme colors (DB setting -> Config\Theme default).
        // Sanitize to a valid hex color; NEVER esc(..,'css') a hex value —
        // that escapes the '#' into "\23 " and breaks the color.
        $hexColor = static function (string $val, string $fallback): string {
            return preg_match('/^#[0-9A-Fa-f]{6}$/', $val) ? $val : $fallback;
        };
        $themeKeys = ['primary', 'secondary', 'success', 'danger', 'navbarBg', 'navbarText', 'bodyBg'];
        $theme     = [];
        foreach ($themeKeys as $k) {
            $theme[$k] = $hexColor((string) setting('Theme.' . $k), '#000000');
        }
        // Decide light/dark navbar variant from the chosen text color's
        // luminance, so the toggler icon & default states stay legible.
        $navText  = $theme['navbarText'] ?: '#ffffff';
        $hex      = ltrim($navText, '#');
        $r        = hexdec(substr($hex, 0, 2) ?: '0');
        $g        = hexdec(substr($hex, 2, 2) ?: '0');
        $b        = hexdec(substr($hex, 4, 2) ?: '0');
        $lum      = (0.299 * $r + 0.587 * $g + 0.114 * $b);
        $navbarDark = $lum > 150; // light text => dark navbar variant
    ?>
    <style>
        :root {
            --bs-primary: <?= $theme['primary'] ?>;
            --bs-secondary: <?= $theme['secondary'] ?>;
            --bs-success: <?= $theme['success'] ?>;
            --bs-danger: <?= $theme['danger'] ?>;
            --app-navbar-bg: <?= $theme['navbarBg'] ?>;
            --app-navbar-text: <?= $theme['navbarText'] ?>;
            --app-body-bg: <?= $theme['bodyBg'] ?>;
        }
        /* Map theme colors onto Bootstrap components */
        .btn-primary { --bs-btn-bg: var(--bs-primary); --bs-btn-border-color: var(--bs-primary); --bs-btn-hover-bg: var(--bs-primary); --bs-btn-hover-border-color: var(--bs-primary); --bs-btn-active-bg: var(--bs-primary); --bs-btn-active-border-color: var(--bs-primary); }
        .btn-outline-primary { --bs-btn-color: var(--bs-primary); --bs-btn-border-color: var(--bs-primary); --bs-btn-hover-bg: var(--bs-primary); --bs-btn-hover-border-color: var(--bs-primary); --bs-btn-active-bg: var(--bs-primary); }
        .btn-outline-danger { --bs-btn-color: var(--bs-danger); --bs-btn-border-color: var(--bs-danger); --bs-btn-hover-bg: var(--bs-danger); --bs-btn-hover-border-color: var(--bs-danger); }
        .bg-success { background-color: var(--bs-success) !important; }
        .bg-danger { background-color: var(--bs-danger) !important; }
        .bg-secondary { background-color: var(--bs-secondary) !important; }
        main a:not(.btn):not(.nav-link):not(.dropdown-item):not(.navbar-brand) { color: var(--bs-primary); }
        .page-link { color: var(--bs-primary); }
        .page-item.active .page-link { background-color: var(--bs-primary); border-color: var(--bs-primary); }
        .app-navbar { background-color: var(--app-navbar-bg) !important; }
        /* Force navbar menu text to the chosen color regardless of navbar-dark/light */
        .app-navbar .navbar-nav .nav-link,
        .app-navbar .navbar-brand,
        .app-navbar .navbar-toggler-icon { color: var(--app-navbar-text) !important; }
        .app-navbar .navbar-nav .nav-link { opacity: .85; }
        .app-navbar .navbar-nav .nav-link:hover,
        .app-navbar .navbar-nav .nav-link.active { opacity: 1; }
        <?php if (! setting('Theme.darkMode')): ?>
        body { background: var(--app-body-bg) !important; }
        <?php endif; ?>
        .navbar-brand { font-weight:600; letter-spacing:.3px; }
        .navbar .nav-link.active { font-weight:600; }
        .card { border:0; box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .table thead th { font-size:.8rem; text-transform:uppercase; letter-spacing:.4px; color:#6c757d; }
        main { padding-bottom:3rem; }

        /* --- nested dropdown (flyout submenu) for level 3+ --- */
        .dropdown-submenu { position: relative; }
        .dropdown-submenu > .dropdown-toggle::after {
            /* point the caret to the side instead of down */
            border-top: .3em solid transparent;
            border-bottom: .3em solid transparent;
            border-left: .3em solid;
            border-right: 0;
            margin-left: .4em;
            vertical-align: middle;
        }
        .dropdown-submenu > .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -.35rem;
            margin-left: .1rem;
        }
        /* open flyout on hover (desktop) */
        @media (min-width: 992px) {
            .dropdown-submenu:hover > .dropdown-menu { display: block; }
        }
        /* stacked/indented look when navbar is collapsed (mobile) */
        @media (max-width: 991.98px) {
            .dropdown-menu .dropdown-menu {
                position: static;
                left: 0;
                margin-left: 1rem;
                border: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg app-navbar <?= $navbarDark ? 'navbar-dark' : 'navbar-light' ?>">
    <div class="container">
        <a class="navbar-brand py-1" href="<?= site_url('admin') ?>"><img src="/assets/logo-light.svg" alt="Admin" style="height:32px;"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="topnav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php $renderMenu($menuTree ?? []); ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= esc($user->username ?? $user->email ?? 'Account') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted">Roles: <?= esc(implode(', ', $user->getGroups() ?: ['—'])) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= site_url('admin/account/password') ?>"><i class="bi bi-key me-2"></i>Ganti Password</a></li>
                        <li><a class="dropdown-item" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if (session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?= esc(session('error')) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (session('message')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?= esc(session('message')) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if (session('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0"><?php foreach ((array) session('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Enable click-to-open nested dropdown submenus without closing the parent menu.
document.querySelectorAll('.dropdown-submenu > .dropdown-toggle').forEach(function (el) {
    el.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var submenu = this.nextElementSibling;
        // close sibling submenus at the same level
        this.closest('.dropdown-menu').querySelectorAll(':scope > .dropdown-submenu > .dropdown-menu.show')
            .forEach(function (open) { if (open !== submenu) open.classList.remove('show'); });
        submenu.classList.toggle('show');
    });
});
// clean up any open submenus when the top-level dropdown closes
document.querySelectorAll('.navbar .dropdown').forEach(function (dd) {
    dd.addEventListener('hidden.bs.dropdown', function () {
        this.querySelectorAll('.dropdown-menu.show').forEach(function (m) { m.classList.remove('show'); });
    });
});
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
