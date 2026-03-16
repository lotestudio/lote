<?php

namespace App\Lote\Selects;

use Carbon\CarbonPeriod;

class Year
{
    protected $years;

    public function __construct(
        protected int $addYears,
        protected int $subYears
    ) {

        $period = CarbonPeriod::create(now()->subYears((int) $this->subYears), '1 year', now()->addYears($this->addYears));

        foreach (array_reverse($period->toArray()) as $year) {
            $this->years[] = [
                'label' => $year->year.'',
                'value' => $year->year.'',
            ];
        }
    }

    public function forSelect($prependLabel = 'Изберете година', $prependValue = '', $prepend = true): string
    {

        if (! $prepend) {
            return collect($this->years)->toJson();
        }

        return collect($this->years)->prepend(['label' => $prependLabel, 'value' => $prependValue])->toJson();
    }

    public static function make($addYears = 1, $subYears = 5): self
    {
        return new static($addYears, $subYears);
    }
}
