<?php

namespace App\Http\Controllers\PaymentProviders;

use App\Http\Controllers\Controller;
use App\Services\PaymentProviders\Stripe\StripeProvider;
use App\Services\PaymentProviders\Stripe\StripeWebhookHandler;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function handleWebhook(Request $request, StripeWebhookHandler $handler)
    {
        return $handler->handleWebhook($request);
    }
}
