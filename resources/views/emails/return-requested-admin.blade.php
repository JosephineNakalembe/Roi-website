<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Return Request</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; margin-top: 20px; margin-bottom: 20px;">
        <tr>
            <td style="padding: 24px; background: linear-gradient(135deg, #1a1a2e, #2d2d44);">
                <img src="https://www.roistore.shop/favicon.png" alt="ROI Store" width="56" height="56" style="width:56px;height:56px;border-radius:50%;background:#ffffff;padding:6px;display:block;margin:0 0 12px;">
                <h1 style="color: #ffffff; margin: 0; font-size: 26px;">New Return Request</h1>
                <p style="color: rgba(255,255,255,0.85); margin: 6px 0 0; font-size: 16px;">Return #{{ $orderReturn->return_number }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="margin: 0 0 16px; color: #374151; font-size: 17px; line-height: 1.6;">
                    A customer has just submitted a return request and needs your attention.
                </p>

                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px; font-size: 15px; color: #6b7280; font-weight: 600;">Customer Details</p>
                    <p style="margin: 0 0 4px; color: #374151; font-size: 16px;"><strong>{{ $orderReturn->user->name }}</strong> ({{ $orderReturn->user->email }})</p>
                    <p style="margin: 0 0 4px; color: #374151; font-size: 16px;">Order #: {{ $orderReturn->order->order_number }}</p>
                    <p style="margin: 0; color: #374151; font-size: 16px;">📞 {{ $orderReturn->user->phone ?? 'N/A' }}</p>
                </div>

                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px; font-size: 15px; color: #6b7280; font-weight: 600;">Return Details</p>
                    <div style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #6b7280; font-size: 15px;">Reason:</span>
                        <span style="color: #374151; font-size: 16px; font-weight: 600;">{{ $orderReturn->reason }}</span>
                    </div>
                    @if($orderReturn->notes)
                    <div style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #6b7280; font-size: 15px;">Notes:</span>
                        <span style="color: #374151; font-size: 16px;">{{ $orderReturn->notes }}</span>
                    </div>
                    @endif
                    <div style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #6b7280; font-size: 15px;">Pickup:</span>
                        <span style="color: #374151; font-size: 16px;">{{ $orderReturn->pickup_address }}, {{ $orderReturn->pickup_area }}</span>
                    </div>
                    <div style="padding: 8px 0;">
                        <span style="color: #6b7280; font-size: 15px;">Refund to:</span>
                        <span style="color: #374151; font-size: 16px;">{{ $orderReturn->refund_network }} — {{ $orderReturn->refund_number }} ({{ $orderReturn->refund_name }})</span>
                    </div>
                </div>

                <p style="margin: 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                    You can review and process this return from your admin dashboard at
                    <a href="{{ url('/admin/returns/' . $orderReturn->id) }}" style="color: #1a1a2e; font-weight: 600; text-decoration: underline;">Manage Returns</a>.
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