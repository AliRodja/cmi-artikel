
@php
    $flashStyles = [
        'success' => 'border-green-200 bg-green-50 text-green-800',
        'status'  => 'border-blue-200 bg-blue-50 text-blue-800',
        'error'   => 'border-red-200 bg-red-50 text-red-800',
    ];
@endphp

@foreach ($flashStyles as $key => $classes)
    @if (session($key))
        <div class="mb-6 rounded-lg border px-4 py-3 text-sm {{ $classes }}">
            {{ session($key) }}
        </div>
    @endif
@endforeach