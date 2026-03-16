<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\InvoiceReplicatorController;
use App\Http\Controllers\Admin\ServiceController;
use App\Models\Invoice;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

// Route::inertia('/', 'Welcome', [
//    'canRegister' => Features::enabled(Features::registration()),
// ])->name('home');

Route::view('/', 'lote.home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('admin')->group(function () {
        Route::resource('client', ClientController::class);
        Route::resource('invoice', InvoiceController::class);
        Route::resource('service', ServiceController::class);
        Route::get('copy_invoice/{id}', InvoiceReplicatorController::class);
        Route::get('download/{id}/{type?}', function ($id, $type = 'original') {
            $invoice = Invoice::with('services')->findOrFail($id);
            $signed = false;
            $pdf = Pdf::loadView('invoice.print', compact('invoice', 'type', 'signed'));

            return $pdf->download($invoice->num.'_'.$type.'.pdf');
        });
    });
});

require __DIR__.'/settings.php';
