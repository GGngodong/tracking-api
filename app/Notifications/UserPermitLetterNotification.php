<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class UserPermitLetterNotification extends Notification
{
    protected $permitLetter;
    protected $message;

    /**
     * @param mixed  $permitLetter The permit letter model instance.
     * @param string $message      The custom message to send.
     */
    public function __construct($permitLetter, string $message)
    {
        $this->permitLetter = $permitLetter;
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable)
    {
        return FcmMessage::create()
            ->data([
                'permit_letter_id' => (string) $this->permitLetter->id,
            ])
            ->notification(FcmNotification::create()
                ->title('Permit Letter Update')
                ->body($this->message)
            );
    }
}
