<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FidaUser
{
    public const ALLOWED_EMAIL = 'hfida6232@gmail.com';

    /**
     * Restrict route access to the allowed FIDA user email.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! hash_equals(strtolower(self::ALLOWED_EMAIL), strtolower((string) $user->email))) {
            abort(403, __('Forbidden'));
        }

        return $next($request);
    }
}
