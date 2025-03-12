<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đăng ký thăm nhà máy</title>
</head>
<body>
    <h2>Xin chào {{ $visitor->name }},</h2>
    <p>Cảm ơn bạn đã đăng ký tham quan nhà máy của chúng tôi vào ngày {{ $visitor->visit_date }}.</p>

    <p><b>QR Code (Base64):</b></p>
    <img src="{!! $qrCodeBase64 !!}" alt="QR Code">

    <p><b>QR Code (File):</b></p>
    <img src="{{ asset('storage/' . $visitor->qr_code) }}" alt="QR Code">

    <p>Trân trọng,<br>Đội ngũ Nhà Máy</p>
</body>
</html>
