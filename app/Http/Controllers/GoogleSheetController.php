<?php

namespace App\Http\Controllers;

use App\Models\FactoryVisitor;
use Google\Client;
use Google\Service\Sheets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GoogleSheetController extends Controller
{
    protected $client;
    protected $service;
    protected $spreadsheetId;

    public function __construct()
    {
        try {
            $this->client = new Client();
            $serviceAccountPath = storage_path('app/google-service-account.json');

            if (!file_exists($serviceAccountPath)) {
                throw new \Exception('Google Service Account file not found');
            }

            $this->client->setAuthConfig($serviceAccountPath);
            $this->client->addScope(Sheets::SPREADSHEETS_READONLY);
            $this->service = new Sheets($this->client);

            $this->spreadsheetId = config('services.google.sheet_id');

            if (empty($this->spreadsheetId)) {
                throw new \Exception('Google Sheet ID not configured');
            }

            Log::info('Google Sheets client initialized successfully');
        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Sheets client: ' . $e->getMessage());
            throw $e;
        }
    }

    public function syncData()
    {
        try {
            Log::info('Starting Google Sheets sync');

            $range = 'Sheet1!A2:D'; // Điều chỉnh range theo cấu trúc sheet của bạn
            Log::info('Fetching data from range: ' . $range);

            $response = $this->service->spreadsheets_values->get(
                $this->spreadsheetId,
                $range
            );

            $values = $response->getValues();
            Log::info('Retrieved ' . count($values) . ' rows from Google Sheets');

            $count = 0;
            $errors = [];

            foreach ($values as $index => $row) {
                try {
                    // Đảm bảo đủ dữ liệu
                    if (count($row) >= 4) {
                        $visitorData = [
                            'name' => $row[0],
                            'email' => $row[1],
                            'phone' => $row[2],
                            'message' => $row[3]
                        ];

                        FactoryVisitor::syncFromGoogleSheets($visitorData);
                        $count++;
                        Log::info("Synced row {$index} successfully");
                    } else {
                        $errors[] = "Row {$index} has insufficient data";
                        Log::warning("Row {$index} has insufficient data");
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error processing row {$index}: " . $e->getMessage();
                    Log::error("Error processing row {$index}: " . $e->getMessage());
                }
            }

            $message = "Đã đồng bộ thành công {$count} bản ghi";
            if (!empty($errors)) {
                $message .= ". Có " . count($errors) . " lỗi xảy ra.";
            }

            Log::info('Google Sheets sync completed', [
                'success_count' => $count,
                'error_count' => count($errors)
            ]);

            return response()->json([
                'message' => $message,
                'count' => $count,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Google Sheets sync error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Có lỗi xảy ra khi đồng bộ dữ liệu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
