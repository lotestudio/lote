<?php

declare(strict_types=1);

namespace App\Lote\DataTables2;

use Illuminate\Support\Facades\Log;

class Columns
{
    private \Illuminate\Support\Collection $columns;

    private string $defaultWidth = '200px';

    public static function make(array $columns, array $options): self
    {
        return new Columns($columns, $options);
    }

    public function __construct(array $columns, array $options = [])
    {
        $this->columns = collect($columns)->map(function (array $column): \App\Lote\DataTables2\Column {
            return Column::make($column);
        });

        if (isset($options['defaultWidth'])) {
            $this->defaultWidth = $options['defaultWidth'];
        }
    }

    public function toArray(): array
    {
        if ($this->hasSticky()) {
            $this->addDefaultWidths();
        }

        $res = [];
        foreach ($this->columns as $column) {
            $res[] = $column->toArray();
        }

        return $res;
    }

    private function hasSticky(): bool
    {
        foreach ($this->columns as $column) {
            if ($column->isSticky()) {
                return true;
            }
        }

        return false;
    }

    private function addDefaultWidths(): void
    {
        $this->columns->each(function (Column $column): void {
            $column->addDefaultWidth($this->defaultWidth);
        });
    }

    public function getByLabel(string $label): ?Column
    {
        try {
            return $this->columns->where('label', '==', $label)->sole();
        } catch (\Exception $exception) {
            Log::error('Label not found: '.$label);

            return null;
        }
    }

    public function getByIndex(int $index): Column
    {
        return $this->columns[intval($index)];
    }
}
