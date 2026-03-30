<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceFormRequest;
use App\Models\Service;
use App\Transformers\DataTable\ServiceDataTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServiceController extends Controller
{
    public function index(Request $request): Response|array|BinaryFileResponse
    {
        if ($request->json === 'true') {
            return ServiceDataTable::make()->get();
        }

        return Inertia::render('admin/Service/index', []);
    }

    public function create(): Response
    {
        return Inertia::render('admin/Service/form', []);
    }

    public function store(ServiceFormRequest $request)
    {
        $data = $request->validated();
        Service::query()->create($data);

        return redirect(route('service.index'));
    }

    public function update(Service $service, ServiceFormRequest $request)
    {
        $data = $request->validated();
        $service->update($data);

        return redirect(route('service.index'));
    }

    public function edit(Service $service): Response
    {
        return Inertia::render('admin/Service/form', [
            'model' => $service,
        ]);
    }

    public function destroy($id)
    {
        Service::destroy([$id]);

        return back();
    }
}
