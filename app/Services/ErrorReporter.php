<?php

namespace App\Services;

use App\Mail\SystemErrorMail;
use App\Models\SystemError;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ErrorReporter
{
    public static function report(Throwable $e, ?Request $request = null): void
    {
        try {
            $key = 'system-error:' . md5(get_class($e) . '|' . $e->getMessage());

            // Throttle so the same error only reports once every 5 minutes.
            if (!Cache::add($key, true, now()->addMinutes(5))) {
                return;
            }

            $url = $request ? $request->fullUrl() : 'n/a';

            // Always store a record so admins can see it in the dashboard.
            SystemError::create([
                'level' => 'error',
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'url' => $url,
            ]);

            Log::error('System error: ' . get_class($e) . ' — ' . $e->getMessage() . ' — ' . $url, [
                'exception' => $e,
            ]);

            $adminEmails = User::all()
                ->filter(fn ($user) => $user->isAdmin())
                ->pluck('email')
                ->unique()
                ->values();

            if ($adminEmails->isEmpty()) {
                $adminEmails = collect(['josephinenakalembe33@gmail.com']);
            }

            foreach ($adminEmails as $email) {
                // Queued so the buyer's error page is never delayed by the mail send.
                Mail::to($email)->queue(new SystemErrorMail($e, $url));
            }
        } catch (Throwable $ignore) {
            // Never let error reporting itself break the request.
        }
    }
}
