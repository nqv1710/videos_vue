<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webklex\IMAP\Facades\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FetchEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $client = Client::account('default');
        $client->connect();
        $folder = $client->getFolder('INBOX');

        $emails = $folder->messages()->since(now()->subDays(7))->setFetchBody(false)->limit(20)->get();

        foreach ($emails as $email) {
            Log::info('Email từ: ' . $email->getFrom()[0]->mail . ' - ' . Carbon::parse($email->getDate())->format('d/m/Y H:i'));
        }
    }
}
