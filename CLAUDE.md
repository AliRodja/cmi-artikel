# CLAUDE.md — Artikel App (Ujian Masuk IT CMI)

## Konteks
Project ini adalah jawaban **ujian masuk IT CMI** milik Ali Imran Rodja (fresh graduate Teknik Informatika UNPAS).
Pihak CMI mengizinkan AI **sebagai asisten**: Claude memberi kode **dan menjelaskannya**, user mempelajari dan memahami
setiap baris. Jadi:
- Selalu jelaskan *kenapa* sebuah keputusan diambil, bukan hanya *apa* kodenya.
- Gunakan **Bahasa Indonesia** dalam penjelasan.
- Kerjakan bertahap; jangan menulis ulang file besar tanpa diminta. Tunjukkan bagian yang berubah.
- Siapkan user agar bisa menjawab pertanyaan teknis penilai (middleware vs policy, INNER JOIN, validasi, dll.).

## Soal & rubrik

### Soal 1 — Website artikel (Laravel)
Fitur wajib: Login, Register, Logout, Lupa password, Reset password · User (nama, email, password) · Dashboard untuk
user login · CRUD artikel · Halaman publik (pengunjung hanya lihat daftar & detail artikel **Published**, tidak bisa
tambah/edit/hapus) · Dashboard menampilkan jumlah artikel, jumlah Published, jumlah Draft, daftar artikel terbaru.

Ketentuan teknis: Laravel, MySQL, Eloquent ORM, Blade, Migration, Validation, Authentication, Middleware, Pagination,
Route Model Binding. **Tidak boleh CMS siap pakai.**

Rubrik (100): Login & Logout 10 · Register 10 · Lupa & Reset Password 10 · Dashboard 5 · CRUD Artikel 20 ·
Upload & Pengelolaan Gambar 10 · Validation Form 5 · Authorization/Middleware 10 · Halaman Publik 5 ·
Database & Migration 5 · Blade & UI 5 · Kerapihan Struktur & Code Quality 5.

### Soal 2 — INNER JOIN
Halaman daftar artikel: judul, nama penulis, email penulis, status, tanggal dibuat.
INNER JOIN `articles` × `users` · hanya artikel yang punya penulis · hanya `status = 1` · urut `articles.created_at`
terbaru · 10 per halaman · Query Builder/Eloquent. Bonus: search.
Rubrik (100): INNER JOIN 30 · Filter status 15 · Sorting 10 · Pagination 15 · Query Laravel 15 · Blade 10 · Bonus search 5.

## Environment
- MacBook Pro M1 (Apple Silicon), macOS.
- PHP + Composer + MySQL via **Homebrew** (`brew services start mysql`). Kredensial DB ada di `.env`
  (`DB_CONNECTION=mysql`, `DB_DATABASE=artikel_app`, user `root`).
- GUI DB: phpMyAdmin via `php -S localhost:8080 -t /opt/homebrew/share/phpmyadmin` (opsional).
- Node.js versi baru (sudah di-upgrade dari Node 20) untuk Vite.
- Mail: `MAIL_MAILER=log` → link reset password muncul di `storage/logs/laravel.log`.

## Stack & keputusan desain
- **Laravel (versi terbaru) + MySQL + Blade + Tailwind CSS v4 via Vite** (bukan Bootstrap — sudah diganti).
- **Autentikasi dibuat manual** (tanpa Breeze) agar setiap baris dipahami.
- Tabel `articles`: `id`, `user_id` (**nullable**, FK ke users, `nullOnDelete`), `title`, `slug` (unique),
  `content` (longText), `image` (nullable, path), `status` (tinyInteger: **0 = Draft, 1 = Published**), timestamps,
  index (`status`, `created_at`).
  - `user_id` nullable sengaja: agar INNER JOIN Soal 2 terbukti menyaring artikel tanpa penulis.
- Model `Article`: konstanta `STATUS_DRAFT`/`STATUS_PUBLISHED`, `$fillable`, `getRouteKeyName()` → `slug`,
  relasi `user()`, scope `published()` & `draft()`, `isPublished()`, slug otomatis unik di `booted()`
  (`generateUniqueSlug()`; slug **tidak** berubah saat judul diedit), accessor `image_url` & `excerpt`.
- Model `User`: relasi `articles()` (hasMany). Password otomatis di-hash oleh cast `'password' => 'hashed'`.
- **Authorization (sejak 2026-10-28 ada peran):** `users.role` = `admin` | `penulis` (default penulis, TIDAK di
  `$fillable`; ubah via `php artisan user:role email admin`). `ArticlePolicy`: view → admin atau pemilik;
  update/delete → admin, atau pemilik **selama belum Published**; `review` → admin dan artikel berstatus Review.
  Middleware alias `admin` (`EnsureUserIsAdmin`, didaftarkan di `bootstrap/app.php`) menjaga `review.*` dan
  `articles.report`. Di controller pakai `Gate::authorize()` (base Controller Laravel 11+ tidak punya
  `$this->authorize()`). Status yang boleh dipilih: `Article::allowedStatusesFor($user, $article)` — dipakai validasi
  `ArticleRequest` DAN form `_form` (penulis: Draft/Review; admin: Draft/Published, + Review bila artikel sedang Review).
- Status artikel: `0` Draft, `1` Published, `2` Menunggu Review (`STATUS_REVIEW`, scope `inReview()`). Admin
  menerbitkan / mengembalikan lewat `ArticleReviewController` (`review.publish`, `review.return` + `review_note`
  wajib). `review_note` dikosongkan saat status bukan Draft.
- **Statistik dashboard per user** (milik user yang login) + kartu Menunggu Review; admin juga melihat jumlah antrean.
  Angka antrean di navbar dari View Composer di `AppServiceProvider`.
- Halaman Soal 2 berada **di area login, khusus admin** karena menampilkan email semua penulis.
- Gambar disimpan di disk `public` folder `articles/`; file lama dihapus saat diganti / checkbox `remove_image` /
  artikel dihapus. Butuh `php artisan storage:link`.
- Validasi artikel lewat **Form Request** `ArticleRequest` (dipakai store & update), pesan & atribut Bahasa Indonesia.
- Seeder: akun demo `admin@example.com` / `password` (role admin, 20 artikel), `penulis@example.com` / `password`
  (3 Published, 2 Review, 1 Draft dengan catatan admin), 10 artikel milik user lain, **3 artikel tanpa
  penulis** (`user_id = null`, **sengaja Published**) untuk uji INNER JOIN — kalau Draft, artikel itu sudah tersaring
  oleh `status = 1` dan tidak membuktikan JOIN. Hasil uji: 19 Published → 16 setelah INNER JOIN.
- Timezone: `config/app.php` membaca `env('APP_TIMEZONE', 'UTC')` (default Laravel menulis mati `'UTC'`),
  `.env` → `APP_TIMEZONE=Asia/Jakarta`.
- Bahasa: `APP_LOCALE=id`. `lang/id/{auth,passwords,validation}.php` untuk pesan berkunci; `lang/id.json` menerjemahkan
  kalimat Inggris bawaan framework (isi email reset password & template email) — tanpa notifikasi custom.
- Halaman error custom: `resources/views/errors/403.blade.php` (status tetap 403).

## Struktur file penting
```
routes/web.php
app/Models/{User,Article}.php
app/Policies/ArticlePolicy.php
app/Http/Requests/ArticleRequest.php
app/Http/Controllers/
  Auth/{LoginController,RegisterController,ForgotPasswordController,ResetPasswordController}.php
  DashboardController.php         (invokable)
  ArticleController.php           (resource, CRUD milik sendiri, search + filter status, paginate 10)
  PublicArticleController.php     (index published + search, paginate 9, eager load user; show → 404 jika draft)
  ArticleReportController.php     (Soal 2: DB::table + join + where status=1 + search dibungkus where(closure)
                                   + orderByDesc + paginate(10)->withQueryString())
database/migrations/*_create_articles_table.php
database/factories/ArticleFactory.php
database/seeders/DatabaseSeeder.php
resources/css/app.css             (Tailwind v4: @import, @source, @layer components: .card .btn .btn-sm .btn-primary
                                   .btn-secondary .btn-danger .form-label .form-input .form-input-error .form-error
                                   .badge .link .drawer-link .drawer-link-active .drawer-heading .th .td .article-content)
resources/views/
  layouts/app.blade.php           (@vite, @yield content, @stack scripts)
  partials/{navbar,nav-drawer,search-panel,flash,footer}.blade.php
  components/{input,status-badge,auth-card}.blade.php
  auth/{login,register,forgot-password,reset-password}.blade.php
  dashboard.blade.php
  articles/{index,_form,create,edit,show,report}.blade.php
  public/{index,show}.blade.php
  errors/403.blade.php
lang/id/{auth,passwords,validation}.php, lang/id.json
```

## Routes
- Publik: `GET /` (`home`), `GET /artikel/{article}` (`public.articles.show`).
- `guest`: `/register`, `/login`, `/forgot-password` (`password.request`, `password.email`),
  `/reset-password/{token}` (`password.reset` — nama wajib persis), `POST /reset-password` (`password.update`).
- `auth`: `POST /logout` (`logout`), `GET /dashboard` (`dashboard`),
  prefix `dashboard`: `Route::resource('articles', ...)` (`articles.*`), `GET laporan-artikel` (`articles.report`).

## Konvensi
- Blade: selalu `{{ }}` (escape). Jangan pakai `{!! !!}` — **satu-satunya pengecualian**:
  `{!! $article->content_html !!}` (HTML isi artikel yang sudah disanitasi `App\Support\ArticleContent`), dengan class
  `.article-content`. Jangan pernah `{!! $article->content !!}` atau `{!! !!}` untuk data lain.
- Tailwind: **jangan merangkai nama class dinamis** (mis. `text-{{ $color }}-600`); tulis class lengkap.
- Pagination memakai view Tailwind bawaan Laravel (jangan aktifkan `Paginator::useBootstrapFive()`).
- Form upload wajib `enctype="multipart/form-data"`; form PUT/DELETE pakai `@csrf` + `@method`.
- `user_id` artikel diisi lewat relasi `$request->user()->articles()->create()`, tidak pernah dari input form.

## Menjalankan
```bash
npm run dev                 # terminal 1 (biarkan berjalan)
php artisan optimize:clear  # terminal 2
php artisan serve           # http://localhost:8000
```

> ⚠️ **Sejak 2026-10-28 database MySQL lokal berisi DATA REAL** (diinput user lewat halaman). **JANGAN** menjalankan
> `migrate:fresh`, `migrate:refresh`, `migrate:reset`, `db:wipe`, atau `db:seed` pada MySQL. Untuk uji/screenshot pakai
> SQLite sementara: `DB_CONNECTION=sqlite DB_DATABASE=/path/tmp.sqlite php artisan migrate --seed --force` lalu
> `php artisan serve` dengan env yang sama (env shell mengalahkan `.env`). Seeder tetap untuk penilai (instalasi baru).
> Backup sebelum pembersihan data dummy: `storage/app/backups/` (gitignored).

## Progres
- [x] Tahap 1 — Database (migration, model, factory, seeder)
- [x] Tahap 2 — Route & Controller (+ Form Request, Policy)
- [x] Tahap 3 — Blade & UI (Tailwind) — **semua 12 checklist lolos** (diuji 2026-10-19 di browser + curl/tinker)
- [ ] Tahap 4 — Testing & finishing — **sedang berjalan**

Catatan: `ArticleRequest`, `ArticlePolicy`, `ArticleController`, view `articles/*` dan `ArticleReportController`
sempat belum ada di repo walau ditandai selesai; dibuat ulang 2026-10-19 (commit 510bc6f, 2a16b48, bfd8176).
Jika dev server Vite tidak mengenali class Tailwind di file/folder Blade baru, `touch resources/css/app.css`.

### Checklist uji Tahap 3 (semua ✅)
1. Beranda: grid artikel Published, search, pagination
2. Detail artikel publik tanpa tombol edit/hapus
3. Login `admin@example.com` / `password` → dashboard + 3 statistik
4. Tulis artikel dengan gambar (preview muncul)
5. Edit: ganti gambar & centang hapus gambar
6. Submit form kosong → error validasi tampil
7. Hapus artikel (dengan konfirmasi)
8. Laporan Artikel: hanya Published, urut terbaru, 10/halaman, search, 3 artikel tanpa penulis tidak muncul
9. Lupa password → pesan status
10. Link reset dari `storage/logs/laravel.log` → set password baru → login
11. User lain membuka `/dashboard/articles/{slug-milik-admin}/edit` → **403**
12. Belum login membuka `/dashboard` → redirect ke login

### Tahap 4
- [x] Jalankan & perbaiki semua checklist di atas.
- [x] Perbaikan kecil: timezone WIB, halaman 403 Bahasa Indonesia, email reset Bahasa Indonesia.
- [x] Cocokkan fitur dengan rubrik Soal 1 & Soal 2 satu per satu (+ uji register & ganti gambar) → README bagian 5.
- [x] README untuk penilai: fitur vs rubrik, bukti INNER JOIN, struktur database, route, keputusan desain.
- [x] Feature test PHPUnit (`php artisan test`, SQLite in-memory dari `phpunit.xml`, MySQL aman): `AuthTest` (11),
  `ArticleAuthorizationTest` (9), `ArticleReportTest` (9) → 29 test / 112 assertion lulus. Sudah dibuktikan bisa gagal
  dengan sabotase sementara (policy selalu true, `leftJoin`, search tanpa closure). `TestCase` memanggil
  `withoutVite()`; `ArticleFactory` punya state `published()` / `draft()`.
- `npm run build` sebelum dikumpulkan.
- **README.md** untuk penilai: requirement (PHP, Composer, MySQL, Node), langkah instalasi
  (`composer install`, `cp .env.example .env`, `php artisan key:generate`, set DB, `php artisan migrate --seed`,
  `php artisan storage:link`, `npm install && npm run build`, `php artisan serve`), akun demo, cara uji reset password
  via log, daftar fitur vs rubrik, keputusan desain & asumsi.
### Tahap 5 — Pengembangan front-end (mulai 2026-10-28, data real diinput user sendiri lewat halaman)
- Arah desain: **corporate / company profile**. Token di `app.css` `@theme`: palet `brand-50..950` dari warna resmi
  CMI (navbar `#E3F2FD` = `brand-50`, footer `#066AC9` = `brand-600`) + font dari **Google Fonts API** lewat provider
  `google()` laravel-vite-plugin (diunduh saat build, disajikan dari server sendiri; `@fonts` di layout): Inter = `font-sans`
  (UI & judul; sebelumnya Plus Jakarta Sans), Lora = `font-serif` (paragraf `.article-content`, subjudul di dalamnya tetap sans). Logo: `public/images/logo-cmi.png` (sudah di-crop 384×160, latar transparan; file asli 1080×1080 di `logo-cmi-square.png`) via komponen `x-logo` (fallback inisial).
- Git di-init ulang 2026-10-28 (branch `main`); user sendiri yang commit & push ke GitHub.
- [x] Halaman publik: layout (`@yield('hero')`, `partials/footer`), navbar, beranda (hero + featured di halaman 1 tanpa
  search + grid), detail (hero, waktu baca, salin tautan, Artikel Lainnya). Komponen baru `x-avatar` (prop `tone`),
  `x-article-card`. Accessor `reading_time`. Pagination Bahasa Indonesia (`lang/id/pagination.php`, `id.json`).
- [x] Rich text editor **Trix** (npm `trix`, entry Vite terpisah `resources/js/editor.js`, hanya dimuat di `_form`;
  lampiran file dimatikan). `<trix-editor class="article-editor article-content">` + `<input type="hidden" name="content">`.
  CSS editor sengaja di luar `@layer` (trix.css tidak berlayer); jangan beri class `.trix-content`.
- [x] Sanitasi: `symfony/html-sanitizer` lewat `App\Support\ArticleContent` (whitelist tag Trix: div p br strong em del
  h1 blockquote pre ul ol li a[href]; link http/https/mailto, dipaksa `target=_blank rel=noopener noreferrer`).
  Dua lapis: mutator `setContentAttribute` (saat simpan) + accessor `content_html` (saat tampil). Teks polos lama →
  `<p>`. `excerpt`/`reading_time` memakai `ArticleContent::toPlainText()`. `ArticleRequest`: `bail` + closure menolak
  editor kosong (`<div><br></div>`). Test: `ArticleContentTest` (6) → total 35 test / 130 assertion, terbukti gagal
  bila sanitizer dimatikan.
- [x] Favicon dari tanda salib logo: `public/favicon.ico` (16/32/48) + `public/apple-touch-icon.png`.
- [x] Nama situs `APP_NAME="Klinik Utama CMI"` (.env & .env.example); copy hero/footer bertema klinik.
- [x] Database dibersihkan dari data dummy (akun user `rodjaali@gmail.com` dipertahankan), backup di
  `storage/app/backups/`.
- [x] Area penulis: komponen `x-page-header` (di `@section('hero')`, slot `meta`/`actions`), `x-icon` (kini Font Awesome,
  prop `size`), `x-article-thumb`. Dashboard (kartu statistik + progres published + empty state + aksi cepat),
  Artikel Saya (tab status + jumlah dari `$statusCounts` groupBy, aksi ikon), form 2 kolom (`_form` menerima
  `$submitLabel`/`$cancelUrl`; status = kartu radio; dropzone gambar), detail milik sendiri (banner Draft), laporan &
  auth-card memakai header/logo baru. `status-badge` Draft = amber. Hapus query mati `$latestArticles` di LoginController.
- Pelajaran CSS: selector toolbar Trix diberi awalan `form` (trix.css dimuat setelah app.css); kolom grid perlu `min-w-0`.
  Pembungkus tabel `overflow-x-auto` wajib `relative`, kalau tidak teks `sr-only` (position:absolute) lolos dan
  membuat halaman melebar di ponsel.
- [x] Navigasi: navbar grid `[1fr_auto_1fr]` → kiri burger + ikon cari, tengah logo (`<x-logo compact>`: nama situs
  disembunyikan di ponsel), kanan tamu: Masuk/Daftar (Daftar disembunyikan di ponsel, ada di drawer) | login: avatar.
  Ikon cari membuka `partials/search-panel` (`<dialog>` dari atas, GET ke `home?search=`, input `autofocus`).
  JS dialog di `app.js` dipakai bersama: `data-dialog-open="<id>"` / `data-dialog-close`, Esc ditangani manual
  (Esc pertama di `type=search` bawaan browser hanya mengosongkan teks). Semua menu di drawer kiri
  `partials/nav-drawer` (di-@include dari navbar, jadi mewarisi `$pendingReviewCount` dari View Composer). Drawer =
  `<dialog>` + `showModal()` (Esc, fokus terkunci, latar inert bawaan browser); JS buka/tutup + klik latar di
  `resources/js/app.js`; animasi geser di app.css (`@starting-style` + `allow-discrete`). Tombol Keluar ada di drawer.
- [x] Ikon **Font Awesome Free 7** (npm `@fortawesome/fontawesome-free`, hanya css solid + regular diimpor di
  `resources/js/app.js`). Semua ikon lewat `<x-icon name=".." size="size-N">`; peta nama → class `fa-*` dan
  `size-*` → font-size ada di `components/icon.blade.php` (CSS FA tidak berlayer, menimpa `width` utilitas Tailwind,
  jadi ukuran ikon = font-size). Jangan menulis class `fa-*` langsung di view lain. Mengganti teks tombol yang berisi
  ikon: ubah `<span>` labelnya saja (lihat tombol "Salin tautan").
- [x] Peran admin/penulis + alur review (2026-10-28): migration `add_role_to_users_table` &
  `add_review_note_to_articles_table` (sudah dijalankan di MySQL, backup `storage/app/backups/artikel_app-sebelum-role-*.sql`;
  akun `rodjaali@gmail.com` = admin). Halaman `review/index` (tab Menunggu Review/Published/Draft/Semua), panel
  Tinjau di `articles/show`, komponen `x-review-note`, `status-badge` violet untuk Review. `ArticleRoleTest` (11) →
  total 46 test / 175 assertion, terbukti gagal bila middleware/policy/validasi status disabotase.

- Persiapan tanya-jawab: middleware vs policy, alur reset password, INNER JOIN vs LEFT JOIN, jebakan
  `orWhere` tanpa closure, N+1 & eager loading, mass assignment & `$fillable`, CSRF, route model binding dengan slug.