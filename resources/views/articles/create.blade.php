@extends('layouts.app')

@section('title', 'Tulis Artikel')

@section('hero')
    <x-page-header title="Tulis Artikel" :back-url="route('articles.index')" back-label="Artikel Saya" />
@endsection

@section('content')
    <form action="{{ route('articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @include('articles._form', [
            'submitLabel' => 'Simpan Artikel',
            'cancelUrl' => route('articles.index'),
        ])
    </form>
@endsection
