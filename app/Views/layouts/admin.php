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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin') ?> &middot; Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background:#f5f6fa; }
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
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= site_url('admin') ?>"><i class="bi bi-grid-1x2-fill me-1"></i>Admin</a>
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
</body>
</html>
