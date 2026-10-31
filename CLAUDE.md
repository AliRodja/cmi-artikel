# CLAUDE.md — Klinik Utama CMI (website artikel kesehatan jantung)

Aturan kerja untuk Claude. **Produk, fitur, tech stack, rute, model data, test case & log progres ada di `PRD.md`**
(baca bagian yang relevan sebelum mengubah fitur). Panduan instalasi & pemetaan rubrik untuk penilai ada di `README.md`.

## Konteks
- Awalnya jawaban **ujian masuk IT CMI** milik Ali Imran Rodja (fresh graduate TI UNPAS): Soal 1 website artikel
  Laravel, Soal 2 INNER JOIN (rubrik di PRD bagian 3). CMI mengizinkan AI **sebagai asisten**.
- Sejak 2026-10-28 fase **pengembangan dengan data real CMI**: Claude boleh langsung menulis kode, tapi tetap
  **menjelaskan singkat *kenapa*** dalam **Bahasa Indonesia** — user masih bisa ditanya penilai (middleware vs policy,
  INNER JOIN, validasi, dll.).
- Sejak 2026-10-29 situs **khusus artikel jantung**; fitur jenis/kategori sudah dihapus — jangan dihidupkan lagi.

## Cara kerja
- Kerjakan bertahap; jangan menulis ulang file besar tanpa diminta. Tunjukkan bagian yang berubah.
- Keputusan yang benar-benar milik user (warna besar, data klinik, nomor kontak) → tanyakan dulu.
- **User sendiri yang commit & push** (branch `main`). Claude hanya menyiapkan (stage + usulan pesan); pesan commit
  tanpa trailer `Co-Authored-By`.
- Setelah fitur selesai: tambah entri di **PRD.md bagian 14 › Tahap 5, paling atas** (`- [x] **Judul** (YYYY-MM-DD): …`),
  perbarui jumlah test di PRD bagian 1 & 12, dan README bila menyangkut penilai. Aturan kerja baru → CLAUDE.md ini.
- Test penting dibuktikan bisa gagal (sabotase sementara, lalu kembalikan). `TestCase` memanggil `withoutVite()`;
  `UserFactory` default penulis + state `admin()`/`pembaca()`; `ArticleFactory` state `published()`/`draft()`/`inReview()`.

## ⚠️ Database berisi DATA REAL
**JANGAN** menjalankan `migrate:fresh`, `migrate:refresh`, `migrate:reset`, `db:wipe`, atau `db:seed` pada MySQL.
- Sebelum `php artisan migrate` di MySQL: backup ke `storage/app/backups/artikel_app-sebelum-<fitur>-*.sql` (gitignored),
  pakai `mysqldump --single-transaction --set-gtid-purged=OFF`. Perintah yang mengubah data real (mis.
  `images:to-cloudinary`) juga didahului backup + `--dry-run`. Cadangan data real (3 artikel asli):
  `artikel_app-sebelum-cloudinary-20261031-1035.sql` (terbaru, gambar masih lokal), `artikel_app-sebelum-versi-inggris-
  20261030-1306.sql`, dan `artikel_app-data-real-20261030-0811.sql` (terbukti bisa di-restore).
- Uji/screenshot pakai SQLite sementara: `DB_CONNECTION=sqlite DB_DATABASE=/path/tmp.sqlite php artisan migrate --seed --force`
  lalu `php artisan serve` dengan env yang sama (env shell mengalahkan `.env`). Seeder tetap untuk penilai.
- `php artisan test` aman: SQLite in-memory dari `phpunit.xml`.
- Data kontak klinik dari user (PRD bagian 13). **Jangan mengarang** data yang belum ada (akreditasi, link peta resmi).

## Environment & menjalankan
- MacBook Pro M1, macOS. PHP 8.5 + Composer + MySQL via Homebrew (`brew services start mysql`), Node 24. Proyek butuh
  **PHP ≥ 8.4.1** (Symfony 8 di `composer.lock`) + ekstensi `intl`, `gd`, `exif`; tutorial instalasi per-OS di README §1–6.
  DB di `.env`: `DB_DATABASE=artikel_app`, user `root`. phpMyAdmin opsional:
  `php -S localhost:8080 -t /opt/homebrew/share/phpmyadmin`.
- `MAIL_MAILER=log` → link reset password di `storage/logs/laravel.log`.
- Waktu: `config/app.php` membaca `env('APP_TIMEZONE', 'UTC')` (bawaan Laravel menulis mati `'UTC'`), `.env` →
  `APP_TIMEZONE=Asia/Jakarta`. Bahasa: `APP_LOCALE=id`; `lang/id/{auth,pagination,passwords,validation}.php` untuk pesan
  berkunci, `lang/id.json` menerjemahkan kalimat Inggris bawaan framework (email reset password) — tanpa notifikasi custom.
```bash
npm run dev                 # terminal 1 (biarkan berjalan)
php artisan optimize:clear  # terminal 2
php artisan serve           # http://localhost:8000
php artisan test            # 175 test per 2026-10-31
npm run build               # wajib sebelum dikumpulkan / deploy
```
- Dev server Vite tidak mengenali class Tailwind di file Blade baru → `touch resources/css/app.css`. Mengubah
  `vite.config.js` (mis. font) → restart `npm run dev` bila tidak otomatis.

## Arsitektur yang wajib dijaga
- **Laravel 13, autentikasi manual** (tanpa Breeze). Tailwind v4 via Vite (bukan Bootstrap).
- **Peran** `users.role` = admin | penulis | pembaca (default pembaca, **tidak di `$fillable`**; diisi `$user->role = …`
  atau `forceFill`). `User::canWrite()` = admin|penulis → middleware alias `writer`; alias `admin`; keduanya di
  `bootstrap/app.php`, juga `redirectUsersTo` (pembaca ke beranda setelah login/daftar). Controller memakai
  `Gate::authorize()` (base Controller Laravel 11+ tidak punya `$this->authorize()`). Angka antrean review &
  notifikasi navbar dari View Composer di `AppServiceProvider`.
- `ArticlePolicy`: view → admin/pemilik; update/delete → admin, atau pemilik **selama belum Published**; review → admin
  & status Review. Status yang boleh dipilih **hanya** dari `Article::allowedStatusesFor($user, $article)` — dipakai
  validasi `ArticleRequest` DAN form `_form`, jangan diduplikasi.
- Status: `0` Draft, `1` Published, `2` Review (konstanta `Article::STATUS_*`). `review_note` wajib saat dikembalikan,
  dikosongkan saat status bukan Draft.
- `user_id` artikel lewat `$request->user()->articles()->create()`, tidak pernah dari input form. `user_id` nullable
  **sengaja** (bukti INNER JOIN; seeder punya 3 artikel Published tanpa penulis).
- **Query Soal 2** (`ArticleReportController`): `DB::table` + `join` + `status = 1` + search dalam `where(closure)` +
  `orderByDesc('articles.created_at')` + `paginate(10)->withQueryString()` — **jangan diubah** saat redesign.
- Slug unik otomatis di `Article::booted()`, tidak berubah saat judul diedit; route model binding memakai `slug`.
- Isi artikel: Trix → disanitasi `App\Support\ArticleContent` dua lapis (mutator saat simpan + accessor `content_html`).
- **Versi Bahasa Inggris artikel** (diterjemahkan penulis, opsional): kolom `title_en`, `content_en` (disanitasi
  `setContentEnAttribute`, kosong → null), `seo_title_en`, `seo_description_en`; judul & isi wajib berpasangan
  (`ArticleRequest`). Halaman publik WAJIB memakai `display_title` / `display_content_html` / `display_excerpt` /
  `display_locale` (+ `meta_title`/`meta_description` yang sudah ikut bahasa) — Inggris bila pengunjung memilih English
  DAN `hasEnglish()`, selain itu Indonesia. Area admin & Laporan Soal 2 tetap memakai kolom Indonesia (naskah induk).
  Query yang membatasi kolom untuk kartu/daftar publik pakai `Article::LIST_COLUMNS` (harus memuat title_en/content_en).
- Gambar artikel **selalu lewat `App\Support\ImageUpload`**: `store()` (digambar ulang GD: EXIF/GPS terbuang, orientasi
  diterapkan), `url()` (dipakai `Article::image_url`), `delete()` — jangan `->store()` / `Storage::delete()` langsung.
  `ARTICLE_IMAGE_DRIVER=cloudinary` → piksel hasil gambar ulang diunggah ke Cloudinary folder `cmi-artikel/articles`,
  kolom `image` = `cloudinary:<public_id>`, alamat CDN `f_auto/q_auto` dihitung tanpa internet; tanpa awalan = file di
  disk `public` (`local`, bawaan penilai/test, butuh `storage:link`). SDK resmi `cloudinary/cloudinary_php` (paket
  `cloudinary-labs/cloudinary-laravel` belum mendukung Laravel 13), di-bind di container (`AppServiceProvider`) agar test
  memakai tiruan — **test tidak boleh menghubungi Cloudinary**. `CLOUDINARY_URL` berisi api_secret → hanya di `.env`,
  dibaca lewat `config('services.cloudinary.url')`. File lama dihapus saat diganti / `remove_image` / artikel dihapus
  (gagal hapus di Cloudinary hanya dicatat log). Gambar lokal lama dipindah dengan `php artisan images:to-cloudinary`.
- Lupa Password sengaja menjawab sama untuk email terdaftar/tidak — jangan kembalikan pesan "email tidak ditemukan".
- Notifikasi lewat `App\Support\ArticleNotifier` (update hanya bila `wasChanged('status')`). Kunjungan lewat
  `App\Support\ArticleViewCounter` dengan `DB::table()->increment()` (agar `updated_at` tidak berubah).
- Kontak klinik di `config/clinic.php` (`CLINIC_*`): WhatsApp **tombol Konsultasi 0821-2159-0000**
  (`whatsapp_consultation_url`) berbeda dengan **WhatsApp footer 0811 9161 166** (`whatsapp_url`) — pilihan user.
- SEO terpusat di `App\Support\Seo` + `partials/seo`; JSON-LD wajib `Seo::JSON_FLAGS` (`JSON_HEX_TAG`).
- Bahasa: `config('app.supported_locales')`, `SetLocale` (sesi `locale`), `POST /bahasa` (`LocaleController`),
  `partials/locale-menu`. Navbar tamu hanya tombol **Masuk** (tombol Daftar dihapus atas permintaan user).
- Keamanan: autentikasi **session, bukan JWT** (tanpa API; kalau kelak butuh API → Sanctum). `SecurityHeaders` (global)
  & `AuthenticateSession` (grup `web`) di `bootstrap/app.php` — jangan dilepas. Halaman yang dimuat dalam iframe situs
  lain akan diblokir (`X-Frame-Options: SAMEORIGIN`); CSP belum ada, uji skrip/font/Vite bila menambahkannya.

## Konvensi kode
- Blade: selalu `{{ }}`. `{!! !!}` **hanya** untuk accessor HTML tersanitasi isi artikel di dalam `.article-content`:
  `content_html`, `content_en_html`, `display_content_html`. Jangan pernah `{!! $article->content !!}` / `content_en`.
- Tailwind: **jangan merangkai nama class dinamis** (`text-{{ $color }}-600`); tulis class lengkap.
- Pagination: view Tailwind bawaan (jangan `Paginator::useBootstrapFive()`).
- Form upload `enctype="multipart/form-data"`; form PUT/DELETE `@csrf` + `@method`. Konfirmasi hapus:
  `data-confirm` + `confirm(this.dataset.confirm)`.
- Ikon **hanya** lewat `<x-icon name=".." size="size-N">` (peta di `components/icon.blade.php`); jangan menulis class
  `fa-*` langsung. Ukuran ikon = font-size.
- Huruf: `font-sans` = Inter (UI & judul), `font-article` = Roboto (isi artikel & judul artikel di daftar). Tanggal
  lewat `<x-date>`. Validasi lewat Form Request dengan pesan Bahasa Indonesia.
- **Dua bahasa (seluruh situs, termasuk area penulis/admin):** SEMUA teks antarmuka baru WAJIB lewat
  `__('Teks Indonesia')` (kunci = teks Indonesia) + terjemahan di `lang/en.json`; teks dengan angka →
  `trans_choice(...)` + `Number::format()`, dan kuncinya juga di `lang/id.json`. Teks untuk JS dikirim lewat `data-*`.
  Nama kolom validasi (`attributes()` Form Request / `validate(attributes:)`) & pesan flash juga lewat `__()`.
  Notifikasi: `message()` = templat Indonesia + `replace()` (nilai `:nama`), diterjemahkan saat tampil di
  `components/notification-item` — jangan merangkai kalimat jadi di notifikasi. `LocaleTest` memindai semua view,
  `app/Http`, `bootstrap/app.php`, dan templat notifikasi; gagal bila ada kunci tanpa terjemahan.
- Ikuti gaya kode sekitar (komentar Bahasa Indonesia secukupnya, penamaan Laravel).

## Keputusan produk (jangan dilanggar tanpa izin user)
- Desain **korporat & polos**: hero publik biru tua (`bg-brand-950`), navbar `#E3F2FD`, footer `#066AC9`; tanpa
  hiasan "seperti AI" (pola garis, glow berlebihan).
- Pembaca usia 25+ (banyak orang tua): huruf ≥ 14px, teks minimal `text-gray-500` (bukan `gray-400`), kontras ≥ 4,5:1,
  area sentuh 44px, ikon disertai teks, tanggal pasti, pop-up gagal tidak hilang sendiri.
- UI **tanpa istilah teknis** (INNER JOIN, SQL, `user_id`) — pengguna nakes & editor konten; penjelasan teknis di README.
- Tidak ada "waktu baca / menit baca" di mana pun. Semua teks, contoh, dan SEO bertema jantung.

## Jebakan teknis yang sudah ditemukan
- CSS Font Awesome & trix.css **tidak berlayer** → menimpa utilitas Tailwind: sembunyikan ikon dengan atribut `hidden`,
  CSS editor di luar `@layer`, selector toolbar Trix diberi awalan `form`; jangan beri class `.trix-content`.
- Blade: satu directive per baris (`@endif@endforeach` menempel tidak dikompilasi).
- Kolom grid berisi konten panjang perlu `min-w-0`; pembungkus `overflow-x-auto` wajib `relative` (teks `sr-only`
  bisa melebarkan halaman di ponsel).
- PHP 8.5 menolak offset array `null` (juga kunci `array_key_exists`) → akses null-safe / cast `(string)`.
- `trans_choice()` memakai bahasa cadangan (`en`) bila kunci tidak ada di bahasa aktif → halaman Indonesia tampil
  Inggris. Kunci `trans_choice` didaftarkan juga di `lang/id.json` (nilai sama). `__()` biasa tidak begitu.
- Middleware yang harus jalan sebelum route model binding (mis. `SetLocale`) → `appendToPriorityList(after:
  StartSession::class, …)`; URL tak terdaftar tidak lewat grup `web` kecuali ada `Route::fallback`.
- Laravel 13: middleware CSRF = `PreventRequestForgery`; request `Sec-Fetch-Site: same-origin` lolos tanpa token
  (419 diuji dengan curl / memalsukan instance di test).
- `env()` membaca `$_SERVER`/`$_ENV` lebih dulu (dari `.env`) → test yang menimpa env harus menimpa ketiganya.
- `public/robots.txt` statis akan menang atas route `/robots.txt` — jangan dibuat lagi.
- `throttle:X,Y` TANPA nama = satu penghitung bersama per IP/akun untuk SEMUA rute → selalu beri nama ketiga
  (`throttle:10,1,comments`) atau pakai limiter bernama (`auth-attempts`, `writes` di AppServiceProvider).
- LIKE dengan input pengunjung: pakai `Article::likePattern()` + `LIKE ? ESCAPE '!'` (backslash beda di MySQL & SQLite).
- Uji E2E Chrome headless: klik CDP kadang hilang setelah `form.submit()` (bukan bug aplikasi) → pakai `element.click()`.
- Alat audit kontras salah membaca latar `oklch()` & teks di atas gambar/gradien — cek manual.
