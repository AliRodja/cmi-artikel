@extends('layouts.app')

@section('title', 'Edit Artikel')

@section('hero')
    <x-page-header title="Edit Artikel" :subtitle="$article->title"
                   :back-url="route('articles.show', $article)" back-label="Detail artikel" />
@endsection

@section('content')
    <form action="{{ route('articles.update', $article) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('articles._form', [
            'article' => $article,
            'submitLabel' => 'Simpan Perubahan',
            'cancelUrl' => route('articles.show', $article),
        ])
    </form>
@endsection
