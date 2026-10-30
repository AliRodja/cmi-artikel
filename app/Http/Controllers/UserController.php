<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Kelola Pengguna (khusus admin, dijaga middleware "admin" di routes/web.php). */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $role = $request->query('role');

        $users = User::query()
            ->withCount('articles')
            // Dibungkus closure → "(name LIKE ? OR email LIKE ?) AND role = ?". Scope withRole() sebenarnya
            // juga otomatis mengurung where sebelumnya, tapi closure membuat maksudnya eksplisit dan tetap
            // aman bila filter peran kelak diganti where() biasa.
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when(in_array($role, User::ROLES, true), fn ($query) => $query->withRole($role))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Jumlah per peran untuk tab filter.
        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('users.index', compact('users', 'search', 'role', 'roleCounts'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['name', 'profession', 'email', 'password']));
        // role tidak ada di $fillable, jadi diisi langsung. Hanya admin (di sini) yang bisa menentukannya.
        $user->role = $request->validated('role');
        $user->save();

        return redirect()->route('users.index')
            ->with('success', __('Akun :name berhasil dibuat sebagai :role.', ['name' => $user->name, 'role' => __($user->role_label)]));
    }

    public function edit(User $user): View
    {
        $user->loadCount('articles');

        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->fill(['name' => $data['name'], 'profession' => $data['profession'] ?? null, 'email' => $data['email']]);

        // Password kosong = tidak diganti. Cast "hashed" di model yang meng-hash-nya.
        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
        }

        // Tidak ada di $data bila admin mengedit akunnya sendiri (aturan "prohibited" di UserRequest).
        if (isset($data['role'])) {
            $user->role = $data['role'];
        }

        $user->save();

        return redirect()->route('users.index')->with('success', __('Akun :name berhasil diperbarui.', ['name' => $user->name]));
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        DB::transaction(function () use ($user) {
            // Notifikasi memakai relasi polymorphic (tanpa foreign key), jadi dihapus manual.
            $user->notifications()->delete();

            // Foreign key mengurus sisanya: artikel tetap ada tanpa penulis (nullOnDelete),
            // komentar & Baca Nanti miliknya ikut terhapus (cascadeOnDelete).
            $user->delete();
        });

        return redirect()->route('users.index')->with('success', __('Akun :name berhasil dihapus.', ['name' => $user->name]));
    }
}
