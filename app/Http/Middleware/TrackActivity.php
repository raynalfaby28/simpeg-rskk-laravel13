<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackActivity
{
    /**
     * Catat aktivitas terakhir user (untuk status "online" di Data Pegawai).
     * Hanya menulis DB maksimal 1x per menit per user agar tidak membebani.
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (! $user->last_activity_at || $user->last_activity_at->lt(now()->subMinute())) {
                $user->forceFill(['last_activity_at' => now()])->saveQuietly();
            }
        }

        return $next($request);
    }
}