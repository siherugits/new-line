# Alur Aplikasi — Admin Panel (CodeIgniter 4 + Shield)

Dokumentasi lengkap cara kerja aplikasi ini: dari login sampai render halaman,
sistem role/menu, dan pola membuat halaman baru.

---

## 1. Gambaran Umum

Aplikasi ini adalah **admin panel** berbasis:

- **CodeIgniter 4** (framework PHP)
- **CodeIgniter Shield** (autentikasi & otorisasi)
- **CodeIgniter Settings** (penyimpanan setting di DB, dipakai untuk Tema)
- **Bootstrap 5.3** (tampilan, via CDN)
- **DataTables** (tabel server-side)
- **Database**: MySQL atau PostgreSQL (lihat `POSTGRESQL_SETUP.md`)

Fitur utama:
- Login dengan **captcha SVG** (tanpa GD/API key)
- Menu topbar **dinamis dari database** (bertingkat, per-role)
- Manajemen **Users, Roles, Permissions, Menus** (CRUD + DataTables)
- **Tema** aplikasi (warna & dark mode) yang bisa diubah admin
- **Ganti password** self-service

---

## 2. Struktur Folder Penting

```
app/
├── Config/
│   ├── Routes.php          # Semua definisi URL -> Controller
│   ├── Filters.php         # Daftar filter (admin, menuaccess)
│   ├── Events.php          # Trigger AuthSync tiap request
│   ├── Auth.php            # Konfigurasi Shield (view login custom, dll)
│   ├── Database.php        # Koneksi database
│   └── Theme.php           # Warna default tema
├── Controllers/
│   ├── Home.php            # Halaman "/" -> redirect ke admin/login
│   ├── CaptchaController.php  # Serve gambar captcha SVG
│   ├── PageNotFound.php    # Handler 404 ramah
│   ├── MenuUtama.php       # Contoh halaman menu custom
│   ├── Auth/
│   │   └── LoginController.php   # Override login Shield (+ cek captcha)
│   └── Admin/              # Semua controller area admin
│       ├── BaseAdminController.php  # Induk: siapkan menuTree
│       ├── Dashboard.php
│       ├── Users.php
│       ├── Roles.php
│       ├── Permissions.php
│       ├── Menus.php
│       ├── Theme.php
│       └── Account.php     # Ganti password
├── Models/
│   ├── MenuModel.php       # Query menu + kontrol akses
│   ├── RoleModel.php
│   ├── PermissionModel.php
│   └── UserGridModel.php   # Query DataTables user (join Shield)
├── Filters/
│   ├── AdminFilter.php     # Gerbang masuk /admin
│   └── MenuAccessFilter.php  # Kunci akses per-menu
├── Libraries/
│   ├── CaptchaSvg.php      # Generate & verifikasi captcha
│   ├── DataTable.php       # Helper server-side DataTables
│   └── AuthSync.php        # Sinkron role/permission DB -> Shield
├── Views/
│   ├── layouts/admin.php   # Layout utama (topbar + konten)
│   ├── auth/login.php      # Form login custom
│   └── admin/...           # View tiap halaman
└── Database/
    ├── Migrations/         # Struktur tabel
    └── Seeds/              # Data awal

public/
└── assets/
    ├── js/                 # File JavaScript luar
    ├── logo.svg            # Logo login
    └── logo-light.svg      # Logo topbar
```

---

## 3. Alur Request (Paling Penting)

Setiap kali browser membuka URL, ini yang terjadi:

```
Browser (URL)
   │
   ▼
[1] Routes.php         Cocokkan URL -> Controller::method
   │
   ▼
[2] Filters            Cek izin SEBELUM controller jalan
   │                   - 'admin'      : harus login + punya menu
   │                   - 'menuaccess' : role harus dicentang di menu ini
   ▼
[3] Controller         Ambil data, olah logika
   │
   ▼
[4] Model              Query ke database
   │
   ▼
[5] View               Render HTML (extend layout)
   │
   ▼
Browser (tampil halaman)
```

**Contoh konkret** — buka `admin/users`:
1. `Routes.php` → `Users::index`
2. Filter `admin` (login?) + `menuaccess` (boleh?) → lolos
3. `Users::index()` → siapkan data
4. View `admin/users/index.php` → render tabel
5. DataTables tarik data via AJAX ke `admin/users/data` (JSON)

---

## 4. Alur Login (dengan Captcha)

```
GET /login
   │
   ▼
Auth\LoginController::loginView   (extends Shield)
   │  tampilkan view app/Views/auth/login.php
   ▼
Form login + gambar captcha (<img src="/captcha">)
   │  captcha di-generate CaptchaController::index (SVG)
   ▼
POST /login  (email, password, captcha)
   │
   ▼
Auth\LoginController::loginAction
   │  [1] Cek captcha (CaptchaSvg::verify)  --salah--> balik + error
   │  [2] Kalau benar -> parent::loginAction (Shield verifikasi user)
   ▼
Berhasil -> redirect "/" -> Home::index
   │
   ▼
Home::index
   │  Punya menu? (MenuModel::hasAnyMenu)
   │    - Ya  -> redirect /admin
   │    - Tidak -> logout + pesan
   ▼
Masuk area admin
```

**File terkait:**
- Route login: `app/Config/Routes.php` (baris ~15-17)
- Controller: `app/Controllers/Auth/LoginController.php`
- Captcha: `app/Libraries/CaptchaSvg.php` + `app/Controllers/CaptchaController.php`
- View: `app/Views/auth/login.php`

---

## 5. Sistem Role & Akses Menu (INTI)

### Konsep

Aplikasi ini memakai **role** (Shield menyebutnya "group") dan **menu** yang
disimpan di database. Aturan aksesnya **sederhana**:

> Seorang user boleh masuk area admin **jika** salah satu role-nya memiliki
> minimal 1 menu yang dicentang. Menu yang tampil di topbar = menu yang
> dicentang untuk role user tersebut. **Superadmin selalu melihat semua menu.**

Jadi kontrol akses **murni dari centang menu** ("Visible to roles" di halaman
Edit Menu), bukan dari permission terpisah.

### Alur pengecekan

```
User login (punya role, mis. "user")
   │
   ▼
AdminFilter::before   (setiap akses /admin/*)
   │  MenuModel::hasAnyMenu(role) ?
   │    - Tidak -> logout + "tidak punya menu"
   │    - Ya    -> lanjut
   ▼
MenuAccessFilter::before   (cek URL spesifik)
   │  MenuModel::canAccessUri(role, url) ?
   │    - URL tak terkait menu -> lolos (mis. ganti password)
   │    - URL terkait menu & role dicentang -> lolos
   │    - role tak dicentang -> tolak, balik ke /admin
   ▼
Controller jalan
   │
   ▼
BaseAdminController   menyiapkan $menuTree (menu yang boleh dilihat role ini)
   ▼
Layout admin render topbar dari $menuTree
```

### Tabel database terkait

| Tabel | Isi |
|---|---|
| `users` | Data user (dari Shield) |
| `auth_identities` | Email & password (dari Shield) |
| `auth_groups_users` | Role/group tiap user (dari Shield) |
| `roles` | Daftar role (mirror dari Shield, dinamis) |
| `permissions` | Daftar permission |
| `role_permissions` | Role punya permission apa (matrix) |
| `menus` | Menu topbar (bertingkat via `parent_id`) |
| `menu_access` | Menu mana boleh dilihat role mana (centang) |
| `settings` | Setting tema (warna, dark mode) |

### AuthSync (penting dipahami)

`app/Libraries/AuthSync.php` dijalankan **tiap request** (via `Events.php`).
Fungsinya menyalin data `roles`, `permissions`, `role_permissions` dari DB ke
konfigurasi Shield saat runtime. Jadi perubahan role/permission lewat UI
langsung dikenali Shield tanpa edit file config.

---

## 6. Menu Dinamis (Topbar)

Menu topbar **tidak hardcode** — dibaca dari tabel `menus`.

- **Bertingkat**: kolom `parent_id` (maks 3 level).
- **Per-role**: tabel `menu_access` menentukan siapa lihat apa.
- **Dikelola via UI**: halaman `admin/menus` (CRUD).

`MenuModel::treeForRoles($roles, $isSuper)` membangun pohon menu yang boleh
dilihat, lalu `layouts/admin.php` me-render-nya jadi dropdown Bootstrap.

**Penting:** menu adalah **data database**, bukan file. Kalau membuat menu baru
lewat UI, menu itu hanya ada di DB komputer tersebut. Untuk memindahkannya ke
komputer lain, buat **seeder** (lihat `MenuThemeSeeder.php` sebagai contoh).

---

## 7. Halaman dengan DataTables (Server-Side)

Users, Roles, Permissions, Menus memakai **DataTables server-side** — data
di-load per halaman via AJAX, paging/sort/search diproses di database.

Polanya (contoh Users):

```
View admin/users/index.php
   │  tabel kosong + JS: adminDataTable('#usersTable', url, columns)
   ▼
AJAX GET admin/users/data
   ▼
Users::data()
   │  pakai UserGridModel + Library DataTable
   │  return JSON { draw, recordsTotal, recordsFiltered, data }
   ▼
DataTables render baris
```

**File terkait:**
- Library: `app/Libraries/DataTable.php` (proses paging/sort/search generik)
- JS helper: `public/assets/js/admin-datatable.js`
- Contoh model: `app/Models/UserGridModel.php`

---

## 8. Tema Aplikasi

Warna & dark mode disimpan di tabel `settings` (via CodeIgniter Settings).

```
Admin buka admin/theme
   │  pilih warna / preset / dark mode
   ▼
POST admin/theme -> Theme::update
   │  setting()->set('Theme.primary', '#...')  dst
   ▼
Tersimpan di DB
   ▼
layouts/admin.php membaca setting -> inject CSS variables
   │  :root { --bs-primary: ...; --app-navbar-bg: ...; }
   ▼
Seluruh tampilan berubah
```

**File terkait:**
- Config default: `app/Config/Theme.php`
- Controller: `app/Controllers/Admin/Theme.php`
- View: `app/Views/admin/theme/index.php`
- Inject CSS: `app/Views/layouts/admin.php` (bagian `<style>`)

---

## 9. Cara Membuat Halaman Baru (Template)

Ikuti 3 langkah ini (contoh nyata ada di `MenuUtama.php`):

### Langkah 1 — Route (`app/Config/Routes.php`)

Kalau URL diawali `admin/`, taruh **di dalam grup admin**:
```php
$routes->get('laporan', 'Laporan::index');   // jadi admin/laporan
```

Kalau URL **di luar** admin, pasang filter manual:
```php
$routes->get('laporan/harian', 'Laporan::harian', ['filter' => ['admin', 'menuaccess']]);
```

### Langkah 2 — Controller (`app/Controllers/Admin/Laporan.php`)

```php
namespace App\Controllers\Admin;

class Laporan extends BaseAdminController   // extends ini -> dapat menuTree otomatis
{
    public function index(): string
    {
        $data = ['title' => 'Laporan'];
        return $this->render('admin/laporan/index', $data, 'Laporan');
    }
}
```

> `BaseAdminController` otomatis menyiapkan `$menuTree` (untuk topbar) dan
> menyediakan method `render()`. Kalau controller di luar `Admin/`, siapkan
> `menuTree` manual (lihat `MenuUtama.php`).

### Langkah 3 — View (`app/Views/admin/laporan/index.php`)

```php
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<h4><?= esc($title) ?></h4>
<div class="card"><div class="card-body">Isi konten…</div></div>

<?= $this->endSection() ?>
```

### Langkah 4 (opsional) — Daftarkan menu

Buka `admin/menus` → New Menu → isi title, URL (`admin/laporan`), pilih parent,
centang role yang boleh. Menu langsung muncul di topbar untuk role tsb.

---

## 10. Memakai AJAX ($.ajax) di View

**File JS luar diletakkan di `public/assets/js/`** (hanya folder `public/`
yang bisa diakses browser). Contoh lengkap ada di
`public/assets/js/submenu1.js`.

```
View (section 'scripts'):
   - load jQuery
   - window.CONFIG = { url: '<?= site_url('...') ?>' }   // oper URL PHP -> JS
   - load file .js luar
        │
        ▼
File .js: $.ajax({ url: window.CONFIG.url }) ...
        │
        ▼
Route -> Controller::method -> return setJSON([...])
        │
        ▼
JS: success -> update tampilan
```

**Aturan penting:**
- Jangan hardcode URL di file `.js` (file statis tak bisa baca PHP). Oper
  lewat `window.XXX` dari view.
- Untuk POST, sertakan CSRF token (Shield/CI4 mewajibkan).

---

## 11. Menjalankan Aplikasi

```bash
# 1. Install dependency (sekali saja)
composer install

# 2. Migrasi tabel + seed data awal
php spark migrate --all
php spark db:seed AdminSeeder
php spark db:seed MenuAdministrationSeeder
php spark db:seed MenuThemeSeeder

# 3. Jalankan server
php spark serve --port 8080
```

Buka http://localhost:8080, login:
- Email : `admin@example.com`
- Password : `admin12345`

Untuk PostgreSQL, lihat **`POSTGRESQL_SETUP.md`**.

---

## 12. Ringkasan File Kunci

| Kalau mau… | Edit file… |
|---|---|
| Tambah/ubah URL | `app/Config/Routes.php` |
| Ubah aturan akses masuk admin | `app/Filters/AdminFilter.php` |
| Ubah aturan akses per-halaman | `app/Filters/MenuAccessFilter.php` |
| Ubah tampilan topbar/layout | `app/Views/layouts/admin.php` |
| Ubah form login / captcha | `app/Views/auth/login.php`, `app/Libraries/CaptchaSvg.php` |
| Ubah warna default tema | `app/Config/Theme.php` |
| Ubah koneksi database | `app/Config/Database.php` |
| Ubah data awal | `app/Database/Seeds/AdminSeeder.php` |
```
