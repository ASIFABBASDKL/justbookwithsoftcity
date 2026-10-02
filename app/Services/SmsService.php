<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * Send an SMS via Twilio. If credentials are missing, skip send
     * (and optionally log the OTP in local/debug — never return it from APIs).
     */
    public function send(string $to, string $message): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (config('services.twilio.log_otp')) {
            Log::debug('SMS payload (Twilio log_otp enabled)', [
                'to' => $to,
                'body' => $message,
            ]);
        }

        if (! $sid || ! $token || ! $from) {
            Log::warning('SMS not sent: Twilio is not configured.');

            return false;
        }

        $response = Http::asForm()
            ->withBasicAuth($sid, $token)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To' => $to,
                'Body' => $message,
            ]);

        if (! $response->successful()) {
            Log::error('Twilio SMS failed', [
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            return false;
        }

        return true;
    }

    public function sendOtp(string $to, string $otp): bool
    {
        $app = config('app.name', 'JustBook');

        return $this->send($to, "{$app} verification code: {$otp}. Valid for 10 minutes.");
    }
}
