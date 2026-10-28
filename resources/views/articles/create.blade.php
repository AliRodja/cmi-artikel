@extends('layouts.app')

@section('title', 'Tulis Artikel')

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('articles.index') }}" class="link text-sm">← Kembali ke Artikel Saya</a>
        <h1 class="mt-4 mb-6 text-2xl font-bold text-gray-900">Tulis Artikel</h1>

        <form action="{{ route('articles.store') }}" method="POST" enctype="multipart/form-data" class="card p-6">
            @csrf

            @include('articles._form')

            <div class="flex justify-end gap-2">
                <a href="{{ route('articles.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Artikel</button>
            </div>
        </form>
    </div>
@endsection
