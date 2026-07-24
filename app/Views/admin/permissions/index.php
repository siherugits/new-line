<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Permissions</h4>
    <a href="<?= site_url('admin/permissions/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Permission</a>
</div>

<div class="card">
    <div class="card-body">
        <table id="permissionsTable" class="table table-hover align-middle w-100">
            <thead>
                <tr><th>#</th><th>Name</th><th>Description</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@2.1.8/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= site_url('assets/js/admin-datatable.js') ?>"></script>
<script>
$(function () {
    adminDataTable('#permissionsTable', '<?= site_url('admin/permissions/data') ?>', [
        { data: 'id' },
        { data: 'name' },
        { data: 'description' },
        { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
    ]);
});
</script>
<?= $this->endSection() ?>
