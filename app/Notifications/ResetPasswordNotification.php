<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
{
    $url = config('app.frontend_url') 
        . '/ResetPassword?token=' . $this->token
        . '&email=' . urlencode($notifiable->email);

    return (new MailMessage)
        ->subject('Reset Password Notification')
        ->view('emails.reset-password', [
            'token' => $this->token,
            'email' => $notifiable->email,
            'url' => $url,
        ]);
}
}
