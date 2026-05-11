<?php

namespace App\Providers;

use App\Modules\File\Models\TaskAttachment;
use App\Modules\Task\Models\Task;
use App\Modules\Task\Models\TaskComment;
use App\Policies\TaskAttachmentPolicy;
use App\Policies\TaskCommentPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(TaskComment::class, TaskCommentPolicy::class);
        Gate::policy(TaskAttachment::class, TaskAttachmentPolicy::class);

        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject('Verify your email — Tenant Workspace')
                ->view('emails.verify', ['user' => $notifiable, 'url' => $url]);
        });
    }
}
