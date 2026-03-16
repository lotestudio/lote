<?php

declare(strict_types=1);

namespace App\Lote\DataTables2;

use App\Lote\DataTables2\Commands\DataTableResourceCommand;
use Illuminate\Support\ServiceProvider;

class DataTableProvider extends ServiceProvider
{
    /**
     * The commands to be registered.
     *
     * @var array
     */
    protected $commands = [
        DataTableResourceCommand::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        $this->commands($this->commands);
    }
}
