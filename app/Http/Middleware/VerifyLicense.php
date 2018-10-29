<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;

class VerifyLicense
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user()->licensed_at < Carbon::now()) {
            return response()->json(
                [
                    "error"   => "account_expired",
                    "message" => "Su cuenta ha expirado."
                ], 403);
        }
        return $next($request);
    }
}
