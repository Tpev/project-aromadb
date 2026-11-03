<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the authenticated user.
     */
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return $request->expectsJson() ? $this->fetch() : redirect()->back();
    }

    public function open($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Keep stored links (including historical domains), but never redirect to an executable URL.
        $url = $notification->data['url'] ?? null;
        if (! is_string($url) || preg_match('/[\x00-\x20\\\\]/', $url)
            || ! ((str_starts_with($url, '/') && ! str_starts_with($url, '//'))
                || (filter_var($url, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($url, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)))) {
            $url = route('notifications.index');
        }

        return redirect()->to($url);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        return $request->expectsJson() ? $this->fetch() : redirect()->back();
    }

    /**
     * Fetch new notifications via AJAX.
     */
    public function fetch()
    {
        $unreadCount = auth()->user()->unreadNotifications()->count();
        $notifications = auth()->user()->unreadNotifications()->limit(5)->get()
            ->map(fn ($notification) => array_merge($notification->toArray(), [
                'open_url' => route('notifications.open', $notification->id),
            ]))->values();

        return response()->json([
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }
}
