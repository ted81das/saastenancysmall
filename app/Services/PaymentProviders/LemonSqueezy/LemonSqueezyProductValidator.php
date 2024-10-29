<?php

namespace App\Services\PaymentProviders\LemonSqueezy;

use App\Client\LemonSqueezyClient;
use App\Models\OneTimeProduct;
use App\Models\Plan;
use App\Services\CalculationManager;

class LemonSqueezyProductValidator
{
    public function __construct(
        private LemonSqueezyClient $client,
        private CalculationManager $calculationManager,
    ) {

    }

    public function validatePlan(string $variantId, Plan $plan): bool
    {
        $response = $this->client->getVariant($variantId);

        if (! $response->successful()) {
            throw new \Exception('Failed to validate product with Lemon Squeezy.');
        }

        $planPrice = $this->calculationManager->getPlanPrice($plan);

        if ($planPrice->price != $response['data']['attributes']['price']) {
            throw new \Exception(sprintf('Price mismatch. Plan price: %d, Lemon Squeezy price: %d', $planPrice->price, $response['data']['attributes']['price']));
        }

        if ($plan->interval->slug != $response['data']['attributes']['interval']) {
            throw new \Exception(sprintf('Interval mismatch. Plan interval: %s, Lemon Squeezy interval: %s', $plan->interval->slug, $response['data']['attributes']['interval']));
        }

        if ($plan->interval_count != $response['data']['attributes']['interval_count']) {
            throw new \Exception(sprintf('Interval count mismatch. Plan interval count: %s, Lemon Squeezy interval count: %s', $plan->interval_count, $response['data']['attributes']['interval_count']));
        }

        if ($plan->has_trial != $response['data']['attributes']['has_free_trial']) {
            throw new \Exception(sprintf('Has trial mismatch. Plan has trial: %s, Lemon Squeezy has trial: %s', $plan->has_trial, $response['data']['attributes']['has_free_trial']));
        }

        if ($plan->has_trial && $plan->trialInterval->slug != $response['data']['attributes']['trial_interval']) {
            throw new \Exception(sprintf('Trial interval mismatch. Plan trial interval: %s, Lemon Squeezy trial interval: %s', $plan->trialInterval->slug, $response['data']['attributes']['trial_interval']));
        }

        if ($plan->has_trial && $plan->trial_interval_count != $response['data']['attributes']['trial_interval_count']) {
            throw new \Exception(sprintf('Trial interval count mismatch. Plan trial interval count: %s, Lemon Squeezy trial interval count: %s', $plan->trial_interval_count, $response['data']['attributes']['trial_interval_count']));
        }

        if (! $response['data']['attributes']['is_subscription']) {
            throw new \Exception('Lemon Squeezy product is not a subscription.');
        }

        return true;
    }

    public function validateOneTimeProduct(string $variantId, OneTimeProduct $oneTimeProduct): bool
    {
        $response = $this->client->getVariant($variantId);

        $price = $this->calculationManager->getOneTimeProductPrice($oneTimeProduct);

        if (! $response->successful()) {
            throw new \Exception('Failed to validate product with Lemon Squeezy.');
        }

        if ($price->price != $response['data']['attributes']['price']) {
            throw new \Exception(sprintf('Price mismatch. One time product price: %d, Lemon Squeezy price: %d', $price->price, $response['data']['attributes']['price']));
        }

        if ($response['data']['attributes']['is_subscription']) {
            throw new \Exception('Lemon Squeezy product is a subscription.');
        }

        return true;
    }
}
