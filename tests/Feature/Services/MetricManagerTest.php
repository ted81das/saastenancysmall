<?php

namespace Tests\Feature\Services;

use App\Constants\SubscriptionStatus;
use App\Constants\TransactionStatus;
use App\Models\Currency;
use App\Models\Interval;
use App\Models\PaymentProvider;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MetricsManager;
use Illuminate\Support\Str;
use Tests\Feature\FeatureTest;

class MetricManagerTest extends FeatureTest
{
    public function test_calculate_daily_revenue()
    {
        Transaction::query()->update(['status' => TransactionStatus::FAILED->value]);

        $tenant = $this->createTenant();
        $user = $this->createUser($tenant);

        Transaction::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'uuid' => Str::uuid(),
            'amount' => 1000,
            'currency_id' => Currency::where('code', 'USD')->firstOrFail()->id,
            'status' => TransactionStatus::SUCCESS,
            'payment_provider_id' => PaymentProvider::firstOrFail()->id,
            'payment_provider_status' => 'success',
            'payment_provider_transaction_id' => '234',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'uuid' => Str::uuid(),
            'amount' => 1000,
            'currency_id' => Currency::where('code', 'USD')->firstOrFail()->id,
            'status' => TransactionStatus::SUCCESS,
            'payment_provider_id' => PaymentProvider::firstOrFail()->id,
            'payment_provider_status' => 'success',
            'payment_provider_transaction_id' => '234',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'uuid' => Str::uuid(),
            'amount' => 1000,
            'currency_id' => Currency::where('code', 'USD')->firstOrFail()->id,
            'status' => TransactionStatus::REFUNDED,
            'payment_provider_id' => PaymentProvider::firstOrFail()->id,
            'payment_provider_status' => 'success',
            'payment_provider_transaction_id' => '234',
        ]);

        $metricManager = new MetricsManager();
        $result = $metricManager->calculateDailyRevenue(now());

        $this->assertEquals($result, 10.00);
    }

    public function test_average_revenue_per_user()
    {
        $tenant = $this->createTenant();

        Transaction::query()->update(['status' => TransactionStatus::FAILED->value]);

        $weekAgo = now()->subWeek()->endOfDay();

        $user1 = User::factory()->create([
            'created_at' => $weekAgo,
        ]);

        $tenant->users()->attach($user1);

        $user2 = User::factory()->create([
            'created_at' => $weekAgo,
        ]);

        $tenant->users()->attach($user2);

        $transaction = Transaction::create([
            'user_id' => $user1->id,
            'tenant_id' => $tenant->id,
            'uuid' => Str::uuid(),
            'amount' => 1000,
            'currency_id' => Currency::where('code', 'USD')->firstOrFail()->id,
            'status' => TransactionStatus::SUCCESS->value,
            'payment_provider_id' => PaymentProvider::firstOrFail()->id,
            'payment_provider_status' => 'success',
            'payment_provider_transaction_id' => '223434',
        ]);

        $transaction->created_at = $weekAgo;
        $transaction->save();

        $transaction = Transaction::create([
            'user_id' => $user2->id,
            'tenant_id' => $tenant->id,
            'uuid' => Str::uuid(),
            'amount' => 1000,
            'currency_id' => Currency::where('code', 'USD')->firstOrFail()->id,
            'status' => TransactionStatus::SUCCESS->value,
            'payment_provider_id' => PaymentProvider::firstOrFail()->id,
            'payment_provider_status' => 'success',
            'payment_provider_transaction_id' => '34555',
        ]);

        $transaction->created_at = $weekAgo;
        $transaction->save();

        $metricManager = new MetricsManager();
        $result = $metricManager->calculateAverageRevenuePerUser($weekAgo);

        $this->assertEquals($result, 10.00);
    }

    public function test_mrr()
    {
        Transaction::query()->update(['status' => TransactionStatus::FAILED->value]);
        Subscription::query()->update(['status' => SubscriptionStatus::NEW->value]);

        $tenant = $this->createTenant();
        $user = $this->createUser();

        $slug = Str::random();
        $plan = Plan::factory()->create([
            'slug' => $slug,
            'is_active' => true,
        ]);

        Subscription::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'status' => SubscriptionStatus::ACTIVE,
            'plan_id' => $plan->id,
            'price' => 5000,
            'interval_id' => Interval::where('slug', 'month')->firstOrFail()->id,
        ])->save();

        $metricManager = new MetricsManager();
        $result = $metricManager->calculateMRR(now());

        $this->assertEquals($result, 50.00);

        Subscription::factory()->create([
            'user_id' => $user->id,
            'tenant_id' => $tenant->id,
            'status' => SubscriptionStatus::ACTIVE,
            'plan_id' => $plan->id,
            'price' => 12000,
            'interval_id' => Interval::where('slug', 'year')->firstOrFail()->id,
        ])->save();

        $result = $metricManager->calculateMRR(now());

        $this->assertEquals($result, 60.00);

    }
}
