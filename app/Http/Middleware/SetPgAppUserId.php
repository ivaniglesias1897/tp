<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SetPgAppUserId
{
    public function handle($request, Closure $next)
    {
        $uid = Auth::id();
        if ($uid) {
            // SET no admite parámetros preparados → concatenar
            DB::statement("SET app.user_id = '" . (int) $uid . "'");
        }

        try {
            return $next($request);
        } finally {
            DB::statement('RESET app.user_id');
        }
    }
}
