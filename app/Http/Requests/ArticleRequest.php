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
            // Nama kolom di pesan validasi, ikut bahasa yang dipilih (lang/en.json).
            'title' => __('judul'),
            'content' => __('isi artikel'),
            'sources' => __('sumber rujukan'),
            'focus_keyword' => __('kata kunci utama'),
            'seo_title' => __('judul SEO'),
            'seo_description' => __('deskripsi SEO'),
            'medical_reviewer_id' => __('peninjau medis'),
            'image' => __('gambar'),
            'status' => __('status'),
            'remove_image' => __('hapus gambar'),
        ];
    }

    public function messages(): array
    {
        return [
            'medical_reviewer_id.prohibited' => __('Peninjau medis hanya dapat ditentukan oleh admin.'),
        ];
    }
}
