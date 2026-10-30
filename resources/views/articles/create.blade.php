@extends('layouts.app')

@section('title', __('Tulis Artikel'))

@section('hero')
    <x-page-header :title="__('Tulis Artikel')" :back-url="route('articles.index')" :back-label="__('Artikel Saya')" />
@endsection

@section('content')
    <form action="{{ route('articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @include('articles._form', [
            'submitLabel' => __('Simpan Artikel'),
            'cancelUrl' => route('articles.index'),
        ])
    </form>
@endsection
