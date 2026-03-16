<?php

declare(strict_types=1);

namespace App\Lote\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait HasReturnUrl
{
    /**
     * Редиректва към 'return_url' ако съществува, или към fallback маршрута.
     */
    protected function redirectAfterSave(Request $request, string|RedirectResponse $fallback): RedirectResponse
    {
        // Взимаме директно от input-а, не е нужно да е във $validated
        $returnUrl = $request->input('return_url');

        // Проверка дали URL-ът е валиден и вътрешен (за сигурност)
        if ($returnUrl && (str_starts_with($returnUrl, '/') || str_starts_with($returnUrl, config('app.url')))) {
            return redirect($returnUrl);
        }

        return $fallback instanceof RedirectResponse ? $fallback : redirect($fallback);
    }
}
