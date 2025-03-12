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
    public $qrCodeBase64;

    public function __construct(FactoryVisitor $visitor, $qrCodeBase64)
    {
        $this->visitor = $visitor;
        $this->qrCodeBase64 = $qrCodeBase64;
    }

    public function build()
    {
        return $this->view('emails.visit-confirmation')
                    ->subject('Xác nhận đăng ký tham quan');
    }
}
