<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Status Update</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; margin-top: 20px; margin-bottom: 20px;">
        <tr>
            <td style="padding: 24px; background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                <h1 style="color: #ffffff; margin: 0; font-size: 26px;">Return Status Update</h1>
                <p style="color: rgba(255,255,255,0.85); margin: 6px 0 0; font-size: 16px;">Return #{{ $orderReturn->return_number }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="margin: 0 0 16px; color: #374151; font-size: 17px; line-height: 1.6;">
                Hello <strong>{{ $orderReturn->user->name }}</strong>,
                </p>
                <p style="margin: 0 0 16px; color: #374151; font-size: 17px; line-height: 1.6;">
                    There has been an update to your return request.
                </p>

                @php
                    $statusColors = [
                        'approved' => ['bg' => '#ecfdf5', 'border' => '#a7f3d0', 'text' => '#065f46', 'label' => '✅ Approved'],
                        'rejected' => ['bg' => '#fef2f2', 'border' => '#fecaca', 'text' => '#991b1b', 'label' => '❌ Rejected'],
                        'refunded' => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1e40af', 'label' => '💰 Refunded'],
                    ];
                    $status = $statusColors[$orderReturn->status] ?? ['bg' => '#f9fafb', 'border' => '#e5e7eb', 'text' => '#374151', 'label' => ucfirst($orderReturn->status)];
                @endphp

                <div style="background: {{ $status['bg'] }}; border: 1px solid {{ $status['border'] }}; border-radius: 12px; padding: 14px; margin-bottom: 16px;">
                    <p style="margin: 0; font-size: 17px; color: {{ $status['text'] }}; font-weight: 700;">{{ $status['label'] }}</p>
                </div>

                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px; font-size: 15px; color: #6b7280; font-weight: 600;">Return Details</p>
                    <div style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #6b7280; font-size: 15px;">Order:</span>
                        <span style="color: #374151; font-size: 16px;">#{{ $orderReturn->order->order_number }}</span>
                    </div>
                    <div style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #6b7280; font-size: 15px;">Reason:</span>
                        <span style="color: #374151; font-size: 16px;">{{ $orderReturn->reason }}</span>
                    </div>
                    <div style="padding: 8px 0;">
                        <span style="color: #6b7280; font-size: 15px;">Status:</span>
                        <span style="color: {{ $status['text'] }}; font-size: 16px; font-weight: 600;">{{ ucfirst($orderReturn->status) }}</span>
                    </div>
                </div>

                @if($orderReturn->admin_notes)
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; margin-bottom: 16px;">
                    <p style="margin: 0; font-size: 15px; color: #6b7280; font-weight: 600;">Admin Note</p>
                    <p style="margin: 6px 0 0; font-size: 15px; color: #374151;">{{ $orderReturn->admin_notes }}</p>
                </div>
                @endif

                <p style="margin: 16px 0 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                    You can track your return from your dashboard for more details.
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
