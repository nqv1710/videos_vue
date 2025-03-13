<?php

namespace App\Services;

use Google\Client;
use Google\Service\Sheets;

class GoogleSheetsService
{
    protected $client;
    protected $service;
    protected $spreadsheetId;

    public function __construct()
    {
        $this->spreadsheetId = env('GOOGLE_SHEET_ID');

        $this->client = new Client();
        $this->client->setApplicationName("Google Sheets API Laravel");
        $this->client->setScopes([Sheets::SPREADSHEETS]);
        $this->client->setAuthConfig(storage_path('app/google-service-account.json'));
        $this->client->setAccessType('offline');

        $this->service = new Sheets($this->client);
    }

    public function getSheetData($range = 'Sheet1!A1:H10')
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        return $response->getValues();
    }
}
