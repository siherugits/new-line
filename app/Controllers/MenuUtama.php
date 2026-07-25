<?php

namespace App\Controllers;

use App\Models\MenuModel;

/**
 * Contoh controller untuk menu custom "Menu Utama".
 *
 * URL  : menuutama/submenu1  ->  MenuUtama::submenu1
 * Alur : Route -> Filter (admin, menuaccess) -> Controller -> View
 *
 * Karena URL ini di LUAR grup "admin", filter dipasang manual di Routes.php.
 */
class MenuUtama extends BaseController
{
    public function submenu1(): string
    {
        // Ambil menu tree untuk topbar (supaya layout admin tetap tampil menu).
        $user      = service('auth')->user();
        $roleNames = $user ? $user->getGroups() : [];
        $isSuper   = in_array('superadmin', $roleNames, true);
        $menuTree  = (new MenuModel())->treeForRoles($roleNames, $isSuper);

        // Data apa saja yang mau dikirim ke view.
        $data = [
            'title'    => 'Sub Menu Utama-1',
            'menuTree' => $menuTree,
            'pesan'    => 'Ini halaman isi konten dari Sub Menu Utama-1.',
        ];

        return view('menuutama/submenu1', $data);
    }

    /**
     * Endpoint yang di-hit oleh $.ajax dari submenu1.js.
     * Mengembalikan JSON, bukan HTML.
     *
     * URL: menuutama/submenu1/data  ->  MenuUtama::submenu1Data
     */
    public function submenu1Data()
    {
        return $this->response->setJSON([
            'pesan' => 'Halo dari AJAX!',
            'waktu' => date('H:i:s'),
        ]);
    }
}
