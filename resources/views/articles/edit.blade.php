@extends('layouts.app')

@section('title', 'Edit Artikel')

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('articles.show', $article) }}" class="link text-sm">← Kembali ke detail artikel</a>
        <h1 class="mt-4 mb-6 text-2xl font-bold text-gray-900">Edit Artikel</h1>

        <form action="{{ route('articles.update', $article) }}" method="POST" enctype="multipart/form-data" class="card p-6">
            @csrf
            @method('PUT')

            @include('articles._form', ['article' => $article])

            <div class="flex justify-end gap-2">
                <a href="{{ route('articles.show', $article) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
@endsection
