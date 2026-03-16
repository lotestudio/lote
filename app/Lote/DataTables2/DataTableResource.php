<?php

declare(strict_types=1);

namespace App\Lote\DataTables2;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

abstract class DataTableResource
{
    protected array $data = [];

    protected int $perPage = 20;

    protected mixed $options = [];

    protected Builder $builder;

    protected array $columns = [];

    protected string $orderRequestKey = 's';

    protected string $orderDirectionRequestKey = 'o';

    protected string $searchRequestKey = 'search';

    public string $defaultOrderField = 'id';

    public ?string $model = null;

    public array $searchableFields = [];

    protected array $excludedFilters = [];

    public ?string $useDatabaseTablePrefix = null;

    public ?string $exportClass = null;

    public string $exportFileName = 'export.xlsx';

    public const NULL_VALUE = 'NULL'; // used in scopeDtFilterable to determine null values for the field is needed

    protected string $defaultWidth = '200px';

    private bool $skip_getColumns_method = false;

    public function __construct(?Builder $builder = null, array $options = [])
    {

        if (is_null($builder) && is_null($this->model)) {
            throw new \Exception('Model or Builder must be provided');
        }

        if (isset($options['columns'])) {
            $this->columns = $options['columns'];
            unset($options['columns']);
            $this->skip_getColumns_method = true;
        }

        // additional options if needed from the transform function
        $this->options = $options;
        $this->builder = $builder ?? $this->model::query();

    }

    public function get(): array|BinaryFileResponse
    {
        if (request()->get('action') === 'export') {
            return $this->export();
        }

        return $this->getTableData();
    }

    // Override this method to manipulate the columns or just right the array $columns manually
    public function getColumns(): array
    {
        $columns = Columns::make($this->columns, ['defaultWidth' => $this->defaultWidth]);

        return $columns->toArray();
    }

    public function getTableData(): array
    {

        $this->getPaginator();

        try {
            $paginator = $this->getPaginator();
        } catch (\Exception $exception) {
            abort(500, 'Възникна грешка при зареждане на данните. Моля опитайте отново (изтрийте всички филтри): '.$exception->getMessage());
        }

        $data = $paginator->getCollection()->map(function ($item): array {
            return $this->transform($item);
        });

        $this->data = $paginator->toArray();
        $this->data['data'] = $data;
        $this->data['info'] = $this->info($paginator->getCollection());
        $this->data['columns'] = $this->skip_getColumns_method ? $this->columns : $this->getColumns();

        return $this->data;
    }

    /**
     * Overwrite this
     */
    protected function transform($item): array
    {
        return $item->toArray();
    }

    // TODO:: NOT TESTED
    protected function info(Collection $collection): ?string
    {
        return null;
    }

    private function getPaginator(): LengthAwarePaginator
    {

        $perPage = request()->get('limit') ?? $this->options['perPage'] ?? $this->perPage;

        $this->applyBuilderConditions();

        return $this->builder->paginate($perPage);
    }

    /**
     * @throws \Exception
     */
    public function export(): BinaryFileResponse
    {

        if (is_null($this->exportClass)) {
            throw new \Exception('Export class must be provided');
        }

        $this->applyBuilderConditions();

        $data = $this->builder->get();

        return Excel::download(new $this->exportClass($data), $this->exportFileName);
    }

    protected function applyBuilderConditions(): void
    {
        $this->preBuild();
        $this->search();
        $this->filter();
        $this->order();

    }

    /**
     * Overwrite this: prebuild global where, with, etc.
     */
    public function preBuild(): void {}

    protected function order(): void
    {
        $defaultOrder = $this->getDefaultOrder();
        $orderBy = request()->get($this->orderRequestKey);
        $orderDirection = request()->get($this->orderDirectionRequestKey);

        if ($orderBy && $orderDirection) {
            if (isset($this->defaultOrderField) && $this->defaultOrderField === $orderBy) {
                $this->builder->orderBy($orderBy, $orderDirection);

                return;
            }

            $sortQueryMethod = 'orderBy'.ucfirst($orderBy);
            if (method_exists($this, $sortQueryMethod)) {
                $this->$sortQueryMethod($orderDirection);
                $this->builder->orderBy($this->defaultOrderField, $defaultOrder);

                return;
            }

            $this->builder->orderBy($orderBy, $orderDirection)->orderBy($this->defaultOrderField, $defaultOrder);
        } elseif (isset($this->defaultOrderField)) {
            $this->builder->orderBy($this->defaultOrderField, $defaultOrder);
        }
    }

    protected function search(): void
    {
        $search = request()->get($this->searchRequestKey);

        if ($search && $this->searchableFields !== []) {
            if (method_exists($this, 'searchQueryName')) {
                $this->searchQueryName();
            } else {
                $this->builder->where(function ($query) use ($search): void {
                    collect(str_getcsv($search, ' ', '"', '\\'))->filter()->each(function (string $term) use ($query): void {
                        foreach ($this->searchableFields as $searchableField) {
                            $query->orWhere($this->setDatabasePrefixToField($searchableField), 'LIKE', '%'.$term.'%');
                        }
                    });
                });
            }
        }
    }

    protected function filter(): void
    {
        $filtersInput = request()->get('filters') ?? [];

        if (! empty($filtersInput)) {

            foreach ($filtersInput as $field => $value) {

                if (in_array($field, $this->excludedFilters)) {
                    continue;
                }

                $filterQueryMethod = 'filterQuery'.ucfirst($field);

                if (method_exists($this, $filterQueryMethod)) {
                    $this->$filterQueryMethod($value);
                } elseif ($value === self::NULL_VALUE) {
                    $this->builder->whereNull($this->setDatabasePrefixToField($field));
                } else {
                    $this->builder->where($this->setDatabasePrefixToField($field), $value);
                }
            }

        }

    }

    private function getDefaultOrder(): string
    {
        return $this->options['defaultOrder'] ?? 'desc';
    }

    protected function setDatabasePrefixToField(string $field): string
    {
        return $this->useDatabaseTablePrefix ? $this->useDatabaseTablePrefix.'.'.$field : $field;
    }

    /**
     * @throws \Exception
     */
    public static function make(?Builder $builder = null, $options = []): static
    {
        return new static($builder, $options);
    }
}
