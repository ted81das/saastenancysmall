<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\InvoiceManager;
use Illuminate\Http\Request;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Seller;
use LaravelDaily\Invoices\Invoice;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceManager $invoiceManager
    ) {

    }
    public function generate(string $transactionUuid)
    {
        $transaction = Transaction::where('uuid', $transactionUuid)->firstOrFail();

        $forceRegenerate = request()->boolean('regenerate', false) && auth()->user()->isAdmin();

        $result = $this->invoiceManager->generate($transaction, $forceRegenerate);

        if ($result === null) {
            abort(404);
        }

        return $result;
    }

    /**
     * Preview invoice (used to generate PDF and show it from admin panel)
     */
    public function preview(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $customer = new Buyer([
            'name'          => 'John Doe',
            'custom_fields' => [
                'email' => 'test@example.com',
                'order number' => '1234',
            ],
        ]);

        $item = InvoiceItem::make('Product 1')->formattedPricePerUnit('$10');

        $seller = new Seller();
        $seller->name = $request->get('seller_name', '');
        $seller->address = $request->get('seller_address', '');
        $seller->code = $request->get('seller_code', '');
        $seller->vat = $request->get('seller_tax_number', '');
        $seller->phone = $request->get('seller_phone', '');

        $invoice = Invoice::make()
            ->buyer($customer)
            ->seller($seller)
            ->series($request->get('serial_number_series') ?? '')
            ->formattedTotalTaxes('$1.99')
            ->formattedTotalAmount('$11.99')
            ->logo(public_path(config('app.logo.dark')))
            ->addItem($item);

        return $invoice->stream();
    }
}
