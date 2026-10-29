<?php

namespace App\Http\Requests;

use App\Models\Article;
use App\Models\User;
use App\Support\ArticleContent;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => [
                'bail',
                'required',
                'string',
                // Editor kosong tetap bisa mengirim "<div><br></div>", jadi cek teksnya.
                function (string $attribute, mixed $value, Closure $fail) {
                    if (ArticleContent::toPlainText($value) === '') {
                        $fail(__('validation.required', ['attribute' => $this->attributes()[$attribute]]));
                    }
                },
            ],
            'category' => ['required', Rule::in(array_keys(Article::CATEGORIES))],
            'sources' => ['nullable', 'string', 'max:5000'],
            // Optimasi SEO (opsional). Batas mengikuti panjang yang ditampilkan Google.
            'focus_keyword' => ['nullable', 'string', 'max:100'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            // Peninjau medis hanya ditentukan admin; penulis yang menyisipkan field ini lewat DevTools ditolak.
            'medical_reviewer_id' => $this->user()->isAdmin()
                ? ['nullable', 'integer', Rule::exists('users', 'id')->whereIn('role', [User::ROLE_ADMIN, User::ROLE_PENULIS])]
                : ['prohibited'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            // Penulis yang memalsukan status=1 lewat DevTools tetap ditolak di sini.
            'status' => ['required', Rule::in(Article::allowedStatusesFor($this->user(), $this->route('article')))],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'judul',
            'content' => 'isi artikel',
            'category' => 'jenis artikel',
            'sources' => 'sumber rujukan',
            'focus_keyword' => 'kata kunci utama',
            'seo_title' => 'judul SEO',
            'seo_description' => 'deskripsi SEO',
            'medical_reviewer_id' => 'peninjau medis',
            'image' => 'gambar',
            'status' => 'status',
            'remove_image' => 'hapus gambar',
        ];
    }

    public function messages(): array
    {
        return [
            'medical_reviewer_id.prohibited' => 'Peninjau medis hanya dapat ditentukan oleh admin.',
        ];
    }
}
