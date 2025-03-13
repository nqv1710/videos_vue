<?php

namespace App\Mail;

use App\Models\FactoryVisitor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VisitConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $visitor;

    public function __construct(FactoryVisitor $visitor,)
    {
        $this->visitor = $visitor;
    }

    public function build()
    {
        return $this->view('emails.visit-confirmation')
        ->subject('Xác nhận đăng ký tham quan')
        ->attach(storage_path('app/public/' . $this->visitor->qr_code), [
            'as' => 'qrcode.png',
            'mime' => 'image/png',
        ]);
    }
}
