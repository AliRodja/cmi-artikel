@extends('layouts.app')

@section('title', __('Edit Artikel'))

@section('hero')
    <x-page-header :title="__('Edit Artikel')" :subtitle="$article->title"
                   :back-url="route('articles.show', $article)" :back-label="__('Detail artikel')" />
@endsection

@section('content')
    @if ($article->review_note)
        <x-review-note :note="$article->review_note" class="mb-6" />
    @endif

    <form action="{{ route('articles.update', $article) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('articles._form', [
            'article' => $article,
            'submitLabel' => __('Simpan Perubahan'),
            'cancelUrl' => route('articles.show', $article),
        ])
    </form>
@endsection
