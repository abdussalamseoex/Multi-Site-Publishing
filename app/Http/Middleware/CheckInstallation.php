<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $installed = env('APP_INSTALLED', false);
        
        $isInstallRoute = $request->is('install') || $request->is('install/*') || $request->is('_debugbar/*');

        // Allow livewire assets if needed during installation, though setup is usually classic blade
        if (!$installed && !$isInstallRoute) {
            return redirect()->route('install.index');
        }

        if ($installed && $isInstallRoute) {
            return redirect('/');
        }

        return $next($request);
    }
}
