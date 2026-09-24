<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves read-only requests from visitors without a session statelessly (no session, cookies or CSRF token)
 * so the response is identical for every such guest and can be cached at the edge. Anyone who already has a
 * session or remember-me cookie, or is authenticated, gets the web group's usual session middleware.
 *
 * Routes using this must exclude StartSession, ShareErrorsFromSession and PreventRequestForgery from the web group.
 */
class SkipSessionForCookielessGuests
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isCookielessGuest($request)) {
            View::share('errors', new ViewErrorBag);

            return $next($request);
        }

        return app(Pipeline::class)
            ->send($request)
            ->through([
                StartSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
            ])
            ->then($next);
    }

    private function isCookielessGuest(Request $request): bool
    {
        if (! $request->isMethodSafe()) {
            return false;
        }

        if (Auth::hasUser()) {
            return false;
        }

        foreach (array_keys($request->cookies->all()) as $cookieName) {
            if ($cookieName === config('session.cookie')) {
                return false;
            }

            if (Str::startsWith($cookieName, 'remember_')) {
                return false;
            }
        }

        return true;
    }
}
