<?php

namespace App\Transformers\DataTable;

use App\Lote\DataTables2\Columns;
use App\Lote\DataTables2\DataTableResource;
use App\Models\Invoice;

class InvoiceDataTable extends DataTableResource
{
    public ?string $model = Invoice::class;

    public string $defaultOrderField = 'id';

    public array $searchableFields = ['num'];
    // public ?string $useDatabaseTablePrefix = \"\";
    // public ?string $exportClass = ExportClass::class;

    public string $exportFileName = 'export.xlsx';

    protected string $defaultWidth = '200px';

    protected array $columns = [
        ['label' => 'Num', 'sort' => 'num'],
        ['label' => 'Copy'],
        ['label' => 'Client'],
        ['label' => 'Total'],
        ['label' => 'Date'],
        ['label' => 'Actions'],
    ];

    public function getColumns(): array
    {
        $columns = Columns::make($this->columns, ['defaultWidth' => $this->defaultWidth]);
        $columns->getByLabel('Num')->width('100px');
        $columns->getByLabel('Copy')->width('100px');
        $columns->getByLabel('Total')->alignRight();
        $columns->getByLabel('Date')->alignRight();
        $columns->getByLabel('Actions')->alignRight();

        return $columns->toArray();
    }

    public function preBuild(): void
    {
        $this->builder->with('client');
    }

    protected function transform($item): array
    {
        $res = $item->toArray();
        $res['client'] = $item->client->company;
        $res['total'] = $item->total->formatted();
        $res['date'] = $item->date;

        $res['actions'] = [
            [
                'label' => 'Редакция',
                'href' => route('invoice.edit', $item->id),
                'icon' => 'i-edit',
                'class' => 'btn btn-warning btn-xs',
            ],
        ];

        return $res;
    }
}
