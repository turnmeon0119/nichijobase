<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminWebToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('app.admin_api_token');
        $sessionToken = (string) $request->session()->get('admin_web_token', '');

        if ($expectedToken === '' || ! hash_equals($expectedToken, $sessionToken)) {
            return new RedirectResponse(route('admin.articles.login'));
        }

        return $next($request);
    }
}
