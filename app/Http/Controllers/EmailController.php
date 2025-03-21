<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Webklex\IMAP\Facades\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EmailController extends Controller
{
    public function index()
    {
        try {
            // Thử lấy cache trước
            $cachedEmails = Cache::get('recent_emails');
            if ($cachedEmails) {
                return Inertia::render('Emails/Index', [
                    'emails' => $cachedEmails
                ]);
            }

            $client = Client::account('default');
            $client->connect();
            $folder = $client->getFolder('INBOX');

            // Giới hạn số lượng email và thời gian lấy
            $emails = $folder->messages()
                ->since(now()->subDays(3)) // Giảm xuống 3 ngày thay vì 7 ngày
                ->limit(10) // Giới hạn số lượng email cần lấy
                ->setFetchBody(false)
                ->setFetchFlags(false)
                ->setFetchAttachments(false) // Không lấy attachments
                ->get()
                ->map(function ($email) {
                    return [
                        'id' => (string) $email->getMessageId(),
                        'from' => optional($email->getFrom()[0])->mail ?? 'Unknown',
                        'subject' => mb_substr($email->getSubject()->first() ?? 'No Subject', 0, 100), // Giới hạn độ dài subject
                        'date' => $email->getDate()
                            ? Carbon::parse($email->getDate())->format('d/m/Y H:i')
                            : 'Unknown',
                    ];
                })
                ->sortByDesc('date')
                ->take(5)
                ->values(); // Chuyển collection thành array

                dd($emails);
            // Cache kết quả trong 5 phút
            Cache::put('recent_emails', $emails, now()->addMinutes(5));

            return Inertia::render('Emails/Index', [
                'emails' => $emails
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching emails:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Trả về mảng rỗng nếu có lỗi
            return Inertia::render('Emails/Index', [
                'emails' => [],
                'error' => 'Không thể tải email. Vui lòng thử lại sau.'
            ]);
        }
    }

    public function show($id)
    {
        $client = Client::account('default');
        $client->connect();
        $folder = $client->getFolder('INBOX');

        // Lấy email cụ thể (tải nội dung khi cần)
        $email = $folder->query()->whereMessageId($id)->get()->first();

        return view('emails.show', compact('email'));
    }
}
