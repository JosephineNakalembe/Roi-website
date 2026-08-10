<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; margin-top: 20px; margin-bottom: 20px;">
        <tr>
            <td style="padding: 24px; background: #1a1a2e;">
                <h1 style="color: #ffffff; margin: 0; font-size: 26px;">Server Error</h1>
                <p style="color: rgba(255,255,255,0.85); margin: 6px 0 0; font-size: 16px;">Occurred at {{ $occurredAt }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px; font-size: 15px; color: #6b7280; font-weight: 600;">Error</p>
                    <p style="margin: 0 0 4px; color: #111827; font-size: 16px; font-weight: 600;">{{ $errorClass }}</p>
                    <p style="margin: 0 0 8px; color: #374151; font-size: 16px;">{{ $errorMessage }}</p>
                    <p style="margin: 0; color: #9ca3af; font-size: 14px;">{{ $errorFile }} on line {{ $errorLine }}</p>
                </div>

                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px; font-size: 15px; color: #6b7280; font-weight: 600;">Requested URL</p>
                    <p style="margin: 0; color: #2563eb; font-size: 15px; word-break: break-all;">{{ $url }}</p>
                </div>

                <p style="margin: 16px 0 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                    This error was automatically reported and logged. Check the application logs for the full stack trace.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                <p style="margin: 0; font-size: 14px; color: #9ca3af; text-align: center;">
                    &copy; {{ date('Y') }} ROI Store. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
