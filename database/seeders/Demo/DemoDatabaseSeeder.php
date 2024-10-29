<?php

namespace Database\Seeders\Demo;

use App\Constants\PlanType;
use App\Constants\RoadmapItemStatus;
use App\Constants\RoadmapItemType;
use App\Constants\SubscriptionStatus;
use App\Constants\TenancyPermissionConstants;
use App\Models\BlogPost;
use App\Models\Currency;
use App\Models\Discount;
use App\Models\Interval;
use App\Models\OauthLoginProvider;
use App\Models\OneTimeProduct;
use App\Models\PaymentProvider;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\MetricsManager;
use App\Services\TenantPermissionManager;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDatabaseSeeder extends Seeder
{
    public function __construct(
        private MetricsManager $metricsManager,
        private TenantPermissionManager $tenantPermissionManager,
    ) {

    }

    private array $blogPostTitles = [
        'The Art of Responsive Web Design: A Comprehensive Guide',
        'Exploring the Power of Machine Learning in Everyday Life',
        "Mastering the Basics: A Beginner's Guide to Python Programming",
        'The Future of Virtual Reality: Trends and Innovations',
        'Sustainable Living: Eco-Friendly Practices for a Greener Planet',
        'Unraveling the Mysteries of Quantum Computing',
        'Crafting Engaging User Experiences: A UX Design Tutorial',
        'Demystifying Blockchain Technology: Beyond Cryptocurrencies',
        'Navigating the World of Cybersecurity: Tips for Online Safety',
        'The Impact of Artificial Intelligence on Healthcare',
        'DIY Home Improvement Projects for a Budget-Friendly Upgrade',
        'Culinary Adventures: Exploring Global Cuisines at Home',
        'Mindfulness in the Digital Age: Finding Balance in a Busy World',
        'Capturing the Perfect Shot: Photography Tips for Beginners',
        'Fitness for All: Tailoring Workouts to Your Lifestyle',
        'Building a Personal Brand: Strategies for Professional Success',
        'The Evolution of Social Media: Trends and Influencer Culture',
        'Unlocking Creativity: A Guide to Overcoming Creative Blocks',
        'The Power of Storytelling: Crafting Compelling Narratives',
        'Remote Work Revolution: Maximizing Productivity in a Virtual World',
    ];

    private array $images = [
        'https://unsplash.com/photos/F1MaILUxscM/download?ixid=M3wxMjA3fDB8MXx0b3BpY3x8d0pMTzN0U0s1QU18fHx8fDJ8fDE3MDY2MTk4NTF8&force=true&w=1920',
        'https://unsplash.com/photos/DvopK4gNs8A/download?ixid=M3wxMjA3fDB8MXxhbGx8fHx8fHx8fHwxNzA2NjI2MDI5fA&force=true&w=1920',
        'https://unsplash.com/photos/c6miNI_WdZ4/download?ixid=M3wxMjA3fDB8MXxhbGx8fHx8fHx8fHwxNzA2NjI2MDMxfA&force=true&w=1920',
        'https://unsplash.com/photos/kF5nFbHBG5E/download?ixid=M3wxMjA3fDB8MXxhbGx8fHx8fHx8fHwxNzA2NjI2MDMyfA&force=true&w=1920',
        'https://unsplash.com/photos/ck2D9pxRbTo/download?ixid=M3wxMjA3fDB8MXxhbGx8fHx8fHx8fHwxNzA2NjIzNDgxfA&force=true&w=1920',
        'https://unsplash.com/photos/FTUSP0ZH49I/download?ixid=M3wxMjA3fDB8MXxhbGx8fHx8fHx8fHwxNzA2NjIzMDQ3fA&force=true&w=1920',
        'https://unsplash.com/photos/QwAcsiuGTaM/download?ixid=M3wxMjA3fDB8MXxhbGx8fHx8fHx8fHwxNzA2NjI3MjI1fA&force=true&w=1920',
        'https://unsplash.com/photos/v4j0rlrTZbc/download?ixid=M3wxMjA3fDB8MXxhbGx8fHx8fHx8fHwxNzA2NjI3MjI5fA&force=true&w=1920',
        'https://unsplash.com/photos/IncXhM8rKSc/download?ixid=M3wxMjA3fDB8MXxhbGx8fHx8fHx8fHwxNzA2NjIzOTE1fA&force=true&w=1920',
        'https://unsplash.com/photos/bwcxNg8dkiI/download?ixid=M3wxMjA3fDB8MXxhbGx8fHx8fHx8fHwxNzA2NjI3MjQwfA&force=true&w=1920',
        'https://unsplash.com/photos/Kt5hRENuotI/download?ixid=M3wxMjA3fDB8MXxhbGx8fHx8fHx8fHwxNzA2NjI1NDcyfA&force=true&w=1920',
    ];

    private string $loremIpsum = 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum';

    /**
     * Seed the testing database.
     */
    public function run(): void
    {
        $this->callOnce([
            DatabaseSeeder::class,
        ]);

        // add admin user
        $adminUser = User::where('email', 'admin@admin.com')->first();
        if (! $adminUser) {

            $adminUser = User::factory()->create([
                'email' => 'admin@admin.com',
                'password' => bcrypt('admin'),
                'name' => 'Admin',
                'public_name' => 'John Doe',
                'is_admin' => true,
            ]);

            $adminUser->assignRole('admin');
        }

        $this->seedDemoData();
        $this->addDiscounts();
        $this->addBlogPosts($adminUser);
        $this->addSomeUsers();
        $this->addMetrics();
        $this->addSomeRoadmapItems();

        // enable google oauth
        OauthLoginProvider::where('provider_name', 'google')->update(['enabled' => true]);
    }

    private function seedDemoData(): void
    {

        $basicProduct = $this->findOrCreateProduct([
            'name' => 'Basic',
            'slug' => 'basic',
            'description' => 'Basic plan',
            'features' => [['feature' => 'Amazing Feature 1'], ['feature' => 'Amazing Feature 2'], ['feature' => 'Amazing Feature 3'], ['feature' => 'Amazing Feature 4'], ['feature' => 'Amazing Feature 5']],
        ]);

        $proProduct = $this->findOrCreateProduct([
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'Pro plan',
            'is_popular' => true,
            'features' => [['feature' => 'Amazing Feature 1'], ['feature' => 'Amazing Feature 2'], ['feature' => 'Amazing Feature 3'], ['feature' => 'Amazing Feature 4'], ['feature' => 'Amazing Feature 5']],
        ]);

        $ultimateProduct = $this->findOrCreateProduct([
            'name' => 'Ultimate',
            'slug' => 'ultimate',
            'description' => 'Ultimate plan',
            'features' => [['feature' => 'Amazing Feature 1'], ['feature' => 'Amazing Feature 2'], ['feature' => 'Amazing Feature 3'], ['feature' => 'Amazing Feature 4'], ['feature' => 'Amazing Feature 5']],
        ]);

        $this->createOneTimeProduct('Lemon', 'lemon', 1000);
        $this->createOneTimeProduct('Orange', 'orange', 2500);
        $this->createOneTimeProduct('Apple', 'apple', 5000);

        $this->createPlans($basicProduct, 1000, 10000);
        $this->createPlans($proProduct, 2500, 25000);
        $this->createPlans($ultimateProduct, 5000, 50000);
    }

    private function createOneTimeProduct($name, $slug, $price): void
    {
        $product = $this->findOrCreateOneTimeProduct([
            'name' => $name,
            'slug' => $slug,
            'description' => 'One time product',
            'features' => [['feature' => 'Amazing Feature 1'], ['feature' => 'Amazing Feature 2'], ['feature' => 'Amazing Feature 3'], ['feature' => 'Amazing Feature 4'], ['feature' => 'Amazing Feature 5']],
            'is_active' => true,
        ]);

        $product->prices()->create([
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => $price,
        ]);

        $this->addOneTimeProductOrders($product);
    }

    private function addOneTimeProductOrders(OneTimeProduct $product): void
    {
        $numberOfOrders = rand(15, 25);

        $paymentProviders = PaymentProvider::all();

        for ($i = 0; $i < $numberOfOrders; $i++) {

            $user = User::factory()->create();

            $tenant = Tenant::factory()->create([
                'created_by' => $user->id,
            ]);
            $tenant->users()->attach($user);

            $this->tenantPermissionManager->assignTenantUserRole($tenant, $user, TenancyPermissionConstants::ROLE_ADMIN);

            $order = $user->orders()->create([
                'uuid' => Str::uuid(),
                'status' => 'success',
                'currency_id' => Currency::where('code', 'USD')->first()->id,
                'total_amount' => $product->prices()->first()->price,
                'total_amount_after_discount' => $product->prices()->first()->price,
                'created_at' => now()->sub(rand(1, 10), 'days'),
                'tenant_id' => $tenant->id,
                // random payment provider
                'payment_provider_id' => $paymentProviders[rand(0, count($paymentProviders) - 1)]->id,
            ]);

            $order->items()->create([
                'one_time_product_id' => $product->id,
                'quantity' => 1,
                'currency_id' => Currency::where('code', 'USD')->first()->id,
                'price_per_unit' => $product->prices()->first()->price,
            ]);
        }
    }

    private function findOrCreateOneTimeProduct(array $data)
    {
        $product = OneTimeProduct::where('slug', $data['slug'])->first();

        if ($product) {
            return $product;
        }

        return OneTimeProduct::create($data);
    }

    private function findOrCreateProduct(array $data)
    {
        $product = Product::where('slug', $data['slug'])->first();

        if ($product) {
            return $product;
        }

        return Product::create($data);
    }

    private function createPlans(Product $product, $priceMonthly, $priceYearly): void
    {
        $basicPlan = $this->findOrCreatePlan([
            'name' => $product->name.' Monthly',
            'slug' => $product->slug.'-monthly',
            'interval_id' => Interval::where('slug', 'month')->first()->id,
            'interval_count' => 1,
            'trial_interval_id' => Interval::where('slug', 'week')->first()->id,
            'has_trial' => true,
            'trial_interval_count' => 1,
            'is_active' => true,
            'product_id' => $product->id,
            'type' => PlanType::SEAT_BASED->value,
        ]);

        $basicPlan->prices()->create([
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => $priceMonthly,
        ]);

        $this->addPlanSubscriptions($basicPlan);

        // add yearly plan

        $basicPlan = $this->findOrCreatePlan([
            'name' => $product->name.' Yearly',
            'slug' => $product->slug.'-yearly',
            'interval_id' => Interval::where('slug', 'year')->first()->id,
            'interval_count' => 1,
            'trial_interval_id' => Interval::where('slug', 'week')->first()->id,
            'has_trial' => true,
            'trial_interval_count' => 1,
            'is_active' => true,
            'product_id' => $product->id,
            'type' => PlanType::SEAT_BASED->value,
        ]);

        $basicPlan->prices()->create([
            'currency_id' => Currency::where('code', 'USD')->first()->id,
            'price' => $priceYearly,
        ]);

        $this->addPlanSubscriptions($basicPlan);

    }

    private function findOrCreatePlan(array $data)
    {
        $plan = Plan::where('slug', $data['slug'])->first();

        if ($plan) {
            return $plan;
        }

        return Plan::create($data);
    }

    private function addPlanSubscriptions(Plan $plan): void
    {
        $numberOfUsers = rand(15, 25);
        $paymentProviders = PaymentProvider::all();

        for ($i = 0; $i < $numberOfUsers; $i++) {

            $numberOfIntervalsBack = $plan->interval == 'month' ? rand(5, 10) : rand(1, 4);
            $createdDate = now()->sub($plan->interval->date_identifier, $numberOfIntervalsBack);

            $user = User::factory()->create(
                [
                    'created_at' => $createdDate,
                    'updated_at' => $createdDate,
                ]
            );

            $tenant = Tenant::factory()->create([
                'created_by' => $user->id,
            ]);
            $tenant->users()->attach($user);

            $this->tenantPermissionManager->assignTenantUserRole($tenant, $user, TenancyPermissionConstants::ROLE_ADMIN);

            $status = rand(0, 1) === 1 ? SubscriptionStatus::ACTIVE : SubscriptionStatus::CANCELED;

            $paymentProviderId = $paymentProviders[rand(0, count($paymentProviders) - 1)]->id;

            $subscription = $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'trial_ends_at' => null,
                'ends_at' => $status == SubscriptionStatus::ACTIVE ? Carbon::now()->add(1, $plan->interval->date_identifier) : (new Carbon($createdDate))->add(1, $plan->interval->date_identifier),
                'price' => $plan->prices()->first()->price,
                'currency_id' => $plan->prices()->first()->currency_id,
                'user_id' => $user->id,
                'uuid' => Str::uuid(),
                'status' => rand(0, 1) === 1 ? SubscriptionStatus::ACTIVE : SubscriptionStatus::CANCELED,
                'payment_provider_id' => $paymentProviderId,
                'interval_id' => $plan->interval->id,
                'interval_count' => $plan->interval_count,
                'created_at' => $createdDate,
                'tenant_id' => $tenant->id,
            ]);

            // add transactions

            $transactionCreatedDate = $createdDate;
            for ($j = 0; $j < $numberOfIntervalsBack; $j++) {
                $user->transactions()->create([
                    'subscription_id' => $subscription->id,
                    'amount' => $plan->prices()->first()->price,
                    'currency_id' => $plan->prices()->first()->currency_id,
                    'payment_provider_id' => $paymentProviderId,
                    'payment_provider_transaction_id' => Str::uuid(),
                    'payment_provider_status' => 'paid',
                    'status' => 'success',
                    'user_id' => $user->id,
                    'uuid' => Str::uuid(),
                    'created_at' => $transactionCreatedDate,
                    'updated_at' => $transactionCreatedDate,
                    'tenant_id' => $tenant->id,
                ]);

                $transactionCreatedDate = $transactionCreatedDate->add(1, $plan->interval->date_identifier);
            }

        }
    }

    private function addDiscounts()
    {
        $discountsToAdd = rand(5, 10);

        for ($i = 1; $i <= $discountsToAdd; $i++) {
            $discount = Discount::create([
                'name' => 'Discount '.$i,
                'amount' => rand(10, 70),
                'type' => 'percentage',
                'valid_until' => null,
                'max_redemptions' => -1,
                'max_redemptions_per_user' => -1,
                'is_recurring' => true,
                'is_active' => true,
            ]);

            // add code to discount
            $discount->codes()->create([
                'code' => Str::random(10),
            ]);
        }
    }

    private function addBlogPosts(User $user)
    {
        foreach ($this->blogPostTitles as $title) {

            BlogPost::flushEventListeners();  // disable event listeners in booted method

            $blog = BlogPost::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'body' => str_repeat('<p>'.$this->loremIpsum.'</p>', rand(10, 15)),
                'is_published' => true,
                'published_at' => now()->sub(rand(1, 10), 'days'),
                'user_id' => $user->id,
                'author_id' => $user->id,
            ]);

            // assign an image to the blog post using spatie media library

            try {
                $blog->addMediaFromUrl($this->images[rand(0, count($this->images) - 1)])
                    ->toMediaCollection('blog-images');
            } catch (\Exception $e) {
                // do nothing
            }
        }
    }

    private function addMetrics()
    {
        $firstUserCreatedDate = User::orderBy('created_at', 'asc')->first()->created_at;
        $lastUserCreatedDate = User::orderBy('created_at', 'desc')->first()->created_at;

        $startDate = $firstUserCreatedDate->copy()->startOfDay();
        $endDate = $lastUserCreatedDate->copy()->endOfDay();

        // loop through each day and calculate metrics

        while ($startDate->lte($endDate)) {
            Carbon::setTestNow($startDate);
            $this->metricsManager->beat();
            $startDate->addDay();
        }
    }

    private function addSomeUsers()
    {
        $numberOfUsers = rand(100, 150);

        for ($i = 0; $i < $numberOfUsers; $i++) {
            $date = now()->subDays(rand(1, 1000));
            $user = User::factory()->create();
            $user->created_at = $date;
            $user->save();
        }
    }

    private function addSomeRoadmapItems()
    {
        $numberOfItems = rand(5, 10);

        for ($i = 0; $i < $numberOfItems; $i++) {
            // get a random user from database
            $user = User::inRandomOrder()->first();

            $item = $user->roadmapItems()->create([
                'title' => 'Roadmap Item '.$i,
                'slug' => 'roadmap-item-'.$i,
                'type' => RoadmapItemType::FEATURE->value,
                'description' => $this->loremIpsum,
                'upvotes' => rand(1, 10),
                'status' => RoadmapItemStatus::APPROVED->value,
                'created_at' => now()->subDays(rand(1, 1000)),
            ]);

            $item->userUpvotes()->attach($user->id, [
                'ip_address' => rand(0, 255).'.'.
                    rand(0, 255).'.'.
                    rand(0, 255).'.'.
                    rand(0, 255),
            ]);
        }
    }
}
