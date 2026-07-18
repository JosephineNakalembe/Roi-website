<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Request Received</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; margin-top: 20px; margin-bottom: 20px;">
        <tr>
            <td style="padding: 24px; background: linear-gradient(135deg, #d97706, #b45309);">
                <h1 style="color: #ffffff; margin: 0; font-size: 26px;">Return Request Received</h1>
                <p style="color: rgba(255,255,255,0.85); margin: 6px 0 0; font-size: 16px;">Return #{{ $orderReturn->return_number }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="margin: 0 0 16px; color: #374151; font-size: 17px; line-height: 1.6;">
                Hello <strong>{{ $orderReturn->user->name }}</strong>,
                </p>
                <p style="margin: 0 0 16px; color: #374151; font-size: 17px; line-height: 1.6;">
                    We have received your return request for Order #{{ $orderReturn->order->order_number }}.
                </p>

                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px; font-size: 15px; color: #6b7280; font-weight: 600;">Return Details</p>
                    <div style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #6b7280; font-size: 15px;">Reason:</span>
                        <span style="color: #374151; font-size: 16px; font-weight: 600;">{{ $orderReturn->reason }}</span>
                    </div>
                    <div style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #6b7280; font-size: 15px;">Status:</span>
                        <span style="color: #d97706; font-size: 16px; font-weight: 600;">Pending Review</span>
                    </div>
                    @if($orderReturn->notes)
                    <div style="padding: 8px 0;">
                        <span style="color: #6b7280; font-size: 15px;">Notes:</span>
                        <span style="color: #374151; font-size: 16px;">{{ $orderReturn->notes }}</span>
                    </div>
                    @endif
                </div>

                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 14px; margin-bottom: 16px;">
                    <p style="margin: 0; font-size: 15px; color: #92400e; font-weight: 600;">📦 Pickup Info</p>
                    <p style="margin: 6px 0 0; font-size: 15px; color: #92400e;">{{ $orderReturn->pickup_address }}, {{ $orderReturn->pickup_area }}</p>
                    <p style="margin: 6px 0 0; font-size: 15px; color: #92400e;">Refund to: {{ $orderReturn->refund_network }} — {{ $orderReturn->refund_number }}</p>
                </div>

                <p style="margin: 16px 0 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                    Our team will review your request and get back to you shortly. You can track your return from your dashboard.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding: 16px 24px; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                <p style="margin: 0; font-size: 14px; color: #9ca3af; text-align: center;">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
