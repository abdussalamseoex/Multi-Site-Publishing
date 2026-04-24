<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BlockedIp;

class BlockIpMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!env('APP_INSTALLED', false)) {
            return $next($request);
        }

        if (BlockedIp::where('ip_address', $request->ip())->exists()) {
            abort(403, 'Your IP address has been banned by the administrator.');
        }

        return $next($request);
    }
}
