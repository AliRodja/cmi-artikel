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
- **Authorization (sejak 2026-10-28 ada peran):** `users.role` = `admin` | `penulis` | `pembaca` (default
  **pembaca** = hasil Daftar, TIDAK di `$fillable`; ubah lewat Kelola Pengguna atau `php artisan user:role email penulis`; `UserFactory`
  default penulis + state `admin()`/`pembaca()`). `User::canWrite()` = admin|penulis → middleware alias `writer`
  (`EnsureUserCanWrite`) menjaga `/dashboard` & `articles.*`; pembaca diarahkan ke beranda setelah login/register
  (`redirectUsersTo` di `bootstrap/app.php`). `ArticlePolicy`: view → admin atau pemilik;
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
  partials/{navbar,nav-drawer,search-panel,user-menu,notification-menu,flash,footer}.blade.php
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
  `x-article-card`. (Accessor `reading_time` dihapus 2026-10-29 — user tidak mau "menit baca" di mana pun.) Pagination Bahasa Indonesia (`lang/id/pagination.php`, `id.json`).
- [x] Rich text editor **Trix** (npm `trix`, entry Vite terpisah `resources/js/editor.js`, hanya dimuat di `_form`;
  lampiran file dimatikan). `<trix-editor class="article-editor article-content">` + `<input type="hidden" name="content">`.
  CSS editor sengaja di luar `@layer` (trix.css tidak berlayer); jangan beri class `.trix-content`.
- [x] Sanitasi: `symfony/html-sanitizer` lewat `App\Support\ArticleContent` (whitelist tag Trix: div p br strong em del
  h1 blockquote pre ul ol li a[href]; link http/https/mailto, dipaksa `target=_blank rel=noopener noreferrer`).
  Dua lapis: mutator `setContentAttribute` (saat simpan) + accessor `content_html` (saat tampil). Teks polos lama →
  `<p>`. `excerpt`/`word_count` memakai `ArticleContent::toPlainText()`. `ArticleRequest`: `bail` + closure menolak
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
  disembunyikan di ponsel), kanan tamu: Masuk/Daftar (Daftar disembunyikan di ponsel; halaman Masuk punya link Daftar) |
  login: lonceng + tombol avatar saja (nama/peran hanya di header dropdown) → dropdown akun `partials/user-menu` (Popover API `popover`/`popovertarget`; klik luar & Esc
  bawaan browser; posisi top/right di-set JS saat `beforetoggle`, digeser bila keluar layar saat `toggle`). Isi:
  profil, **Kelola Akun** (`profile.edit`, akun sendiri, semua peran), Baca Nanti,
  Notifikasi, Keluar. Drawer kiri hanya
  navigasi (tanpa profil/Masuk/Daftar/Keluar).
  Redesign 2026-10-28: garis aksen `brand-600` di atas, tinggi `h-16`, tombol ikon bulat `.nav-icon-btn` (app.css),
  burger berlabel "Menu" (≥sm), ikon cari saja di semua ukuran (kolom "Cari artikel…" palsu dihapus atas permintaan user) + pintasan `/`, bayangan saat scroll
  (`data-scrolled` dari app.js).
  Ikon cari membuka `partials/search-panel` (`<dialog>` dari atas, GET ke `home?search=`, input `autofocus`).
  JS dialog di `app.js` dipakai bersama: `data-dialog-open="<id>"` / `data-dialog-close`, Esc ditangani manual
  (Esc pertama di `type=search` bawaan browser hanya mengosongkan teks). Semua menu di drawer kiri
  `partials/nav-drawer` (di-@include dari navbar, jadi mewarisi `$pendingReviewCount` dari View Composer). Drawer =
  `<dialog>` + `showModal()` (Esc, fokus terkunci, latar inert bawaan browser); JS buka/tutup + klik latar di
  `resources/js/app.js`; animasi geser di app.css (`@starting-style` + `allow-discrete`).
  Catatan uji E2E: klik CDP (`Input.dispatchMouseEvent`) di Chrome headless kadang hilang setelah login lewat
  `form.submit()` — terbukti bukan bug aplikasi (tetap hilang walau menu dimatikan); pakai `element.click()` bila begitu.
- [x] Peran **pembaca** + komentar + Baca Nanti + notifikasi (2026-10-28): migration ubah default role, tabel
  `comments` (cascade), `bookmarks` (pivot unik user×article), `notifications` (bawaan Laravel). `CommentController`
  (artikel harus Published; user_id via `associate`), `CommentPolicy` (pemilik/admin), `BookmarkController` (`toggle()`,
  daftar hanya Published), `NotificationController` (`open` mencari lewat `$request->user()->notifications()` → 404
  untuk milik orang lain). `App\Notifications\ArticleNotification` (abstract, channel database, data
  title/message/url relatif/icon) + `ArticleSubmittedForReview`, `ArticlePublished`, `ArticleReturned`,
  `NewArticlePublished`, `NewComment`. Penerima diatur `App\Support\ArticleNotifier`, dipanggil di ArticleController
  (store; update hanya bila `wasChanged('status')`), ArticleReviewController, CommentController. Navbar: lonceng
  `partials/notification-menu` (dropdown `.dropdown-panel`, data dari View Composer). Test: `ReaderFeaturesTest` (8),
  `NotificationTest` (9) → 63 test / 225 assertion, terbukti gagal bila middleware/policy/notifier disabotase.
  MySQL dimigrasi (backup `storage/app/backups/artikel_app-sebelum-pembaca-*.sql`).
- [x] **Redesign form tulis/edit artikel** (2026-10-29): `_form` = langkah bernomor (komponen `x-form-step`): ①
  Judul & jenis, ② Isi (penghitung kata `[data-word-count]`), ③ Sumber rujukan, ④ Optimasi SEO
  (`_seo-panel` kini memakai `x-form-step number=4`). Sidebar sticky & bisa di-scroll sendiri
  (`lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto`): Publikasi, **Kelengkapan artikel** (6 poin + bilah progres; wajib
  hanya judul/jenis/isi), Gambar Utama. JS `resources/js/article-form.js` (diimpor `editor.js`): penghitung kata,
  kelengkapan, pratinjau gambar (pindahan skrip inline), peringatan `beforeunload` bila ada perubahan belum disimpan
  (mati saat submit; Chrome hanya memunculkannya setelah interaksi pengguna sungguhan). Toolbar Trix sticky
  `top-[67px]` (tinggi navbar). Test di `PagesRedesignTest` → 132 test.
- [x] **Situs khusus artikel JANTUNG — fitur jenis/kategori DIHAPUS** (2026-10-29, revisi soal dari CMI). Migration
  `drop_category_from_articles_table`: artikel `category` ≠ jantung → status Draft (query builder), lalu drop index +
  kolom (MySQL dimigrasi; backup `artikel_app-sebelum-hapus-kategori-*.sql`; artikel real "Risiko Operasi Kanker…" kini
  Draft atas pilihan user). Dihapus: `Article::CATEGORIES`/`category_label`/`scopeOfCategory`/
  `publishedCountsByCategory`, aturan `category`, komponen `x-category-badge`, filter `?jenis=` (beranda, canonical,
  sitemap, search-panel), sidebar "Jenis Artikel", "Berita Utama per jenis". Beranda: Sorotan + Artikel Lainnya (hanya
  landing), lalu grid `lg:grid-cols-3` = daftar artikel 2 kolom (paginate **10**) | Terpopuler (selalu) +
  `x-consultation-card` (judul bawaan kini "Alami gejala jantung?"); banner WA hasil cari dihapus. Artikel Terkait =
  `PublicArticleController::relatedArticles()`: cocok `focus_keyword` (scope `search`) dulu, sisanya `inRandomOrder` di
  luar sidebar. SEO: judul beranda "Artikel Kesehatan Jantung", `config/seo.php` description jantung, breadcrumb
  Beranda › judul, JSON-LD `specialty` = `https://schema.org/Cardiovascular`, `articleSection` "Kesehatan Jantung".
  Teks: hero, footer, menu "Artikel Jantung", placeholder contoh jantung. Form: langkah ① "Judul artikel", kelengkapan
  5 poin (form) / 6 poin (detail admin). Test `ArticleCategoryTest` → `HeartOnlySiteTest` (4, termasuk uji migration
  down/up). Entri lama di bawah yang menyebut jenis/kategori sudah tidak berlaku.
- [x] **Redesign detail artikel area login** (`articles/show`, 2026-10-29): banner status di atas grid; grid
  `lg:grid-cols-[minmax(0,1fr)_21rem]` — aside ditulis lebih dulu di DOM (ponsel: panel Tinjau sebelum isi panjang),
  pratinjau `lg:order-first`. Aside: panel **Tinjau** (Terbitkan + peninjau medis / "atau" / Kembalikan + catatan),
  Informasi artikel (penulis+profesi, peninjau medis, dibuat/diperbarui, jumlah kata; waktu baca sengaja tidak ditampilkan atas permintaan user), **Kelengkapan** 7
  poin dihitung server (aturan = form tulis + peninjau medis), Statistik Kunjungan (total/7 hari/komentar + 5 terbaru),
  Tampilan di Google (`meta_title`/`meta_description` + kata kunci). Pratinjau = kartu seperti halaman publik; blok
  peninjau medis + sumber dipisah ke partial `public/_credibility` (dipakai publik & pratinjau). Accessor baru
  `Article::word_count`. Controller `show` eager load `user`, `loadCount('comments')`.
- [x] **Redesign Laporan Artikel** (2026-10-29; query INNER JOIN TIDAK diubah, hanya `articles.category` ditambah ke
  select): ringkasan 3 angka dalam satu kartu (Artikel terbit / Tampil di laporan / Tanpa penulis — **tanpa istilah
  teknis**: pengguna halaman ini nakes & editor konten, bukan developer; penjelasan INNER JOIN untuk penilai cukup di
  README), bilah cari di atas tabel + "Menampilkan 1–10 dari N", kata kunci disorot `x-highlight`, jenis (komponen
  `x-category-badge` kini juga menerima prop `category` karena baris `DB::table` bukan model), avatar penulis, email
  `mailto:`, tanggal + jam zona (`format('T')` → WIB), `aria-sort` di kolom tanggal. Ponsel: kartu per artikel (tabel
  `hidden md:block`). Tombol **Cetak** (`window.print()`): `@media print` di app.css menyembunyikan navbar/footer/toast
  & merapatkan `.th/.td`; elemen interaktif `print:hidden`, tabel `print:block`, header cetak berisi waktu cetak.
  Test di `PagesRedesignTest` → 133 test.
- [x] **Optimasi SEO per artikel** (2026-10-29): migration `add_seo_fields_to_articles_table` (`focus_keyword` 100,
  `seo_title` 70, `seo_description` 160, nullable; MySQL dimigrasi, backup `artikel_app-sebelum-seo-artikel-*.sql`),
  fillable + validasi `ArticleRequest`. Accessor `meta_title` (seo_title ?: title) & `meta_description`
  (seo_description ?: plain text 155) dipakai `public/show` (title/description) & `Seo::article()` (+ `keywords`).
  Partial `articles/_seo-panel` (di bawah kartu isi di `_form`; kolom kiri kini dibungkus `space-y-6`): pratinjau Google,
  penghitung karakter, checklist 7 poin; JS `resources/js/seo-panel.js` (diimpor `editor.js`, slugify menyerupai
  Str::slug, slug tetap saat edit). Tidak memakai `<meta keywords>` (diabaikan Google) — sampaikan ke user bahwa
  tidak ada jaminan peringkat. Test `ArticleSeoFieldsTest` (4) → 131 test / 523 assertion.
- [x] **Redesign halaman sisa** (2026-10-29): Baca Nanti → daftar (thumbnail, jenis, judul serif, ringkasan,
  "Disimpan <x-date pivot>", tombol **Baca** & **Hapus** = form `bookmarks.toggle`), paginate 10. Notifikasi → tab
  Semua/Belum dibaca (`?filter=belum-dibaca`, controller kirim `onlyUnread/totalCount/unreadCount`), dikelompokkan
  Hari ini/Kemarin/tanggal; `x-notification-item`: ikon berwarna per jenis, `x-date`, badge "Baru". Error: komponen
  `x-error-page`; `errors/{403,404,419,429}` pakai layouts.app + `@section('robots','noindex, follow')` (partials/seo kini
  mendukung override `robots`; 404 artikel Draft tidak lagi "index"); 404 menyarankan 3 artikel terbaru (try/catch);
  `errors/{500,503}` pakai `layouts/minimal` (tanpa navbar/DB). Dashboard: kartu statistik = link ke Artikel Saya
  terfilter, kartu "Total dibaca" (`totalViews`), "Paling banyak dibaca" (`topArticles`), tips + sumber/peninjau.
  Artikel Saya, Review, Kelola Pengguna: tombol aksi berlabel (Lihat/Edit/Hapus/Tinjau), jenis di bawah judul, profesi
  user. Laporan (query INNER JOIN TIDAK diubah): ringkasan Published seluruhnya / tampil / tersaring INNER JOIN
  (`publishedTotal`, `withoutAuthor`), tanggal `translatedFormat`, pembungkus tabel `relative`. Test
  `PagesRedesignTest` (7) → 127 test / 505 assertion.
- [x] **SEO** (2026-10-28, sebelum deploy): `config/seo.php` (description bawaan, `image` = `images/og-default.png`
  1200×630 dibuat GD dari logo, latar brand-950). `App\Support\Seo`: `isIndexable()` (hanya `home` tanpa ?search &
  `public.articles.show`), `canonicalUrl()` (beranda: hanya ?jenis valid & ?page>1; lainnya `$request->url()`),
  `organization()` (MedicalClinic + WebSite/SearchAction), `article()` (MedicalWebPage reviewedBy/lastReviewed +
  Article author/jobTitle + BreadcrumbList), `JSON_FLAGS` (wajib `JSON_HEX_TAG`). `partials/seo` di layout
  (menggantikan `<title>`): title, description, robots, canonical, theme-color, og:*, twitter:card, `@stack('seo-meta')`,
  `@stack('structured-data')`. Halaman mengisi `@section('description'|'og_type'|'og_image')`. `SeoController`
  `/sitemap.xml` (XMLWriter, lazy) & `/robots.txt` (dinamis; non-production `Disallow: /`) — `public/robots.txt`
  dihapus (file statis menang atas route). `config/clinic.php` kini juga `whatsapp_number`. Test `SeoTest` (7) →
  120 test / 477 assertion. README punya "Checklist SEO saat deploy".
- [x] **Kredibilitas & aksesibilitas (rekomendasi no. 4 & 5)** (2026-10-28). Migration `add_profession_to_users_table`
  (`users.profession` string 100, fillable) & `add_medical_review_and_sources_to_articles_table`
  (`medical_reviewer_id` FK users nullOnDelete, `sources` text) — MySQL dimigrasi, backup
  `artikel_app-sebelum-kredibilitas-*.sql`. `Article::medicalReviewer()`, accessor `source_list`; `User::scopeWriters()`.
  `ArticleRequest`: `sources` nullable max 5000; `medical_reviewer_id` admin → exists users role admin/penulis,
  selain admin → `prohibited`. `ArticleReviewController@publish` menerima `medical_reviewer_id` opsional. Partial
  `articles/_reviewer-select` (form admin & panel Terbitkan; `$reviewers` dari `ArticleController::reviewersFor()`).
  Publik: profesi di bawah nama penulis, chip "Ditinjau oleh" di hero, kotak "Ditinjau secara medis oleh … · Terakhir
  diperbarui" + "Sumber rujukan" (`x-source-line`: hanya http/https jadi link, tampil host) di akhir artikel.
  Footer 3 kolom: "Hubungi Kami" dari `config/clinic.php` (`CLINIC_ADDRESS/HOURS/EMAIL/MAPS_URL/ACCREDITATION` di
  .env, kosong = disembunyikan) + WhatsApp CS `whatsapp_display` (0821-2159-0000, dari link resmi). **Data alamat,
  jam, email, akreditasi CMI belum diisi — tunggu dari user; jangan dikarang.** No. 4: `.nav-icon-btn` & `.share-btn`
  44px, tombol navbar "Cari" berlabel (sm+), `x-share-buttons labels` (dipakai di akhir artikel), pill jenis py-2.5,
  tombol tutup pop-up 40px. Test `CredibilityTest` (6) → 113 test / 442 assertion.
- [x] **Keterbacaan untuk pembaca usia matang** (2026-10-28; target user: 25+, mayoritas orang tua & mapan). Token
  `@theme`: `--text-xs` 14px, `--text-sm` 15px (dulu 12/14) → teks < 14px di beranda 52 → 0. `x-icon` size-3/size-4
  kini nilai tetap `text-[0.75rem]`/`text-[0.875rem]` agar ikon tidak ikut membesar. Semua `text-gray-400` →
  `text-gray-500` (2,5:1 → 4,8:1), placeholder & `.drawer-heading` juga. Hijau WhatsApp untuk tombol/ikon di latar
  terang `#0E7A3F` (hover `#0B6534`), kontras 5,4:1 (dulu #25D366 = 2,0:1); `#25D366` hanya di panel gelap auth.
  Angka peringkat terpopuler `text-brand-400` (3,3:1). Komponen `x-date` (`<time>`; < 24 jam relatif, selebihnya
  tanggal pasti; title tanggal-jam lengkap) dipakai di beranda & komentar. Pop-up: berhasil 8 dtk, info 10 dtk,
  gagal tanpa timer (hanya tombol ×). Test `ReadabilityTest` (3) → 107 test / 415 assertion. Catatan alat audit:
  warna latar `oklch()` & teks di atas gambar/gradien terbaca salah sebagai kontras rendah — cek manual.
- [x] **Redesign Lupa & Reset Password** (2026-10-28): panel `auth/_aside`. Lupa password: form email (ikon, hint) →
  setelah terkirim (`session('status')`) kartu berganti "Cek email Anda" (email dari `old('email')` — controller kini
  `withInput` juga saat sukses, masa berlaku dari `config('auth.passwords.users.expire')`, langkah, tombol Masuk &
  "Kirim ulang link"; throttle 60 dtk bawaan broker → error "Mohon tunggu…"). Reset: "Buat password baru", ikon,
  hint, `auth/_password-match` (indikator cocok kini generik di app.js `[data-password-match]`, dipakai juga Daftar).
  Catatan uji: `LOG_SINGLE_PATH` tidak dipakai config proyek; email reset uji masuk `storage/logs/laravel.log`.
- [x] **Redesign halaman Daftar** (2026-10-28): sama dengan Masuk; isi panel kiri kini partial `auth/_aside`
  ($heading, $text + kotak "Punya keluhan kesehatan?" WA) dipakai login & register. Form ikon user/envelope/lock,
  password & konfirmasi berdampingan (sm), `x-input` prop baru `hint` (teks bantuan, disembunyikan saat error via
  `@error … @else`), indikator "✓ Password cocok / belum sama" (JS kecil di `@push('scripts')`, validasi tetap
  `confirmed` di server), tombol Daftar + "Masuk", kembali ke beranda. Subjudul lama "mulai menulis artikel" diganti
  (pendaftar = pembaca).
- [x] **Redesign halaman Masuk** (2026-10-28): `x-auth-card` punya slot opsional `aside` (panel biru tua kiri, hanya
  lg; tanpa aside = kartu tengah seperti dulu → daftar/lupa/reset tidak berubah). Login (revisi user: panel kiri cukup
  ringkas, tanpa daftar manfaat): logo, "Selamat datang kembali" + 1 kalimat, kotak "Punya keluhan kesehatan?" + link WA; form dengan ikon, tombol Masuk & "Daftar akun baru", "Kembali ke beranda".
  `x-input` kini punya prop `icon` (ikon kiri, `pl-10`) dan untuk `type="password"` otomatis tombol mata
  (`data-password-toggle`, `aria-pressed`) + peringatan Caps Lock (`#{name}-capslock`, `getModifierState`) — JS di
  app.js, berlaku untuk semua kolom password. Ikon baru: envelope, lock, eye-slash, shield.
- [x] **Beranda ala portal berita (pola Kompas.id)** (2026-10-28, permintaan user; hero biru tua tetap, kontennya
  rata tengah — label, judul, deskripsi, form cari `mx-auto` — dengan cahaya radial di tengah atas). Hanya saat
  `$isLanding`: baris 1 dua kolom = **Sorotan** (kotak besar artikel terbaru, judul serif) | **Artikel Lainnya** (5
  berikutnya: jenis, judul serif, waktu relatif, thumbnail kanan); baris 2 = **Berita Utama** (per jenis: label jenis →
  judul utama serif + ringkasan + 3 judul terkait bergaris, gambar kanan; jenis kosong dilewati) | **Terpopuler**
  (5, bernomor serif) + `x-consultation-card` (sticky). Lalu `#daftar-artikel` ("Semua Artikel" + pill + grid +
  pagination). Saat cari/filter: hanya daftar + banner WhatsApp. Controller mengirim `$latestArticles` (6),
  `$categorySections` (4 per jenis), `$popularArticles` (5). Komponen baru `x-section-title` (garis aksen kiri
  brand-600) & `x-consultation-card` (dipakai juga di detail). Judul berita di beranda memakai `font-serif` (Lora).
- [x] **Live search navbar** (2026-10-28): `GET /cari?q=` (`articles.live-search`, `throttle:60,1`) →
  `PublicArticleController@liveSearch` (bukan AJAX → redirect `home?search=`; < 2 huruf → kosong; Published +
  scope baru `Article::search()` yang juga dipakai beranda; `orderByRaw('CASE WHEN title LIKE ? ...')` judul dulu;
  6 hasil + total) → view potongan `public/_live-search-results` (role=listbox/option). Komponen `x-highlight`
  (`preg_split` + `PREG_SPLIT_DELIM_CAPTURE`, `<mark>` dengan `{{ }}`; satu directive per baris — `@endif@endforeach`
  menempel tidak dikompilasi Blade). `partials/search-panel`: input `role=combobox`, `[data-live-search-idle]` (pill
  jenis), `[data-live-search-results]`. JS `resources/js/live-search.js` (diimpor app.js): debounce 250 ms,
  `AbortController`, header `X-Requested-With`, panah/Enter via `aria-activedescendant`. Test `LiveSearchTest` (6) →
  99 test / 376 assertion.
- [x] **Kelola Akun** (2026-10-28, koreksi user: "Kelola Pengguna" di menu avatar maksudnya akun milik user itu sendiri,
  bukan seluruh user). `ProfileController` (`/akun` GET/PATCH, `/akun/password` PUT; selalu `$request->user()`; role
  tidak bisa diubah; password baru wajib `current_password` + `different:current_password`). View `profile/edit`
  (form Data Akun + Ganti Password, kartu ringkasan: avatar `size="lg"` baru, peran, jumlah artikel/komentar/tersimpan).
  Menu avatar: Kelola Akun untuk semua; **Kelola Pengguna (UserController) pindah ke drawer kiri bagian Admin**.
  Test `ProfileTest` (5) → 93 test / 360 assertion.
- [x] **Pop-up berhasil/gagal** (2026-10-28): `partials/flash` kini toast (di-include langsung di `<body>` setelah navbar,
  bukan di `<main>`): `success`/`status`/`error` + pop-up gagal umum bila `$errors->any()`. Auto-hilang lewat
  `animationend` bar `.toast-timer` (CSS, berhenti saat hover/focus; durasi `[--toast-duration:5s]`), tombol × →
  `.toast-leaving`. Gagal keluar/simpan: `bootstrap/app.php` → `redirectGuestsTo` flash error untuk tamu di
  `bookmarks.toggle` (kembali ke artikel setelah login sudah otomatis: `redirect()->guest()` untuk POST memakai URL
  sebelumnya sebagai intended) + render `HttpException` 419 → `back()->withInput(...)->with('error', ...)` (pesan
  khusus untuk route `logout`). Tombol Simpan tamu kini form POST (bukan link). **Laravel 13**: middleware CSRF =
  `PreventRequestForgery`; request ber-header `Sec-Fetch-Site: same-origin` lolos tanpa cek token, jadi 419 praktis
  hanya terjadi tanpa header itu (HTTP non-localhost / browser lama) — diuji dengan curl. Test `PopupMessageTest` (6)
  memalsukan token gagal dengan `app()->instance(PreventRequestForgery::class, ...)` → 88 test / 335 assertion.
- [x] **Redesign beranda** (2026-10-28): hero biru tua tetap (tanpa pola garis). `PublicArticleController@index` kirim
  `$isLanding` (tanpa cari/filter & halaman 1), `$popularArticles` (hanya saat landing), `$categoryCounts`. Urutan:
  Sorotan (1 besar + 2 kartu kecil dengan thumbnail & jenis) → Artikel Terpopuler (komponen `x-popular-article`,
  dipakai juga di detail; ponsel hanya 3 teratas) → `#daftar-artikel` (judul dinamis Terbaru / Artikel {Jenis} /
  Hasil Pencarian + jumlah, pill filter jenis + jumlah, link pill berakhiran `#daftar-artikel`) → banner WhatsApp.
  Query bersama dipindah ke model: scope `popular()` & `Article::publishedCountsByCategory()`. User tidak suka hiasan
  yang "kelihatan seperti AI" (pola garis, glow berlebihan) — jaga desain tetap polos & korporat.
- [x] **Redesign detail artikel publik** (2026-10-28): hero **biru tua sama dengan beranda** (user minta tetap biru tua,
  bukan terang; `bg-brand-950` + radial gradient, tanpa pola garis (user: "kelihatan seperti AI"), breadcrumb chevron, baris penulis + tanggal/dilihat); latar konten sama dengan beranda (`bg-gray-50` dari layout); bar progres membaca `.reading-progress` (murni CSS
  `animation-timeline: scroll(root)`, disembunyikan via `@supports not`); grid `lg:grid-cols-[3rem_minmax(0,1fr)_18rem]`
  tanpa `items-start` (agar kolom kanan setinggi baris → kartu CTA `lg:sticky`); rel kiri = tombol Simpan
  (`public/_bookmark-button`, dipakai juga di kotak penulis) + `x-share-buttons` (ikon berwarna merek di tombol putih
  `.share-btn`; salin tautan generik via `data-copy-link`/`data-copy-icon`/`data-copy-done`, bisa >1 per halaman).
  Isi = satu kartu (gambar menempel di atas, `.article-lead` membesarkan `<p>` pertama saja — bukan `<div>`, karena
  Trix menyimpan satu blok berisi banyak baris), footer "Artikel ini bermanfaat? Bagikan", kotak "Ditulis oleh",
  komentar dalam kartu.
- [x] **Kunjungan, Terpopuler, Terkait** (2026-10-28): tabel `article_views` (article_id cascade, user_id nullOnDelete,
  `visitor` = `user:{id}` / `guest:{sha256 id sesi}`, `viewed_at`; model `ArticleView` CREATED_AT=viewed_at,
  UPDATED_AT=null) + kolom `articles.view_count` (tidak di `$fillable`). `App\Support\ArticleViewCounter::record()`
  dipanggil di `PublicArticleController@show`: skip penulis sendiri, skip visitor yang sudah tercatat, lalu dalam
  transaksi insert riwayat + `DB::table('articles')->increment()` (bukan increment model → updated_at tetap).
  Halaman publik: "N kali dilihat" di hero; di bawah grid 3 kolom ada **Artikel Terkait** (jenis sama, 3 kartu) dan
  **Artikel Terpopuler** (≥ `config('clinic.popular_min_views')` = 5, urut view_count, 6 item bernomor; empty state
  bila belum ada). Variabel sidebar kini `$latestArticles`. Area penulis: kolom "Dilihat" di Artikel Saya + panel
  Statistik Kunjungan di `articles/show` (total, 7 hari, 5 riwayat terbaru). Seeder membuat 0–40 riwayat acak per
  artikel Published. MySQL dimigrasi (backup `artikel_app-sebelum-kunjungan-*.sql`). Test `ArticleViewTest` (6) →
  81 test / 307 assertion; terbukti gagal saat dedup/cek penulis/hash sesi/increment/batas minimal disabotase.
- [x] **Jenis artikel + detail 3 kolom** (2026-10-28): migration `add_category_to_articles_table` (`category` string
  nullable + index; MySQL sudah dimigrasi, backup `storage/app/backups/artikel_app-sebelum-jenis-artikel-*.sql`).
  `Article::CATEGORIES` (diabetes/jantung/kanker/ginjal), `category_label` (null-safe, PHP 8.5 menolak offset null),
  scope `ofCategory`. `ArticleRequest`: `category` required + `Rule::in`. Form: radio pill di bawah Judul. Komponen
  `x-category-badge` (warna per jenis). Beranda: tab filter `?jenis=` (featured mati saat difilter). `public/show`:
  grid `lg:grid-cols-[4.5rem_minmax(0,1fr)_18rem]` → kolom Bagikan (sticky; WA/X/FB via `http_build_query`, salin
  tautan ikon-saja — ikon centang disembunyikan pakai atribut `hidden`, bukan class, karena CSS FA tak berlayer),
  kolom isi + komentar, sidebar (Artikel Lainnya 5, Jenis Artikel + jumlah Published, kartu CTA "Alami gejala …?"
  → `config('clinic.whatsapp_consultation_url')`). Font Awesome **brands** kini diimpor di `app.js` (ikon `whatsapp`,
  `x-twitter`, `facebook`). Test `ArticleCategoryTest` (4) → 75 test / 287 assertion.
- [x] **Kelola Pengguna** (2026-10-28, admin): `UserController` resource `users.*` tanpa show (`/dashboard/users`, grup
  middleware `admin`), `UserRequest` (store & update; email unik `ignore`; password wajib saat tambah, kosong = tidak
  diganti saat edit; `role` → `prohibited` bila `cannot('changeRole')`), `UserPolicy` (`changeRole`, `delete`: admin
  & bukan akun sendiri → selalu ada admin). `role` diisi langsung `$user->role = ...` (bukan mass assignment).
  Hapus akun: `DB::transaction` → hapus notifikasi manual (morph, tanpa FK) → `delete()` (artikel nullOnDelete,
  komentar & bookmark cascade). View `users/{index,_form,create,edit}`, komponen `x-role-badge` (admin brand, penulis
  emerald, pembaca abu). Konfirmasi hapus memakai `data-confirm` + `confirm(this.dataset.confirm)` agar nama bertanda
  kutip aman. Test `UserManagementTest` (8) → 71 test / 267 assertion; terbukti gagal bila policy/notifikasi disabotase.
  Catatan: scope Eloquent (`withRole`) otomatis mengurung where sebelumnya, closure search tetap dipakai agar eksplisit.
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