<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $rejectionReason;

    public function __construct(User $user, string $rejectionReason)
    {
        $this->user = $user;
        $this->rejectionReason = $rejectionReason;
    }

    public function build()
    {
        return $this->subject('بشأن طلبك في منصة إهجزلي')
            ->view('emails.owner_rejected')
            ->with([
                'name' => $this->user->name,
                'rejection_reason' => $this->rejectionReason,
            ]);
    }
}
