<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $token
    ) {}

    public function via(object $notifiable): array
    {
        return [
            'mail'
        ];
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = config('app.frontend_url')
            . '/reset-password?token='
            . $this->token
            . '&email='
            . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Redefinição de senha')
            ->greeting('Olá!')
            ->line('Recebemos uma solicitação para redefinir sua senha.')
            ->action('Redefinir senha', $url)
            ->line('Esse link expira em 60 minutos.')
            ->line('Caso você não tenha solicitado, ignore este e-mail.');
    }
}
