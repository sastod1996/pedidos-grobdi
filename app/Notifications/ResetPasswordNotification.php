<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends BaseNotification
{
    /**
     * Build the mail representation of the notification.
     */
    protected function buildMailMessage($url)
    {
        $appName = 'sysgrob';
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name', 'Equipo de sistemas de sysgrob');
        $supportEmail = $fromAddress ?: 'soporte@grobdi.com';
        $expiration = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        $viewData = [
            'appName' => $appName,
            'actionUrl' => $url,
            'expiration' => $expiration,
            'supportEmail' => $supportEmail,
        ];

        return (new MailMessage)
            ->subject('sysgrob | Restablece tu contraseña')
            ->from($fromAddress, $fromName)
            ->view('emails.auth.password-reset', $viewData)
            ->text('emails.auth.password-reset-text', $viewData);
    }
}
