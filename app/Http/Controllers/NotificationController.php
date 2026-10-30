<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        // ?filter=belum-dibaca → hanya yang belum dibaca.
        $onlyUnread = $request->query('filter') === 'belum-dibaca';

        $notifications = ($onlyUnread ? $user->unreadNotifications() : $user->notifications())
            ->paginate(15)
            ->withQueryString();

        $totalCount = $user->notifications()->count();
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'onlyUnread', 'totalCount', 'unreadCount'));
    }

    /**
     * Klik notifikasi: tandai dibaca lalu menuju halaman terkait.
     * Dicari lewat $request->user()->notifications(), sehingga user tidak bisa
     * membuka notifikasi milik orang lain (hasilnya 404).
     */
    public function open(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('notifications.index'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', __('Semua notifikasi ditandai sudah dibaca.'));
    }
}
