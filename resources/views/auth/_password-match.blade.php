{{-- Indikator "✓ Password cocok / Password belum sama", diisi resources/js/app.js ([data-password-match]). --}}
<p data-password-match data-password="password" data-confirmation="password_confirmation"
   data-text-match="{{ __('✓ Password cocok') }}" data-text-mismatch="{{ __('Password belum sama') }}"
   class="-mt-2 mb-4 flex items-center gap-1.5 text-xs font-medium" aria-live="polite" hidden></p>
