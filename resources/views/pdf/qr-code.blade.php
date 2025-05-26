@php
    // $qrCode: data URI PNG
    // $code: document code
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Document QR Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .qr-wrapper {
            position: relative;
            width: 256px;
            height: 256px;
            margin: 40px auto 0 auto;
        }
        .overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .overlay-text {
            background: rgba(255,255,255,0.9);
            color: #111;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 6px;
            padding: 6px 18px;
            border: 1px solid #bbb;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            min-width: 80px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="qr-wrapper">
            <img src="{{ $qrCode }}" alt="QR Code" style="width: 100%; height: 100%;" />
            <div class="overlay">
                <span class="overlay-text">{{ $code }}</span>
            </div>
        </div>
    </div>
</body>
</html> 