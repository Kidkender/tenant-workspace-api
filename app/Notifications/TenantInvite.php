<?php

namespace App\Notifications;

use App\Modules\Tenant\Models\TenantInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantInvite extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public TenantInvitation $invitation,
        public string $tenantName = '',
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        $url = config('app.frontend_url').'/accept-invite?token='.$this->invitation->token;

        return (new MailMessage)
            ->subject('You\'ve been invited to '.($this->tenantName ?: 'a workspace').' — Tenant Workspace')
            ->view('emails.invite', [
                'url' => $url,
                'invitation' => $this->invitation,
                'tenantName' => $this->tenantName,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
