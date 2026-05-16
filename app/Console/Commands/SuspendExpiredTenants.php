<?php

namespace App\Console\Commands;

use App\Modules\Billing\Models\Subscription;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:suspend-expired-tenants')]
#[Description('Command description')]
class SuspendExpiredTenants extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $subscriptions = Subscription::where('status', 'active')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            $tenant = $subscription->tenant;
            $this->info("Suspending tenant: {$tenant->id}");
            $tenant->update(['status' => 'expired']);
            $this->info("Suspending subscription: {$subscription->id}");
            $subscription->update(['status' => 'suspended']);
        }
    }
}
