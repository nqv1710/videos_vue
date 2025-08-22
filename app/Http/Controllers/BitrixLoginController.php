<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class BitrixLoginController extends Controller
{

    public function serviceBitrix()
    {
        if(env('APP_ENV') == 'local') {
            return config('services.bitrix_dev');
        }
        else {
            return config('services.bitrix');
        }
    }

    public function redirectToBitrix()
    {
        $clientId = $this->serviceBitrix()['client_id'];
        $redirectUri = urlencode($this->serviceBitrix()['redirect']);
        if(env('APP_ENV') == 'local') {
            $authUrl = "https://bitrixdev.esuhai.org/oauth/authorize/?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}";
        } else {
            $authUrl = "https://bitrix.esuhai.org/oauth/authorize/?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}";
        }

        return redirect($authUrl);
    }

    public function handleCallback(Request $request)
    {
        $code = $request->get('code');

        $response = Http::asForm()->post('https://oauth.bitrix.info/oauth/token/', [
            'grant_type' => 'authorization_code',
            'client_id' => $this->serviceBitrix()['client_id'],
            'client_secret' => $this->serviceBitrix()['client_secret'],
            'redirect_uri' => $this->serviceBitrix()['redirect'],
            'code' => $code,
        ]);
        $tokenData = $response->json();
        if (!isset($tokenData['access_token'])) {
            return response()->json(['error' => 'Access token not found.']);
        }
        $url = 'https://bitrixdev.esuhai.org/rest/user.current.json?access_token=' . $tokenData['access_token'];
        $userResponse = Http::get($url);
        $userData = $userResponse->json('result');
        
        // dd($userData);

        $user = User::updateOrCreate(
            ['bitrix_user_id' => (int) $userData['ID']],
            [
                'name' => trim(($userData['NAME'] ?? '') . ' ' . ($userData['LAST_NAME'] ?? '')),
                'bitrix_access_token' => $tokenData['access_token'] ?? null,
                'bitrix_refresh_token' => $tokenData['refresh_token'] ?? null,
                'bitrix_expires' => isset($tokenData['expires_in'])
                    ? now()->addSeconds($tokenData['expires_in'])
                    : null,
            ]
        );

        // dd($user);
        // $user->tokens()->delete();
        // $passportToken = $user->createToken('bitrix-login')->accessToken;

        Auth::login($user);

        return redirect()->intended('/dashboard');


        // if ($request->wantsJson()) {
        //     return response()->json(['token' => $passportToken, 'user' => $user]);
        // }

        // return redirect('/dashboard?apiToken=' . urlencode($passportToken));
    }
}
