<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrOwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || 
            (!auth()->user()->isAdmin() && !auth()->user()->isOwner())) {

            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}