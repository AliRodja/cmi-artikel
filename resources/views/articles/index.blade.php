@extends('layouts.app')

@section('title', 'Artikel Saya')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Artikel Saya</h1>
            <p class="text-sm text-gray-500">Kelola semua artikel yang Anda tulis.</p>
        </div>
        <a href="{{ route('articles.create') }}" class="btn btn-primary">+ Tulis Artikel</a>
    </div>

    <form action="{{ route('articles.index') }}" method="GET" class="card mb-6 flex flex-wrap gap-3 p-4">
        <input type="search" name="search" value="{{ $search }}" placeholder="Cari judul..." class="form-input flex-1">
        <select name="status" class="form-input w-auto">
            <option value="">Semua status</option>
            <option value="1" @selected($status === '1')>Published</option>
            <option value="0" @selected($status === '0')>Draft</option>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        @if ($search !== '' || filled($status))
            <a href="{{ route('articles.index') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>

    <div class="card overflow-hidden">
        @if ($articles->isEmpty())
            <p class="px-4 py-12 text-center text-sm text-gray-500">
                Tidak ada artikel. <a href="{{ route('articles.create') }}" class="link">Tulis artikel baru</a>.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="th">Judul</th>
                            <th class="th">Status</th>
                            <th class="th">Dibuat</th>
                            <th class="th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($articles as $article)
                            <tr>
                                <td class="td font-medium text-gray-900">
                                    <a href="{{ route('articles.show', $article) }}" class="hover:text-brand-700">{{ $article->title }}</a>
                                </td>
                                <td class="td"><x-status-badge :status="$article->status" /></td>
                                <td class="td whitespace-nowrap text-gray-500">{{ $article->created_at->format('d M Y') }}</td>
                                <td class="td">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('articles.edit', $article) }}" class="btn btn-secondary btn-sm">Edit</a>
                                        <form action="{{ route('articles.destroy', $article) }}" method="POST"
                                              onsubmit="return confirm('Hapus artikel ini? Tindakan ini tidak bisa dibatalkan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">{{ $articles->links() }}</div>
@endsection
