<?php

namespace App\Models;

use App\Support\ArticleContent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 0;

    public const STATUS_PUBLISHED = 1;

    /** Diajukan penulis, menunggu admin menerbitkan atau mengembalikannya. */
    public const STATUS_REVIEW = 2;

    protected $fillable = ['user_id', 'title', 'slug', 'content', 'image', 'status', 'review_note'];

    protected $casts = [
        'status' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function getExcerptAttribute(): string
    {
        return Str::limit(ArticleContent::toPlainText($this->content), 140);
    }

    public function getReadingTimeAttribute(): int
    {
        return max(1, (int) ceil(str_word_count(ArticleContent::toPlainText($this->content)) / 200));
    }
}
