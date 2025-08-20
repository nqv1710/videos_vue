<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class CheckAccessToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->guest(route('login'));
        }
        $accessToken = $user->bitrix_access_token ?? null;
        $refreshToken = $user->bitrix_refresh_token ?? null;
        $expiresAt = $user->bitrix_expires ? Carbon::parse($user->bitrix_expires) : null;
        if ($refreshToken && $expiresAt && $expiresAt->lt(now()->addMinutes(2))) {
            try {
                $res = Http::asForm()->post('https://oauth.bitrix.info/oauth/token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => config('services.bitrix.client_id'),
                    'client_secret' => config('services.bitrix.client_secret'),
                ]);

                if ($res->successful()) {
                    $user->bitrix_access_token = $res['access_token'];
                    $user->bitrix_refresh_token = $res['refresh_token'] ?? $refreshToken;
                    $user->bitrix_expires = now()->addSeconds((int) $res['expires_in']);
                    $user->save();
                } else {
                    return response()->json(['error' => 'Refresh token failed'], 401);
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Token check failed'], 500);
            }
        }

        return $next($request);
    }
}
