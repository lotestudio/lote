<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientFormRequest;
use App\Models\Client;
use App\Transformers\DataTable\ClientDataTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClientController extends Controller
{
    public function index(Request $request): Response|array|BinaryFileResponse
    {
        if ($request->json === 'true') {
            return ClientDataTable::make()->get();
        }

        return Inertia::render('admin/Client/index', []);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Client/form', []);
    }

    public function store(ClientFormRequest $request)
    {
        $data = $request->validated();
        Client::query()->create($data);

        return redirect(route('client.index'));
    }

    public function update(Client $client, ClientFormRequest $request)
    {
        $data = $request->validated();
        $client->update($data);

        return redirect(route('client.index'));
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('admin/Client/form', [
            'model' => $client,
        ]);
    }

    public function destroy($id)
    {
        Client::destroy([$id]);

        return back();
    }
}
