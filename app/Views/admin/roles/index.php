<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Roles</h4>
    <a href="<?= site_url('admin/roles/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Role</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr><th>#</th><th>Name</th><th>Title</th><th>Description</th><th>Permissions</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($roles as $r): ?>
                <tr>
                    <td><?= esc($r['id']) ?></td>
                    <td><code><?= esc($r['name']) ?></code> <?= $r['is_system'] ? '<span class="badge bg-info text-dark ms-1">system</span>' : '' ?></td>
                    <td class="fw-semibold"><?= esc($r['title']) ?></td>
                    <td class="text-muted small"><?= esc($r['description']) ?></td>
                    <td><span class="badge bg-secondary"><?= esc($permCounts[$r['id']] ?? 0) ?></span></td>
                    <td class="text-end">
                        <a href="<?= site_url('admin/roles/' . $r['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <?php if (! $r['is_system']): ?>
                        <form action="<?= site_url('admin/roles/' . $r['id'] . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this role?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
