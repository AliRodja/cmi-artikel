<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu baris riwayat kunjungan artikel (tabel article_views). */
class ArticleView extends Model
{
    use HasFactory;

    // Hanya punya waktu kunjungan (viewed_at), tanpa updated_at.
    public const CREATED_AT = 'viewed_at';

    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'visitor'];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
