<?php

namespace App\Models;

use App\Support\ArticleContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 0;

    public const STATUS_PUBLISHED = 1;

    /** Diajukan penulis, menunggu admin menerbitkan atau mengembalikannya. */
    public const STATUS_REVIEW = 2;

    /**
     * Kolom minimum untuk daftar/kartu artikel publik (live search, sidebar, 404). title_en & content_en wajib ikut:
     * tanpa keduanya display_title selalu jatuh ke versi Indonesia walau pengunjung memilih English.
     */
    public const LIST_COLUMNS = ['id', 'title', 'title_en', 'content_en', 'slug', 'image', 'created_at'];

    protected $fillable = [
        'user_id', 'medical_reviewer_id', 'title', 'slug', 'content', 'sources', 'image', 'status', 'review_note',
        'focus_keyword', 'seo_title', 'seo_description',
        // Versi Bahasa Inggris (opsional, diterjemahkan penulis).
        'title_en', 'content_en', 'seo_title_en', 'seo_description_en',
    ];

    protected $casts = [
        'status' => 'integer',
        'view_count' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Dokter yang meninjau isi artikel ("Ditinjau secara medis oleh ..."). */
    public function medicalReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'medical_reviewer_id');
    }

    /** Sumber rujukan per baris, baris kosong dibuang. */
    public function getSourceListAttribute(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->sources))));
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** Riwayat kunjungan halaman publik (lihat App\Support\ArticleViewCounter). */
    public function views(): HasMany
    {
        return $this->hasMany(ArticleView::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeInReview(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REVIEW);
    }

    /**
     * Cari di judul ATAU isi. Dibungkus where(closure) → "(title LIKE ? OR content LIKE ?)", agar OR tidak
     * "lolos" dari filter lain (mis. status = Published). Dipakai beranda & live search navbar.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $pattern = static::likePattern($term);

        // Termasuk versi Bahasa Inggris, agar pengunjung bisa mencari dengan kata Inggris juga.
        return $query->where(fn (Builder $query) => $query
            ->whereRaw("title LIKE ? ESCAPE '!'", [$pattern])
            ->orWhereRaw("content LIKE ? ESCAPE '!'", [$pattern])
            ->orWhereRaw("title_en LIKE ? ESCAPE '!'", [$pattern])
            ->orWhereRaw("content_en LIKE ? ESCAPE '!'", [$pattern]));
    }

    /**
     * "%kata%" untuk LIKE, dengan % dan _ ketikan pengunjung dianggap huruf biasa (bukan wildcard), mis. cari "50%".
     * Pakai ESCAPE '!' karena perilakunya sama di MySQL dan SQLite (backslash berbeda di keduanya).
     */
    public static function likePattern(string $term): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';
    }

    /**
     * Artikel Terpopuler (beranda & halaman detail): Published, sudah dilihat minimal
     * config('clinic.popular_min_views') kali, urut jumlah pembaca terbanyak.
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->published()
            ->where('view_count', '>=', config('clinic.popular_min_views'))
            ->orderByDesc('view_count')
            ->latest();
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isInReview(): bool
    {
        return $this->status === self::STATUS_REVIEW;
    }

    /**
     * Status yang boleh dipilih $user di form tulis/edit. Dipakai oleh validasi
     * (ArticleRequest) dan oleh form, sehingga keduanya tidak pernah berbeda.
     * Penulis tidak bisa menerbitkan sendiri; ia mengajukan review ke admin.
     */
    public static function allowedStatusesFor(User $user, ?self $article = null): array
    {
        if (! $user->isAdmin()) {
            return [self::STATUS_DRAFT, self::STATUS_REVIEW];
        }

        // Admin yang mengedit artikel di antrean boleh membiarkannya tetap menunggu review.
        return $article?->isInReview()
            ? [self::STATUS_DRAFT, self::STATUS_REVIEW, self::STATUS_PUBLISHED]
            : [self::STATUS_DRAFT, self::STATUS_PUBLISHED];
    }

    protected static function booted(): void
    {
        static::creating(function (Article $article) {
            if (blank($article->slug)) {
                $article->slug = static::generateUniqueSlug($article->title);
            }
        });
    }

    public static function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    /**
     * Isi selalu disanitasi sebelum masuk database, dari jalur mana pun
     * (form, seeder, tinker) selama lewat Eloquent.
     */
    public function setContentAttribute(?string $value): void
    {
        $this->attributes['content'] = ArticleContent::toHtml($value);
    }

    /**
     * HTML yang siap ditampilkan dengan {!! !!}. Disanitasi ulang saat dibaca
     * sebagai lapisan kedua, untuk data yang masuk tanpa Eloquent.
     */
    public function getContentHtmlAttribute(): string
    {
        return ArticleContent::toHtml($this->content);
    }

    /** Isi Bahasa Inggris disanitasi sama seperti isi Indonesia; editor kosong disimpan sebagai null. */
    public function setContentEnAttribute(?string $value): void
    {
        $this->attributes['content_en'] = ArticleContent::toPlainText((string) $value) === ''
            ? null
            : ArticleContent::toHtml($value);
    }

    public function getContentEnHtmlAttribute(): string
    {
        return ArticleContent::toHtml($this->content_en);
    }

    /** Versi Inggris dianggap ada bila judul DAN isinya terisi (ArticleRequest mewajibkan keduanya berpasangan). */
    public function hasEnglish(): bool
    {
        return filled($this->title_en) && ArticleContent::toPlainText((string) $this->content_en) !== '';
    }

    /**
     * Versi yang tampil untuk pengunjung: Inggris bila antarmuka sedang English DAN penulis sudah menerjemahkan,
     * selain itu Indonesia. Dipakai halaman publik lewat accessor display_* di bawah.
     */
    public function showsEnglish(): bool
    {
        return app()->getLocale() === 'en' && $this->hasEnglish();
    }

    /** Kode bahasa versi yang tampil, untuk atribut lang="..." (lafal pembaca layar). */
    public function getDisplayLocaleAttribute(): string
    {
        return $this->showsEnglish() ? 'en' : 'id';
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->showsEnglish() ? $this->title_en : $this->title;
    }

    /** HTML tersanitasi versi yang tampil, aman untuk {!! !!} (lihat getContentHtmlAttribute). */
    public function getDisplayContentHtmlAttribute(): string
    {
        return $this->showsEnglish() ? $this->content_en_html : $this->content_html;
    }

    public function getDisplayExcerptAttribute(): string
    {
        return Str::limit(ArticleContent::toPlainText($this->showsEnglish() ? $this->content_en : $this->content), 140);
    }

    /** Judul di hasil Google: Judul SEO bila diisi, selain itu judul artikel. */
    public function getMetaTitleAttribute(): string
    {
        if ($this->showsEnglish()) {
            return $this->seo_title_en ?: $this->title_en;
        }

        return $this->seo_title ?: $this->title;
    }

    /** Penjelasan di hasil Google: Deskripsi SEO bila diisi, selain itu ringkasan isi artikel. */
    public function getMetaDescriptionAttribute(): string
    {
        if ($this->showsEnglish()) {
            return $this->seo_description_en ?: Str::limit(ArticleContent::toPlainText($this->content_en), 155);
        }

        return $this->seo_description ?: Str::limit(ArticleContent::toPlainText($this->content), 155);
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(ArticleContent::toPlainText($this->content), 140);
    }

    public function getWordCountAttribute(): int
    {
        return str_word_count(ArticleContent::toPlainText($this->content));
    }
}
