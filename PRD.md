# PRD — Klinik Utama CMI · Website Artikel Kesehatan Jantung

> Dokumen produk: apa yang dibangun, untuk siapa, tech stack, fitur & statusnya, test case, dan log progres.
> Aturan kerja untuk Claude ada di `CLAUDE.md`; panduan instalasi untuk penilai ada di `README.md`.
> **Terakhir diperbarui: 2026-10-31.**

## 1. Ringkasan

| | |
|---|---|
| Produk | Website artikel kesehatan **jantung** untuk Klinik Utama CMI (Bandung) |
| Asal | Jawaban **ujian masuk IT CMI** (Soal 1 website artikel Laravel, Soal 2 INNER JOIN) milik Ali Imran Rodja |
| Fase | Sejak 2026-10-28 pengembangan lanjutan dengan **data real** CMI (artikel diinput lewat halaman) |
| Revisi soal | 2026-10-29: situs dikhususkan untuk artikel jantung; fitur jenis/kategori penyakit dihapus |
| Status | Semua fitur wajib Soal 1 & 2 selesai + fitur lanjutan; **166 test / 843 assertion lulus** |

**Tujuan:** pasien dan masyarakat mendapat informasi kesehatan jantung yang tepercaya (penulis jelas, ditinjau tenaga
medis, ada sumber rujukan), mudah dibaca, dan mudah menghubungi klinik (WhatsApp, telepon).

## 2. Pengguna

| Pengguna | Kebutuhan | Implikasi desain |
|---|---|---|
| **Pengunjung / pembaca** — usia 25+, mayoritas orang tua & mapan | Membaca artikel, mencari topik, konsultasi | Huruf ≥ 14px, kontras ≥ 4,5:1, tanggal pasti, tombol berlabel & 44px, kredibilitas penulis tampil |
| **Penulis** — tenaga kesehatan | Menulis artikel, mengajukan review | Form bertahap dengan kelengkapan & pratinjau Google; tidak bisa menerbitkan sendiri |
| **Admin** — editor konten | Meninjau & menerbitkan, kelola pengguna, laporan | Antrean review, laporan bisa dicetak; UI **tanpa istilah teknis** (bukan developer) |
| **Penilai CMI** | Memeriksa rubrik | Penjelasan teknis (INNER JOIN, middleware, dll.) cukup di README & kode |

Desain: **korporat & polos** — hero biru tua, navbar `#E3F2FD`, footer `#066AC9`, tanpa hiasan "seperti AI".

## 3. Lingkup soal ujian & rubrik

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

Pemetaan rubrik → fitur & file ada di **README bagian 7**. Halaman Soal 2 = **Laporan Artikel** (`/dashboard/laporan-artikel`,
khusus admin karena menampilkan email semua penulis): `DB::table('articles')->join('users', …)` + `where status = 1` +
search dalam `where(closure)` + `orderByDesc('articles.created_at')` + `paginate(10)->withQueryString()`. Seeder
membuat 3 artikel Published **tanpa penulis** agar terbukti tersaring oleh INNER JOIN.

## 4. Tech stack

| Lapisan | Teknologi |
|---|---|
| Bahasa & framework | PHP 8.5 lokal (**minimal 8.4.1** — syarat Symfony 8 di `composer.lock`; `composer.json` `^8.4`), **Laravel 13** |
| Database | **MySQL** (Homebrew; lokal berisi data real) · SQLite in-memory untuk test |
| ORM & query | Eloquent (relasi, scope, accessor, policy) · Query Builder untuk Soal 2 |
| Tampilan | **Blade** (komponen `x-*`, partial) · **Tailwind CSS v4** lewat **Vite 8** + laravel-vite-plugin 3 |
| Huruf | Google Fonts via provider `google()` laravel-vite-plugin (diunduh saat build, disajikan sendiri): **Inter** (UI & judul), **Roboto** (huruf artikel, `font-article`) |
| Ikon | Font Awesome Free 7 (solid, regular, brands) lewat komponen `x-icon` |
| Editor artikel | **Trix** 2 (rich text, lampiran dimatikan) |
| Keamanan konten | `symfony/html-sanitizer` lewat `App\Support\ArticleContent` |
| Autentikasi | **Manual** (tanpa Breeze/Jetstream): session, password broker bawaan untuk reset |
| Notifikasi | Laravel Notifications, channel `database` |
| Email (lokal) | `MAIL_MAILER=log` → link reset di `storage/logs/laravel.log` |
| Bahasa tampilan | Indonesia (bawaan) & English untuk seluruh situs (publik, akun, penulis, admin) — `__()` dengan kunci teks Indonesia, terjemahan `lang/en.json`, middleware `SetLocale` |
| Test | PHPUnit (`php artisan test`), feature test |
| Lokal | MacBook Pro M1, Node.js 24 |

## 5. Peran & hak akses

Peran di `users.role` (default **pembaca** untuk hasil Daftar; diubah lewat Kelola Pengguna atau
`php artisan user:role email peran`).

| Kemampuan | Tamu | Pembaca | Penulis | Admin |
|---|:-:|:-:|:-:|:-:|
| Baca artikel Published, cari | ✅ | ✅ | ✅ | ✅ |
| Komentar, Baca Nanti, notifikasi, Kelola Akun | – | ✅ | ✅ | ✅ |
| Dashboard & tulis artikel | – | – | ✅ | ✅ |
| Lihat/ubah/hapus artikel | – | – | milik sendiri, selama belum Published | semua |
| Status yang boleh dipilih | – | – | Draft, Menunggu Review | Draft, Published (+ Review bila sedang Review) |
| Terbitkan / kembalikan dari antrean review | – | – | – | ✅ |
| Pilih peninjau medis | – | – | – | ✅ |
| Laporan Artikel (Soal 2), Kelola Pengguna | – | – | – | ✅ |
| Hapus komentar | – | milik sendiri | milik sendiri | semua |

Penjaga: middleware `auth`, `guest`, `writer` (admin|penulis), `admin`; policy `ArticlePolicy`, `CommentPolicy`,
`UserPolicy` (admin tidak bisa mengubah peran / menghapus akunnya sendiri → selalu ada admin).

## 6. Alur status artikel

```
Penulis: Draft (0) ──ajukan──▶ Menunggu Review (2) ──admin Terbitkan──▶ Published (1)
                ▲                        │
                └──admin Kembalikan + catatan wajib (review_note)
Admin: bisa langsung Draft ⇄ Published.
```
Setiap perpindahan mengirim notifikasi (diajukan → admin; terbit → penulis & pembaca; dikembalikan → penulis;
komentar baru → penulis). Penulis tidak bisa mengubah/menghapus artikel yang sudah Published.

## 7. Fitur & status

Semua fitur di bawah ✅ selesai dan teruji kecuali ditandai lain.

**Halaman publik**
- Beranda: hero biru tua + cari; Sorotan & Artikel Lainnya (halaman 1 tanpa cari); daftar artikel (10/halaman);
  Terpopuler (minimal 5 kunjungan); kartu konsultasi WhatsApp.
- Live search di navbar (hasil saat mengetik, kata kunci disorot, panah/Enter).
- Detail artikel: penulis + profesi, "Ditinjau oleh", tanggal, jumlah dilihat, bar progres membaca, bagikan (WA/X/FB/salin),
  Simpan, sumber rujukan, peninjau medis, komentar, Artikel Terkait (kata kunci fokus), Terpopuler. Draft → 404.
- Navbar tamu: Menu, Cari, logo, **pilihan bahasa (ID/EN)**, **Masuk** (tombol Daftar dihapus; daftar lewat halaman Masuk & footer).
- **Artikel dua bahasa**: penulis bisa menulis sendiri versi Bahasa Inggris (langkah ⑤ form, opsional). Pengunjung yang
  memilih English melihat versi itu; belum diterjemahkan → versi Indonesia + pemberitahuan. Pencarian mencakup teks Inggris.
- **Dua bahasa tampilan**: Indonesia & English untuk **seluruh situs** — halaman publik, Masuk/Daftar/Lupa password,
  akun (Baca Nanti, Notifikasi, Kelola Akun), area penulis & admin (dashboard, tulis artikel + panel SEO, detail,
  Review, Laporan, Kelola Pengguna), pop-up, pesan validasi, notifikasi, halaman error. Isi artikel tetap Indonesia.
- Footer: info kontak klinik, media sosial resmi, navigasi.
- SEO: meta & Open Graph, canonical, noindex untuk halaman privat/cari, JSON-LD (MedicalClinic, MedicalWebPage, Article,
  Breadcrumb), `/sitemap.xml`, `/robots.txt` dinamis.
- Halaman error Bahasa Indonesia: 403, 404 (menyarankan artikel), 419, 429, 500, 503.

**Akun**
- Daftar, Masuk (throttle 5/menit), Keluar, Lupa & Reset password (Bahasa Indonesia), Kelola Akun (data & ganti password).
- Tombol tampilkan password, peringatan Caps Lock, indikator password cocok.

**Penulis**
- Dashboard: statistik milik sendiri (total, Published, Draft, Menunggu Review, total dibaca), paling banyak dibaca,
  artikel terbaru.
- Artikel Saya: tab status, cari, aksi berlabel.
- Form tulis bertahap: judul, isi (Trix, penghitung kata), sumber rujukan, optimasi SEO (pratinjau Google, checklist);
  sidebar publikasi, kelengkapan (5 poin), gambar utama (pratinjau, ganti, hapus); peringatan perubahan belum disimpan.
- Detail artikel milik sendiri: pratinjau, informasi, kelengkapan (6 poin), statistik kunjungan, tampilan di Google.

**Admin**
- Antrean review (Terbitkan + pilih peninjau medis / Kembalikan + catatan).
- Laporan Artikel (Soal 2): ringkasan bahasa awam, cari, sorotan kata kunci, tampilan ponsel, Cetak.
- Kelola Pengguna: cari, filter peran, tambah/ubah/hapus.

**Sistem**
- Notifikasi (lonceng navbar + halaman Notifikasi, filter belum dibaca).
- Pencatat kunjungan unik per sesi/akun (penulis sendiri tidak dihitung).
- Pop-up berhasil/gagal (gagal tidak hilang sendiri); 419 → kembali ke halaman dengan pesan.
- Sanitasi HTML dua lapis (saat simpan & saat tampil).

## 8. Model data

| Tabel | Kolom utama | Catatan |
|---|---|---|
| `users` | name, email (unique), password (hashed), `role` (admin/penulis/pembaca, default pembaca), `profession` | `role` tidak di `$fillable` |
| `articles` | `user_id` (**nullable**, nullOnDelete), `medical_reviewer_id` (nullable, FK users), title, `slug` (unique), content (longText, HTML tersanitasi), `sources`, image, `status` (0 Draft · 1 Published · 2 Review), `review_note`, `focus_keyword`, `seo_title`, `seo_description`, `view_count`, versi Inggris opsional `title_en`, `content_en`, `seo_title_en`, `seo_description_en` | index (status, created_at); `user_id` nullable sengaja untuk bukti INNER JOIN |
| `comments` | article_id, user_id (cascade), body | index (article_id, created_at) |
| `bookmarks` | user_id, article_id (cascade) | unique (user_id, article_id) — Baca Nanti |
| `article_views` | article_id (cascade), user_id (nullOnDelete), `visitor` (`user:{id}` / `guest:{hash sesi}`), viewed_at | riwayat kunjungan unik |
| `notifications` | uuid, type, notifiable (morph), data, read_at | bawaan Laravel |
| bawaan | `password_reset_tokens`, `sessions`, `cache`, `jobs` | |

Seeder (instalasi baru / penilai; password semua akun `password`): `admin@example.com` (admin, 20 artikel acak),
`penulis@example.com` (3 Published dengan peninjau medis, 2 Menunggu Review, 1 Draft dengan catatan admin),
`pembaca@example.com` (komentar + Baca Nanti), 10 artikel milik user lain, **3 artikel tanpa penulis yang sengaja
Published** (kalau Draft, sudah tersaring oleh `status = 1` dan tidak membuktikan JOIN), riwayat kunjungan acak
0–40 per artikel Published. Jumlah Published acak, tapi Laporan Artikel selalu 3 lebih sedikit dari total Published.

## 9. Rute

- Publik: `GET /` (`home`), `GET /artikel/{article}` (`public.articles.show`), `GET /cari` (`articles.live-search`,
  throttle 60/menit), `POST /bahasa` (`locale.update`, throttle 30/menit), `GET /sitemap.xml` (`sitemap`),
  `GET /robots.txt` (`robots`), `Route::fallback` → 404 lewat grup `web` (sesi & bahasa ikut).
- `guest`: `/register`, `/login` (POST throttle 5/menit), `/forgot-password` (`password.request`, `password.email`),
  `/reset-password/{token}` (`password.reset` — nama wajib persis), `POST /reset-password` (`password.update`).
- `auth` (semua peran): `POST /logout`, `POST /artikel/{article}/komentar` (`comments.store`),
  `DELETE /komentar/{comment}` (`comments.destroy`), `/baca-nanti` (`bookmarks.index`),
  `POST /artikel/{article}/simpan` (`bookmarks.toggle`), `/notifikasi` (`notifications.index`, `.open`, `.read-all`),
  `/akun` (`profile.edit`, `profile.update`), `PUT /akun/password` (`profile.password`).
- `auth` + `writer` (admin/penulis): `GET /dashboard` (`dashboard`), prefix `dashboard`:
  `Route::resource('articles', ...)` (`articles.*`).
- `auth` + `writer` + `admin`: `laporan-artikel` (`articles.report`), `review` (`review.index`),
  `PATCH articles/{article}/publish|return` (`review.publish`, `review.return`), `Route::resource('users')`
  tanpa show (`users.*`).

## 10. Struktur kode
```
routes/web.php, routes/console.php   (console: perintah `user:role`)
bootstrap/app.php                 (alias middleware admin/writer, redirectUsersTo/redirectGuestsTo, render 419)
app/Models/{User,Article,Comment,ArticleView}.php
app/Policies/{ArticlePolicy,CommentPolicy,UserPolicy}.php
app/Http/Middleware/{EnsureUserCanWrite,EnsureUserIsAdmin,SetLocale,SecurityHeaders}.php
app/Http/Requests/{ArticleRequest,UserRequest}.php
app/Notifications/ArticleNotification.php (abstract) + ArticleSubmittedForReview, ArticlePublished, ArticleReturned,
  NewArticlePublished, NewComment
app/Support/{ArticleContent,ArticleNotifier,ArticleViewCounter,ImageUpload,Seo}.php
app/Providers/AppServiceProvider.php (View Composer: antrean review & notifikasi navbar)
app/Http/Controllers/
  Auth/{LoginController,RegisterController,ForgotPasswordController,ResetPasswordController}.php
  DashboardController.php         (invokable, statistik milik user yang login)
  ArticleController.php           (resource, CRUD + search + tab status, paginate 10)
  ArticleReviewController.php     (admin: antrean, publish, sendBack)
  PublicArticleController.php     (index Published + search, paginate 10; show → 404 jika bukan Published;
                                   liveSearch; relatedArticles())
  ArticleReportController.php     (Soal 2: DB::table + join + where status=1 + search dibungkus where(closure)
                                   + orderByDesc + paginate(10)->withQueryString())
  CommentController, BookmarkController, NotificationController, ProfileController, UserController, SeoController,
  LocaleController                (invokable, POST /bahasa → simpan pilihan bahasa di sesi)
config/{clinic,seo}.php           (data klinik & WhatsApp & media sosial; SEO bawaan)
database/migrations/              (articles + perubahan bertahap: role, review_note, comments, bookmarks,
                                   notifications, article_views, view_count, profession, peninjau medis & sumber,
                                   field SEO, drop category)
database/factories/{User,Article,Comment,ArticleView}Factory.php
database/seeders/DatabaseSeeder.php
resources/css/app.css             (Tailwind v4 @theme token brand + font, @layer components: .card .btn* .form-*
                                   .badge .link .drawer-* .th .td .article-content .nav-icon-btn .share-btn dll.;
                                   di luar layer: editor Trix, .toast, drawer, @media print)
resources/js/app.js               (dialog, drawer, popover, password toggle, salin tautan, toast) + live-search.js;
             editor.js            (Trix, hanya di _form) + article-form.js + seo-panel.js
resources/views/
  layouts/{app,minimal}.blade.php
  partials/{navbar,nav-drawer,search-panel,user-menu,notification-menu,locale-menu,flash,footer,seo}.blade.php
  components/  article-card article-thumb auth-card avatar consultation-card date error-page form-step highlight
               icon input logo notification-item page-header popular-article review-note role-badge section-title
               share-buttons source-line status-badge
  auth/{login,register,forgot-password,reset-password,_aside,_password-match}.blade.php
  dashboard.blade.php
  articles/{index,_form,_seo-panel,_english-version,_reviewer-select,create,edit,show,report}.blade.php
  public/{index,show,_bookmark-button,_credibility,_live-search-results}.blade.php
  review/index, users/{index,_form,create,edit}, bookmarks/index, notifications/index, profile/edit
  errors/{403,404,419,429,500,503}.blade.php
lang/id/{auth,pagination,passwords,validation}.php, lang/id.json
lang/en.json (terjemahan Inggris, kunci = teks Indonesia), lang/en/passwords.php (hanya menimpa `sent`)
```

## 11. Kebutuhan non-fungsional

- **Keamanan:** autentikasi berbasis session (cookie `http_only`, `same_site=lax`), **bukan JWT** — aplikasi server-rendered
  tanpa API; header keamanan HTTP (`SecurityHeaders`); sesi perangkat lain keluar saat password berubah
  (`AuthenticateSession`); batas percobaan bernama di semua aksi tulis (Masuk/Lupa Password per email + IP); Lupa
  Password tidak membocorkan email terdaftar; foto upload digambar ulang (EXIF/GPS terbuang, orientasi diterapkan);
  `composer audit` bersih; pengaturan production di README "Checklist keamanan saat deploy"; CSRF di semua form; output Blade selalu `{{ }}` (pengecualian tunggal: isi artikel tersanitasi);
  `user_id` dari user login, bukan dari form; `role` tidak bisa diisi massal; throttle login, lupa password, komentar,
  live search; link eksternal `rel="noopener noreferrer"`.
- **Keterbacaan & aksesibilitas:** huruf terkecil 14px, kontras ≥ 4,5:1, area sentuh 44px, ikon selalu disertai teks
  (atau `sr-only`), tanggal pasti (relatif hanya < 24 jam), pop-up gagal menunggu ditutup, navigasi keyboard
  (dialog, popover, live search).
- **Bahasa:** seluruh UI, validasi, email, dan halaman error Bahasa Indonesia; zona waktu WIB.
- **Ponsel:** semua halaman responsif (tabel laporan menjadi kartu), tanpa scroll horizontal.
- **Performa:** eager loading (tanpa N+1), pagination, huruf disajikan dari server sendiri, Trix hanya dimuat di form.
- **Data real:** MySQL lokal tidak boleh di-reset; setiap migration didahului backup (`storage/app/backups/`). Cadangan
  yang ada (keduanya 3 artikel asli): `artikel_app-sebelum-versi-inggris-20261030-1306.sql` (terbaru, sebelum migration
  versi Inggris, belum diuji restore) dan `artikel_app-data-real-20261030-0811.sql` (uji restore berhasil); cadangan
  per-tahap lama, data dummy, dan gambar dummy dihapus 2026-10-30 — nama file backup lain di log progres di bawah tinggal
  catatan sejarah.

## 12. Test case

Jalankan `php artisan test` (SQLite in-memory dari `phpunit.xml`, MySQL tidak tersentuh). **21 file · 166 test ·
843 assertion — semua lulus (2026-10-30).** Test penting dibuktikan bisa gagal dengan sabotase sementara (mis. policy
selalu `true`, `leftJoin`, search tanpa closure, sanitizer dimatikan, konversi nomor +62 dirusak).

| File | Test | Yang diuji |
|---|:-:|---|
| `AuthTest` | 17 | Navbar tamu hanya tombol Masuk; daftar (+ validasi), Masuk benar/salah, throttle 5×, Keluar, lupa password & tampilan "Cek email", reset password (token sekali pakai, token salah), tamu ke `/dashboard` → login, user login tidak bisa buka login/daftar; lupa password menjawab sama untuk email terdaftar/tidak |
| `ArticleAuthorizationTest` | 9 | Pemilik bisa lihat/ubah/hapus artikel belum terbit; user lain 403 (lihat, ubah, hapus); daftar hanya milik sendiri; tamu ditolak; `user_id` dari user login; publik hanya Published; halaman publik tanpa tombol kelola |
| `ArticleReportTest` | 9 | **Soal 2:** INNER JOIN membuang artikel tanpa penulis, hanya Published, nama & email dari `users`, urut terbaru, 10/halaman, cari judul/nama/email, cari tidak membocorkan Draft, kata kunci ikut di link halaman, tamu ditolak |
| `ArticleRoleTest` | 11 | Pendaftar selalu pembaca, penulis tidak bisa terbit langsung / mengubah artikel terbit / membuka halaman admin, ajukan review, admin terbitkan & kembalikan (catatan wajib), catatan terhapus saat diajukan ulang, aksi review hanya untuk status Review, perintah `user:role` |
| `ArticleContentTest` | 6 | Format Trix dipertahankan, HTML berbahaya dibuang sebelum simpan, link aman di tab baru, HTML tanpa Eloquent disanitasi saat tampil, editor kosong ditolak, teks polos jadi paragraf |
| `ReaderFeaturesTest` | 8 | Pembaca tidak bisa masuk area penulis & diarahkan ke beranda, komentar (validasi, escape, wajib login, hanya artikel Published), hapus komentar pemilik/admin, Baca Nanti toggle & artikel tidak terbit hilang dari daftar |
| `NotificationTest` | 9 | Admin diberi tahu saat artikel diajukan (tanpa duplikat), terbit → penulis & pembaca, admin terbitkan artikel sendiri, dikembalikan → penulis, komentar baru (bukan komentar sendiri), lonceng & tandai dibaca, notifikasi orang lain 404, tandai semua dibaca |
| `UserManagementTest` | 8 | Hanya admin, cari & filter peran, tambah user + bisa login, validasi, ubah (password kosong = tetap), reset password orang lain, admin tidak bisa ubah peran/hapus akun sendiri, hapus user: artikel tetap (tanpa penulis), komentar/bookmark/notifikasi terhapus |
| `ProfileTest` | 5 | Semua peran bisa buka Kelola Akun (tamu tidak), menu navbar, ubah nama & email tanpa bisa ubah peran, ganti password wajib password lama, password baru harus berbeda & dikonfirmasi |
| `ArticleViewTest` | 8 | Kunjungan unik per sesi/akun, penulis sendiri tidak dihitung, `updated_at` tetap & Draft tidak dihitung, Terpopuler (batas minimal & urutan), bagian beranda hanya di halaman pertama, Artikel Terkait berdasarkan kata kunci, statistik kunjungan penulis |
| `LiveSearchTest` | 6 | Panel cari di navbar, hasil Published + sorotan + judul dulu, batas hasil & link semua hasil, judul tetap di-escape saat disorot, kata pendek/tidak ditemukan, buka endpoint langsung → redirect |
| `ArticleSeoFieldsTest` | 4 | Simpan field SEO & panel SEO tampil, batas panjang ala Google, halaman publik memakai judul/deskripsi/kata kunci SEO, fallback ke judul & isi |
| `SeoTest` | 7 | Meta description, Open Graph & canonical; gambar bagikan bawaan; JSON-LD penulis/peninjau/breadcrumb; canonical beranda; noindex halaman privat & cari; sitemap; robots.txt memblokir di luar production |
| `CredibilityTest` | 8 | Profesi penulis, peninjau medis & sumber tampil; hanya admin memilih peninjau (harus admin/penulis); isi profesi sendiri; footer hanya menampilkan info yang diisi; konversi nomor ke +62 & link peta; link media sosial resmi; tombol ikon punya teks |
| `ReadabilityTest` | 3 | Tanggal relatif hanya < 24 jam; pop-up gagal tanpa timer, berhasil dengan timer; hijau WhatsApp kontras tinggi |
| `PopupMessageTest` | 6 | Pop-up berhasil keluar & simpan; halaman kedaluwarsa (419) → pop-up gagal; tamu menyimpan artikel → pop-up & kembali ke artikel setelah login; form tidak valid → pop-up gagal umum; tanpa pesan → tanpa pop-up |
| `PagesRedesignTest` | 11 | Halaman 404/429/500; Baca Nanti; filter notifikasi; ringkasan & cetak laporan; detail artikel penulis (pratinjau, kelengkapan, Google); waktu baca tidak tampil; form bertahap + penghitung kata; statistik dashboard |
| `ArticleTranslationTest` | 6 | Simpan versi Inggris (tersanitasi, form edit menampilkannya); judul & isi Inggris wajib berpasangan (editor kosong `<div><br></div>` dianggap kosong, disimpan null); pengunjung English melihat terjemahan (judul halaman, `<h1 lang="en">`, isi, kartu beranda, sidebar); tanpa terjemahan → Indonesia + pemberitahuan; pencarian & live search cocok dengan teks Inggris; detail admin menampilkan status & pratinjau versi Inggris |
| `SecurityTest` | 13 | Header keamanan (termasuk 404) & HSTS hanya HTTPS; sesi lain keluar saat password berubah, sesi pengganti tetap login; batas login per email + IP; batas daftar/reset/ganti password; batas aksi tulis per akun; penghitung batas terpisah per rute; foto upload kehilangan EXIF/GPS & orientasi diterapkan; `%`/`_` bukan wildcard; tombol kembali halaman error tidak ke situs luar; rute storage lokal mati |
| `LocaleTest` | 8 | Bawaan Indonesia + tombol bahasa; ganti ke English & bertahan (isi artikel tetap Indonesia); bahasa tak dikenal ditolak; area penulis (dashboard, Artikel Saya, form tulis) & pesan validasi ikut bahasa; halaman admin (Review, Laporan, Kelola Pengguna) & notifikasi ikut bahasa (templat + data); 404 ikut bahasa; pop-up & pemisah ribuan ikut bahasa; setiap teks di view/`app/Http`/notifikasi punya terjemahan (+ kunci `trans_choice` di `id.json`) |
| `HeartOnlySiteTest` | 4 | Kolom kategori sudah tidak ada, beranda bertema jantung tanpa filter jenis, tombol bagikan & konsultasi jantung, migration menjadikan artikel non-jantung Draft sebelum kolom dihapus |

### Checklist uji manual (browser) — semua ✅ (2026-10-19)
1. Beranda: daftar artikel Published, search, pagination
2. Detail artikel publik tanpa tombol edit/hapus
3. Login `admin@example.com` / `password` → dashboard + statistik
4. Tulis artikel dengan gambar (preview muncul)
5. Edit: ganti gambar & centang hapus gambar
6. Submit form kosong → error validasi tampil
7. Hapus artikel (dengan konfirmasi)
8. Laporan Artikel: hanya Published, urut terbaru, 10/halaman, search, 3 artikel tanpa penulis tidak muncul
9. Lupa password → pesan status
10. Link reset dari `storage/logs/laravel.log` → set password baru → login
11. User lain membuka `/dashboard/articles/{slug-milik-admin}/edit` → **403**
12. Belum login membuka `/dashboard` → redirect ke login

## 13. Data klinik & yang masih menunggu

Kontak resmi (diisi 2026-10-29, `CLINIC_*` di `.env` & `.env.example`): Jl. Tubagus Ismail VII, Sekeloa, Kecamatan
Coblong, Kota Bandung, Jawa Barat 40134 · Buka setiap hari, 24 jam · Telepon (022) 253 1000 · WhatsApp (footer)
0811 9161 166 · info@cmihospital.com · WhatsApp tombol Konsultasi 0821-2159-0000 · media sosial `cmihospitals`.

**Menunggu / backlog:**
- [ ] Akreditasi CMI (`CLINIC_ACCREDITATION`) — tunggu data dari user, jangan dikarang.
- [ ] Link Google Maps resmi (`CLINIC_MAPS_URL`) — sementara link pencarian dari alamat.
- [ ] `npm run build` sebelum dikumpulkan / deploy; isi `CLINIC_*` di `.env` server; jalankan "Checklist keamanan
  saat deploy" & "Checklist SEO saat deploy" di README.
- Persiapan tanya-jawab penilai: middleware vs policy, alur reset password, INNER JOIN vs LEFT JOIN, jebakan
  `orWhere` tanpa closure, N+1 & eager loading, mass assignment & `$fillable`, CSRF, route model binding dengan slug.

## 14. Log progres

Cara menambah entri: tulis di **paling atas** subbagian Tahap 5 (di bawah paragraf pembuka), format
`- [x] **Judul** (YYYY-MM-DD): ...`, lalu perbarui angka test di bagian 1 & 12.

### Tahap 1–4 (2026-10-18 s.d. 2026-10-19) — jawaban ujian
- [x] Tahap 1 — Database (migration, model, factory, seeder)
- [x] Tahap 2 — Route & Controller (+ Form Request, Policy)
- [x] Tahap 3 — Blade & UI (Tailwind) — 12 checklist manual lolos (2026-10-19)
- [x] Tahap 4 — Testing & finishing: checklist diperbaiki; timezone WIB, halaman 403 & email reset Bahasa Indonesia;
  fitur dicocokkan dengan rubrik (README bagian 5); README untuk penilai; feature test awal `AuthTest`,
  `ArticleAuthorizationTest`, `ArticleReportTest` (29 test / 112 assertion, terbukti gagal saat disabotase).
  Sisa: `npm run build` sebelum dikumpulkan.

Catatan: `ArticleRequest`, `ArticlePolicy`, `ArticleController`, view `articles/*` dan `ArticleReportController`
sempat belum ada di repo walau ditandai selesai; dibuat ulang 2026-10-19 (commit 510bc6f, 2a16b48, bfd8176).
Git kemudian di-init ulang 2026-10-28 (commit pertama 54097ff).

### Tahap 5 — Pengembangan front-end (mulai 2026-10-28, data real diinput user sendiri lewat halaman)
Entri pekerjaan diurutkan **dari yang terbaru** (urutan mengikuti riwayat commit); entri baru ditambahkan tepat di
bawah "Git di-init ulang".
- Arah desain: **corporate / company profile**. Token di `app.css` `@theme`: palet `brand-50..950` dari warna resmi
  CMI (navbar `#E3F2FD` = `brand-50`, footer `#066AC9` = `brand-600`) + font dari **Google Fonts API** lewat provider
  `google()` laravel-vite-plugin (diunduh saat build, disajikan dari server sendiri; `@fonts` di layout): Inter = `font-sans`
  (UI & judul; sebelumnya Plus Jakarta Sans), **Roboto = `font-article`** (huruf artikel: paragraf `.article-content` + judul artikel di beranda, Baca Nanti, 404;
  subjudul di dalam isi tetap Inter; sejak 2026-10-29 menggantikan Lora/`font-serif` atas permintaan user). Logo: `public/images/logo-cmi.png` (sudah di-crop 384×160, latar transparan; file asli 1080×1080 di `logo-cmi-square.png`) via komponen `x-logo` (fallback inisial).
- Git di-init ulang 2026-10-28 (branch `main`); user sendiri yang commit & push ke GitHub.
- [x] **Tampilan baru kartu halaman auth di `x-auth-card`** (2026-10-31). Desain dari user (awalnya ditulis langsung di
  `login.blade.php` → error 500 karena `:title` tidak dikirim + logo/judul dobel) dipindah ke komponen sehingga Masuk,
  Daftar, Lupa & Reset Password seragam: kartu `rounded-2xl` + bayangan lembut, lebar `max-w-4xl`, dipusatkan vertikal,
  isian & tombol di dalam kartu `rounded-xl` (varian `[&_.form-input]`/`[&_.btn]`, `.form-input` global tidak diubah),
  panel biru memakai cahaya tipis di pojok (gantinya gradien radial). `auth/_aside`: logo jadi link ke beranda + catatan
  "Akses aman & terenkripsi…" (ikon perisai). Penyesuaian aturan keterbacaan: teks ≥ 14px & `text-brand-100`/`slate-500`
  (versi user `text-xs`, `slate-400`, `font-serif`).
- [x] **README jadi tutorial instalasi macOS, Linux, Windows** (2026-10-30). Bagian 1–6 ditulis ulang: kebutuhan (PHP
  **8.4.1+**, bukan 8.3 seperti tertulis sebelumnya; ekstensi `intl`, `gd`, `exif`, dll.; Node 20.19+/22.12+; internet saat
  build untuk Google Fonts), persiapan perangkat per OS (Homebrew; apt + PPA ondrej/sury PHP 8.4, NodeSource, user MySQL
  khusus karena `root` = auth_socket; Windows via WSL 2 atau PHP zip + `php.ini` + Composer-Setup + MySQL Installer),
  10 langkah instalasi, akun demo, uji reset password (tail / Get-Content), menjalankan lagi & memperbarui (`git pull`
  … `migrate`). Bagian rubrik dst. jadi 7–11, ringkasan perintah 12, troubleshooting 13 (+ error khas OS). Alamat clone
  dibetulkan ke `github.com/AliRodja/cmi-artikel.git` (sebelumnya `artikel-app.git`). Ikut diperbaiki: `composer.json`
  `php ^8.4`; `ImageUpload` tidak crash bila ekstensi `exif` tidak ada (Windows). Diverifikasi dengan mengikuti tutorial
  dari clone baru di folder sementara (DB `artikel_app_tutorial`, lalu dihapus): semua halaman 200, 166 test lulus.
- [x] **Versi Bahasa Inggris artikel, diterjemahkan sendiri oleh penulis** (2026-10-30; user memilih opsi C, bukan
  terjemahan otomatis). Migration `add_english_version_to_articles_table` (`title_en`, `content_en` longText,
  `seo_title_en` 70, `seo_description_en` 160, nullable; MySQL dimigrasi, backup
  `artikel_app-sebelum-versi-inggris-20261030-1306.sql`, 3 artikel asli utuh). Model: `setContentEnAttribute` (sanitasi,
  kosong → null), `content_en_html`, `hasEnglish()`, `showsEnglish()` (locale en + hasEnglish), accessor `display_title`,
  `display_content_html`, `display_excerpt`, `display_locale`; `meta_title`/`meta_description` ikut bahasa; `scopeSearch`
  & urutan live search juga mencakup kolom Inggris; konstanta `LIST_COLUMNS`. `ArticleRequest`: `prepareForValidation`
  (editor kosong → null), `required_with` berpasangan, batas SEO. Form: partial `articles/_english-version` (langkah ⑤,
  `<details>` terbuka bila terisi/ada error, editor Trix kedua). Publik (`public/*`, `article-card`, `popular-article`,
  `share-buttons`, live search, Baca Nanti, 404) memakai `display_*`; artikel tanpa terjemahan → pemberitahuan. Detail
  admin: baris "Versi Bahasa Inggris: Tersedia/Belum ada" + pratinjau. Seeder: 3 artikel penulis demo `withEnglish()`.
  **Bug tertangkap test:** query live search & sidebar memilih kolom terbatas sehingga judul Inggris tak terbaca → kini
  `Article::LIST_COLUMNS`. Test `ArticleTranslationTest` (6, terbukti gagal saat locale diabaikan, kolom Inggris tak
  dipilih, sanitasi dilepas, atau aturan berpasangan dilepas) → 166 test / 843 assertion.
- [x] **Perbaikan keamanan no. 1–5 & 11** (2026-10-30, hasil audit). (1) `symfony/mime` 8.1.7 → 8.1.10 (CVE-2026-45070,
  medium); deploy wajib `composer install --no-dev` (rute `_boost/...` dari paket dev). (2) Batas percobaan di semua aksi
  tulis: limiter bernama `writes` (30/menit per akun, GET tidak dihitung) di grup `auth` & `writer`; daftar &
  reset-password 10/menit, ganti password 5/menit. **Temuan:** `throttle:X,Y` tanpa nama memakai SATU penghitung per
  IP/akun untuk semua rute (live search menghabiskan jatah komentar) → semua diberi nama ketiga (`throttle:10,1,comments`).
  (3) Limiter `auth-attempts` untuk Masuk & Lupa Password: 5/menit per email + IP, plus 30/menit per IP. (4) Lupa Password
  menjawab sama untuk email terdaftar, tidak terdaftar, maupun throttle broker; teks "Jika email terdaftar…"
  (`lang/id/passwords.php`, `lang/en/passwords.php` hanya menimpa `sent`, digabung dengan bawaan). (5)
  `App\Support\ImageUpload::store()` menggambar ulang JPG/PNG/WebP dengan GD (EXIF/GPS terbuang, Orientation diterapkan
  dulu, transparansi dipertahankan, nama acak) — dipakai `ArticleController`; gambar lama di server tidak diproses ulang.
  (11) Tombol kembali halaman 419/429 hanya ke host sendiri; `Article::likePattern()` + `LIKE ? ESCAPE '!'` di pencarian
  publik & live search (query Soal 2 sengaja tidak diubah); `filesystems.local.serve = false`. Test `SecurityTest` +8,
  `AuthTest` +1 → 160 test / 800 assertion; keenam perbaikan terbukti ditangkap test saat disabotase.
- [x] **Ganti bahasa juga di area penulis/admin & halaman akun** (2026-10-30). `SetLocale` tidak lagi memaksa `id` di
  `/dashboard…`; tombol bahasa tampil di semua halaman. Dibungkus `__()`: dashboard, `articles/{index,_form,_seo-panel,
  _reviewer-select,create,edit,show,report}`, `review/index`, `users/*`, `bookmarks/index`, `notifications/index`,
  `profile/edit`, komponen `status-badge` (Published/Menunggu Review/Draft), `review-note`, `notification-item`;
  pesan flash ArticleController/ArticleReviewController/UserController/ProfileController; `attributes()`/`messages()`
  `ArticleRequest` & `UserRequest` + `validate(attributes:)` (pesan validasi Inggris: "The title field is required.").
  JS `article-form.js` & `seo-panel.js` membaca templat dari `data-text-*` (`:count kata`, `:length karakter · ideal
  :min–:max`, `:passed dari :total`, `(sekarang :length)`). **Notifikasi** kini disimpan `message` = templat Indonesia +
  `replace` (mis. `:name mengomentari artikel Anda.` + `{name}`), diterjemahkan saat tampil; notifikasi lama (kalimat jadi)
  tetap tampil apa adanya. `lang/en.json` 480 entri; kunci `trans_choice` baru (`:count artikel`, `:count kali`, `:count
  kali dibaca`, `:count artikel tersimpan`, `artikel`) juga di `id.json`. EN "Artikel" = "Articles" (judul kolom/jumlah).
  Uji: screenshot CDP dashboard/form+SEO/detail/laporan/pengguna dalam English; pemindai teks tanpa `__()` bersih.
  `LocaleTest` 8 test (2 baru, terbukti gagal bila `__()` notifikasi atau nama kolom validasi dilepas) → 151 test / 646.
- [x] **Tombol Daftar dihapus dari navbar + pilihan bahasa Indonesia/English** (2026-10-30). Navbar tamu hanya **Masuk**
  (kini `btn-primary`, di ponsel tanpa ikon & `whitespace-nowrap`); daftar lewat halaman Masuk & footer. Bahasa — cakupan
  dipilih user: **halaman publik dulu**. `config('app.supported_locales')` (id, en) · `POST /bahasa` → `LocaleController`
  (validasi `Rule::in`, simpan di sesi, `back()`) · middleware `SetLocale` (grup `web`, prioritas tepat setelah
  `StartSession` agar 404 binding ikut bahasa; `/dashboard…` dipaksa `id`; `Number::useLocale`; Carbon otomatis) ·
  `Route::fallback(abort 404)` agar URL asing lewat grup `web` · `partials/locale-menu` (popover `.dropdown-panel`,
  ikon `globe` + kode ID/EN, nama bahasa dalam bahasanya sendiri, catatan "isi artikel tetap Indonesia"; disembunyikan
  di `/dashboard…`; di ponsel ikon saja, logo navbar `h-8` di ponsel, gap navbar `gap-2`). Teks: **kunci = teks
  Indonesia** `__('Masuk')`, terjemahan `lang/en.json` (213 entri). Dibungkus: layout, navbar, drawer, user-menu,
  notification-menu, search-panel, flash, footer (jam klinik `__($clinic['hours'])`), seo (`og:locale`), komponen publik
  (article-card, consultation-card, error-page, popular-article, section-title, share-buttons, logo, input, role-badge),
  `public/*`, `auth/*`, `errors/*`, pesan flash BookmarkController/CommentController/Login/Register/Notification, pesan
  419 & tamu di `bootstrap/app.php`. JS (`app.js`, `live-search.js`, salin tautan) membaca teks dari `data-*` Blade.
  Jamak: `trans_choice` (`:count view|:count views`) + angka `Number::format`. **Jebakan:** `trans_choice` memakai
  bahasa cadangan (en) bila kunci tak ada di bahasa aktif → kunci `trans_choice` wajib juga di `lang/id.json`. `<html
  lang>` dinamis, judul & isi artikel `lang="id"`. SEO tetap satu URL (crawler tanpa sesi = Indonesia). Uji: navbar
  diukur di 320/360/390px (ID & EN) via Chrome DevTools Protocol — tidak bertumpuk/melebar. Test `LocaleTest` (7,
  terbukti gagal bila `SetLocale` dilepas, kunci dashboard dibuka, atau kunci `id.json` dihapus) + 1 test navbar di
  `AuthTest` → 150 test / 631 assertion.
- [x] **Keamanan: header HTTP, keluar dari perangkat lain, pengaturan production** (2026-10-30). User bertanya soal JWT
  → tidak dipakai (aplikasi Blade tanpa API; session + cookie `http_only` + CSRF lebih aman & bisa dicabut). Middleware
  baru `App\Http\Middleware\SecurityHeaders` (global `append` di `bootstrap/app.php`, ikut di 404): `X-Frame-Options:
  SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy`,
  HSTS `max-age=31536000` hanya bila `isSecure()`. Belum ada CSP (perlu uji skrip/font). `AuthenticateSession` bawaan
  Laravel ditambahkan ke grup `web`: sidik password di sesi → ganti password lewat Kelola Akun / reset email / admin
  mengeluarkan sesi lain (cookie "ingat saya" juga dicek), sesi yang mengganti tetap login; sesi lama tanpa sidik tidak
  dikeluarkan. Diuji juga E2E dua perangkat (curl, SQLite sementara): A ganti password → A 200, B → login. `.env.example`
  diberi komentar production + `SESSION_SECURE_COOKIE`; README bagian "Checklist keamanan saat deploy" (debug off,
  HTTPS, cookie aman & enkripsi sesi, SMTP, document root `public/`, `trustProxies` bila di balik proxy, cache).
  Test `SecurityTest` (5, terbukti gagal bila tiap middleware dimatikan) → 142 test / 596 assertion.
- [x] **Info kontak resmi CMI di footer** (2026-10-29, data dari user): alamat Jl. Tubagus Ismail VII, Sekeloa,
  Kecamatan Coblong, Kota Bandung, Jawa Barat 40134 · Buka setiap hari, 24 jam · Telepon (022) 253 1000 · WhatsApp
  0811 9161 166 · Email info@cmihospital.com. Diisi di `.env` **dan** `.env.example` (`CLINIC_ADDRESS/HOURS/PHONE/
  WHATSAPP/EMAIL`; `.env` di-gitignore). `config/clinic.php` baru: `phone`, `phone_international` ("(022) 253 1000" →
  `+62222531000`, untuk `tel:` & JSON-LD `telephone`), `whatsapp`, `whatsapp_url` (`https://wa.me/628119161166`),
  `maps_url` kosong → link pencarian Google Maps dari alamat. **Pilihan user: nomor 0811 hanya di footer** ("Telepon:",
  "WhatsApp:"); tombol Konsultasi, kartu "Alami gejala jantung?", halaman error & `contactPoint` JSON-LD tetap
  0821-2159-0000 (`whatsapp_number`/`whatsapp_display`, dari link konsultasi resmi). Akreditasi belum ada (kosong =
  disembunyikan). Ikon baru `phone` di `x-icon`. Test di `CredibilityTest` (terbukti gagal bila konversi +62
  disabotase) → 137 test / 562 assertion. (Backup `.env` lama sudah dihapus 2026-10-30.)
- [x] **Media sosial di footer** (2026-10-29): `config('clinic.social')` = daftar label/ikon/url akun resmi CMI
  (Facebook, Instagram, X, TikTok, YouTube — **disalin dari footer cmihospital.com**, semuanya `cmihospitals`; Twitter
  tanpa `www.`). Footer kolom 1 "Ikuti Kami": tombol bulat 44px, `target=_blank rel="noopener noreferrer"`, `title` +
  teks `sr-only`. Ikon brand baru di `x-icon`: instagram, tiktok, youtube. Test di `CredibilityTest` → 136 test.
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
  down/up). Entri lain di bawah sudah disesuaikan (dirapikan 2026-10-29).
- [x] **Redesign detail artikel area login** (`articles/show`, 2026-10-29): banner status di atas grid; grid
  `lg:grid-cols-[minmax(0,1fr)_21rem]` — aside ditulis lebih dulu di DOM (ponsel: panel Tinjau sebelum isi panjang),
  pratinjau `lg:order-first`. Aside: panel **Tinjau** (Terbitkan + peninjau medis / "atau" / Kembalikan + catatan),
  Informasi artikel (penulis+profesi, peninjau medis, dibuat/diperbarui, jumlah kata; waktu baca sengaja tidak ditampilkan atas permintaan user), **Kelengkapan** 6
  poin dihitung server (aturan = form tulis + peninjau medis), Statistik Kunjungan (total/7 hari/komentar + 5 terbaru),
  Tampilan di Google (`meta_title`/`meta_description` + kata kunci). Pratinjau = kartu seperti halaman publik; blok
  peninjau medis + sumber dipisah ke partial `public/_credibility` (dipakai publik & pratinjau). Accessor baru
  `Article::word_count`. Controller `show` eager load `user`, `loadCount('comments')`.
- [x] **Redesign Laporan Artikel** (2026-10-29; query INNER JOIN TIDAK diubah): ringkasan 3 angka dalam satu kartu (Artikel terbit / Tampil di laporan / Tanpa penulis — **tanpa istilah
  teknis**: pengguna halaman ini nakes & editor konten, bukan developer; penjelasan INNER JOIN untuk penilai cukup di
  README), bilah cari di atas tabel + "Menampilkan 1–10 dari N", kata kunci disorot `x-highlight`, avatar penulis, email
  `mailto:`, tanggal + jam zona (`format('T')` → WIB), `aria-sort` di kolom tanggal. Ponsel: kartu per artikel (tabel
  `hidden md:block`). Tombol **Cetak** (`window.print()`): `@media print` di app.css menyembunyikan navbar/footer/toast
  & merapatkan `.th/.td`; elemen interaktif `print:hidden`, tabel `print:block`, header cetak berisi waktu cetak.
  Test di `PagesRedesignTest` → 133 test.
- [x] **Redesign form tulis/edit artikel** (2026-10-29): `_form` = langkah bernomor (komponen `x-form-step`): ①
  Judul artikel, ② Isi (penghitung kata `[data-word-count]`), ③ Sumber rujukan, ④ Optimasi SEO
  (`_seo-panel` kini memakai `x-form-step number=4`). Sidebar sticky & bisa di-scroll sendiri
  (`lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto`): Publikasi, **Kelengkapan artikel** (5 poin + bilah progres; wajib
  hanya judul/isi), Gambar Utama. JS `resources/js/article-form.js` (diimpor `editor.js`): penghitung kata,
  kelengkapan, pratinjau gambar (pindahan skrip inline), peringatan `beforeunload` bila ada perubahan belum disimpan
  (mati saat submit; Chrome hanya memunculkannya setelah interaksi pengguna sungguhan). Toolbar Trix sticky
  `top-[67px]` (tinggi navbar). Test di `PagesRedesignTest` → 132 test.
- [x] **Optimasi SEO per artikel** (2026-10-29): migration `add_seo_fields_to_articles_table` (`focus_keyword` 100,
  `seo_title` 70, `seo_description` 160, nullable; MySQL dimigrasi, backup `artikel_app-sebelum-seo-artikel-*.sql`),
  fillable + validasi `ArticleRequest`. Accessor `meta_title` (seo_title ?: title) & `meta_description`
  (seo_description ?: plain text 155) dipakai `public/show` (title/description) & `Seo::article()` (+ `keywords`).
  Partial `articles/_seo-panel` (di bawah kartu isi di `_form`; kolom kiri kini dibungkus `space-y-6`): pratinjau Google,
  penghitung karakter, checklist 7 poin; JS `resources/js/seo-panel.js` (diimpor `editor.js`, slugify menyerupai
  Str::slug, slug tetap saat edit). Tidak memakai `<meta keywords>` (diabaikan Google) — sampaikan ke user bahwa
  tidak ada jaminan peringkat. Test `ArticleSeoFieldsTest` (4) → 131 test / 523 assertion.
- [x] **Redesign halaman sisa** (2026-10-29): Baca Nanti → daftar (thumbnail, judul serif, ringkasan,
  "Disimpan <x-date pivot>", tombol **Baca** & **Hapus** = form `bookmarks.toggle`), paginate 10. Notifikasi → tab
  Semua/Belum dibaca (`?filter=belum-dibaca`, controller kirim `onlyUnread/totalCount/unreadCount`), dikelompokkan
  Hari ini/Kemarin/tanggal; `x-notification-item`: ikon berwarna per jenis, `x-date`, badge "Baru". Error: komponen
  `x-error-page`; `errors/{403,404,419,429}` pakai layouts.app + `@section('robots','noindex, follow')` (partials/seo kini
  mendukung override `robots`; 404 artikel Draft tidak lagi "index"); 404 menyarankan 3 artikel terbaru (try/catch);
  `errors/{500,503}` pakai `layouts/minimal` (tanpa navbar/DB). Dashboard: kartu statistik = link ke Artikel Saya
  terfilter, kartu "Total dibaca" (`totalViews`), "Paling banyak dibaca" (`topArticles`), tips + sumber/peninjau.
  Artikel Saya, Review, Kelola Pengguna: tombol aksi berlabel (Lihat/Edit/Hapus/Tinjau), profesi user. Laporan:
  `publishedTotal` & `withoutAuthor` dikirim controller (label UI kini bahasa awam, lihat Redesign Laporan Artikel),
  tanggal `translatedFormat`, pembungkus tabel `relative`. Test
  `PagesRedesignTest` (7) → 127 test / 505 assertion.
- [x] **SEO** (2026-10-28, sebelum deploy): `config/seo.php` (description bawaan, `image` = `images/og-default.png`
  1200×630 dibuat GD dari logo, latar brand-950). `App\Support\Seo`: `isIndexable()` (hanya `home` tanpa ?search &
  `public.articles.show`), `canonicalUrl()` (beranda: hanya ?page>1; lainnya `$request->url()`),
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
  Footer 3 kolom: "Hubungi Kami" dari `config/clinic.php` (`CLINIC_*` di .env,
  kosong = disembunyikan). Data kontak sudah diisi 2026-10-29 (lihat entri "Info kontak resmi CMI"); **akreditasi
  belum ada — tunggu dari user, jangan dikarang.** No. 4: `.nav-icon-btn` & `.share-btn`
  44px, `x-share-buttons labels` (dipakai di akhir artikel), tombol tutup pop-up 40px. (Tombol navbar "Cari" berlabel
  kemudian diganti ikon saja atas permintaan user.) Test `CredibilityTest` (6) → 113 test / 442 assertion.
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
- [x] **Beranda ala portal berita (pola Kompas.id)** (2026-10-28, permintaan user; tata letak sekarang lihat entri
  "Situs khusus artikel JANTUNG"). Hero biru tua tetap, kontennya rata tengah — label, judul, deskripsi, form cari
  `mx-auto` — dengan cahaya radial di tengah atas. Hanya saat `$isLanding`: **Sorotan** (kotak besar artikel terbaru,
  judul serif) | **Artikel Lainnya** (5 berikutnya: judul serif, waktu relatif, thumbnail kanan). Lalu daftar artikel
  (`#daftar-artikel`) | **Terpopuler** (5, bernomor serif) + `x-consultation-card` (sticky). Komponen `x-section-title`
  (garis aksen kiri brand-600) & `x-consultation-card` (dipakai juga di detail). Judul berita di beranda memakai
  `font-article` (kini Roboto).
- [x] **Live search navbar** (2026-10-28): `GET /cari?q=` (`articles.live-search`, `throttle:60,1`) →
  `PublicArticleController@liveSearch` (bukan AJAX → redirect `home?search=`; < 2 huruf → kosong; Published +
  scope baru `Article::search()` yang juga dipakai beranda; `orderByRaw('CASE WHEN title LIKE ? ...')` judul dulu;
  6 hasil + total) → view potongan `public/_live-search-results` (role=listbox/option). Komponen `x-highlight`
  (`preg_split` + `PREG_SPLIT_DELIM_CAPTURE`, `<mark>` dengan `{{ }}`; satu directive per baris — `@endif@endforeach`
  menempel tidak dikompilasi Blade). `partials/search-panel`: input `role=combobox`, `[data-live-search-idle]` (teks
  petunjuk contoh pencarian jantung), `[data-live-search-results]`. JS `resources/js/live-search.js` (diimpor app.js): debounce 250 ms,
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
- [x] **Redesign beranda** (2026-10-28; tata letaknya kemudian diganti, lihat entri "Beranda ala portal berita" & "Situs khusus artikel
  JANTUNG"): hero biru tua tetap
  (tanpa pola garis). `PublicArticleController@index` kirim `$isLanding` (tanpa cari & halaman 1). Komponen
  `x-popular-article` (dipakai juga di detail) dan scope `Article::popular()` berasal dari sini. User tidak suka hiasan
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
  Halaman publik: "N kali dilihat" di hero; di bawah grid 3 kolom ada **Artikel Terkait** (kini
  `relatedArticles()`: cocok `focus_keyword` dulu, sisanya acak; 3 kartu) dan
  **Artikel Terpopuler** (≥ `config('clinic.popular_min_views')` = 5, urut view_count, 6 item bernomor; empty state
  bila belum ada). Variabel sidebar kini `$latestArticles`. Area penulis: kolom "Dilihat" di Artikel Saya + panel
  Statistik Kunjungan di `articles/show` (total, 7 hari, 5 riwayat terbaru). Seeder membuat 0–40 riwayat acak per
  artikel Published. MySQL dimigrasi (backup `artikel_app-sebelum-kunjungan-*.sql`). Test `ArticleViewTest` (6) →
  81 test / 307 assertion; terbukti gagal saat dedup/cek penulis/hash sesi/increment/batas minimal disabotase.
- [x] **Detail 3 kolom + bagikan** (2026-10-28; fitur jenis artikel yang dibuat bersamaan sudah dihapus 2026-10-29,
  kolom `category` di-drop lewat migration). `public/show`: kolom Bagikan (sticky; WA/X/FB via `http_build_query`,
  salin tautan ikon-saja — ikon centang disembunyikan pakai atribut `hidden`, bukan class, karena CSS FA tak berlayer),
  kolom isi + komentar, sidebar (Artikel Lainnya, kartu CTA "Alami gejala jantung?" →
  `config('clinic.whatsapp_consultation_url')`). Font Awesome **brands** diimpor di `app.js` (ikon `whatsapp`,
  `x-twitter`, `facebook`). Catatan: PHP 8.5 menolak offset array `null` — pakai akses null-safe.
- [x] **Kelola Pengguna** (2026-10-28, admin): `UserController` resource `users.*` tanpa show (`/dashboard/users`, grup
  middleware `admin`), `UserRequest` (store & update; email unik `ignore`; password wajib saat tambah, kosong = tidak
  diganti saat edit; `role` → `prohibited` bila `cannot('changeRole')`), `UserPolicy` (`changeRole`, `delete`: admin
  & bukan akun sendiri → selalu ada admin). `role` diisi langsung `$user->role = ...` (bukan mass assignment).
  Hapus akun: `DB::transaction` → hapus notifikasi manual (morph, tanpa FK) → `delete()` (artikel nullOnDelete,
  komentar & bookmark cascade). View `users/{index,_form,create,edit}`, komponen `x-role-badge` (admin brand, penulis
  emerald, pembaca abu). Konfirmasi hapus memakai `data-confirm` + `confirm(this.dataset.confirm)` agar nama bertanda
  kutip aman. Test `UserManagementTest` (8) → 71 test / 267 assertion; terbukti gagal bila policy/notifikasi disabotase.
  Catatan: scope Eloquent (`withRole`) otomatis mengurung where sebelumnya, closure search tetap dipakai agar eksplisit.
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
- [x] Area penulis: komponen `x-page-header` (di `@section('hero')`, slot `meta`/`actions`), `x-icon` (kini Font Awesome,
  prop `size`), `x-article-thumb`. Dashboard (kartu statistik + progres published + empty state + aksi cepat),
  Artikel Saya (tab status + jumlah dari `$statusCounts` groupBy, aksi ikon), form 2 kolom (`_form` menerima
  `$submitLabel`/`$cancelUrl`; status = kartu radio; dropzone gambar), detail milik sendiri (banner Draft), laporan &
  auth-card memakai header/logo baru. `status-badge` Draft = amber. Hapus query mati `$latestArticles` di LoginController.
- Pelajaran CSS: selector toolbar Trix diberi awalan `form` (trix.css dimuat setelah app.css); kolom grid perlu `min-w-0`.
  Pembungkus tabel `overflow-x-auto` wajib `relative`, kalau tidak teks `sr-only` (position:absolute) lolos dan
  membuat halaman melebar di ponsel.
- [x] Database dibersihkan dari data dummy (akun user `rodjaali@gmail.com` dipertahankan), backup di
  `storage/app/backups/`.
- [x] Nama situs `APP_NAME="Klinik Utama CMI"` (.env & .env.example); copy hero/footer bertema klinik.
- [x] Favicon dari tanda salib logo: `public/favicon.ico` (16/32/48) + `public/apple-touch-icon.png`.
- [x] Sanitasi: `symfony/html-sanitizer` lewat `App\Support\ArticleContent` (whitelist tag Trix: div p br strong em del
  h1 blockquote pre ul ol li a[href]; link http/https/mailto, dipaksa `target=_blank rel=noopener noreferrer`).
  Dua lapis: mutator `setContentAttribute` (saat simpan) + accessor `content_html` (saat tampil). Teks polos lama →
  `<p>`. `excerpt`/`word_count` memakai `ArticleContent::toPlainText()`. `ArticleRequest`: `bail` + closure menolak
  editor kosong (`<div><br></div>`). Test: `ArticleContentTest` (6) → total 35 test / 130 assertion, terbukti gagal
  bila sanitizer dimatikan.
- [x] Rich text editor **Trix** (npm `trix`, entry Vite terpisah `resources/js/editor.js`, hanya dimuat di `_form`;
  lampiran file dimatikan). `<trix-editor class="article-editor article-content">` + `<input type="hidden" name="content">`.
  CSS editor sengaja di luar `@layer` (trix.css tidak berlayer); jangan beri class `.trix-content`.
- [x] Halaman publik: layout (`@yield('hero')`, `partials/footer`), navbar, beranda (hero + featured di halaman 1 tanpa
  search + grid), detail (hero, waktu baca, salin tautan, Artikel Lainnya). Komponen baru `x-avatar` (prop `tone`),
  `x-article-card`. (Accessor `reading_time` dihapus 2026-10-29 — user tidak mau "menit baca" di mana pun.) Pagination Bahasa Indonesia (`lang/id/pagination.php`, `id.json`).
