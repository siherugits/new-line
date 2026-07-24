<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-6">
        <h4 class="mb-3">Ganti Password</h4>

        <div class="card">
            <div class="card-body">
                <form action="<?= site_url('admin/account/password') ?>" method="post" autocomplete="off">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="current_password">Password Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="new_password">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="new-password" required>
                        <div class="form-text">Minimal <?= esc(config('Auth')->minimumPasswordLength) ?> karakter.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="pass_confirm">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="pass_confirm" name="pass_confirm" autocomplete="new-password" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-key me-1"></i>Simpan</button>
                        <a href="<?= site_url('admin') ?>" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
