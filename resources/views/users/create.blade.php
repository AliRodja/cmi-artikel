@extends('layouts.app')

@section('title', __('Tambah Pengguna'))

@section('hero')
    <x-page-header :title="__('Tambah Pengguna')" :back-url="route('users.index')" :back-label="__('Kelola Pengguna')" />
@endsection

@section('content')
    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        @include('users._form', ['submitLabel' => __('Simpan Pengguna')])
    </form>
@endsection
