<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInviteNotification extends Notification
{
    public function __construct(public string $token) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
            'invite' => 1,
        ]);

        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('You have been invited to AgriStock')
            ->greeting("Hello, {$notifiable->name}!")
            ->line("An administrator created an AgriStock account for you with the role: {$notifiable->role->value}.")
            ->action('Set your password', $url)
            ->line("This link expires in {$minutes} minutes. If it expires, ask your administrator to resend the invite.")
            ->line('If you were not expecting this, you can ignore this email.');
    }
}
