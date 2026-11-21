<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Silakan masuk terlebih dahulu untuk mengakses layanan ini.',
                ], 401);
            }

            return redirect()->route('login');
        }

        // Aturan otorisasi:
        // - Admin memiliki hak akses penuh ke rute admin dan rute loket
        // - Petugas hanya memiliki hak akses ke rute loket (tidak boleh mengakses rute admin)
        $hasAccess = false;
        if ($user->role === 'admin') {
            $hasAccess = true;
        } elseif ($user->role === 'petugas' && in_array('petugas', $roles, true)) {
            $hasAccess = true;
        }

        if (!$hasAccess) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'forbidden',
                    'message' => 'Akses ditolak: Anda tidak memiliki wewenang untuk tindakan ini.',
                ], 403);
            }

            abort(403, 'Akses Ditolak: Anda tidak memiliki hak akses untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
