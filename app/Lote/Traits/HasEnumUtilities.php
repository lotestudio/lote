<?php

declare(strict_types=1);

namespace App\Lote\Traits;

use BackedEnum;
use ReflectionEnum;

trait HasEnumUtilities
{
    public static function forSelect($prepend = false, $prependLabel = 'Изберете', $prependValue = '', $toArray = false, $except = []): string|array
    {
        $collection = collect(static::cases())->map(function ($item): array {
            return [
                'value' => $item->value,
                'label' => method_exists($item, 'label')
                    ?
                    $item->label($item->name)
                    :
                    $item->value,
            ];
        })
            ->when($prepend, function ($collection) use ($prependValue, $prependLabel) {
                return $collection->prepend(['label' => $prependLabel, 'value' => $prependValue]);
            });

        if (! empty($except)) {
            $collection = $collection->filter(function (array $item) use ($except): bool {
                return ! in_array($item['value'], $except);
            });
        }

        return $toArray ? $collection->toArray() : $collection->toJson();
    }

    public static function getCase($case): ?BackedEnum
    {
        if (is_null($case)) {
            return null;
        }

        try {
            return (new ReflectionEnum(self::class))->getCase($case)->getValue();
        } catch (\ReflectionException $reflectionException) {
            return null;
        }
    }

    public static function getCaseValue($case): ?string
    {
        return self::getCase($case)->value;
    }

    // TO Array
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function array(): array
    {
        return array_combine(self::values(), self::names());
    }
}
