<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminApiToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('app.admin_api_token');
        $providedToken = (string) $request->header('X-Admin-Token');

        if ($expectedToken === '' || ! hash_equals($expectedToken, $providedToken)) {
            return new JsonResponse([
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
