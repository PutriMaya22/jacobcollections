<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOtpIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->hasVerifiedEmail()) {
            // Jika user belum verifikasi email, redirect ke halaman OTP
            return redirect()->route('otp')->with('error', 'Anda harus verifikasi OTP terlebih dahulu sebelum mengakses halaman ini.');
        }

        return $next($request);
    }
}
