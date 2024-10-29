<?php

namespace App\Services;

use App\Constants\MetricConstants;
use App\Constants\SubscriptionStatus;
use App\Constants\TransactionStatus;
use App\Models\Currency;
use App\Models\Interval;
use App\Models\MetricData;
use App\Models\Metrics;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MetricsManager
{
    public function beat()
    {
        $this->storeMetricData(MetricConstants::TOTAL_USER_COUNT, $this->getTotalUsers());

        $this->storeMetricData(MetricConstants::MRR, $this->calculateMRR());

        $this->storeMetricData(MetricConstants::DAILY_TOTAL_REVENUE, $this->calculateDailyRevenue());

        $this->storeMetricData(MetricConstants::ARPU, $this->calculateAverageRevenuePerUser());

        $this->storeMetricData(MetricConstants::ACTIVE_SUBSCRIPTION_COUNT, $this->getActiveSubscriptions());

        $this->storeMetricData(MetricConstants::USER_SUBSCRIPTION_CONVERSION_RATE, $this->calculateUserSubscriptionConversionRate());

        $this->storeMetricData(MetricConstants::SUBSCRIPTION_CHURN, $this->calculateSubscriptionChurnRate());
    }

    public function calculateUserSubscriptionConversionRate(?Carbon $date = null)
    {
        $date = $date ?? Carbon::yesterday()->endOfDay();

        $userCount = $this->getTotalUsers($date);
        $usersWhoHaveSubscription = User::whereHas('subscriptions')
            ->where('created_at', '<=', $date)
            ->count();

        $result = $userCount > 0 ? ($usersWhoHaveSubscription) / $userCount * 100 : 0;

        return number_format($result, 2);
    }

    private function adjustToPeriod(
        array $data,
        string $period = 'week',
        string $aggregate = 'average',
        ?callable $formatValueUsing = null
    ) {
        switch ($period) {
            case 'week':
                $dateGroupFormat = '#W Y';
                break;
            case 'month':
                $dateGroupFormat = 'F Y';
                break;
            case 'year':
                $dateGroupFormat = 'Y';
                break;
            default:
                // format key to show date
                $result = [];
                foreach ($data as $key => $value) {
                    $result[Carbon::parse($key)->format(config('app.date_format'))] = $value;
                }

                return $result;
        }

        $result = [];

        $chunks = [];

        foreach ($data as $key => $item) {
            $month = Carbon::parse($key)->format($dateGroupFormat);
            if (! isset($chunks[$month])) {
                $chunks[$month] = [];
            }

            $chunks[$month][] = $item;
        }

        foreach ($chunks as $month => $chunk) {
            $result[$month] =
                $this->formatValueIfRequired(
                    $this->aggregateData($chunk, $aggregate),
                    $formatValueUsing
                );
        }

        return $result;
    }

    private function formatValueIfRequired($value, ?callable $formatValueUsing = null)
    {
        if ($formatValueUsing !== null) {
            return $formatValueUsing($value);
        }

        return $value;
    }

    private function aggregateData(array $data, string $aggregate = 'average')
    {
        $data = array_map(function ($item) {
            return floatval($item);
        }, $data);

        if ($aggregate === 'sum') {
            return array_sum($data);
        } elseif ($aggregate === 'last_value') {
            return end($data);
        } elseif ($aggregate === 'max') {
            return max($data);
        }

        return array_sum($data) / count($data);  // average
    }

    public function calculateDailyRevenue(?Carbon $date = null)
    {
        $date = $date ?? Carbon::yesterday()->endOfDay();

        $currency = Currency::where('code', config('app.default_currency'))->firstOrFail();

        $yesterdaysTotalRevenue = Transaction::where('status', TransactionStatus::SUCCESS->value)
            ->whereDate('created_at', $date)
            ->sum('amount');

        $yesterdaysTotalRefunds = Transaction::where('status', TransactionStatus::REFUNDED->value)
            ->whereDate('created_at', $date)
            ->sum('amount');

        $yesterdaysTotalRevenue -= $yesterdaysTotalRefunds;

        return money(intval($yesterdaysTotalRevenue), $currency->code)->formatByDecimal();
    }

    public function calculateDailyRevenueChart(string $period = 'month', ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $revenueData = $this->getMetricChartData(MetricConstants::DAILY_TOTAL_REVENUE, $startDate, $endDate);

        $today = now();
        $todaysRevenue = $this->calculateDailyRevenue($today);

        $revenueData[$today->toString()] = $todaysRevenue;

        return $this->adjustToPeriod($revenueData, $period, 'sum');
    }

    public function calculateAverageRevenuePerUserChart(string $period = 'month', ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $now = Carbon::now();
        $todaysARPU = $this->calculateAverageRevenuePerUser($now);

        $arpuData = $this->getMetricChartData(MetricConstants::ARPU, $startDate, $endDate);

        $arpuData[$now->toString()] = $todaysARPU;

        return $this->adjustToPeriod($arpuData, $period, 'average');
    }

    public function calculateAverageRevenuePerUser(?Carbon $date = null)
    {
        $date = $date ?? Carbon::yesterday()->endOfDay();

        $userCount = $this->getTotalUsers($date);

        $currency = Currency::where('code', config('app.default_currency'))->firstOrFail();

        $totalTransactionAmounts = Transaction::where('status', TransactionStatus::SUCCESS->value)
            ->where('created_at', '<=', $date)
            ->sum('amount');

        $totalTransactionAmounts = money(intval($totalTransactionAmounts), $currency->code)->formatByDecimal();
        $this->storeMetricData(MetricConstants::TOTAL_REVENUE_AMOUNT, $totalTransactionAmounts);

        $result = $userCount > 0 ? $totalTransactionAmounts / $userCount : 0;

        return number_format($result, 2);
    }

    public function calculateSubscriptionChurnRate(?Carbon $date = null)
    {
        $date = $date ?? Carbon::yesterday()->endOfDay();
        $aMonthAgo = new Carbon($date);
        $aMonthAgo->subMonth();

        // get number of active subscriptions 1 month ago
        $metric = Metrics::where('name', MetricConstants::ACTIVE_SUBSCRIPTION_COUNT)->first();

        if (! $metric) {
            return 0;
        }

        $activeSubscriptionsResult = MetricData::where('metric_id', $metric->id)
            ->where('created_at', '<', $aMonthAgo)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $activeSubscriptionsResult) {
            return 0;
        }

        $activeSubscriptions = $activeSubscriptionsResult->value;

        // get the subscriptions that where created 1 month ago or before that have been canceled or inactive and their ends_at date is in the past month
        $lostSubscriptions = Subscription::whereIn('status', [SubscriptionStatus::CANCELED->value, SubscriptionStatus::INACTIVE->value])
            ->where('ends_at', '>=', $aMonthAgo)
            ->where('ends_at', '<', $date)
            ->where('created_at', '<', $aMonthAgo)
            ->count();

        return $activeSubscriptions > 0 ? $lostSubscriptions / $activeSubscriptions * 100 : 0;

    }

    private function storeMetricData(string $metricName, float $value, $date = null)
    {
        $date = $date ?? Carbon::yesterday()->endOfDay();
        // find or create the metric
        $metric = Metrics::firstOrCreate(['name' => $metricName]);

        // if there is a metric for that day already, update it
        $metricData = MetricData::where('metric_id', $metric->id)->whereDate('created_at', $date)->first();
        if ($metricData) {
            $metricData->value = $value;
            $metricData->save();

            return;
        }

        $metricData = new MetricData();
        $metricData->metric_id = $metric->id;
        $metricData->value = $value;
        $metricData->created_at = $date;
        $metricData->save();
    }

    private function getMetricChartData(string $metricName, ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $metric = Metrics::where('name', $metricName)->first();

        if ($metric) {
            $query = MetricData::where('metric_id', $metric->id)
                ->orderBy('created_at', 'asc');

            if ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
            }

            $metricData = $query->get();
            $results = [];

            foreach ($metricData as $data) {
                $results[$data->created_at] = $data->value;
            }

            return $results;
        }

        return [];
    }

    public function calculateAverageUserSubscriptionConversionChart(string $period = 'month', ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $now = Carbon::now();
        $todaysConversionRate = $this->calculateUserSubscriptionConversionRate($now);

        $conversionData = $this->getMetricChartData(MetricConstants::USER_SUBSCRIPTION_CONVERSION_RATE, $startDate, $endDate);

        $conversionData[$now->toString()] = $todaysConversionRate;

        return $this->adjustToPeriod($conversionData, $period, 'average');
    }

    public function calculateSubscriptionChurnRateChart(string $period = 'month', ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $now = Carbon::now();
        $todaysChurnRate = $this->calculateSubscriptionChurnRate($now);

        $churnData = $this->getMetricChartData(MetricConstants::SUBSCRIPTION_CHURN, $startDate, $endDate);

        $churnData[$now->toString()] = $todaysChurnRate;

        return $this->adjustToPeriod($churnData, $period, 'max');
    }

    public function calculateMRRChart(string $period = 'month', ?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $now = Carbon::now();
        $mrrNow = $this->calculateMRR($now);

        $results = $this->getMetricChartData(MetricConstants::MRR, $startDate, $endDate);

        $results[$now->toString()] = $mrrNow;

        return $this->adjustToPeriod($results, $period, 'last_value');
    }

    public function calculateMRR(?Carbon $date = null)
    {
        $date = $date ?? Carbon::yesterday()->endOfDay();

        $intervals = Interval::all();

        $intervalMap = [];
        foreach ($intervals as $interval) {
            $intervalMap[$interval->id] = $interval;
        }

        $intervalsInDays = [
            'day' => 1,
            'week' => 7,
            'month' => 30,
            'year' => 360,  // to get a monthly value, we divide by 30 (12 months * 30 days)
        ];

        $cases = [];
        foreach ($intervals as $interval) {
            $calculationDays = $intervalsInDays[$interval->name];
            $cases[] = "WHEN interval_id = $interval->id THEN 1.0 * subscriptions.price * subscriptions.interval_count / ".$calculationDays.' * 30';
        }

        $results = DB::table('subscriptions')
            ->select([
                DB::raw('
                SUM(CASE
                    '.implode("\n", $cases).'
                    ELSE 0
                END) as monthly_revenue
            '),
            ])
            ->where('status', SubscriptionStatus::ACTIVE->value)
            ->where('ends_at', '>=', $date)
            ->where('created_at', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('trial_ends_at')
                    ->orWhereDate('trial_ends_at', '<', $date);
            })
            ->get();

        $currency = Currency::where('code', config('app.default_currency'))->firstOrFail();

        if ($results->first() === null) {
            return 0;
        }

        $mrr = $results->first()->monthly_revenue;

        return money(intval(round($mrr)), $currency->code)->formatByDecimal();
    }

    public function getTotalUsers(?Carbon $date = null)
    {
        $date = $date ?? Carbon::yesterday()->endOfDay();

        return User::where('created_at', '<=', $date)
            ->count();
    }

    public function getTotalTransactions()
    {
        return Transaction::count();
    }

    public function getTotalRevenue()
    {
        return money(Transaction::where('status', TransactionStatus::SUCCESS->value)->sum('amount'), config('app.default_currency'));
    }

    public function getActiveSubscriptions(?Carbon $date = null)
    {
        $date = $date ?? Carbon::yesterday()->endOfDay();
        $startOfTheDay = new Carbon($date);
        $startOfTheDay->startOfDay();

        return Subscription::where('status', SubscriptionStatus::ACTIVE->value)
            ->where('ends_at', '>=', $startOfTheDay)
            ->where('created_at', '<=', $date)
            ->count();
    }

    public function getTotalCustomerConversion()
    {
        $totalSubscriptions = $this->getActiveSubscriptions();
        $totalUsers = User::count();

        return number_format(($totalUsers > 0 ? $totalSubscriptions / $totalUsers * 100 : 0), 2).'%';
    }
}
