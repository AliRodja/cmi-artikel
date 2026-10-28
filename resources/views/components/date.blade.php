@props(['value', 'format' => 'd M Y'])

{{--
    Tanggal untuk pembaca: kurang dari 24 jam → relatif ("3 jam yang lalu"), selebihnya tanggal pasti
    ("22 Okt 2026"), karena pembaca usia matang lebih mudah memahami tanggal daripada "6 hari yang lalu".
    Tanggal & jam lengkap tampil saat kursor diarahkan (title) dan terbaca mesin (datetime).
--}}
<time datetime="{{ $value->toIso8601String() }}" title="{{ $value->translatedFormat('l, d F Y, H:i') }}" {{ $attributes }}>
    {{ $value->greaterThan(now()->subDay()) ? $value->diffForHumans() : $value->translatedFormat($format) }}
</time>
