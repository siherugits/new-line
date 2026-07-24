<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$isEdit = $role !== null;
$action = $isEdit ? site_url('admin/roles/' . $role['id']) : site_url('admin/roles');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $isEdit ? 'Edit Role' : 'New Role' ?></h4>
    <a href="<?= site_url('admin/roles') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= $action ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Machine name</label>
                    <input type="text" name="name" class="form-control" value="<?= esc(old('name', $isEdit ? $role['name'] : '')) ?>"
                        <?= $isEdit && $role['is_system'] ? 'readonly' : '' ?> required>
                    <div class="form-text">lowercase, no spaces (used in code)</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="<?= esc(old('title', $isEdit ? $role['title'] : '')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" value="<?= esc(old('description', $isEdit ? $role['description'] : '')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Permissions</label>
                    <div class="row">
                        <?php foreach ($permissions as $p): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= esc($p['id']) ?>"
                                        id="perm_<?= esc($p['id']) ?>"
                                        <?= in_array($p['name'], (array) old('permissions', $rolePerms), true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="perm_<?= esc($p['id']) ?>">
                                        <code><?= esc($p['name']) ?></code>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (! $permissions): ?><div class="col-12 text-muted small">No permissions defined yet.</div><?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save</button></div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
