@props(['user'])

<span {{ $attributes->class([
    'badge',
    'bg-brand-50 text-brand-700' => $user->role === \App\Models\User::ROLE_ADMIN,
    'bg-emerald-50 text-emerald-700' => $user->role === \App\Models\User::ROLE_PENULIS,
    'bg-gray-100 text-gray-600' => $user->role === \App\Models\User::ROLE_PEMBACA,
]) }}>{{ __($user->role_label) }}</span>
