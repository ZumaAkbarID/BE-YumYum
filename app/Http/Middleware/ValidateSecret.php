<?php

namespace App\Http\Middleware;

use App\Traits\ResponseJson;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSecret
{
    use ResponseJson;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->hasHeader('Secret') || $request->header('Secret') !== config('secret.plain'))
            return $this->response_error('Invalid Secret', 403, [
                'error' => 'secret'
            ]);

        return $next($request);
    }
}
