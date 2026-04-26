<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    // Known good search engine bots
    const GOOD_BOT_REGEX = '/(googlebot|bingbot|yandexbot|baiduspider|duckduckbot|slurp|ahrefsbot|semrushbot)/i';

    // Bad bots / scrapers / spam crawlers
    const BAD_BOT_REGEX = '/(bot|crawl|spider|scraper|mediapartners|inspection|wget|curl|python-requests|go-http-client|libwww|lwp-|java\/|perl\/|ruby\/|php\/|htmlparser)/i';

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
            $userAgent = $request->userAgent() ?? '';

            // --- Bot Detection (done first, no IP lookup for bots) ---
            $isBot = false;
            $botType = null;

            if (preg_match(self::GOOD_BOT_REGEX, $userAgent)) {
                $isBot = true;
                $botType = 'good';
            } elseif (preg_match(self::BAD_BOT_REGEX, $userAgent)) {
                $isBot = true;
                $botType = 'bad';
            }

            $country = 'Unknown';
            $countryCode = null;

            // Only do geo-lookup for real human visitors to save API calls
            if (!$isBot) {
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
            }

            \App\Models\Visit::create([
                'ip_address'   => $ip,
                'url'          => $request->fullUrl(),
                'referrer'     => $request->headers->get('referer'),
                'user_agent'   => substr($userAgent, 0, 255),
                'country'      => $country,
                'country_code' => $countryCode,
                'is_bot'       => $isBot,
                'bot_type'     => $botType,
            ]);
        } catch (\Exception $e) {
            // Fail silently to never break the application if tracking fails
        }

        return $next($request);
    }
}
