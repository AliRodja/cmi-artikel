# Artikel App

Website artikel berbasis **Laravel 13 + MySQL + Blade + Tailwind CSS v4** untuk ujian masuk IT CMI.
Pengunjung dapat membaca artikel yang sudah *Published*. User yang login dapat mengelola artikelnya sendiri
dan melihat laporan artikel (INNER JOIN `articles` × `users`).

---

## 1. Kebutuhan Sistem

| Software | Versi minimal | Cek dengan |
|---|---|---|
| PHP | 8.3 | `php -v` |
| Composer | 2.x | `composer -V` |
| MySQL / MariaDB | 8.0 / 10.6 | `mysql --version` |
| Node.js + npm | 20.19 | `node -v` dan `npm -v` |
| Git | — | `git --version` |

Ekstensi PHP yang dibutuhkan (biasanya sudah aktif): `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd`.

> **Windows:** paket seperti [Laragon](https://laragon.org) atau [XAMPP](https://www.apachefriends.org) sudah menyertakan
> PHP dan MySQL. Pastikan `php` dan `composer` bisa dipanggil dari terminal.
>
> **macOS (Homebrew):** `brew install php composer mysql node`, lalu jalankan MySQL dengan `brew services start mysql`.

---

## 2. Instalasi dari Awal

### Langkah 1 — Clone repository

```bash
git clone https://github.com/AliRodja/artikel-app.git
cd artikel-app
```

### Langkah 2 — Install dependency PHP

```bash
composer install
```

Perintah ini membaca `composer.lock` dan mengunduh Laravel beserta library lain ke folder `vendor/`.

### Langkah 3 — Buat file konfigurasi `.env`

```bash
# macOS / Linux / Git Bash
cp .env.example .env

# Windows (Command Prompt)
copy .env.example .env
```

Lalu buat *application key* (dipakai untuk enkripsi session & cookie):

```bash
php artisan key:generate
```

### Langkah 4 — Jalankan MySQL dan buat database

Pastikan service MySQL sudah berjalan:

- **macOS (Homebrew):** `brew services start mysql`
- **Laragon / XAMPP:** klik *Start* pada MySQL di control panel.

Buat database kosong bernama `artikel_app`, pilih salah satu cara:

**Cara A — lewat terminal**

```bash
mysql -u root -p
```

```sql
CREATE DATABASE artikel_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

> Jika user `root` tidak memakai password, jalankan `mysql -u root` (tanpa `-p`).

**Cara B — lewat phpMyAdmin / HeidiSQL / TablePlus**

Buat database baru dengan nama `artikel_app` dan collation `utf8mb4_unicode_ci`.

### Langkah 5 — Hubungkan aplikasi ke database

Buka file `.env` dan sesuaikan bagian berikut dengan MySQL di komputer Anda:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=artikel_app
DB_USERNAME=root
DB_PASSWORD=          # isi jika user MySQL Anda memakai password
```

Kosongkan cache konfigurasi agar perubahan `.env` terbaca, lalu cek koneksi:

```bash
php artisan config:clear
php artisan db:show
```

Jika koneksi berhasil, akan tampil informasi seperti:

```
MySQL ............ 8.x
Connection ....... mysql
Database ......... artikel_app
Host ............. 127.0.0.1
```

Jika muncul error, lihat bagian [Troubleshooting](#11-troubleshooting).

### Langkah 6 — Buat tabel dan isi data contoh

```bash
php artisan migrate --seed
```

- `migrate` membuat tabel `users`, `articles`, `password_reset_tokens`, `sessions`, `cache`, dan `jobs`.
- `--seed` mengisi data contoh: 1 akun demo dengan 20 artikel, 10 artikel milik user lain,
  dan 3 artikel **tanpa penulis** (untuk membuktikan INNER JOIN pada halaman Laporan Artikel).

Untuk mengulang database dari nol kapan saja:

```bash
php artisan migrate:fresh --seed
```

### Langkah 7 — Aktifkan akses gambar upload

```bash
php artisan storage:link
```

Gambar artikel disimpan di `storage/app/public/articles`. Perintah ini membuat shortcut `public/storage`
agar gambar tersebut bisa dibuka dari browser.

### Langkah 8 — Install dan build aset front-end (Tailwind CSS)

```bash
npm install
npm run build
```

### Langkah 9 — Jalankan aplikasi

```bash
php artisan serve
```

Buka **http://localhost:8000** di browser.

> **Mode development:** jika ingin mengubah tampilan, jalankan `npm run dev` di terminal terpisah
> (biarkan tetap berjalan) agar perubahan CSS langsung terlihat tanpa build ulang.

---

## 3. Akun Demo

| Email | Password |
|---|---|
| `admin@example.com` | `password` |

User lain hasil seeder juga memakai password `password` (email-nya dapat dilihat di tabel `users`).

---

## 4. Menguji Lupa & Reset Password

Aplikasi memakai `MAIL_MAILER=log`, sehingga email **tidak benar-benar dikirim**, melainkan ditulis ke file log.

1. Buka halaman **Masuk → Lupa password?** dan masukkan `admin@example.com`.
2. Buka file `storage/logs/laravel.log`, cari baris paling bawah yang berisi `reset-password/...`.
3. Salin link tersebut ke browser, isi password baru, lalu masuk dengan password baru.

Link reset berlaku 60 menit dan hanya bisa dipakai satu kali.

---

## 5. Fitur vs Rubrik Penilaian

### Soal 1 — Website Artikel

| No | Kriteria | Bobot | Implementasi | Cara menguji |
|---|---|---|---|---|
| 1 | Login & Logout | 10 | `Auth\LoginController` — `Auth::attempt()`, "ingat saya", `session()->regenerate()` (cegah session fixation), `throttle:5,1` (maks 5 percobaan/menit), logout meng-*invalidate* session & token CSRF | Masuk dengan akun demo → klik **Keluar** |
| 2 | Register | 10 | `Auth\RegisterController` — validasi nama, email unik, password min 8 + konfirmasi; password di-hash otomatis oleh cast `hashed`; langsung login setelah daftar | Klik **Daftar** |
| 3 | Lupa & Reset Password | 10 | `ForgotPasswordController` + `ResetPasswordController` dengan *password broker* Laravel; token disimpan ter-hash, berlaku 60 menit, sekali pakai; email Bahasa Indonesia | Lihat [bagian 4](#4-menguji-lupa--reset-password) |
| 4 | Dashboard | 5 | `DashboardController` (invokable) — jumlah artikel, Published, Draft, dan 5 artikel terbaru **milik user yang login** | Masuk → halaman Dashboard |
| 5 | CRUD Artikel | 20 | `ArticleController` (resource) — daftar milik sendiri + search judul + filter status + pagination, tambah, detail, edit, hapus dengan konfirmasi; slug unik otomatis | Menu **Artikel Saya** |
| 6 | Upload & Pengelolaan Gambar | 10 | Disimpan di disk `public` folder `articles/` (nama file acak); preview sebelum upload; file lama **dihapus dari disk** saat gambar diganti, saat checkbox "Hapus gambar" dicentang, dan saat artikel dihapus | Tulis artikel dengan gambar → edit → ganti/hapus gambar |
| 7 | Validation Form | 5 | Form Request `ArticleRequest` (dipakai `store` & `update`): judul wajib maks 255, isi wajib, gambar `jpg/jpeg/png/webp` maks 2 MB, status hanya 0/1; pesan error Bahasa Indonesia, input lama dipertahankan (`old()`) | Kirim form kosong |
| 8 | Authorization / Middleware | 10 | Middleware `auth` (area dashboard), `guest` (halaman login/daftar), `throttle`; `ArticlePolicy` + `Gate::authorize()` → user hanya bisa melihat/mengedit/menghapus artikel **miliknya** (lainnya **403**); `user_id` tidak pernah diambil dari input form | Buka `/dashboard` tanpa login → diarahkan ke login. Buka `/dashboard/articles/{slug-milik-user-lain}/edit` → 403 |
| 9 | Halaman Publik Artikel | 5 | `PublicArticleController` — daftar artikel **Published** (search, 9 per halaman, eager loading penulis), detail artikel; artikel Draft → **404**; tidak ada tombol tambah/edit/hapus | Buka `/` tanpa login |
| 10 | Database & Migration | 5 | Migration `articles` dengan foreign key, unique slug, index (`status`, `created_at`); factory & seeder | `php artisan migrate:fresh --seed` |
| 11 | Blade & UI | 5 | Layout `layouts/app`, partial (`navbar`, `flash`), Blade component (`x-input`, `x-status-badge`, `x-auth-card`), form bersama `articles/_form`, Tailwind CSS v4, responsif, halaman 403 custom | — |
| 12 | Kerapihan Struktur & Code Quality | 5 | Validasi di Form Request, otorisasi di Policy, query scope (`published()`, `draft()`), konstanta status, accessor (`image_url`, `excerpt`), controller tipis | — |

**Ketentuan teknis**

| Ketentuan | Dipakai di |
|---|---|
| Laravel | Laravel 13 |
| MySQL | `DB_CONNECTION=mysql` |
| Eloquent ORM | Model `User` & `Article`, relasi `hasMany` / `belongsTo`, query scope, `$user->articles()->create()` |
| Blade | Seluruh tampilan di `resources/views` |
| Laravel Migration | `database/migrations` |
| Laravel Validation | `ArticleRequest`, `$request->validate()` di controller autentikasi |
| Authentication | Dibuat manual (tanpa Breeze/Jetstream) di `app/Http/Controllers/Auth` |
| Middleware | `auth`, `guest`, `throttle` di `routes/web.php` |
| Pagination | `paginate()` + `withQueryString()` di halaman publik, Artikel Saya, Laporan Artikel |
| Route Model Binding | `{article}` dicari lewat kolom `slug` (`getRouteKeyName()` di model `Article`) |
| Tanpa CMS siap pakai | ✔ |

### Soal 2 — INNER JOIN

Halaman: menu **Laporan Artikel** (`/dashboard/laporan-artikel`), file `app/Http/Controllers/ArticleReportController.php`
dan `resources/views/articles/report.blade.php`.

```php
DB::table('articles')
    ->join('users', 'articles.user_id', '=', 'users.id')          // INNER JOIN
    ->select('articles.id', 'articles.title', 'articles.slug', 'articles.status', 'articles.created_at',
             'users.name as author_name', 'users.email as author_email')
    ->where('articles.status', Article::STATUS_PUBLISHED)         // status = 1
    ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {   // bonus search
        $q->where('articles.title', 'like', "%{$search}%")
          ->orWhere('users.name', 'like', "%{$search}%")
          ->orWhere('users.email', 'like', "%{$search}%");
    }))
    ->orderByDesc('articles.created_at')                          // terbaru dulu
    ->paginate(10)                                                // 10 per halaman
    ->withQueryString();
```

SQL yang dihasilkan:

```sql
select ... from `articles`
inner join `users` on `articles`.`user_id` = `users`.`id`
where `articles`.`status` = 1 [and (`articles`.`title` like ? or `users`.`name` like ? or `users`.`email` like ?)]
order by `articles`.`created_at` desc
limit 10 offset ...
```

| Kriteria | Bobot | Implementasi |
|---|---|---|
| INNER JOIN | 30 | `->join()` pada Query Builder = `INNER JOIN`; artikel dengan `user_id = NULL` tidak punya pasangan di `users` sehingga tidak ikut |
| Filtering status | 15 | `where('articles.status', 1)` (lewat konstanta `Article::STATUS_PUBLISHED`) |
| Sorting | 10 | `orderByDesc('articles.created_at')` |
| Pagination | 15 | `paginate(10)` + `withQueryString()` agar kata kunci tetap terbawa saat pindah halaman; nomor urut berlanjut antar halaman |
| Query Laravel | 15 | Laravel Query Builder; kolom ditulis dengan prefix tabel (hindari *ambiguous column*) dan alias |
| Blade | 10 | Tabel judul, nama penulis, email penulis, status, tanggal dibuat |
| Bonus search | 5 | Cari berdasarkan judul, nama penulis, atau email penulis; kondisi `OR` dibungkus *closure* sehingga menjadi `status = 1 AND (... OR ...)` dan artikel Draft tidak ikut lolos |

**Bukti INNER JOIN** — seeder sengaja membuat 3 artikel **Published tanpa penulis** (`user_id = NULL`):

| Data | Jumlah |
|---|---|
| Semua artikel Published | 19 |
| Hasil INNER JOIN + `status = 1` (tampil di halaman) | **16** |
| Pembanding LEFT JOIN + `status = 1` | 19 |

Selisih 3 = artikel tanpa penulis yang disaring oleh INNER JOIN. *(Jumlah Published bisa berbeda karena status artikel
lain diacak oleh factory, tetapi selisihnya selalu 3.)*

---

## 6. Struktur Database

```
users                                   articles
─────────────────────                   ─────────────────────────────────────────
id            PK                 1 ──── N  id          PK
name                                       user_id     FK → users.id, NULLABLE, ON DELETE SET NULL
email         UNIQUE                       title
password      (bcrypt)                     slug        UNIQUE  (dipakai di URL)
remember_token                             content     LONGTEXT
timestamps                                 image       NULLABLE (path, mis. articles/abc.jpg)
                                           status      TINYINT  0 = Draft, 1 = Published
password_reset_tokens                      timestamps
─────────────────────                      INDEX (status, created_at)
email  PK · token (ter-hash) · created_at
```

Tabel bawaan Laravel lainnya: `sessions`, `cache`, `jobs`.

- **Relasi:** satu user memiliki banyak artikel (`User::articles()` → `hasMany`, `Article::user()` → `belongsTo`).
- **`user_id` nullable + `nullOnDelete`:** jika user dihapus, artikelnya tetap ada tetapi tanpa penulis — kondisi
  inilah yang disaring oleh INNER JOIN pada Soal 2.
- **Index (`status`, `created_at`):** mempercepat query yang paling sering dijalankan, yaitu
  `WHERE status = 1 ORDER BY created_at DESC`.

---

## 7. Daftar Route

| Method | URL | Nama | Middleware | Keterangan |
|---|---|---|---|---|
| GET | `/` | `home` | — | Daftar artikel Published |
| GET | `/artikel/{slug}` | `public.articles.show` | — | Detail artikel Published |
| GET/POST | `/register` | `register` | `guest` | Daftar akun |
| GET/POST | `/login` | `login` | `guest` (+ `throttle` saat POST) | Masuk |
| GET/POST | `/forgot-password` | `password.request` / `password.email` | `guest` | Minta link reset |
| GET | `/reset-password/{token}` | `password.reset` | `guest` | Form password baru |
| POST | `/reset-password` | `password.update` | `guest` | Simpan password baru |
| POST | `/logout` | `logout` | `auth` | Keluar |
| GET | `/dashboard` | `dashboard` | `auth` | Statistik & artikel terbaru |
| — | `/dashboard/articles/...` | `articles.*` | `auth` + Policy | CRUD artikel (resource) |
| GET | `/dashboard/laporan-artikel` | `articles.report` | `auth` | Soal 2 — INNER JOIN |

---

## 8. Keputusan Desain & Asumsi

- **Autentikasi dibuat manual** (tanpa Breeze/Jetstream) agar setiap alurnya dipahami dan bisa dijelaskan.
- **User hanya mengelola artikel miliknya sendiri.** Pemeriksaan dilakukan di dua lapis: middleware `auth`
  ("sudah login?") dan `ArticlePolicy` ("apakah ini artikel milikmu?").
- **Statistik dashboard per user**, bukan statistik seluruh situs.
- **Laporan Artikel (Soal 2) hanya untuk user yang login**, karena menampilkan email penulis (data pribadi).
  Laporan menampilkan artikel Published **semua penulis**, sesuai soal.
- **Slug dibuat otomatis dan tidak berubah saat judul diedit**, supaya link yang sudah dibagikan tidak rusak.
  Judul yang sama menghasilkan `judul`, `judul-2`, `judul-3`, dst.
- **Status disimpan sebagai angka** (`0` Draft, `1` Published) sesuai ketentuan Soal 2 (`status = 1`); di kode
  dipakai konstanta `Article::STATUS_DRAFT` / `STATUS_PUBLISHED` agar mudah dibaca.
- **Soal 2 memakai Query Builder (`DB::table()->join()`)**, bukan `Article::with('user')`. Eager loading `with()`
  menjalankan dua query terpisah (bukan JOIN) dan tetap menampilkan artikel tanpa penulis.
- **Mail memakai driver `log`** agar reset password bisa diuji tanpa server email.
- **Bahasa & zona waktu:** `APP_LOCALE=id` (pesan validasi, autentikasi, dan email reset dalam Bahasa Indonesia) dan
  `APP_TIMEZONE=Asia/Jakarta`.
- **Keamanan:** semua output Blade memakai `{{ }}` (auto-escape, cegah XSS), semua form memakai `@csrf`,
  mass assignment dibatasi `$fillable`, password & token reset disimpan ter-hash.

### Struktur kode utama

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/{Login,Register,ForgotPassword,ResetPassword}Controller.php
│   │   ├── DashboardController.php        statistik dashboard
│   │   ├── ArticleController.php          CRUD artikel milik sendiri
│   │   ├── PublicArticleController.php    halaman publik
│   │   └── ArticleReportController.php    Soal 2 (INNER JOIN)
│   └── Requests/ArticleRequest.php        validasi artikel
├── Models/{User,Article}.php
└── Policies/ArticlePolicy.php             otorisasi pemilik artikel
database/{migrations,factories,seeders}/
resources/views/
├── layouts/app.blade.php
├── partials/  components/  auth/  public/  errors/
├── dashboard.blade.php
└── articles/{index,_form,create,edit,show,report}.blade.php
routes/web.php
lang/id/  lang/id.json                     terjemahan Bahasa Indonesia
```

---

## 9. Automated Test

```bash
php artisan test
```

Test memakai database **SQLite in-memory** (diatur di `phpunit.xml`), sehingga database MySQL `artikel_app`
**tidak tersentuh**. Butuh ekstensi PHP `pdo_sqlite`. Jika muncul koneksi ke MySQL saat test, jalankan
`php artisan config:clear` terlebih dahulu (konfigurasi yang ter-cache mengabaikan pengaturan `phpunit.xml`).

| File | Jumlah | Yang diuji |
|---|---|---|
| `tests/Feature/AuthTest.php` | 11 | Register (+ validasi, password ter-hash), login benar/salah, throttle login (429), logout, kirim link reset, reset password + token tidak bisa dipakai ulang, token palsu ditolak, middleware `auth` & `guest` |
| `tests/Feature/ArticleAuthorizationTest.php` | 9 | Pemilik bisa lihat/edit/update/hapus; user lain **403** (lihat, edit, update, hapus); daftar hanya artikel sendiri; tamu diarahkan ke login; `user_id` tidak bisa disisipkan dari form; publik hanya melihat Published (Draft 404) dan tidak ada tombol kelola |
| `tests/Feature/ArticleReportTest.php` | 9 | Soal 2: INNER JOIN membuang artikel tanpa penulis, hanya `status = 1`, nama & email dari tabel `users`, urut `created_at` terbaru, 10 per halaman, search judul/nama/email, search tidak membocorkan Draft, kata kunci terbawa di link pagination, tamu ditolak |

Hasil: **29 test, 112 assertion, semua lulus.**

---

## 10. Ringkasan Perintah

```bash
git clone https://github.com/AliRodja/artikel-app.git
cd artikel-app
composer install
cp .env.example .env
php artisan key:generate
# buat database "artikel_app" di MySQL, lalu sesuaikan DB_* di .env
php artisan config:clear
php artisan db:show
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```

---

## 11. Troubleshooting

| Pesan error | Penyebab | Solusi |
|---|---|---|
| `SQLSTATE[HY000] [2002] Connection refused` | MySQL belum berjalan, atau host/port salah | Jalankan MySQL (`brew services start mysql` / Start di Laragon/XAMPP), cek `DB_HOST` & `DB_PORT` |
| `SQLSTATE[HY000] [1045] Access denied for user 'root'` | Username/password MySQL salah | Sesuaikan `DB_USERNAME` & `DB_PASSWORD` di `.env`, lalu `php artisan config:clear` |
| `SQLSTATE[HY000] [1049] Unknown database 'artikel_app'` | Database belum dibuat | Ulangi [Langkah 4](#langkah-4--jalankan-mysql-dan-buat-database) |
| `could not find driver` | Ekstensi `pdo_mysql` belum aktif | Aktifkan `extension=pdo_mysql` di `php.ini`, lalu buka ulang terminal |
| `No application encryption key has been specified` | `APP_KEY` di `.env` masih kosong | `php artisan key:generate` |
| `Vite manifest not found` | Aset front-end belum di-build | `npm install && npm run build` |
| Gambar artikel tidak muncul (404) | Symlink storage belum dibuat | `php artisan storage:link` |
| `419 Page Expired` saat submit form | Session kedaluwarsa / tabel `sessions` belum ada | Refresh halaman; pastikan `php artisan migrate` sudah dijalankan |
| Perubahan `.env` tidak berpengaruh | Konfigurasi masih ter-cache | `php artisan optimize:clear` |
