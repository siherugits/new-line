<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Dashboard</h4>

<div class="row g-3">
    <?php
    $cards = [
        ['label' => 'Users', 'value' => $stats['users'], 'icon' => 'people', 'color' => 'primary', 'url' => 'admin/users'],
        ['label' => 'Roles', 'value' => $stats['roles'], 'icon' => 'shield-lock', 'color' => 'success', 'url' => 'admin/roles'],
        ['label' => 'Permissions', 'value' => $stats['permissions'], 'icon' => 'key', 'color' => 'warning', 'url' => 'admin/permissions'],
        ['label' => 'Menus', 'value' => $stats['menus'], 'icon' => 'list', 'color' => 'info', 'url' => 'admin/menus'],
        ['label' => 'Tema', 'value' => null, 'icon' => 'palette', 'color' => 'danger', 'url' => 'admin/theme'],
    ];
    foreach ($cards as $c):
        // Only show a card if the user has access to that menu.
        if (! isset($allowedUrls[trim($c['url'], '/')])) {
            continue;
        }
        ?>
        <div class="col-sm-6 col-lg-3">
            <a href="<?= site_url($c['url']) ?>" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="rounded-circle bg-<?= $c['color'] ?> bg-opacity-10 text-<?= $c['color'] ?> d-flex align-items-center justify-content-center me-3" style="width:52px;height:52px;">
                            <i class="bi bi-<?= $c['icon'] ?> fs-4"></i>
                        </div>
                        <div>
                            <div class="h4 mb-0 text-dark"><?= $c['value'] === null ? '<i class="bi bi-arrow-right-circle"></i>' : esc($c['value']) ?></div>
                            <div class="text-muted small"><?= esc($c['label']) ?></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>

    <?php if (empty(array_filter($cards, static fn ($c) => isset($allowedUrls[trim($c['url'], '/')])))): ?>
        <div class="col-12">
            <div class="card"><div class="card-body text-muted">Selamat datang. Gunakan menu di atas untuk mulai.</div></div>
        </div>
    <?php endif; ?>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h6 class="text-muted">Welcome</h6>
        <p class="mb-0">This admin panel uses <strong>CodeIgniter Shield</strong> for authentication and authorization. Roles, permissions and the top navigation menu are all managed dynamically from the database and synced into Shield at runtime.</p>
    </div>
</div>

<?= $this->endSection() ?>
