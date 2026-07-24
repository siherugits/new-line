<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
$isEdit = $menu !== null;
$action = $isEdit ? site_url('admin/menus/' . $menu['id']) : site_url('admin/menus');
$get    = static fn (string $f, $def = '') => old($f, $def);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><?= $isEdit ? 'Edit Menu' : 'New Menu' ?></h4>
    <a href="<?= site_url('admin/menus') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= $action ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" value="<?= esc($get('title', $isEdit ? $menu['title'] : '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">URL <span class="text-muted small">(relative, e.g. admin/reports)</span></label>
                    <input type="text" name="url" class="form-control" value="<?= esc($get('url', $isEdit ? $menu['url'] : '')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Icon <span class="text-muted small">(Bootstrap icon name)</span></label>
                    <input type="text" name="icon" class="form-control" value="<?= esc($get('icon', $isEdit ? $menu['icon'] : '')) ?>" placeholder="e.g. people">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Parent</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— None (top level) —</option>
                        <?php foreach ($parents as $p): ?>
                            <option value="<?= esc($p['id']) ?>" <?= (string) $get('parent_id', $isEdit ? $menu['parent_id'] : '') === (string) $p['id'] ? 'selected' : '' ?>>
                                <?= esc($p['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Order</label>
                    <input type="number" name="sort_order" class="form-control" value="<?= esc($get('sort_order', $isEdit ? $menu['sort_order'] : 0)) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                            <?= old('is_active', $isEdit ? ($menu['is_active'] ? '1' : '') : '1') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Visible to roles</label>
                    <div class="row">
                        <?php foreach ($roles as $r): ?>
                            <div class="col-sm-4 col-lg-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" value="<?= esc($r['id']) ?>"
                                        id="mrole_<?= esc($r['id']) ?>"
                                        <?= in_array((int) $r['id'], array_map('intval', (array) old('roles', $menuRoleIds)), true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="mrole_<?= esc($r['id']) ?>"><?= esc($r['title']) ?></label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-text">Superadmin always sees every menu regardless of these settings.</div>
                </div>
            </div>
            <div class="mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save</button></div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
