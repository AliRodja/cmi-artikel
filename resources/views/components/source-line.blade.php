@props(['line'])

@php
    // Satu baris sumber rujukan: pisahkan teks dan tautan http(s) pertama. Hanya tautan http/https yang dijadikan
    // link (bukan javascript: dsb.), dan semuanya tetap ditampilkan lewat {{ }} sehingga aman dari XSS.
    $url = preg_match('~https?://[^\s<>"]+~i', $line, $match) ? rtrim($match[0], '.,;)') : null;
    $text = $url ? trim(str_replace($url, '', $line), " \t-–—:,") : $line;
@endphp
@if ($text !== '')
<span>{{ $text }}</span>
@endif
@if ($url)
<a href="{{ $url }}" title="{{ $url }}" target="_blank" rel="noopener noreferrer nofollow" class="link break-all">{{ parse_url($url, PHP_URL_HOST) ?? $url }}</a>
@endif
