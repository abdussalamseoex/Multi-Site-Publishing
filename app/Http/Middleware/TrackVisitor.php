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

        // Don't track logged-in admins on the frontend
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->role === 'admin') {
            return $next($request);
        }

        try {
            $ip = $request->ip();
            $country = 'Unknown';
            $countryCode = null;

            if ($ip && $ip !== '127.0.0.1' && $ip !== '::1') {
                $locationData = \Illuminate\Support\Facades\Cache::remember("ip_location_{$ip}", 86400, function () use ($ip) {
                    $response = \Illuminate\Support\Facades\Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
                    if ($response->successful() && $response->json('status') === 'success') {
                        return [
                            'country' => $response->json('country'),
                            'countryCode' => strtolower($response->json('countryCode'))
                        ];
                    }
                    return ['country' => 'Unknown', 'countryCode' => null];
                });

                $country = $locationData['country'];
                $countryCode = $locationData['countryCode'];
            } else {
                $country = 'Localhost';
                $countryCode = 'us'; // Fallback flag for localhost
            }

            \App\Models\Visit::create([
                'ip_address' => $ip,
                'url' => $request->fullUrl(),
                'referrer' => $request->headers->get('referer'),
                'user_agent' => substr($request->userAgent(), 0, 255),
                'country' => $country,
                'country_code' => $countryCode,
            ]);
        } catch (\Exception $e) {
            // Fail silently to never break the application if tracking fails
        }

        return $next($request);
    }
}
