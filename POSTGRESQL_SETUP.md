# Migrasi ke PostgreSQL

Panduan menjalankan aplikasi ini di PostgreSQL. Dijalankan di komputer yang
sudah terpasang PostgreSQL.

---

## 1. Prasyarat

- **PostgreSQL** terpasang & servicenya jalan (default port `5432`).
- **PHP extension pgsql** aktif. Cek dengan:

  ```bash
  php -m | grep pgsql
  ```

  Kalau kosong, buka `php.ini` (XAMPP: `C:\xampp\php\php.ini`) dan hapus tanda
  titik-koma di dua baris ini:

  ```ini
  extension=pgsql
  extension=pdo_pgsql
  ```

  Lalu restart Apache / terminal.

---

## 2. Buat database di PostgreSQL

Lewat `psql` (atau pgAdmin):

```bash
psql -U postgres
```

```sql
CREATE DATABASE ci4_admin;
\q
```

> Ganti `postgres` dengan user PG Anda kalau berbeda.

---

## 3. Konfigurasi koneksi

Koneksi database sekarang diatur di **`app/Config/Database.php`** (bukan `.env`),
pada properti `$default`. Nilainya sudah diisi untuk PostgreSQL:

```php
public array $default = [
    'hostname' => '127.0.0.1',
    'username' => 'postgres',
    'password' => '',          // <-- ISI password PostgreSQL Anda
    'database' => 'ci4_admin',
    'schema'   => 'public',
    'DBDriver' => 'Postgre',
    'port'     => 5432,
    // ...
];
```

Yang perlu Anda sesuaikan:
- **`password`** : isi password user PostgreSQL Anda.
- `username` / `hostname` / `port` : ubah kalau berbeda dari default.

> Baris `database.default.*` di `.env` sudah **dinonaktifkan** (dikomentari),
> supaya tidak menimpa nilai di config. Kalau suatu saat ingin override lewat
> `.env` (mis. beda password per komputer), tinggal hapus tanda `#`-nya.

---

## 4. Jalankan migrasi & seeder

Dari **terminal VS Code** (pastikan berada di folder project):

```bash
# 1. Migrasi tabel bawaan Shield (users, auth_*) + Settings
php spark migrate --all

# 2. Migrasi tabel admin (roles, permissions, menus, dll)
#    Sudah termasuk di --all, tapi kalau perlu terpisah:
php spark migrate

# 3. Isi data awal (role, permission, menu, user admin default)
php spark db:seed AdminSeeder

# 4. Susun menu jadi bertingkat (Administration > Users, dll)
php spark db:seed MenuAdministrationSeeder
```

> `migrate --all` menjalankan migrasi dari **semua namespace** (app + Shield +
> Settings). Wajib dipakai supaya tabel `users`, `auth_identities`, `settings`
> ikut terbuat.

---

## 5. Verifikasi

```bash
# Lihat status migrasi
php spark migrate:status

# Jalankan aplikasi
php spark serve --port 8080
```

Buka http://localhost:8080, login dengan:
- Email : `admin@example.com`
- Password : `admin12345`

---

## 6. Kalau perlu ulang dari nol

```bash
# Hapus SEMUA tabel lalu migrasi + seed ulang
php spark migrate:refresh --all
php spark db:seed AdminSeeder
php spark db:seed MenuAdministrationSeeder
```

> ⚠️ `migrate:refresh` menghapus semua data. Jangan dijalankan di produksi.

---

## Catatan teknis (sudah disesuaikan di kode)

Perubahan berikut sudah dibuat agar query jalan di PostgreSQL:

| Berkas | Penyesuaian |
|---|---|
| `app/Models/UserGridModel.php` | `GROUP_CONCAT` (MySQL) → `STRING_AGG` (PG), kolom `group` di-quote `"group"`, `GROUP BY` menyertakan semua kolom non-agregat |
| `app/Libraries/DataTable.php` | Pencarian pakai `insensitiveSearch` agar tetap case-insensitive (LIKE di PG case-sensitive) |

Migrasi (`app/Database/Migrations`) memakai **Forge**, sudah portable —
`TINYINT` otomatis dipetakan ke `SMALLINT` di PostgreSQL.

Jika ingin kembali ke MySQL, cukup kembalikan `DBDriver = MySQLi`, `port = 3306`
di `.env`. Kode mendeteksi driver secara otomatis.
