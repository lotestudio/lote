<?php

namespace App\Lote\Services\JsonEscapedRepair;

use Illuminate\Database\Eloquent\Model;

class JsonEscapeRepair
{
    public static function handle(Model|string $model, string|array $columns): void
    {

        $columns = is_array($columns) ? $columns : [$columns];

        $model::query()
            ->where(function ($query) use ($columns) {
                foreach ($columns as $column) {
                    $query->orWhereNotNull($column);
                }
            })
            ->chunkById(100, function ($rows) use ($columns): void {
                foreach ($rows as $row) {
                    $updates = [];

                    foreach ($columns as $column) {
                        $raw = $row->getRawOriginal($column);

                        if ($raw === null) {
                            continue;
                        }

                        $decoded = json_decode($raw, true);

                        if (json_last_error() !== JSON_ERROR_NONE) {
                            continue;
                        }

                        $encoded = json_encode($decoded, JSON_UNESCAPED_UNICODE);

                        if ($encoded === false || $encoded === $raw) {
                            continue;
                        }

                        $updates[$column] = $encoded;
                    }

                    if ($updates !== []) {
                        $row->newQuery()
                            ->whereKey($row->getKey())
                            ->update($updates);
                    }
                }
            });
    }
}
