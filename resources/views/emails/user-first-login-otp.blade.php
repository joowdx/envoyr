<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 8px; }
        .content { padding: 20px 0; }
        .otp-code { background: #e3f2fd; padding: 15px; text-align: center; border-radius: 8px; margin: 20px 0; }
        .otp-number { font-size: 24px; font-weight: bold; color: #1976d2; letter-spacing: 3px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 14px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to {{ config('app.name') }}!</h1>
        </div>
        
        <div class="content">
            <p>Your account has been created successfully. Use the one-time password below to log in and set up your permanent password.</p>
            
            <div class="otp-code">
                <p><strong>Your one-time login code:</strong></p>
                <div class="otp-number">{{ $otp }}</div>
            </div>
            
            <p><strong>Important:</strong></p>
            <ul>
                <li>This code will expire after your first login</li>
                <li>You'll be prompted to create a new password after using this code</li>
                <li>Keep this code secure and don't share it with anyone</li>
            </ul>
        </div>
        
        <div class="footer">
            <p>If you didn't expect this email, please contact our support team.</p>
            <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>