@extends('layouts.app')

@section('title', $article->title)

@section('hero')
    <section class="bg-brand-950">
        <div class="container-page py-12 sm:py-16">
            <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-2 text-xs font-medium text-brand-300">
                <a href="{{ route('home') }}" class="hover:text-white">Beranda</a>
                <span>/</span>
                <a href="{{ route('home') }}" class="hover:text-white">Berita &amp; Artikel</a>
                @if ($article->category_label)
                    <span>/</span>
                    <a href="{{ route('home', ['jenis' => $article->category]) }}" class="hover:text-white">{{ $article->category_label }}</a>
                @endif
            </nav>

            <x-category-badge :article="$article" class="mt-5" />

            <h1 class="mt-4 max-w-4xl text-3xl leading-tight font-extrabold tracking-tight text-white sm:text-4xl lg:text-5xl">
                {{ $article->title }}
            </h1>

            <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-brand-200">
                <div class="flex items-center gap-3">
                    <x-avatar :name="$article->user?->name" size="md" tone="dark" />
                    <div class="leading-tight">
                        <p class="font-semibold text-white">{{ $article->user?->name ?? 'Anonim' }}</p>
                        <p class="text-xs">Penulis</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5">
                    <x-icon name="calendar" size="size-4" class="text-brand-300" />
                    {{ $article->created_at->translatedFormat('l, d F Y') }}
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <x-icon name="clock" size="size-4" class="text-brand-300" />
                    {{ $article->reading_time }} menit baca
                </span>
            </div>
        </div>
    </section>
@endsection

@section('content')
    @php
        // Link bagikan. http_build_query() meng-encode judul & URL agar aman dipakai di query string.
        $shareUrl = route('public.articles.show', $article);
        $shareLinks = [
            [
                'label' => 'WhatsApp',
                'icon' => 'whatsapp',
                'href' => 'https://wa.me/?'.http_build_query(['text' => $article->title."\n".$shareUrl]),
                'class' => 'bg-[#25D366] hover:bg-[#1DA851]',
            ],
            [
                'label' => 'X',
                'icon' => 'x-twitter',
                'href' => 'https://x.com/intent/post?'.http_build_query(['text' => $article->title, 'url' => $shareUrl]),
                'class' => 'bg-gray-900 hover:bg-black',
            ],
            [
                'label' => 'Facebook',
                'icon' => 'facebook',
                'href' => 'https://www.facebook.com/sharer/sharer.php?'.http_build_query(['u' => $shareUrl]),
                'class' => 'bg-[#1877F2] hover:bg-[#0F62D0]',
            ],
        ];
    @endphp

    {{-- Tiga kolom di layar lebar: bagikan | isi artikel | sidebar. Di ponsel tersusun ke bawah. --}}
    <div class="grid items-start gap-8 lg:grid-cols-[4.5rem_minmax(0,1fr)_18rem] xl:gap-10">
        {{-- Kolom 1: bagikan. Di layar lebar ikon tersusun ke bawah dan ikut menempel saat scroll. --}}
        <aside aria-label="Bagikan artikel" class="lg:sticky lg:top-24">
            <div class="flex flex-wrap items-center gap-3 lg:flex-col">
                <p class="flex items-center gap-1.5 text-xs font-semibold tracking-wider text-gray-500 uppercase lg:flex-col lg:gap-1">
                    <x-icon name="share" size="size-4" class="text-brand-600" /> Bagikan
                </p>
                @foreach ($shareLinks as $link)
                    <a href="{{ $link['href'] }}" target="_blank" rel="noopener noreferrer" title="Bagikan ke {{ $link['label'] }}"
                       class="flex size-11 items-center justify-center rounded-full text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $link['class'] }}">
                        <x-icon :name="$link['icon']" size="size-5" />
                        <span class="sr-only">Bagikan ke {{ $link['label'] }}</span>
                    </a>
                @endforeach
                <button type="button" id="copy-link" data-url="{{ $shareUrl }}" title="Salin tautan"
                        class="flex size-11 cursor-pointer items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:-translate-y-0.5 hover:text-brand-700 hover:shadow-md">
                    <x-icon name="link" size="size-4" id="copy-link-icon" />
                    {{-- Atribut hidden (bukan class): CSS Font Awesome yang tak berlayer menimpa class "hidden" Tailwind. --}}
                    <x-icon name="check" size="size-4" id="copy-link-done" class="text-green-600" hidden />
                    <span id="copy-link-label" class="sr-only">Salin tautan</span>
                </button>
            </div>
        </aside>

        {{-- Kolom 2: isi artikel & komentar. --}}
        <div class="min-w-0">
            @if ($article->image_url)
                <img src="{{ $article->image_url }}" alt="{{ $article->title }}"
                     class="mb-8 aspect-[16/9] w-full rounded-2xl object-cover shadow-md">
            @endif

            <div class="card px-6 py-4 sm:px-10 sm:py-6">
                {{-- content_html sudah disanitasi (App\Support\ArticleContent), aman untuk {!! !!}. --}}
                <div class="article-content">{!! $article->content_html !!}</div>
            </div>

            <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('home') }}" class="link inline-flex items-center gap-1.5 text-sm">
                    <x-icon name="arrow-left" size="size-4" /> Kembali ke Berita &amp; Artikel
                </a>
                @auth
                    <form action="{{ route('bookmarks.toggle', $article) }}" method="POST">
                        @csrf
                        <button type="submit" @class([
                            'btn btn-sm',
                            'btn-primary' => $isBookmarked,
                            'btn-secondary' => ! $isBookmarked,
                        ])>
                            <x-icon :name="$isBookmarked ? 'bookmark-solid' : 'bookmark'" size="size-3" />
                            {{ $isBookmarked ? 'Tersimpan' : 'Simpan' }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-secondary btn-sm">
                        <x-icon name="bookmark" size="size-3" /> Simpan
                    </a>
                @endauth
            </div>

            {{-- Komentar --}}
            <section id="komentar" class="mt-12 scroll-mt-24">
                <h2 class="flex items-center gap-2 text-xl font-bold text-gray-900">
                    <x-icon name="comments" class="text-brand-600" /> Komentar
                    <span class="text-base font-medium text-gray-400">({{ $article->comments->count() }})</span>
                </h2>

                @auth
                    <form action="{{ route('comments.store', $article) }}" method="POST" class="card mt-5 p-4">
                        @csrf
                        <div class="flex gap-3">
                            <x-avatar :name="auth()->user()->name" />
                            <div class="min-w-0 flex-1">
                                <label for="body" class="sr-only">Tulis komentar</label>
                                <textarea id="body" name="body" rows="3" maxlength="2000" required
                                          placeholder="Tulis komentar atau pertanyaan Anda..."
                                          @class(['form-input', 'form-input-error' => $errors->has('body')])>{{ old('body') }}</textarea>
                                @error('body')
                                    <p class="form-error">{{ $message }}</p>
                                @enderror
                                <div class="mt-3 flex justify-end">
                                    <button type="submit" class="btn btn-primary btn-sm">Kirim Komentar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="card mt-5 px-5 py-4 text-sm text-gray-600">
                        <a href="{{ route('login') }}" class="link">Masuk</a> atau
                        <a href="{{ route('register') }}" class="link">daftar</a> untuk ikut berkomentar.
                    </div>
                @endauth

                @if ($article->comments->isEmpty())
                    <p class="mt-6 text-sm text-gray-500">Belum ada komentar. Jadilah yang pertama berkomentar.</p>
                @else
                    <ul class="mt-6 space-y-4">
                        @foreach ($article->comments as $comment)
                            <li class="flex gap-3">
                                <x-avatar :name="$comment->user->name" />
                                <div class="min-w-0 flex-1 rounded-xl border border-gray-200 bg-white px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <p class="text-sm font-semibold text-gray-900">{{ $comment->user->name }}</p>
                                        @if ($comment->user_id === $article->user_id)
                                            <span class="badge bg-brand-50 text-brand-700">Penulis artikel</span>
                                        @elseif ($comment->user->isAdmin())
                                            <span class="badge bg-gray-100 text-gray-600">Admin</span>
                                        @endif
                                        <span class="text-xs text-gray-400">· {{ $comment->created_at->diffForHumans() }}</span>

                                        @can('delete', $comment)
                                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="ml-auto"
                                                  onsubmit="return confirm('Hapus komentar ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Hapus komentar" class="cursor-pointer rounded-md p-1 text-gray-400 hover:bg-red-50 hover:text-red-600">
                                                    <x-icon name="trash" size="size-3" />
                                                    <span class="sr-only">Hapus komentar</span>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                    {{-- Teks biasa ({{ }} = di-escape), baris baru dipertahankan dengan whitespace-pre-line. --}}
                                    <p class="mt-1 text-sm whitespace-pre-line text-gray-700">{{ $comment->body }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        {{-- Kolom 3: artikel lainnya, jenis artikel, ajakan konsultasi. --}}
        <aside class="min-w-0 space-y-6">
            @if ($relatedArticles->isNotEmpty())
                <section class="card p-5">
                    <h2 class="font-bold text-gray-900">Artikel Lainnya</h2>
                    <ul class="mt-2 divide-y divide-gray-100">
                        @foreach ($relatedArticles as $related)
                            <li>
                                <a href="{{ route('public.articles.show', $related) }}" class="group flex gap-3 py-3">
                                    <x-article-thumb :article="$related" class="size-16" />
                                    <div class="min-w-0">
                                        <x-category-badge :article="$related" class="mb-1" />
                                        <p class="line-clamp-2 text-sm leading-snug font-semibold text-gray-900 group-hover:text-brand-700">{{ $related->title }}</p>
                                        <p class="mt-1 text-xs text-gray-500">{{ $related->created_at->translatedFormat('d M Y') }}</p>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="card p-5">
                <h2 class="font-bold text-gray-900">Jenis Artikel</h2>
                <ul class="mt-3 space-y-1">
                    @foreach (\App\Models\Article::CATEGORIES as $value => $label)
                        <li>
                            <a href="{{ route('home', ['jenis' => $value]) }}" @class([
                                'flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition hover:bg-brand-50 hover:text-brand-800',
                                'bg-brand-50 text-brand-800' => $article->category === $value,
                                'text-gray-700' => $article->category !== $value,
                            ])>
                                <span class="flex items-center gap-2">
                                    <x-icon name="tag" size="size-3" class="text-brand-500" /> {{ $label }}
                                </span>
                                <span class="rounded-full bg-gray-100 px-2 text-xs text-gray-600">{{ $categoryCounts->get($value, 0) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Ajakan konsultasi: membuka chat WhatsApp customer service (URL di config/clinic.php). --}}
            <section class="overflow-hidden rounded-xl bg-gradient-to-br from-brand-600 to-brand-900 p-5 text-white shadow-md">
                <span class="flex size-11 items-center justify-center rounded-full bg-white/15">
                    <x-icon name="stethoscope" size="size-5" />
                </span>
                <h2 class="mt-4 text-lg leading-snug font-bold">
                    {{ $article->category_label ? 'Alami gejala '.Str::lower($article->category_label).'?' : 'Punya keluhan kesehatan?' }}
                </h2>
                <p class="mt-1 text-sm text-brand-100">Konsultasi segera dengan tim {{ config('app.name') }}. Kami siap membantu Anda.</p>
                <a href="{{ config('clinic.whatsapp_consultation_url') }}" target="_blank" rel="noopener noreferrer"
                   class="btn mt-5 w-full bg-[#25D366] text-white shadow-sm hover:bg-[#1DA851] focus-visible:ring-white">
                    <x-icon name="whatsapp" size="size-5" /> Konsultasi via WhatsApp
                </a>
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        // Tombol salin hanya berisi ikon: ganti ikon tautan → centang sebentar, label untuk pembaca layar ikut berubah.
        document.getElementById('copy-link').addEventListener('click', async function () {
            const icon = document.getElementById('copy-link-icon');
            const done = document.getElementById('copy-link-done');
            const label = document.getElementById('copy-link-label');
            try {
                await navigator.clipboard.writeText(this.dataset.url);
                icon.hidden = true;
                done.hidden = false;
                label.textContent = 'Tautan disalin';
            } catch {
                label.textContent = 'Gagal menyalin';
            }
            setTimeout(() => {
                icon.hidden = false;
                done.hidden = true;
                label.textContent = 'Salin tautan';
            }, 2000);
        });
    </script>
@endpush
