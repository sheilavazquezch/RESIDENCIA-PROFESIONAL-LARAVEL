<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|array  $roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            Log::error('Usuario no autenticado');
            abort(403, 'Acceso no autorizado');
        }

        $user = $request->user();
        $userRoles = is_array($user->role) ? $user->role : [$user->role];

        // Agrega mensajes de registro para ayudar a depurar
        Log::info('ID de usuario: ' . $user->_id);
        Log::info('Usuario roles: ' . implode(', ', $userRoles));
        Log::info('Roles esperados: ' . implode(', ', $roles));

        foreach ($roles as $role) {
            if (in_array($role, $userRoles)) {
                return $next($request);
            }
        }

        abort(403, 'Acceso no autorizado');
    }
}
