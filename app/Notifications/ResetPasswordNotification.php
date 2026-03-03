<?php

namespace App\Notifications;

use App\Services\ResendMailService;
use Illuminate\Support\Facades\Log;

class ResetPasswordNotification
{
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Send the password reset email via Resend HTTP API.
     */
    public function send(object $notifiable): void
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $userName = $notifiable->name ?? 'there';

        $html = view('emails.password-reset', [
            'resetUrl' => $resetUrl,
            'userName' => $userName,
        ])->render();

        $sent = ResendMailService::send(
            $notifiable->getEmailForPasswordReset(),
            'Reset Your Password — Real AI Trading',
            $html
        );

        if ($sent) {
            Log::info('Password reset email sent', ['email' => $notifiable->getEmailForPasswordReset()]);
        } else {
            Log::error('Password reset email failed', ['email' => $notifiable->getEmailForPasswordReset()]);
            throw new \Exception('Failed to send password reset email');
        }
    }
}
