<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Menus</h4>
    <a href="<?= site_url('admin/menus/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Menu</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr><th>#</th><th>Title</th><th>URL</th><th>Icon</th><th>Parent</th><th>Order</th><th>Active</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($menus as $m): ?>
                <tr>
                    <td><?= esc($m['id']) ?></td>
                    <td class="fw-semibold">
                        <?php if ($m['parent_id']): ?><span class="text-muted">&mdash; </span><?php endif; ?>
                        <?php if ($m['icon']): ?><i class="bi bi-<?= esc($m['icon']) ?> me-1"></i><?php endif; ?>
                        <?= esc($m['title']) ?>
                    </td>
                    <td><code><?= esc($m['url']) ?></code></td>
                    <td class="text-muted small"><?= esc($m['icon']) ?></td>
                    <td class="text-muted small"><?= esc($m['parent_id'] ? ($titles[$m['parent_id']] ?? $m['parent_id']) : '—') ?></td>
                    <td><?= esc($m['sort_order']) ?></td>
                    <td><?= $m['is_active'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                    <td class="text-end">
                        <a href="<?= site_url('admin/menus/' . $m['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form action="<?= site_url('admin/menus/' . $m['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this menu?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (! $menus): ?><tr><td colspan="8" class="text-center text-muted py-4">No menus yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
