<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$isEdit = $user !== null;
$action = $isEdit ? site_url('admin/users/' . $user->id) : site_url('admin/users');
$val    = static fn (string $f, $def = '') => old($f, $def);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $isEdit ? 'Edit User' : 'New User' ?></h4>
    <a href="<?= site_url('admin/users') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= $action ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= esc($val('username', $isEdit ? $user->username : '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= esc($val('email', $isEdit ? $user->email : '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password <?= $isEdit ? '<span class="text-muted small">(leave blank to keep)</span>' : '' ?></label>
                    <input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="active" value="1" id="active"
                            <?= old('active', $isEdit ? ($user->active ? '1' : '') : '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="active">Active</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Roles</label>
                    <div class="row">
                        <?php foreach ($roles as $r): ?>
                            <div class="col-sm-4 col-lg-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" value="<?= esc($r['name']) ?>"
                                        id="role_<?= esc($r['id']) ?>"
                                        <?= in_array($r['name'], (array) old('roles', $userRoles), true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="role_<?= esc($r['id']) ?>"><?= esc($r['title']) ?></label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save</button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
