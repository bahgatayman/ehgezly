<?php 
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WelcomeNotification extends Notification
{
    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Welcome to Ehgezly ⚽')
            ->view('emails.welcome', [
                'name' => $notifiable->name,
                'email' => $notifiable->email,
            ]);
    }
}