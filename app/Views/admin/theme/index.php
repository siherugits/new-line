<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Tema Aplikasi</h4>
    <form action="<?= site_url('admin/theme/reset') ?>" method="post" onsubmit="return confirm('Kembalikan tema ke default?');">
        <?= csrf_field() ?>
        <button class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset Default</button>
    </form>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-body">
                <form action="<?= site_url('admin/theme') ?>" method="post">
                    <?= csrf_field() ?>

                    <!-- Presets: klik untuk isi warna otomatis -->
                    <div class="mb-4">
                        <label class="form-label d-block small text-muted">Preset Cepat</label>
                        <div class="d-flex flex-wrap gap-2" id="presetButtons"></div>
                    </div>

                    <?php foreach ($fields as $key => $label): ?>
                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-5 col-form-label" for="c_<?= esc($key) ?>"><?= esc($label) ?></label>
                            <div class="col-sm-7">
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color theme-color"
                                           id="c_<?= esc($key) ?>" name="<?= esc($key) ?>"
                                           value="<?= esc($values[$key]) ?>" data-target="#t_<?= esc($key) ?>"
                                           title="<?= esc($label) ?>">
                                    <input type="text" class="form-control theme-hex" id="t_<?= esc($key) ?>"
                                           value="<?= esc($values[$key]) ?>" data-target="#c_<?= esc($key) ?>"
                                           maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="mb-3 row">
                        <div class="col-sm-7 offset-sm-5">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="darkMode"
                                       name="darkMode" value="1" <?= $darkMode ? 'checked' : '' ?>>
                                <label class="form-check-label" for="darkMode">Mode Gelap (dark mode seluruh admin)</label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark btn-lg"><i class="bi bi-check-lg me-1"></i>Simpan Tema</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Live preview -->
    <div class="col-12 col-lg-5">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-3">Pratinjau</h6>
                <div id="previewNavbar" class="rounded px-3 py-2 mb-3 d-flex align-items-center">
                    <span class="fw-semibold me-auto" id="previewBrand">Admin</span>
                    <span class="small" id="previewNavText">Menu</span>
                </div>
                <button type="button" class="btn mb-2 w-100" id="previewPrimary">Tombol Primary</button>
                <div class="d-flex gap-2 mb-3">
                    <span class="badge" id="previewSecondary">secondary</span>
                    <span class="badge" id="previewSuccess">success</span>
                    <span class="badge" id="previewDanger">danger</span>
                </div>
                <div class="rounded p-3 small text-muted" id="previewBody">Area konten halaman</div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Preset themes: keys match the color field names.
var THEME_PRESETS = [
    { label: 'Bootstrap',  swatch: '#0d6efd', primary: '#0d6efd', secondary: '#6c757d', success: '#198754', danger: '#dc3545', navbarBg: '#212529', navbarText: '#ffffff', bodyBg: '#f5f6fa', darkMode: false },
    { label: 'Laut',       swatch: '#0ea5e9', primary: '#0ea5e9', secondary: '#64748b', success: '#10b981', danger: '#ef4444', navbarBg: '#0f172a', navbarText: '#e2e8f0', bodyBg: '#f1f5f9', darkMode: false },
    { label: 'Hutan',      swatch: '#16a34a', primary: '#16a34a', secondary: '#6b7280', success: '#22c55e', danger: '#dc2626', navbarBg: '#14532d', navbarText: '#dcfce7', bodyBg: '#f0fdf4', darkMode: false },
    { label: 'Anggur',     swatch: '#7c3aed', primary: '#7c3aed', secondary: '#6b7280', success: '#16a34a', danger: '#e11d48', navbarBg: '#2e1065', navbarText: '#ede9fe', bodyBg: '#f5f3ff', darkMode: false },
    { label: 'Senja',      swatch: '#f97316', primary: '#f97316', secondary: '#78716c', success: '#65a30d', danger: '#dc2626', navbarBg: '#431407', navbarText: '#ffedd5', bodyBg: '#fff7ed', darkMode: false },
    { label: 'Terang',     swatch: '#e2e8f0', primary: '#2563eb', secondary: '#64748b', success: '#16a34a', danger: '#dc2626', navbarBg: '#ffffff', navbarText: '#1f2937', bodyBg: '#f8fafc', darkMode: false },
    { label: 'Gelap',      swatch: '#1a1d21', primary: '#3b82f6', secondary: '#6b7280', success: '#22c55e', danger: '#ef4444', navbarBg: '#0b0d10', navbarText: '#e5e7eb', bodyBg: '#121417', darkMode: true }
];

(function () {
    // Two-way sync between color inputs and their hex text fields.
    document.querySelectorAll('.theme-color').forEach(function (picker) {
        var hex = document.querySelector(picker.dataset.target);
        picker.addEventListener('input', function () { hex.value = picker.value; updatePreview(); });
    });
    document.querySelectorAll('.theme-hex').forEach(function (hex) {
        var picker = document.querySelector(hex.dataset.target);
        hex.addEventListener('input', function () {
            if (/^#[0-9A-Fa-f]{6}$/.test(hex.value)) { picker.value = hex.value; updatePreview(); }
        });
    });

    function val(id) { return document.getElementById('c_' + id).value; }

    function updatePreview() {
        var nav = document.getElementById('previewNavbar');
        nav.style.background = val('navbarBg');
        var navText = val('navbarText');
        document.getElementById('previewBrand').style.color = navText;
        document.getElementById('previewNavText').style.color = navText;

        var pri = document.getElementById('previewPrimary');
        pri.style.background = val('primary'); pri.style.borderColor = val('primary'); pri.style.color = '#fff';

        setBadge('previewSecondary', val('secondary'));
        setBadge('previewSuccess', val('success'));
        setBadge('previewDanger', val('danger'));
        var body = document.getElementById('previewBody');
        body.style.background = val('bodyBg');
        body.style.color = document.getElementById('darkMode').checked ? '#e5e7eb' : '#6c757d';
    }
    function setBadge(id, color) { var e = document.getElementById(id); e.style.background = color; e.style.color = '#fff'; }

    document.getElementById('darkMode').addEventListener('change', updatePreview);

    // Build preset buttons.
    var wrap = document.getElementById('presetButtons');
    THEME_PRESETS.forEach(function (preset) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-secondary d-flex align-items-center gap-2';
        btn.innerHTML = '<span style="display:inline-block;width:14px;height:14px;border-radius:3px;background:'
            + preset.swatch + ';border:1px solid rgba(0,0,0,.15)"></span>' + preset.label;
        btn.addEventListener('click', function () { applyPreset(preset); });
        wrap.appendChild(btn);
    });

    function applyPreset(preset) {
        ['primary', 'secondary', 'success', 'danger', 'navbarBg', 'navbarText', 'bodyBg'].forEach(function (key) {
            var picker = document.getElementById('c_' + key);
            var hex = document.getElementById('t_' + key);
            if (picker && preset[key]) { picker.value = preset[key]; hex.value = preset[key]; }
        });
        document.getElementById('darkMode').checked = !!preset.darkMode;
        updatePreview();
    }

    updatePreview();
})();
</script>
<?= $this->endSection() ?>
