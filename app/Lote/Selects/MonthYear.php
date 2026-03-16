<?php

namespace App\Lote\Selects;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

class MonthYear
{
    protected $months;

    public function __construct(
        protected int $addMonths,
        protected int $subMonths,
        protected string $valueFormat = 'd-m-Y'
    ) {

        $period = CarbonPeriod::create(now()->startOfMonth()->subMonths($this->subMonths), '1 month', now()->startOfMonth()->addMonths($this->addMonths));

        foreach (array_reverse($period->toArray()) as $month) {
            $this->months[] = [
                'label' => $month->translatedFormat('F Y'),
                'value' => $month->format($this->valueFormat),
            ];
        }
    }

    public function forSelect(string $prependLabel = 'Изберете месец', string $prependValue = '', bool $prepend = true, bool $toArray = true): string|array
    {
        $collection = collect($this->months)->when($prepend, function ($collection) use ($prependLabel, $prependValue) {
            $collection->prepend(['label' => $prependLabel, 'value' => $prependValue]);
        });

        if ($toArray) {
            return $collection->toArray();
        }

        return $collection->toJson();

    }

    public static function make($addMonths = 0, $subMonths = 12, $valueFormat = 'd-m-Y'): MonthYear
    {
        return new static($addMonths, $subMonths, $valueFormat);
    }

    public static function carbonFromMonthYear(string $monthYear, string $separator = '-'): Carbon
    {
        $monthYear = explode($separator, $monthYear);

        return Carbon::create($monthYear[1], $monthYear[0])->startOfMonth();
    }
}
