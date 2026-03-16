<?php

declare(strict_types=1);

namespace App\Lote\DataTables2\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

class DataTableResourceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:datatable-resource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new DataTableResource class';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 1. Питане за пътя
        $relativePathInput = $this->ask('Въведете пътя за DataTableResource класа (относително към app директорията)',
            'Transformers/DataTable');

        // Нормализиране на пътя (PascalCase за всяка част)
        $pathParts = array_map(fn ($part) => Str::studly(trim($part)), explode('/', $relativePathInput));
        $relativePath = implode('/', array_filter($pathParts)); // array_filter премахва празни части, ако има //

        if ($relativePath === '' || $relativePath === '0') {
            $this->error('Пътят не може да бъде празен.');

            return CommandAlias::FAILURE;
        }

        // 2. Питане за името на модела
        $modelNameInput = $this->ask('Въведете името на модела (например User, Product)');
        if (empty($modelNameInput)) {
            $this->error('Името на модела е задължително.');

            return CommandAlias::FAILURE;
        }

        $modelName = Str::studly(trim($modelNameInput)); // Гарантира CamelCase (PascalCase)

        // 3. Питане за полето за търсене
        $searchableFieldInput = $this->ask('Въведете основното поле за търсене в модела');
        if (empty($searchableFieldInput)) {
            $this->error('Полето за търсене е задължително.');

            return CommandAlias::FAILURE;
        }

        $searchableField = trim($searchableFieldInput);

        // Подготовка на имена и пътища
        $className = Str::plural($modelName).'DataTable';
        $fullPath = app_path($relativePath);
        $filePath = $fullPath.'/'.$className.'.php';

        // Подготовка на namespace
        $namespace = rtrim('App\\'.str_replace('/', '\\', $relativePath), '\\');

        // FQN на модела и променлива
        $modelFQN = 'App\\Models\\'.$modelName; // Приемаме, че моделите са в App\Models
        $modelVariable = Str::camel($modelName); // например: user, product

        // Проверка дали файлът вече съществува
        if (File::exists($filePath)) {
            $this->error(sprintf('Файлът %s вече съществува!', $filePath));

            return CommandAlias::FAILURE;
        }

        // Път до stub файла
        $stubPath = __DIR__.'/../stubs/DataTableResource.stub';

        if (! File::exists($stubPath)) {
            $this->error(sprintf('Stub файлът не е намерен на адрес: %s. Моля, създайте го.', $stubPath));

            return CommandAlias::FAILURE;
        }

        // Вземане на съдържанието на stub файла
        $stubContent = File::get($stubPath);

        // Замяна на плейсхолдърите
        $replacements = [
            '{{namespace}}' => $namespace,
            '{{className}}' => $className,
            '{{modelName}}' => $modelName,
            '{{modelFQN}}' => $modelFQN,
            '{{modelVariable}}' => $modelVariable,
            '{{searchableField}}' => $searchableField,
            '{{modelNamePlural}}' => Str::plural($modelName), // За колекции от ресурси
        ];

        $generatedContent = str_replace(array_keys($replacements), array_values($replacements), $stubContent);

        // Създаване на директорията, ако не съществува
        File::ensureDirectoryExists($fullPath);

        // Записване на файла
        File::put($filePath, $generatedContent);

        $this->info(sprintf('DataTableResource класът %s е създаден успешно в %s', $className, $filePath));
        $this->comment(sprintf("Не забравяйте да проверите импортите 'use App\\Http\\Resources\\DataTable\\DataTableResource;' и 'use %s;' в генерирания файл, ако са различни от вашите.", $modelFQN));
        $this->comment('Също така, актуализирайте метода toArray() със съответните полета за вашия модел.');

        return CommandAlias::SUCCESS;
    }
}
