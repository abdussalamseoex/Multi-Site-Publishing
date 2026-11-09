<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Visit;
use App\Models\ActiveSession;

class TrackVisitor
{
    // Known good search engine bots
    const GOOD_BOT_REGEX = '/(googlebot|bingbot|yandexbot|baiduspider|duckduckbot|slurp|ahrefsbot|semrushbot)/i';

    // Bad bots / scrapers / spam crawlers
    const BAD_BOT_REGEX = '/(bot|crawl|spider|scraper|mediapartners|inspection|wget|curl|python-requests|go-http-client|libwww|lwp-|java\/|perl\/|ruby\/|php\/|htmlparser|headlesschrome|phantomjs)/i';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't track admin, install, api, or tracking endpoints
        if ($request->is('admin/*', 'install/*', 'install', 'traffic/*', 'api/*', 'storage/*')) {
            return $next($request);
        }

        // Don't track logged-in admins on the frontend
        if (Auth::check() && Auth::user()->role === 'admin') {
            return $next($request);
        }

        $sessionId = $request->cookie('_va_sid') ?? (string) Str::uuid();

        try {
            $ip = $request->ip();
            $userAgent = $request->userAgent() ?? '';

            // --- Bot Detection ---
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
            $city = null;

            // Only do geo-lookup for non-bots to save API calls
            if (!$isBot) {
                if ($ip && $ip !== '127.0.0.1' && $ip !== '::1') {
                    $locationData = Cache::remember("ip_location_{$ip}", 86400, function () use ($ip) {
                        try {
                            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,city,proxy,hosting");
                            if ($response->successful() && $response->json('status') === 'success') {
                                return [
                                    'country'     => $response->json('country'),
                                    'countryCode' => strtolower($response->json('countryCode') ?? ''),
                                    'city'        => $response->json('city'),
                                    'is_hosting'  => (bool) ($response->json('hosting') || $response->json('proxy')),
                                ];
                            }
                        } catch (\Exception $e) {
                            // ignore timeout
                        }
                        return ['country' => 'Unknown', 'countryCode' => null, 'city' => null, 'is_hosting' => false];
                    });

                    $country = $locationData['country'] ?? 'Unknown';
                    $countryCode = $locationData['countryCode'] ?? null;
                    $city = $locationData['city'] ?? null;

                    // If datacenter hosting IP disguised as browser, mark as bad bot
                    if (!empty($locationData['is_hosting']) && !$isBot) {
                        $isBot = true;
                        $botType = 'bad';
                    }
                } else {
                    $country = 'Localhost';
                    $countryCode = 'us';
                    $city = 'Local';
                }
            }

            // Parse device, browser, OS
            $parsed = self::parseUserAgent($userAgent);

            // Referrer handling
            $referrer = $request->headers->get('referer');
            $referrerDomain = null;
            if ($referrer) {
                $referrerDomain = parse_url($referrer, PHP_URL_HOST) ?? null;
                // If referrer is internal site, treat as internal navigation
                if ($referrerDomain && str_contains(url('/'), $referrerDomain)) {
                    $referrerDomain = 'Internal';
                }
            }

            // 1. Record Page Visit
            Visit::create([
                'session_id'    => $sessionId,
                'ip_address'    => $ip,
                'url'           => $request->fullUrl(),
                'page_title'    => null, // Will be updated by client tracker ping if available
                'referrer'      => $referrer,
                'user_agent'    => substr($userAgent, 0, 255),
                'country'       => $country,
                'country_code'  => $countryCode,
                'city'          => $city,
                'device_type'   => $parsed['device'],
                'browser'       => $parsed['browser'],
                'os'            => $parsed['os'],
                'dwell_seconds' => 0,
                'is_bot'        => $isBot,
                'bot_type'      => $botType,
            ]);

            // 2. Manage Active Session (Human visitors only or tracked bots)
            if (!$isBot) {
                $session = ActiveSession::where('session_id', $sessionId)->first();
                if ($session) {
                    $session->update([
                        'current_page'     => $request->fullUrl(),
                        'page_views'       => $session->page_views + 1,
                        'status'           => 'active',
                        'last_activity_at' => now(),
                    ]);
                } else {
                    ActiveSession::create([
                        'session_id'       => $sessionId,
                        'ip_address'       => $ip,
                        'country'          => $country,
                        'country_code'     => $countryCode,
                        'city'             => $city,
                        'device_type'      => $parsed['device'],
                        'browser'          => $parsed['browser'],
                        'os'               => $parsed['os'],
                        'referrer'         => $referrer,
                        'referrer_domain'  => $referrerDomain,
                        'landing_page'     => $request->fullUrl(),
                        'current_page'     => $request->fullUrl(),
                        'page_views'       => 1,
                        'dwell_seconds'    => 0,
                        'is_bot'           => false,
                        'status'           => 'active',
                        'started_at'       => now(),
                        'last_activity_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Fail silently to never break the application if tracking fails
        }

        $response = $next($request);

        // Attach the session cookie if not already set
        if (!$request->hasCookie('_va_sid')) {
            $response->cookie('_va_sid', $sessionId, 1440, '/', null, false, false);
        }

        return $response;
    }

    /**
     * Parse device, OS, and browser from User-Agent string.
     */
    public static function parseUserAgent(string $ua): array
    {
        // Device
        $device = 'Desktop';
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua)) {
            $device = 'Tablet';
        } elseif (preg_match('/Mobile|iP(hone|od)|Android|BlackBerry|IEMobile|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/i', $ua)) {
            $device = 'Mobile';
        }

        // OS
        $os = 'Unknown';
        if (preg_match('/windows nt 10/i', $ua))         $os = 'Windows 10/11';
        elseif (preg_match('/windows nt 6\.3/i', $ua))    $os = 'Windows 8.1';
        elseif (preg_match('/windows nt 6\.2/i', $ua))    $os = 'Windows 8';
        elseif (preg_match('/windows nt 6\.1/i', $ua))    $os = 'Windows 7';
        elseif (preg_match('/windows/i', $ua))            $os = 'Windows';
        elseif (preg_match('/android/i', $ua))            $os = 'Android';
        elseif (preg_match('/iphone|ipad|ipod/i', $ua))   $os = 'iOS';
        elseif (preg_match('/macintosh|mac os x/i', $ua)) $os = 'macOS';
        elseif (preg_match('/linux/i', $ua))              $os = 'Linux';
        elseif (preg_match('/cros/i', $ua))               $os = 'ChromeOS';

        // Browser
        $browser = 'Other';
        if (preg_match('/Edg/i', $ua))                   $browser = 'Edge';
        elseif (preg_match('/OPR|Opera/i', $ua))         $browser = 'Opera';
        elseif (preg_match('/SamsungBrowser/i', $ua))    $browser = 'Samsung Internet';
        elseif (preg_match('/Chrome/i', $ua))            $browser = 'Chrome';
        elseif (preg_match('/Safari/i', $ua))            $browser = 'Safari';
        elseif (preg_match('/Firefox/i', $ua))           $browser = 'Firefox';
        elseif (preg_match('/MSIE|Trident/i', $ua))      $browser = 'Internet Explorer';

        return [
            'device'  => $device,
            'os'      => $os,
            'browser' => $browser,
        ];
    }
}
