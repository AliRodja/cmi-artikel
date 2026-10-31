<div>
    @auth
        <button type="button"
                wire:click="toggle"
                wire:loading.attr="disabled"
                aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}"
                title="{{ $isBookmarked ? __('Hapus dari Baca Nanti') : __('Simpan ke Baca Nanti') }}"
                @class([$buttonClass, 'text-brand-700' => $isBookmarked, 'text-gray-600 hover:text-brand-700' => ! $isBookmarked])>
            <x-icon :name="$isBookmarked ? 'bookmark-solid' : 'bookmark'" :size="$withLabel ? 'size-3' : 'size-4'" />
            @if ($withLabel)
                {{ $isBookmarked ? __('Tersimpan') : __('Simpan') }}
            @else
                <span class="sr-only">{{ $isBookmarked ? __('Tersimpan di Baca Nanti') : __('Simpan ke Baca Nanti') }}</span>
            @endif
        </button>
    @else
        {{-- Tamu tetap memakai form POST biasa agar redirectGuestsTo di bootstrap/app.php menyimpan URL artikel
             sebagai intended URL setelah login dan menampilkan pop-up penjelasan. --}}
        <form action="{{ route('bookmarks.toggle', $article) }}" method="POST">
            @csrf
            <button type="submit"
                    aria-pressed="false"
                    title="{{ __('Simpan ke Baca Nanti') }}"
                    class="{{ $buttonClass }} text-gray-600 hover:text-brand-700">
                <x-icon name="bookmark" :size="$withLabel ? 'size-3' : 'size-4'" />
                @if ($withLabel)
                    {{ __('Simpan') }}
                @else
                    <span class="sr-only">{{ __('Simpan ke Baca Nanti') }}</span>
                @endif
            </button>
        </form>
    @endauth
</div>
