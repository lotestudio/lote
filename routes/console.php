<?php

use App\Lote\InertiaCrudCommand\MakeInertiaCrud;
use App\Lote\Services\DevDBSync\Dumper;
use Illuminate\Support\Facades\Artisan;

Artisan::command('dev:download_db', function (): void {
    $dumper = new Dumper;

    $this->info($dumper->dumpDb(true));
    $this->info($dumper->download(true));
    $this->info($dumper->deleteDumpFile(true));

})->describe('Download database from production');

Artisan::command('lote:inertia-crud', function (): void {
    $generator = new MakeInertiaCrud;
    $generator->run($this);
})->describe('Generate DataTableResource and optional Inertia CRUD scaffolding');
