/**
 * Contoh $.ajax untuk halaman Sub Menu Utama-1.
 *
 * File JS luar diletakkan di: public/assets/js/
 * Dipanggil dari view lewat: <script src="<?= site_url('assets/js/submenu1.js') ?>">
 *
 * URL endpoint & csrf dioper dari view lewat variabel global (window.SUBMENU1).
 */
$(function () {
    // Ambil config yang dikirim view (URL endpoint, dsb).
    var cfg = window.SUBMENU1 || {};

    $('#btnLoad').on('click', function () {
        $.ajax({
            url: cfg.url,          // endpoint dari controller
            type: 'GET',
            dataType: 'json',
            beforeSend: function () {
                $('#hasil').text('Memuat…');
            },
            success: function (res) {
                // res = JSON dari controller
                $('#hasil').text(res.pesan + ' (jam server: ' + res.waktu + ')');
            },
            error: function (xhr) {
                $('#hasil').text('Gagal memuat. Status: ' + xhr.status);
            },
        });
    });
});
