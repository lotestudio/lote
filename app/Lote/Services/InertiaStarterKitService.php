<?php

declare(strict_types=1);

namespace App\Lote\Services;

class InertiaStarterKitService
{
    public static function isAdminRoute(): bool
    {
        $path = request()->path();

        return str($path)->startsWith(['dashboard', 'admin']);
    }
}
