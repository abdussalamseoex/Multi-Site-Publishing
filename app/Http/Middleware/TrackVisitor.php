<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't track admin or install routes
        if ($request->is('admin/*') || $request->is('install/*') || $request->is('install')) {
            return $next($request);
        }

        try {
            $ip = $request->ip();
            $country = 'Unknown';

            if ($ip && $ip !== '127.0.0.1' && $ip !== '::1') {
                $country = \Illuminate\Support\Facades\Cache::remember("ip_country_{$ip}", 86400, function () use ($ip) {
                    $response = \Illuminate\Support\Facades\Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
                    if ($response->successful() && $response->json('status') === 'success') {
                        return $response->json('country');
                    }
                    return 'Unknown';
                });
            } else {
                $country = 'Localhost';
            }

            \App\Models\Visit::create([
                'ip_address' => $ip,
                'url' => $request->fullUrl(),
                'referrer' => $request->headers->get('referer'),
                'user_agent' => substr($request->userAgent(), 0, 255),
                'country' => $country,
            ]);
        } catch (\Exception $e) {
            // Fail silently to never break the application if tracking fails
        }

        return $next($request);
    }
}
