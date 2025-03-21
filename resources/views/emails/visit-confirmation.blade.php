<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đăng ký thăm nhà máy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .content {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }

        .qr-code {
            text-align: center;
            margin: 20px 0;
        }

        .qr-code img {
            max-width: 300px;
            height: auto;
        }

        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Xin chào {{ $visitor->name }},</h2>
    </div>

    <div class="content">
        <p>Cảm ơn bạn đã đăng ký tham quan nhà máy của chúng tôi.</p>

        <p><strong>Thông tin đăng ký:</strong></p>
        <ul>
            <li>Ngày tham quan: {{ \Carbon\Carbon::parse($visitor->visit_date)->format('d/m/Y') }}</li>
            <li>Số lượng người: {{ $visitor->number_of_visitors }}</li>
            @if ($visitor->company)
                <li>Công ty: {{ $visitor->company }}</li>
            @endif
        </ul>

        <div class="qr-code">
            <p><strong>Mã QR Code của bạn:</strong></p>
            <img src="{{ asset('storage/' . $visitor->qr_code) }}" alt="QR Code">


            <p>Vui lòng mang theo mã QR code này khi đến tham quan.</p>
        </div>

        <p><strong>Lưu ý:</strong></p>
        <ul>
            <li>Vui lòng đến đúng giờ</li>
            <li>Mang theo giấy tờ tùy thân</li>
            <li>Tuân thủ nội quy an toàn của nhà máy</li>
        </ul>
    </div>

    <div class="footer">
        <p>Trân trọng,<br>Đội ngũ Nhà Máy</p>
        <p>Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>
</body>

</html>
