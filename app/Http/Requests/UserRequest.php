<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Validasi form Kelola Pengguna (dipakai store & update di UserController). */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // null saat menambah pengguna, berisi model User saat mengedit.
        $target = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target)],
            // Admin yang mengedit akunnya sendiri tidak boleh mengirim role (UserPolicy::changeRole),
            // walaupun input-nya ditambahkan lewat DevTools.
            'role' => $target && $this->user()->cannot('changeRole', $target)
                ? ['prohibited']
                : ['required', Rule::in(User::ROLES)],
            // Saat edit, password boleh dikosongkan = tidak diganti.
            'password' => [$target ? 'nullable' : 'required', 'confirmed', Password::min(8)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'role' => 'peran',
            'password' => 'password',
        ];
    }

    public function messages(): array
    {
        return [
            'role.prohibited' => 'Anda tidak dapat mengubah peran akun Anda sendiri.',
        ];
    }
}
