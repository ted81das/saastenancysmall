<?php

namespace App\Http\Controllers\PaymentProviders;

use App\Http\Controllers\Controller;
use App\Services\PaymentProviders\Paddle\PaddleWebhookHandler;
use Illuminate\Http\Request;

class PaddleController extends Controller
{
    public function handleWebhook(Request $request, PaddleWebhookHandler $handler)
    {
        return $handler->handleWebhook($request);
    }

    public function paymentLink()
    {
        return view('payment-providers.paddle.payment-link');
    }
}
