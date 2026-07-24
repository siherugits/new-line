<?= $this->extend('layouts/admin') ?>

<?= $this->section('styles') ?>
<link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@2.1.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Users</h4>
    <a href="<?= site_url('admin/users/new') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New User</a>
</div>

<div class="card">
    <div class="card-body">
        <table id="usersTable" class="table table-hover align-middle w-100">
            <thead>
                <tr>
                    <th>#</th><th>Username</th><th>Email</th><th>Roles</th><th>Status</th><th class="text-end">Actions</th>
                </tr>
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
    adminDataTable('#usersTable', '<?= site_url('admin/users/data') ?>', [
        { data: 'id' },
        { data: 'username' },
        { data: 'email' },
        { data: 'roles', orderable: false },
        { data: 'status' },
        { data: 'actions', orderable: false, searchable: false, className: 'text-end' },
    ]);
});
</script>
<?= $this->endSection() ?>
