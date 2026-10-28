@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('hero')
    <x-page-header title="Tambah Pengguna" :back-url="route('users.index')" back-label="Kelola Pengguna" />
@endsection

@section('content')
    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        @include('users._form', ['submitLabel' => 'Simpan Pengguna'])
    </form>
@endsection
