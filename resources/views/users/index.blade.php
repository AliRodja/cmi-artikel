@extends('layouts.app')

@use('App\Models\User')

@section('title', 'Kelola Pengguna')

@section('hero')
    <x-page-header title="Kelola Pengguna" subtitle="Atur akun dan peran admin, penulis, serta pembaca.">
        <x-slot:actions>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <x-icon name="user-plus" size="size-4" /> Tambah Pengguna
            </a>
        </x-slot:actions>
    </x-page-header>
@endsection

@section('content')
    @php
        $tabs = [
            ['label' => 'Semua', 'value' => null, 'count' => $roleCounts->sum()],
            ['label' => 'Admin', 'value' => User::ROLE_ADMIN, 'count' => $roleCounts->get(User::ROLE_ADMIN, 0)],
            ['label' => 'Penulis', 'value' => User::ROLE_PENULIS, 'count' => $roleCounts->get(User::ROLE_PENULIS, 0)],
            ['label' => 'Pembaca', 'value' => User::ROLE_PEMBACA, 'count' => $roleCounts->get(User::ROLE_PEMBACA, 0)],
        ];
        $activeRole = in_array($role, User::ROLES, true) ? $role : null;
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <nav class="flex flex-wrap gap-1 rounded-lg bg-gray-100 p-1" aria-label="Filter peran">
            @foreach ($tabs as $tab)
                <a href="{{ route('users.index', array_filter(['role' => $tab['value'], 'search' => $search], 'filled')) }}"
                   @class([
                       'flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition',
                       'bg-white text-gray-900 shadow-sm' => $activeRole === $tab['value'],
                       'text-gray-600 hover:text-gray-900' => $activeRole !== $tab['value'],
                   ])>
                    {{ $tab['label'] }}
                    <span class="rounded-full bg-gray-200/70 px-1.5 text-xs text-gray-600">{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </nav>

        <form action="{{ route('users.index') }}" method="GET" class="flex w-full gap-2 sm:w-auto">
            @if ($activeRole !== null)
                <input type="hidden" name="role" value="{{ $activeRole }}">
            @endif
            <div class="relative w-full sm:w-72">
                <x-icon name="magnifying-glass" size="size-4" class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-gray-500" />
                <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau email..." class="form-input pl-9">
            </div>
            <button type="submit" class="btn btn-secondary">Cari</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        @if ($users->isEmpty())
            <div class="px-6 py-14 text-center">
                <p class="font-semibold text-gray-900">Tidak ada pengguna yang cocok</p>
                <p class="mt-1 text-sm text-gray-500">Coba kata kunci atau filter lain.</p>
                <a href="{{ route('users.index') }}" class="btn btn-secondary mt-5">Reset filter</a>
            </div>
        @else
            <div class="relative overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="th">Pengguna</th>
                            <th class="th">Peran</th>
                            <th class="th text-center">Artikel</th>
                            <th class="th">Bergabung</th>
                            <th class="th text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="td">
                                    <div class="flex min-w-56 items-center gap-3">
                                        <x-avatar :name="$user->name" />
                                        <div class="min-w-0">
                                            <p class="flex items-center gap-2 font-semibold text-gray-900">
                                                <span class="truncate">{{ $user->name }}</span>
                                                @if ($user->is(auth()->user()))
                                                    <span class="badge bg-amber-50 text-amber-700">Anda</span>
                                                @endif
                                            </p>
                                            <p class="truncate text-xs text-gray-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="td"><x-role-badge :user="$user" /></td>
                                <td class="td text-center text-gray-700">{{ $user->articles_count }}</td>
                                <td class="td whitespace-nowrap text-gray-500">{{ $user->created_at->translatedFormat('d M Y') }}</td>
                                <td class="td">
                                    <div class="flex justify-end gap-1">
                                        <a href="{{ route('users.edit', $user) }}" title="Edit"
                                           class="rounded-md p-2 text-gray-500 hover:bg-brand-50 hover:text-brand-700">
                                            <x-icon name="pencil-square" size="size-4" />
                                            <span class="sr-only">Edit {{ $user->name }}</span>
                                        </a>
                                        @can('delete', $user)
                                            {{-- Pesan konfirmasi lewat data-* agar nama berisi tanda kutip tidak merusak JavaScript. --}}
                                            <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                  data-confirm="Hapus akun {{ $user->name }}? Artikelnya tetap ada tanpa penulis, sedangkan komentar dan Baca Nanti miliknya ikut terhapus."
                                                  onsubmit="return confirm(this.dataset.confirm)">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus" class="cursor-pointer rounded-md p-2 text-gray-500 hover:bg-red-50 hover:text-red-600">
                                                    <x-icon name="trash" size="size-4" />
                                                    <span class="sr-only">Hapus {{ $user->name }}</span>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
@endsection
