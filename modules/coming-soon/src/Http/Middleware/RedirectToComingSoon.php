<?php

namespace Modules\ComingSoon\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\ComingSoon\Settings\ComingSoonSettings;
use Symfony\Component\HttpFoundation\Response;

class RedirectToComingSoon
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)
            || (! $request->header('X-Inertia') && ! str_contains($request->header('Accept', ''), 'text/html'))
            || $request->routeIs('coming-soon.index', ...config('coming-soon.except_routes'))
            || $request->user()?->can('manage settings')
            || ! app(ComingSoonSettings::class)->enabled) {
            return $next($request);
        }

        return redirect()->route('coming-soon.index');
    }
}
