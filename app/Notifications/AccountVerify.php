<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as AccountVerifyBase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class AccountVerify extends AccountVerifyBase implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable) {
        $verificationUrl = "https://mirror-app.com/verify-account/".$notifiable->token;
        return (new MailMessage)
            ->greeting('Hi!')
            ->subject('Please confirm your email address.')
            ->line('Please click on the link below to verify your email address.')
            ->action('Confirm your email address.', $verificationUrl);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    /**
     * Get The verification URL for
     *
     * @param mixed $notifiable
     *
     */
    protected function verificationUrl($notifiable) {
        return URL::temporarySignedRoute('verification.verify',
            Carbon::now()->addMinutes(60), ['token' => $notifiable->token]
        );
    }
}
