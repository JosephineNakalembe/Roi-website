<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #111;
            margin: 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .code {
            background-color: #f0f0f0;
            padding: 15px 30px;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 8px;
            text-align: center;
            border-radius: 4px;
            margin: 20px 0;
            color: #111;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Reset Code</h1>
        </div>
        <div class="content">
            <p>Hello {{ $user->name }},</p>
            <p>We received a request to reset your password. Use the verification code below to proceed:</p>
            <div class="code">{{ $code }}</div>
            <p>This code will expire in 15 minutes.</p>
            <p>If you didn't request a password reset, you can safely ignore this email.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} ROI Shop. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
