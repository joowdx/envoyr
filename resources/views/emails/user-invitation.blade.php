<!DOCTYPE html>
<html>
<head>
    <title>You're Invited to Join {{ config('app.name') }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #ffffff; padding: 30px; border: 1px solid #e9ecef; }
        .footer { background: #f8f9fa; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; color: #6c757d; }
        .btn { display: inline-block; padding: 12px 24px; background: #007bff; color: #ffffff; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .btn:hover { background: #0056b3; }
        .details { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <h2>You're Invited!</h2>
        </div>
        
        <div class="content">
            <p>Hello,</p>
            
            <p>You have been invited to join <strong>{{ config('app.name') }}</strong> as a <strong>{{ $roleName }}</strong>{{ $officeName ? ' at ' . $officeName : '' }}.</p>
            
            <div class="details">
                <strong>Your Account Details:</strong><br>
                <strong>Email:</strong> {{ $user->email }}<br>
                <strong>Role:</strong> {{ $roleName }}<br>
                @if($officeName)
                <strong>Office:</strong> {{ $officeName }}<br>
                @endif
            </div>
            
            <p>To complete your registration, please click the button below:</p>
            
            <div style="text-align: center;">
                <a href="{{ $registrationUrl }}" class="btn">Complete Registration</a>
            </div>
            
            <p><strong>What's next?</strong></p>
            <ol>
                <li>Click the registration link above</li>
                <li>Enter your full name and choose a password</li>
                <li>Provide your designation/job title</li>
                <li>Start using the system!</li>
            </ol>
            
            <p><strong>Security Note:</strong> This invitation link is secure and will expire automatically. If you didn't expect this invitation, please ignore this email.</p>
            
            <p>If you have any questions, please contact your system administrator.</p>
            
            <p>Welcome to the team!</p>
        </div>
        
        <div class="footer">
            <p>This is an automated message from {{ config('app.name') }}.</p>
        </div>
    </div>
</body>
</html>
