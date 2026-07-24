<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="bi bi-cone-striped text-warning" style="font-size:3rem;"></i>
                </div>
                <h4 class="mb-2">Halaman belum tersedia</h4>
                <p class="text-muted mb-1">Menu ini sudah terdaftar, tapi halamannya masih dalam pengembangan.</p>
                <p class="text-muted small mb-4"><code><?= esc('/' . $uri) ?></code></p>
                <a href="<?= site_url('admin') ?>" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-1"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
