<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceFormRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Transformers\DataTable\InvoiceDataTable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceController extends Controller
{
    /**
     * @throws \Exception
     */
    public function index(Request $request): Response|array|BinaryFileResponse
    {
        if ($request->json === 'true') {
            return InvoiceDataTable::make()->get();
        }

        return Inertia::render('admin/Invoice/index', []);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Invoice/form', [
            'clientSelect' => Client::forSelect(),
        ]);
    }

    public function store(InvoiceFormRequest $request)
    {
        $data = $request->validated();
        $data['num'] = Invoice::getNextNumber();

        $client = Client::query()->find($data['client_id']);
        $data['client_details'] = [
            'company' => $client->company,
            'address' => $client->address_1,
            'number' => $client->number,
            'vat' => $client->vat,
            'mol' => $client->mol,
        ];

        $services = $data['services'];
        unset($data['services']);
        $invoice = Invoice::query()->create($data);
        $invoice->services()->createMany($services);

        return redirect(route('invoice.index'));
    }

    public function update(Invoice $invoice, InvoiceFormRequest $request)
    {
        $data = $request->validated();
        $services = $data['services'];

        unset($data['services']);
        $invoice->update($data);
        $invoice->services()->delete();
        $invoice->services()->createMany($services);

        return redirect(route('invoice.index'));
    }

    public function show(Invoice $invoice): Renderable
    {
        $invoice->load('services');
        $signed = false;
        $type = 'original';

        return view('invoice.print', compact('invoice', 'type', 'signed'));
    }

    public function edit(Invoice $invoice): Response
    {
        return Inertia::render('admin/Invoice/form', [
            'model' => $invoice,
            'clientSelect' => Client::forSelect(),
        ]);
    }

    public function destroy($id)
    {
        Invoice::destroy([$id]);

        return back();
    }
}
