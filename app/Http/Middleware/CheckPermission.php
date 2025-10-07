<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentRoute = $request->route()->getName();
        // 🔹 Rutas públicas que deben quedar fuera del permiso
        $publicRoutes = [
            'login',
            'logout',
            'register',
            'password.request',
            'password.email',
            'password.reset',
            'password.update',
            'roles.permissions',
            'roles.updatePermissions',
        ];
        if (in_array($currentRoute, $publicRoutes)) {
            return $next($request);
        }
        $user = Auth::user();
        // dd($user);
        // // Si no hay usuario logueado, lo mandamos a login
        if (!$user) {
            return redirect()->route('login');
        }
        // dd($currentRoute);
        $hasPermission = $user->role->views()->where('url',$currentRoute)->exists();

        if (!$hasPermission) {
            abort(403, 'No tienes permiso para acceder a esta página.');
        }

        return $next($request);
    }
}
