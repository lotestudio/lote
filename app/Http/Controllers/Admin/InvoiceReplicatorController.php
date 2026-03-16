<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;

class InvoiceReplicatorController extends Controller
{
    public function __invoke($id)
    {
        // replicate invoice with services
        $invoice = Invoice::with(['services', 'client'])->findOrFail($id);
        // replicate invoice with the correct number
        $invoice->replicate()
            ->forceFill(
                [
                    'num' => Invoice::getNextNumber(),
                    'client_details' => [
                        'company' => $invoice->client->company,
                        'address' => $invoice->client->address_1,
                        'number' => $invoice->client->number,
                        'vat' => $invoice->client->vat,
                        'mol' => $invoice->client->mol,
                    ],
                    'date' => now(),
                ])
            ->save();

        // get new invoice
        $new_invoice = Invoice::query()->latest()->first();

        // replicate services
        $invoice->services->each(function ($service) use ($new_invoice) {
            $service->replicate()->forceFill(['invoice_id' => $new_invoice->id])->save();
        });

        return back()->with('success', 'Фактурата е копирана успешно.');
    }
}
