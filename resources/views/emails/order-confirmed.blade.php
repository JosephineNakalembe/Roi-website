<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; margin-top: 20px; margin-bottom: 20px;">
        <tr>
            <td style="padding: 24px; background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                <h1 style="color: #ffffff; margin: 0; font-size: 26px;">Order Confirmed</h1>
                <p style="color: rgba(255,255,255,0.85); margin: 6px 0 0; font-size: 16px;">Order #{{ $order->order_number }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="margin: 0 0 16px; color: #374151; font-size: 17px; line-height: 1.6;">
                Hello <strong>{{ $order->user->name }}</strong>,
                </p>
                <p style="margin: 0 0 16px; color: #374151; font-size: 17px; line-height: 1.6;">
                    Thank you for your order! We have received your order and it is now being processed.
                </p>

                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px; font-size: 15px; color: #6b7280; font-weight: 600;">Order Summary</p>
                    @foreach($order->items as $item)
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #374151; font-size: 16px;">{{ $item->product_name }} × {{ $item->quantity }}</span>
                        <span style="color: #374151; font-size: 16px; font-weight: 600;">UGX {{ number_format($item->total_price) }}</span>
                    </div>
                    @endforeach
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                        <span style="color: #6b7280; font-size: 15px;">Delivery ({{ $order->delivery_area }})</span>
                        <span style="color: #374151; font-size: 15px;">UGX {{ number_format($order->shipping) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0 0;">
                        <span style="color: #111827; font-size: 17px; font-weight: 700;">Total</span>
                        <span style="color: #2563eb; font-size: 17px; font-weight: 700;">UGX {{ number_format($order->total) }}</span>
                    </div>
                </div>

                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 14px; margin-bottom: 16px;">
                    <p style="margin: 0; font-size: 15px; color: #1e40af;">📍 Delivery to: {{ $order->shipping_name }}, {{ $order->delivery_area }}</p>
                    <p style="margin: 6px 0 0; font-size: 15px; color: #1e40af;">📞 {{ $order->shipping_phone }}</p>
                </div>

                <p style="margin: 16px 0 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                    You can track your order status from your account dashboard.
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
