<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class BitrixLoginController extends Controller
{
    public function redirectToBitrix()
    {
        $clientId = config('services.bitrix.client_id');
        $redirectUri = urlencode(config('services.bitrix.redirect'));

        $authUrl = "https://bitrixdev.esuhai.org/oauth/authorize/?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}";

        return redirect($authUrl);
    }

    public function handleCallback(Request $request)
    {
        $code = $request->get('code');

        $response = Http::asForm()->post('https://oauth.bitrix.info/oauth/token/', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.bitrix.client_id'),
            'client_secret' => config('services.bitrix.client_secret'),
            'redirect_uri' => config('services.bitrix.redirect'),
            'code' => $code,
        ]);
        $tokenData = $response->json();
        if (!isset($tokenData['access_token'])) {
            return response()->json(['error' => 'Access token not found.']);
        }
        $url = 'https://bitrixdev.esuhai.org/rest/profile.json?access_token=' . $tokenData['access_token'];
        $userResponse = Http::get($url);
        $userData = $userResponse->json('result');

        $user = User::updateOrCreate(
            ['bitrix_user_id' => $userData['ID']],
            [
                'name' => $userData['NAME'] . ' ' . $userData['LAST_NAME'],
                'bitrix_access_token' => $tokenData['access_token'],
                'bitrix_refresh_token' => $tokenData['refresh_token'],
                'bitrix_expires' => now()->addSeconds($tokenData['expires_in'])
            ]
        );

     

        Auth::login($user);

        $user->tokens()->delete();
        $passportToken = $user->createToken('bitrix-login')->accessToken;

        if ($request->wantsJson()) {
            return response()->json(['token' => $passportToken, 'user' => $user]);
        }

        return redirect('/dashboard?apiToken=' . urlencode($passportToken));
    }
}
