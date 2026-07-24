<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Users</h4>
    <a href="<?= site_url('admin/users/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New User</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th><th>Username</th><th>Email</th><th>Roles</th><th>Status</th><th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <?php $groups = $u->getGroups() ?: []; ?>
                <tr>
                    <td><?= esc($u->id) ?></td>
                    <td class="fw-semibold"><?= esc($u->username) ?></td>
                    <td><?= esc($u->email) ?></td>
                    <td>
                        <?php foreach ($groups as $g): ?>
                            <span class="badge bg-secondary"><?= esc($g) ?></span>
                        <?php endforeach; ?>
                        <?php if (! $groups): ?><span class="text-muted small">—</span><?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u->active): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <a href="<?= site_url('admin/users/' . $u->id . '/edit') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        <form action="<?= site_url('admin/users/' . $u->id . '/delete') ?>" method="post" class="d-inline" onsubmit="return confirm('Delete this user?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (! $users): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No users yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
