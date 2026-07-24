<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$isEdit = $permission !== null;
$action = $isEdit ? site_url('admin/permissions/' . $permission['id']) : site_url('admin/permissions');
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $isEdit ? 'Edit Permission' : 'New Permission' ?></h4>
    <a href="<?= site_url('admin/permissions') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= $action ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="<?= esc(old('name', $isEdit ? $permission['name'] : '')) ?>" placeholder="e.g. reports.view" required>
                    <div class="form-text">Dotted format like <code>group.action</code></div>
                </div>
                <div class="col-md-7">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" value="<?= esc(old('description', $isEdit ? $permission['description'] : '')) ?>">
                </div>
            </div>
            <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save</button></div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
