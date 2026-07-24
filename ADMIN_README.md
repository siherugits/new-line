# Admin Panel — CodeIgniter 4 + Shield

Panel admin dengan **manajemen User, Role, Permission, dan Menu** dinamis di atas
[CodeIgniter Shield](https://shield.codeigniter.com/) (auth resmi CI4).

## Fitur

- **Auth**: registrasi/login/logout via Shield.
- **User management**: CRUD user + assign banyak role (Shield groups), aktif/nonaktif.
- **Role management**: CRUD role dinamis, tiap role punya banyak permission.
- **Permission management**: CRUD permission (format `group.action`).
- **Menu management**: topbar dinamis dari DB, tiap menu bisa punya sub-menu (dropdown)
  dan diatur **role mana** yang boleh melihatnya. Superadmin selalu melihat semua menu.
- Role & permission DB otomatis disinkronkan ke `Config\AuthGroups` Shield saat runtime
  (`app/Libraries/AuthSync.php`, dipanggil di `app/Config/Events.php`), sehingga
  `$user->can('...')` dan filter Shield tetap memakai data yang dikelola dari UI.

## Menjalankan

```bash
# 1. Pastikan MySQL (XAMPP) menyala. DB dibuat: ci4_admin
# 2. Konfigurasi ada di .env (hostname 127.0.0.1, user root, tanpa password)

# 3. Migrasi + seed (sudah dijalankan, ulang bila perlu reset)
php spark migrate --all
php spark db:seed AdminSeeder

# 4. Jalankan
php spark serve
# buka http://localhost:8080  (atau 8081 bila 8080 dipakai)
```

## Login default

| Field    | Value            |
|----------|------------------|
| Email    | admin@example.com|
| Password | admin12345       |
| Role     | superadmin       |

> Ganti password admin setelah login pertama.

## Struktur utama

```
app/
├─ Config/
│  ├─ Routes.php          # grup /admin dengan filter 'admin'
│  ├─ Filters.php         # alias 'admin' -> AdminFilter
│  └─ Events.php          # panggil AuthSync::apply() tiap request
├─ Filters/AdminFilter.php        # wajib login + permission admin.access
├─ Libraries/AuthSync.php         # DB roles/permissions -> Config\AuthGroups
├─ Controllers/Admin/
│  ├─ BaseAdminController.php     # inject menuTree ke semua view
│  ├─ Dashboard.php  Users.php  Roles.php  Permissions.php  Menus.php
├─ Models/  RoleModel  PermissionModel  MenuModel   (+ Shield UserModel)
├─ Database/
│  ├─ Migrations/..._CreateAdminTables.php   # roles, permissions, role_permissions, menus, menu_access
│  └─ Seeds/AdminSeeder.php
└─ Views/
   ├─ layouts/admin.php           # Bootstrap 5 + topbar dinamis
   └─ admin/{dashboard,users,roles,permissions,menus}/...
```

## Catatan otorisasi

- **Role** di tabel `roles` = *group* Shield. **Permission** di `permissions` = *permission* Shield.
- Pivot `role_permissions` membentuk *matrix* Shield.
- `menu_access` menentukan role mana yang melihat sebuah menu (superadmin bypass).
- Role bertanda `is_system` (superadmin, user) tidak bisa dihapus & machine-name-nya dikunci.
```
