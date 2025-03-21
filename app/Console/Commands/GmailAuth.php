<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google\Client as Google_Client;
use Google\Service\Gmail;

class GmailAuth extends Command
{
    protected $signature = 'gmail:auth';
    protected $description = 'Authenticate with Gmail API';

    public function handle()
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/credentials.json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $client->setIncludeGrantedScopes(true);
        $client->addScope(Gmail::GMAIL_READONLY);

        // Thêm redirect URI
        $client->setRedirectUri('http://localhost:8000/auth/google/callback');

        // Lấy URL xác thực
        $authUrl = $client->createAuthUrl();
        $this->info('Truy cập URL sau để xác thực:');
        $this->info($authUrl);

        // Nhập code từ người dùng
        $authCode = $this->ask('Nhập code xác thực:');

        try {
            // Exchange authorization code for an access token
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Refresh token if needed
            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                $accessToken = $client->getAccessToken();
            }

            // Lưu token
            if (!file_exists(storage_path('app'))) {
                mkdir(storage_path('app'), 0700, true);
            }
            file_put_contents(storage_path('app/token.json'), json_encode($accessToken));

            $this->info('Xác thực thành công!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Lỗi xác thực: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
