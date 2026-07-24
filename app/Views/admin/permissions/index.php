<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Permissions</h4>
    <a href="<?= site_url('admin/permissions/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Permission</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr><th>#</th><th>Name</th><th>Description</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($permissions as $p): ?>
                <tr>
                    <td><?= esc($p['id']) ?></td>
                    <td><code><?= esc($p['name']) ?></code></td>
                    <td class="text-muted"><?= esc($p['description']) ?></td>
                    <td class="text-end">
                        <a href="<?= site_url('admin/permissions/' . $p['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form action="<?= site_url('admin/permissions/' . $p['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this permission?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (! $permissions): ?><tr><td colspan="4" class="text-center text-muted py-4">No permissions yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
