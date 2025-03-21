<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckGoogleToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = session('google_token');

        // Nếu không có token hoặc token không hợp lệ thì xóa session
        if (!$token || !isset($token['access_token'])) {
            session()->forget('google_token');
            return redirect()->route('gmail.auth');
        }

        return $next($request);
    }
}
