<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class AdminPermitLetterNotification extends Notification
{
    protected $permitLetter;

    public function __construct($permitLetter)
    {
        $this->permitLetter = $permitLetter;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class, 'database'];
    }

    public function toFcm($notifiable)
    {
        // Optionally, include extra data here if needed.
        return FcmMessage::create()
            ->data([
                'permit_letter_id' => (string) $this->permitLetter->id,
            ])
            ->notification(FcmNotification::create()
                ->title('Permit Letter Submitted')
                ->body("{$this->permitLetter->user->username} from {$this->permitLetter->user->division} has submitted a permit letter and is awaiting your review.")
            );
    }

    public function toDatabase($notifiable)
    {
        return [
            'permit_letter_id' => (string) $this->permitLetter->id,
            'message' => "{$this->permitLetter->user->username} from {$this->permitLetter->user->division} has submitted a permit letter and is awaiting your review.",
            'type' => 'admin_permit_letter',
        ];
    }
}
