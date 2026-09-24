<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a shared cache (Cloudflare) keep stateless guest pages for five minutes while browsers always revalidate,
 * so logging in shows the member view at once. Any response tied to a visitor stays private. Both carry an ETag.
 */
class SetPageCacheHeaders
{
    private const int EDGE_TTL_SECONDS = 300;

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->isMethodCacheable() || ! $response->isSuccessful()) {
            return $response;
        }

        if ($this->isSharedByEveryGuest($request, $response)) {
            $response->setPublic();
            $response->setMaxAge(0);
            $response->setSharedMaxAge(self::EDGE_TTL_SECONDS);
        } else {
            $response->setPrivate();
        }

        $response->setEtag(hash('xxh128', (string) $response->getContent()));
        $response->isNotModified($request);

        return $response;
    }

    private function isSharedByEveryGuest(Request $request, Response $response): bool
    {
        if ($request->hasSession()) {
            return false;
        }

        if (Auth::check()) {
            return false;
        }

        return $response->headers->getCookies() === [];
    }
}
