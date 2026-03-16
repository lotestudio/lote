<?php

declare(strict_types=1);

namespace App\Lote\InertiaCrudCommand;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeInertiaCrud
{
    public function __construct(
        protected Filesystem $files = new Filesystem,
    ) {}

    protected function replaceTokens(string $stub, array $tokens): string
    {
        foreach ($tokens as $key => $value) {
            $stub = str_replace('%%'.strtoupper($key).'%%', $value, $stub);
        }

        return $stub;
    }

    public function run(Command $command): void
    {
        $modelClass = $this->promptForModel($command);
        $modelShort = class_basename($modelClass);

        // Step 1: Create DataTableResource (Transformer)
        $dtClassName = $this->askDefault($command, 'DataTable class name', $modelShort.'DataTable');
        $dtNamespace = 'App\\Transformers\\DataTable';
        $dtPath = base_path('app/Transformers/DataTable/'.$dtClassName.'.php');

        // Ask for properties with defaults
        $defaultWidth = $this->askDefault($command, 'defaultWidth', '200px');
        $defaultOrderField = $this->askDefault($command, 'defaultOrderField', 'id');
        $useDatabaseTablePrefix = $this->askDefault($command, 'useDatabaseTablePrefix (empty for null)', '');
        $searchableFieldsInput = $this->askDefault($command, 'searchableFields (comma separated)', '');
        $exportClass = $this->askDefault($command, 'exportClass (FQN, empty for none)', '');
        $exportFileName = $this->askDefault($command, 'exportFileName', 'export.xlsx');

        // Title column inference
        [$table, $titleColumnGuess] = $this->inferTitleColumn($modelClass);
        $titleColumn = $this->askDefault($command, sprintf('Title column (from %s)', $table), $titleColumnGuess);

        $searchableFields = $this->csvToArray($searchableFieldsInput);

        Str::of($modelShort)->snake('-')->lower()->toString();

        $this->ensureDirectory(dirname($dtPath));
        $dtStub = $this->getStub('datatable.stub');
        $dtContents = $this->replaceTokens($dtStub, [
            'NAMESPACE' => $dtNamespace,
            'CLASS_NAME' => $dtClassName,
            'MODEL_FQN' => ltrim($modelClass, '\\'),
            'MODEL_CLASS' => Str::afterLast($modelClass, '\\'),
            'DEFAULT_ORDER_FIELD' => $defaultOrderField,
            'SEARCHABLE_FIELDS_CODE' => $this->renderSearchableFields($searchableFields),
            'USE_DB_PREFIX_CODE' => $this->renderUseDbPrefix($useDatabaseTablePrefix),
            'EXPORT_CLASS_CODE' => $this->renderExportClass($exportClass),
            'EXPORT_FILE_NAME' => $exportFileName,
            'DEFAULT_WIDTH' => $defaultWidth,
            'TITLE_COLUMN_NAME' => $titleColumn,
            'ROUTE_NAME_PREFIX' => Str::of($modelShort)->snake()->lower()->toString(),
        ]);
        $this->writeFile($dtPath, $dtContents, $command, 'DataTable');

        // Step 2: Ask for CRUD generation
        if (! $command->confirm('Create CRUD for the same model?', true)) {
            $command->info('Finished without CRUD.');

            return;
        }

        // Ask for controller folder
        $folder = $this->askDefault($command, 'Controller subfolder relative to app/Http/Controllers (empty for none)', 'Admin');
        $controllerNamespace = trim('App\\Http\\Controllers'.($folder !== '' ? '\\'.str_replace('/', '\\', $folder) : ''), '\\');
        $controllerDir = base_path('app/Http/Controllers'.($folder !== '' ? '/'.$folder : ''));
        $this->ensureDirectory($controllerDir);

        $controllerClass = $modelShort.'Controller';
        $controllerPath = $controllerDir.'/'.$controllerClass.'.php';

        $formRequestClass = $modelShort.'FormRequest';
        $formRequestNamespace = 'App\\Http\\Requests';
        $formRequestPath = base_path('app/Http/Requests/'.$formRequestClass.'.php');
        $this->ensureDirectory(dirname($formRequestPath));

        $baseRoutePath = '/admin/'.Str::of($modelShort)->snake('-')->lower()->toString();
        $inertiaBase = 'admin/'.Str::studly($modelShort);

        // Create FormRequest
        $frStub = $this->getStub('formrequest.stub');
        $frContents = $this->replaceTokens($frStub, [
            'NAMESPACE' => $formRequestNamespace,
            'CLASS_NAME' => $formRequestClass,
            'TITLE_FIELD' => $titleColumn,
        ]);
        $this->writeFile($formRequestPath, $frContents, $command, 'FormRequest');

        // Create Controller
        $ctrlStub = $this->getStub('controller.stub');
        $ctrlContents = $this->replaceTokens($ctrlStub, [
            'NAMESPACE' => $controllerNamespace,
            'CLASS_NAME' => $controllerClass,
            'MODEL_FQN' => ltrim($modelClass, '\\'),
            'MODEL_SHORT' => $modelShort,
            'MODEL_VAR' => Str::camel($modelShort),
            'FORM_REQUEST_FQN' => $formRequestNamespace.'\\'.$formRequestClass,
            'FORM_REQUEST_CLASS' => $formRequestClass,
            'INERTIA_BASE' => $inertiaBase,
            'DATATABLE_FQN' => $dtNamespace.'\\'.$dtClassName,
            'DATATABLE_CLASS' => $dtClassName,
            'ROUTE_NAME_PREFIX' => Str::of($modelShort)->snake()->lower()->toString(),
            'BASE_ROUTE' => $baseRoutePath,
        ]);
        $this->writeFile($controllerPath, $ctrlContents, $command, 'Controller');

        // Create Vue files
        $vueDir = base_path('resources/js/pages/admin/'.Str::studly($modelShort));
        $this->ensureDirectory($vueDir);

        $vueIndexStub = $this->getStub('vue_index.stub');
        $vueIndex = $this->replaceTokens($vueIndexStub, [
            'MODEL_STUDLY' => Str::studly($modelShort),
            'MODEL_VAR' => Str::camel($modelShort),
            'BASE_ROUTE' => $baseRoutePath,
            'TITLE_FIELD' => $titleColumn,
            'CONTROLLER_FOLDER' => $folder !== '' ? $folder.'/' : '',
        ]);
        $this->writeFile($vueDir.'/index.vue', $vueIndex, $command, 'Vue index.vue');

        $vueFormStub = $this->getStub('vue_form.stub');
        $vueForm = $this->replaceTokens($vueFormStub, [
            'MODEL_STUDLY' => Str::studly($modelShort),
            'BASE_ROUTE' => $baseRoutePath,
            'TITLE_FIELD' => $titleColumn,
            'TITLE_FIELD_LABEL' => ucfirst($titleColumn),
            'CONTROLLER_FOLDER' => $folder !== '' ? $folder.'/' : '',
        ]);
        $this->writeFile($vueDir.'/form.vue', $vueForm, $command, 'Vue form.vue');

        Artisan::call('wayfinder:generate');

        $command->info('CRUD generation complete.');
    }

    protected function promptForModel(Command $command): string
    {
        while (true) {
            $input = $this->askDefault($command, 'Model class (FQN or just name under App\\Models)', \App\Models\Post::class);
            $class = $this->normalizeModelClass($input);
            if (! class_exists($class)) {
                $command->error('Model class not found: '.$class);

                continue;
            }

            if (! is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class)) {
                $command->error('Provided class is not an Eloquent model.');

                continue;
            }

            return $class;
        }
    }

    protected function normalizeModelClass(string $input): string
    {
        $input = trim($input, '\\');
        if (str_starts_with($input, 'App\\')) {
            return $input;
        }

        if (! str_contains($input, '\\')) {
            return 'App\\Models\\'.Str::studly($input);
        }

        return $input;
    }

    protected function askDefault(Command $command, string $question, string $default): string
    {
        $answer = $command->ask($question.' ['.$default.']');

        return $answer === null || $answer === '' ? $default : $answer;
    }

    protected function csvToArray(string $csv): array
    {
        return array_values(array_filter(array_map(function ($v): string {
            return trim($v);
        }, explode(',', $csv)), fn ($v): bool => $v !== ''));
    }

    protected function renderSearchableFields(array $fields): string
    {
        if ($fields === []) {
            return '    // public array $searchableFields = [\'title\'];';
        }

        $export = var_export($fields, true);

        // format to short array syntax with single quotes, preserving PHP formatting
        return '    public array $searchableFields = '.str_replace(["\n", '  '], '', $export).';';
    }

    protected function renderUseDbPrefix(?string $prefix): string
    {
        if ($prefix === null || $prefix === '') {
            return '    // public ?string $useDatabaseTablePrefix = \"\";';
        }

        return '    public ?string $useDatabaseTablePrefix = \''.$prefix."';";
    }

    protected function renderExportClass(?string $exportClass): string
    {
        if ($exportClass === null || $exportClass === '') {
            return '    // public ?string $exportClass = ExportClass::class;';
        }

        return '    public ?string $exportClass = \\'.ltrim($exportClass, '\\').'::class;';
    }

    protected function escape(string $value): string
    {
        return str_replace('"', '\\"', $value);
    }

    protected function inferTitleColumn(string $modelClass): array
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = new $modelClass;
        $table = $model->getTable();
        $columns = [];
        try {
            $columns = Schema::getColumnListing($table);
        } catch (\Throwable $throwable) {
            // ignore, fallback guesses
        }

        $preferred = ['title', 'name', 'label', 'subject', 'slug'];
        foreach ($preferred as $p) {
            if (in_array($p, $columns, true)) {
                return [$table, $p];
            }
        }

        // fallback to first non-id string-like column
        foreach ($columns as $column) {
            if (! in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                return [$table, $column];
            }
        }

        return [$table, 'title'];
    }

    protected function ensureDirectory(string $path): void
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    protected function getStub(string $name): string
    {
        $path = base_path('app/Lote/InertiaCrudCommand/stubs/'.$name);
        if (! File::exists($path)) {
            throw new \RuntimeException('Stub not found: '.$path);
        }

        return File::get($path);
    }

    protected function writeFile(string $path, string $contents, Command $command, string $label): void
    {
        if (File::exists($path) && ! $command->confirm($label.' file already exists at '.$this->relative($path).'. Overwrite?', false)) {
            $command->warn('Skipped existing '.$label.': '.$this->relative($path));

            return;
        }

        File::put($path, $contents);
        $command->info($label.' created: '.$this->relative($path));
    }

    protected function relative(string $path): string
    {
        return str_replace(base_path().'/', '', $path);
    }
}
