<section id="komentar" class="card scroll-mt-24 border-t-4 border-t-brand-600 p-5 sm:p-7">
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-4">
        <h2 class="flex items-center gap-2.5 text-lg font-bold text-gray-900">
            <span class="flex size-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                <x-icon name="comments" size="size-4" />
            </span>
            {{ __('Komentar') }}
            <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-bold text-brand-700 ring-1 ring-brand-200/70">{{ $comments->count() }}</span>
        </h2>
    </div>

    @if ($feedbackMessage)
        <div role="status" class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-green-200 bg-green-50/80 px-4 py-3 text-sm font-medium text-green-800">
            <span class="flex items-center gap-2">
                <x-icon name="check-circle" size="size-4" class="text-green-600" />
                {{ $feedbackMessage }}
            </span>
        </div>
    @endif

    @auth
        <form wire:submit="store" class="mt-5 flex gap-3.5">
            <x-avatar :name="auth()->user()->name" />
            <div class="min-w-0 flex-1">
                <label for="body" class="sr-only">{{ __('Tulis komentar') }}</label>
                <textarea id="body" wire:model="body" name="body" rows="3" maxlength="2000" required
                          placeholder="{{ __('Tulis komentar atau pertanyaan Anda...') }}"
                          @class(['form-input', 'form-input-error' => $errors->has('body')])></textarea>
                @error('body')
                    <p class="form-error">{{ $message }}</p>
                @enderror
                <div class="mt-3 flex justify-end">
                    <button type="submit" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                        {{ __('Kirim Komentar') }}
                    </button>
                </div>
            </div>
        </form>
    @else
        <div class="mt-5 rounded-xl border border-brand-200/80 bg-brand-50/70 px-4 py-3.5 text-sm text-brand-900">
            <a href="{{ route('login') }}" class="link font-semibold underline underline-offset-2">{{ __('Masuk') }}</a> {{ __('atau') }}
            <a href="{{ route('register') }}" class="link font-semibold underline underline-offset-2">{{ __('daftar') }}</a> {{ __('untuk ikut berkomentar.') }}
        </div>
    @endauth

    @if ($comments->isEmpty())
        <p class="mt-6 text-sm text-gray-500">{{ __('Belum ada komentar. Jadilah yang pertama berkomentar.') }}</p>
    @else
        <ul class="mt-6 space-y-4">
            @foreach ($comments as $comment)
                <li wire:key="comment-{{ $comment->id }}" class="flex gap-3.5">
                    <x-avatar :name="$comment->user->name" />
                    <div class="min-w-0 flex-1 rounded-xl border border-gray-200/90 bg-gray-50/50 px-4 py-3.5">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                            <p class="text-sm font-bold text-gray-900">{{ $comment->user->name }}</p>
                            @if ($comment->user_id === $article->user_id)
                                <span class="badge bg-brand-50 text-brand-700 ring-1 ring-brand-200/70">{{ __('Penulis artikel') }}</span>
                            @elseif ($comment->user->isAdmin())
                                <span class="badge bg-gray-100 text-gray-700">{{ __('Admin') }}</span>
                            @endif
                            <span class="text-xs text-gray-500">· <x-date :value="$comment->created_at" format="d M Y, H:i" /></span>

                            @can('delete', $comment)
                                <button type="button"
                                        wire:click="destroy({{ $comment->id }})"
                                        wire:confirm="{{ __('Hapus komentar ini?') }}"
                                        title="{{ __('Hapus komentar') }}"
                                        class="ml-auto cursor-pointer rounded-md p-2 text-gray-500 hover:bg-red-50 hover:text-red-600">
                                    <x-icon name="trash" size="size-3" />
                                    <span class="sr-only">{{ __('Hapus komentar') }}</span>
                                </button>
                            @endcan
                        </div>
                        {{-- Teks biasa ({{ }} = di-escape), baris baru dipertahankan dengan whitespace-pre-line. --}}
                        <p class="mt-1.5 text-sm leading-relaxed whitespace-pre-line text-gray-700">{{ $comment->body }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</section>
