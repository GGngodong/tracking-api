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
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        $username = $this->permitLetter->user ? $this->permitLetter->user->username : 'A user';

        return FcmMessage::create()
            ->data([
                'permit_letter_id' => (string) $this->permitLetter->id,
            ])
            ->notification(FcmNotification::create()
                ->title('Permit Letter Submitted')
                ->body("$username has submitted a permit letter and is awaiting your review.")
            );
    }
}
