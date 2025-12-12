<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class OptimizeNotifications
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Add cache headers for notification endpoints
        if ($request->is('api/notifications*')) {
            $response = $next($request);
            
            // Set cache control headers
            $response->headers->set('Cache-Control', 'private, max-age=30'); // 30 seconds cache
            $response->headers->set('X-Notification-Cache', 'active');
            
            return $response;
        }
        
        return $next($request);
    }
}