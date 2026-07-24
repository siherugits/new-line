/**
 * Shared DataTables initializer for the admin panel.
 * Server-side processing with Indonesian language + Bootstrap 5 styling.
 *
 *   adminDataTable('#myTable', '/admin/foo/data', [ {data:'id'}, ... ]);
 */
function adminDataTable(selector, url, columns, options) {
    return jQuery(selector).DataTable(
        jQuery.extend(
            {
                processing: true,
                serverSide: true,
                ajax: { url: url, type: 'GET' },
                order: [[0, 'asc']],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'Semua'],
                ],
                columns: columns,
                language: {
                    processing: 'Memuat…',
                    search: 'Cari:',
                    lengthMenu: 'Tampil _MENU_ data',
                    info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(disaring dari _MAX_ total)',
                    zeroRecords: 'Data tidak ditemukan',
                    paginate: {
                        first: 'Awal',
                        last: 'Akhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya',
                    },
                },
            },
            options || {}
        )
    );
}
