<?php

namespace App\Services\PaymentProviders;

use App\Models\PaymentProvider;

class PaymentManager
{
    private array $paymentProviders;

    public function __construct(PaymentProviderInterface ...$paymentProviders)
    {
        $this->paymentProviders = $paymentProviders;
    }

    public function getActivePaymentProviders(): array
    {
        $paymentProviders = [];
        $activePaymentProviders = PaymentProvider::where('is_active', true)->get();

        $activePaymentProvidersMap = [];

        foreach ($activePaymentProviders as $activePaymentProvider) {
            $activePaymentProvidersMap[$activePaymentProvider->slug] = $activePaymentProvider;
        }

        foreach ($this->paymentProviders as $paymentProvider) {
            if (isset($activePaymentProvidersMap[$paymentProvider->getSlug()])) {
                $paymentProviders[] = $paymentProvider;
            }
        }

        return $paymentProviders;
    }

    public function getPaymentProviderBySlug(string $slug): PaymentProviderInterface
    {
        $activePaymentProviders = PaymentProvider::where('is_active', true)->get();

        $activePaymentProvidersMap = [];

        foreach ($activePaymentProviders as $activePaymentProvider) {
            $activePaymentProvidersMap[$activePaymentProvider->slug] = $activePaymentProvider;
        }

        foreach ($this->paymentProviders as $paymentProvider) {
            if (isset($activePaymentProvidersMap[$paymentProvider->getSlug()])) {
                if ($paymentProvider->getSlug() === $slug) {
                    return $paymentProvider;
                }
            }
        }

        throw new \Exception('Payment provider not found: '.$slug);
    }
}
