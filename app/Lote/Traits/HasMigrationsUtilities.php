<?php

namespace App\Lote\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait HasMigrationsUtilities
{
    public function dropColumnIfExists($myTable, $column): void
    {
        if (Schema::hasColumn($myTable, $column)) { //check the column
            Schema::table($myTable, function (Blueprint $table) use ($column) {
                $table->dropColumn($column); //drop it
            });
        }
    }
}
