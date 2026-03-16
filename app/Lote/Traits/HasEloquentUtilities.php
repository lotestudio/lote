<?php

namespace App\Lote\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;


/**
 * @method static Builder whereInMultiple() @see scopeWhereInMultiple()
 * @method static Builder inMonth() @see scopeInMonth()
 * @method static Builder inCurrentMonth() @see scopeInCurrentMonth()
 */


trait HasEloquentUtilities
{
    public static function scopeWhereInMultiple(Builder $query, array $columns, array $values): Builder
    {
        collect($values)
            ->transform(function ($v) use ($columns) {
                $clause = [];
                foreach ($columns as $index => $column) {
                    $clause[] = [$column, '=', $v[$index]];
                }

                return $clause;
            })
            ->each(function ($clause, $index) use ($query) {
                $query->where($clause, null, null, $index === 0 ? 'and' : 'or');
            });

        return $query;
    }

    public static function scopeInMonth(Builder $q, string $column, string|Carbon $month, string $format='d-m-Y'):Builder
    {
        if(!$month instanceof Carbon) {
            $month = Carbon::createFromFormat($format, $month);
        }
        return $q->whereBetween($column,[$month->clone()->startOfMonth(),$month->clone()->endOfMonth()]);
    }

    public static function scopeInCurrentMonth(Builder $q, string $column):Builder
    {
        return $q->whereBetween($column,[now()->startOfMonth(),now()->endOfMonth()]);

    }


    public static function getNextId()
    {
        $table_name = explode('.', self::getTableName());
        $table_name = count($table_name) > 1 ? $table_name[1] : $table_name[0];

        $statement = \DB::select("SHOW TABLE STATUS LIKE '".$table_name."'");

        return $statement[0]->Auto_increment;
    }

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
