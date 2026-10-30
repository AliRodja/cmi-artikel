{{--
    Pilihan "Ditinjau secara medis oleh" (khusus admin): di form artikel & panel Terbitkan.
    Variabel: $reviewers (penulis & admin dari controller), $selected (id terpilih, boleh null).
--}}
<label for="medical_reviewer_id" class="form-label">{{ __('Ditinjau secara medis oleh') }}</label>
<select id="medical_reviewer_id" name="medical_reviewer_id"
        @class(['form-input', 'form-input-error' => $errors->has('medical_reviewer_id')])>
    <option value="">— {{ __('Belum ditinjau dokter') }} —</option>
    @foreach ($reviewers as $reviewer)
        <option value="{{ $reviewer->id }}" @selected((string) $selected === (string) $reviewer->id)>
            {{ $reviewer->name }}{{ $reviewer->profession ? ' — '.$reviewer->profession : '' }}
        </option>
    @endforeach
</select>
<p class="mt-1 text-xs text-gray-500">{{ __('Tampil di artikel sebagai tanda isi sudah diperiksa tenaga medis. Isi gelar/profesi dokter lewat Kelola Pengguna.') }}</p>
@error('medical_reviewer_id')
    <p class="form-error">{{ $message }}</p>
@enderror
