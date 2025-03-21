<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Google\Client as Google_Client;
use Google\Service\Gmail as Google_Service_Gmail;
use Inertia\Inertia;

class GmailController extends Controller
{
    public function getClient()
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(route('gmail.callback'));
        $client->addScope(Google_Service_Gmail::GMAIL_READONLY);
        $client->setAccessType('offline'); // Quan trọng để nhận refresh_token
        $client->setPrompt('consent'); // Yêu cầu cấp quyền mỗi lần
        return $client;
    }

    public function redirectToGoogle()
    {
        $client = $this->getClient();
        $client->setRedirectUri(route('gmail.callback')); // Đảm bảo callback đúng
        $authUrl = $client->createAuthUrl();
        return redirect()->away($authUrl);
    }
    public function handleGoogleCallback(Request $request)
    {
        $client = $this->getClient();
        $token = $client->fetchAccessTokenWithAuthCode($request->code);

        if (isset($token['error'])) {
            return redirect()->route('gmail.auth')->with('error', 'Xác thực thất bại.');
        }

        // Lưu thời gian hết hạn
        $token['expires_at'] = now()->addSeconds($token['expires_in']);
        session(['google_token' => $token]);

        session(['google_token' => $token]);
        return redirect()->route('gmail.inbox');
    }

    public function listEmails()
    {
        $client = $this->getClient();
        $token = session('google_token');
        // Kiểm tra nếu token đã hết hạn
        if (!$token) {
            session()->forget('google_token');
            return redirect()->route('gmail.auth');
        }

        $client->setAccessToken($token);
        $service = new Google_Service_Gmail($client);
        $messages = $service->users_messages->listUsersMessages('me', ['maxResults' => 10]);

        $emails = [];

        foreach ($messages->getMessages() as $message) {
            $msg = $service->users_messages->get('me', $message->getId());
            $headers = $msg->getPayload()->getHeaders();

            $emailData = [
                'id' => $message->getId(),
                'from' => $this->getHeader($headers, 'From'),
                'subject' => $this->getHeader($headers, 'Subject'),
                'date' => $this->formatDate($this->getHeader($headers, 'Date')),
                'snippet' => $msg->getSnippet(),
            ];

            $emails[] = $emailData;
        }

        // Sắp xếp theo thời gian gửi (mới nhất trước)
        usort($emails, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return Inertia::render('Emails/Index', ['emails' => $emails]);
    }

    private function getHeader($headers, $name)
    {
        foreach ($headers as $header) {
            if ($header->getName() === $name) {
                return $header->getValue();
            }
        }
        return 'Không có dữ liệu';
    }

    private function formatDate($dateString)
    {
        try {
            return Carbon::parse($dateString)->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s');
        } catch (\Exception $e) {
            return 'Không có ngày';
        }
    }

    public function showEmail($id)
    {
        $client = new Google_Client();
        $client->setAuthConfig(storage_path('app/credentials.json'));
        $client->setAccessType('offline');
        $client->setScopes([Google_Service_Gmail::GMAIL_READONLY]);

        $token = session('google_token');
        if (!$token) {
            return redirect()->route('gmail.auth'); // Yêu cầu đăng nhập nếu chưa có token
        }

        $client->setAccessToken($token);
        $service = new Google_Service_Gmail($client);

        try {
            $message = $service->users_messages->get('me', $id);
            $payload = $message->getPayload();
            $headers = $payload->getHeaders();

            $emailDetail = [
                'id' => $id,
                'from' => $this->getHeader($headers, 'From'),
                'subject' => $this->getHeader($headers, 'Subject'),
                'date' => $this->formatDate($this->getHeader($headers, 'Date')),
                'body' => $this->getEmailBody($payload),
                'images' => $this->getEmailImages($payload),
                'attachments' => $this->getAttachments($service, $message)
            ];
            
            // dd($emailDetail);

            return Inertia::render('Emails/Show', ['email' => $emailDetail]);
        } catch (\Exception $e) {
            return redirect()->route('emails.list')->with('error', 'Không thể lấy email.');
        }
    }


    private function getEmailBody($payload)
    {
        // Nếu email không có phần tử con, lấy nội dung trực tiếp từ `body`
        if ($payload->getBody() && $payload->getBody()->getData()) {
            return base64_decode(str_replace(['-', '_'], ['+', '/'], $payload->getBody()->getData()));
        }

        // Nếu email có các phần con (multipart), duyệt qua các phần đó
        if ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                $mimeType = $part->getMimeType();

                // Debug để kiểm tra loại MIME
                // dd($mimeType);

                // Nếu gặp `multipart/related` hoặc `multipart/alternative`, thì cần đi sâu hơn
                if (strpos($mimeType, 'multipart/') === 0 && $part->getParts()) {
                    return $this->getEmailBody($part); // Gọi đệ quy để lấy nội dung sâu hơn
                }

                // Nếu có HTML, ưu tiên lấy nội dung HTML
                if ($mimeType === "text/html") {
                    return base64_decode(str_replace(['-', '_'], ['+', '/'], $part->getBody()->getData()));
                }
            }

            // Nếu không tìm thấy HTML, thử lấy text/plain
            foreach ($payload->getParts() as $part) {
                if ($part->getMimeType() === "text/plain") {
                    return nl2br(base64_decode(str_replace(['-', '_'], ['+', '/'], $part->getBody()->getData())));
                }
            }
        }

        return 'Không có nội dung email.';
    }



    private function getEmailImages($payload)
    {
        $images = [];
    
        if ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                if ($part->getMimeType() && strpos($part->getMimeType(), 'image/') === 0) {
                    $imageData = base64_decode(str_replace(['-', '_'], ['+', '/'], $part->getBody()->getData()));
    
                    // Tạo mã base64 cho ảnh
                    $images[$part->getHeaders()[0]->getValue()] = 'data:' . $part->getMimeType() . ';base64,' . base64_encode($imageData);
                }
    
                // Kiểm tra nếu có sub-parts
                if ($part->getParts()) {
                    foreach ($part->getParts() as $subPart) {
                        if ($subPart->getMimeType() && strpos($subPart->getMimeType(), 'image/') === 0) {
                            $imageData = base64_decode(str_replace(['-', '_'], ['+', '/'], $subPart->getBody()->getData()));
    
                            // Lưu ảnh vào danh sách (dùng CID làm key)
                            $images[$subPart->getHeaders()[0]->getValue()] = 'data:' . $subPart->getMimeType() . ';base64,' . base64_encode($imageData);
                        }
                    }
                }
            }
        }
    
        return $images;
    }
    
    private function getAttachments($service, $message)
{
    $attachments = [];

    if (!$message->getPayload()->getParts()) {
        return $attachments;
    }

    foreach ($message->getPayload()->getParts() as $part) {
        if (!empty($part->getFilename())) {
            $attachmentId = $part->getBody()->getAttachmentId();
            if ($attachmentId) {
                $attachment = $service->users_messages_attachments->get('me', $message->getId(), $attachmentId);
                $data = base64_decode(str_replace(['-', '_'], ['+', '/'], $attachment->getData()));

                $attachments[] = [
                    'filename' => $part->getFilename(),
                    'mimeType' => $part->getMimeType(),
                    'data' => 'data:' . $part->getMimeType() . ';base64,' . base64_encode($data),
                ];
            }
        }
    }

    return $attachments;
}

}
