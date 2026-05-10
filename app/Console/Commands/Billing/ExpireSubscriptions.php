<?php

namespace App\Console\Commands\Billing;

use App\Modules\Billing\Models\Subscription;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('billing:expire-subscriptions')]
#[Description('Mark active subscriptions as expired when their expired_at has passed')]
class ExpireSubscriptions extends Command
{
    public function handle(): int
    {
        $count = Subscription::where('status', 'active')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$count} subscription(s).");

        return Command::SUCCESS;
    }
}
