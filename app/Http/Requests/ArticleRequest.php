<?php

namespace App\Http\Requests;

use App\Models\Article;
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
            'image' => 'gambar',
            'status' => 'status',
            'remove_image' => 'hapus gambar',
        ];
    }
}
