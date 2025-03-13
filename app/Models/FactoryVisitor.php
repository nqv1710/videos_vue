<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FactoryVisitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'visit_date',
        'number_of_visitors',
        'purpose',
        'qr_code',
        'status',
        'message'
    ];

    protected $casts = [
        'visit_date' => 'datetime',
    ];

    /**
     * Sync visitor data from Google Sheets
     *
     * @param array $data
     * @return FactoryVisitor
     */
    public static function syncFromGoogleSheets($data)
    {
        try {
            Log::info('Syncing visitor data from Google Sheets', ['data' => $data]);

            // Validate required fields
            if (empty($data['email'])) {
                throw new \Exception('Email is required for syncing');
            }

            // Prepare visitor data
            $visitorData = [
                'name' => $data['name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'message' => $data['message'] ?? null,
                'status' => 'synced',
                'visit_date' => now(), // Set default visit date to current time
                'number_of_visitors' => 1, // Default value
                'company' => 'Synced from Google Sheets', // Default value
                'purpose' => 'Synced from Google Sheets' // Default value
            ];

            // Update or create visitor
            $visitor = self::updateOrCreate(
                ['email' => $data['email']],
                $visitorData
            );

            Log::info('Successfully synced visitor', [
                'visitor_id' => $visitor->id,
                'email' => $visitor->email
            ]);

            return $visitor;
        } catch (\Exception $e) {
            Log::error('Error syncing visitor from Google Sheets', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Get the visitor's QR code URL
     *
     * @return string|null
     */
    public function getQrCodeUrlAttribute()
    {
        if (!$this->qr_code) {
            return null;
        }
        return asset('storage/' . $this->qr_code);
    }

    /**
     * Format the visit date for display
     *
     * @return string
     */
    public function getFormattedVisitDateAttribute()
    {
        return $this->visit_date ? $this->visit_date->format('d/m/Y H:i') : '';
    }
}
