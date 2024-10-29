<?php

namespace App\Http\Controllers\PaymentProviders;

use App\Http\Controllers\Controller;
use App\Services\PaymentProviders\LemonSqueezy\LemonSqueezyWebhookHandler;
use Illuminate\Http\Request;

class LemonSqueezyController extends Controller
{
    public function handleWebhook(Request $request, LemonSqueezyWebhookHandler $handler)
    {
        return $handler->handleWebhook($request);
    }
}
