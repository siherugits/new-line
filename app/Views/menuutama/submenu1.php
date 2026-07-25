<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h4 class="mb-4"><?= esc($title) ?></h4>

<div class="card">
    <div class="card-body">
        <p class="mb-3"><?= esc($pesan) ?></p>

        <!-- Tombol yang memicu $.ajax (lihat submenu1.js) -->
        <button id="btnLoad" class="btn btn-primary">
            <i class="bi bi-arrow-repeat me-1"></i>Muat Data via AJAX
        </button>

        <div id="hasil" class="mt-3 text-muted">Belum ada data.</div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    // Oper data PHP -> JS lewat variabel global (URL endpoint, csrf, dll).
    window.SUBMENU1 = {
        url: '<?= site_url('menuutama/submenu1/data') ?>'
    };
</script>
<script src="<?= site_url('assets/js/submenu1.js') ?>"></script>
<?= $this->endSection() ?>
