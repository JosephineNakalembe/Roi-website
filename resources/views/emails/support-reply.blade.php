<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket Reply</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; margin-top: 20px; margin-bottom: 20px;">
        <tr>
            <td style="padding: 24px; background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                <h1 style="color: #ffffff; margin: 0; font-size: 26px;">Support Ticket Update</h1>
                <p style="color: rgba(255,255,255,0.85); margin: 6px 0 0; font-size: 16px;">{{ $ticket->subject }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 24px;">
                <p style="margin: 0 0 16px; color: #374151; font-size: 17px; line-height: 1.6;">
                Hello <strong>{{ $ticket->user->name }}</strong>,
                </p>
                <p style="margin: 0 0 16px; color: #374151; font-size: 17px; line-height: 1.6;">
                    The admin has responded to your support ticket. Here is the full conversation history:
                </p>

                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <!-- Initial User Message -->
                    <div style="margin-bottom: 12px; text-align: right;">
                        <div style="display: inline-block; background: #2563eb; color: #fff; padding: 10px 14px; border-radius: 12px 12px 4px 12px; font-size: 16px; max-width: 80%;">
                            <p style="margin: 0; font-size: 13px; opacity: 0.7;">You</p>
                            <p style="margin: 4px 0 0;">{{ $ticket->message }}</p>
                            <p style="margin: 4px 0 0; font-size: 13px; opacity: 0.7;">{{ $ticket->created_at->format('M d, H:i') }}</p>
                        </div>
                    </div>

                    <!-- Replies -->
                    @if(!empty($allReplies))
                        @foreach($allReplies as $reply)
                            @if($reply['sender'] === 'admin')
                                <div style="margin-bottom: 12px; text-align: left;">
                                    <div style="display: inline-block; background: #f3f4f6; color: #111; padding: 10px 14px; border-radius: 12px 12px 12px 4px; font-size: 16px; max-width: 80%;">
                                        <p style="margin: 0; font-weight: 600; font-size: 13px; color: #6b7280;">Admin</p>
                                        <p style="margin: 4px 0 0;">{{ $reply['message'] }}</p>
                                        <p style="margin: 4px 0 0; font-size: 13px; color: #9ca3af;">{{ \Carbon\Carbon::parse($reply['created_at'])->format('M d, H:i') }}</p>
                                    </div>
                                </div>
                            @else
                                <div style="margin-bottom: 12px; text-align: right;">
                                    <div style="display: inline-block; background: #2563eb; color: #fff; padding: 10px 14px; border-radius: 12px 12px 4px 12px; font-size: 16px; max-width: 80%;">
                                        <p style="margin: 0; font-size: 13px; opacity: 0.7;">You</p>
                                        <p style="margin: 4px 0 0;">{{ $reply['message'] }}</p>
                                        <p style="margin: 4px 0 0; font-size: 13px; opacity: 0.7;">{{ \Carbon\Carbon::parse($reply['created_at'])->format('M d, H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif

                    <!-- Latest Admin Reply (the one just sent) - it will be the last admin reply in the list -->
                    @php
                        $latestAdminReply = collect($allReplies)->where('sender', 'admin')->last();
                    @endphp
                    @if($latestAdminReply)
                        <div style="text-align: left; padding: 12px; background: #fefce8; border: 1px solid #fde68a; border-radius: 8px; margin-top: 8px;">
                            <p style="margin: 0; font-size: 14px; color: #92400e; font-weight: 600;">⬆ Latest Reply</p>
                        </div>
                    @endif
                </div>

                <p style="margin: 16px 0 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                    To reply to this ticket, please visit your dashboard:
                    <a href="{{ url('/customer-service') }}" style="color: #2563eb; font-weight: 600; text-decoration: underline;">Customer Service</a>
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