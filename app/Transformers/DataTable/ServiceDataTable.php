<?php

namespace App\Transformers\DataTable;

use App\Lote\DataTables2\Columns;
use App\Lote\DataTables2\DataTableResource;
use App\Models\Service;

class ServiceDataTable extends DataTableResource
{
    public ?string $model = Service::class;

    public string $defaultOrderField = 'id';

    // public array $searchableFields = ['title'];
    // public ?string $useDatabaseTablePrefix = \"\";
    // public ?string $exportClass = ExportClass::class;

    public string $exportFileName = 'export.xlsx';

    protected string $defaultWidth = '200px';

    protected array $columns = [
        ['label' => 'Title', 'sort' => 'invoice_id'],
    ];

    public function getColumns(): array
    {
        $columns = Columns::make($this->columns, ['defaultWidth' => $this->defaultWidth]);
        return $columns->toArray();
    }

    protected function transform($item): array
    {
        $res = $item->toArray();
        $res['actions'] = [
            [
                'label' => 'Редакция',
                'href' => route('service.edit', $item->id),
                'icon' => 'i-edit',
                'class' => 'btn btn-warning btn-xs',
            ],
        ];
        return $res;
    }
}
