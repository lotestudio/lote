<?php

namespace App\Lote\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;
use InvalidArgumentException;

class CustomDateFormats
{
    /**
     * Built-in token => static method name.
     * (No closures here, so it's PHP 8.2-friendly.)
     *
     * Token syntax:
     *  - {bg:ddd}  => short weekday bg (пон, вто, сря, чет, пет, съб, нед)
     *  - {bg:dddd} => full weekday bg (Понеделник, ...)
     *  - {bg:W1}   => first letter of weekday bg (П, В, С, Ч, П, С, Н)
     *  - {bg:W2}   => 2-letter weekday bg (Пн, Вт, Ср, Чт, Пт, Сб, Нд)
     */
    private const TOKEN_METHODS = [
        'bg:ddd' => 'weekdayShortBg',
        'bg:dddd' => 'weekdayFullBg',
        'bg:W1' => 'weekdayFirstLetterBg',
        'bg:W2' => 'weekdayTwoLettersBg',
    ];

    /**
     * Runtime token resolvers (can be closures).
     *
     * @var array<string, callable(CarbonInterface): string>
     */
    private static array $runtimeTokenResolvers = [];

    /**
     * Format that supports custom tokens and Carbon/PHP date format chars.
     *
     * Example: "j/{bg:ddd}" => "4/чет"
     */
    public static function format(CarbonInterface|DateTimeInterface|string $date, string $pattern, ?string $timezone = null): string
    {
        $c = self::toCarbon($date, $timezone);

        // Replace custom tokens first, turning them into placeholders so Carbon won't touch them.
        $placeholders = [];
        $i = 0;

        $patternWithPlaceholders = preg_replace_callback(
            '/\{([a-z0-9_]+:[a-z0-9_]+)\}/iu',
            function (array $m) use ($c, &$placeholders, &$i): string {
                $token = $m[1];
                $replacement = self::resolveToken($token, $c);

                if ($replacement === null) {
                    // Unknown token -> keep as-is (good for spotting typos)
                    return $m[0];
                }

                $key = "\x1D".($i++)."\x1E"; // low-risk placeholder markers
                $placeholders[$key] = $replacement;

                return $key;
            },
            $pattern
        );

        // Apply standard Carbon->format to the remaining pattern.
        $formatted = $c->format($patternWithPlaceholders);

        // Put back token values.
        if ($placeholders !== []) {
            $formatted = strtr($formatted, $placeholders);
        }

        return $formatted;
    }

    /**
     * Register/override token resolver at runtime.
     * Example: registerToken('bg:MMMM', function (CarbonInterface $c) { return 'март'; });
     *
     * @param  callable(CarbonInterface): string  $resolver
     */
    public static function registerToken(string $token, callable $resolver): void
    {
        self::$runtimeTokenResolvers[$token] = $resolver;
    }

    private static function resolveToken(string $token, CarbonInterface $c): ?string
    {
        // 1) Runtime overrides win
        if (isset(self::$runtimeTokenResolvers[$token])) {
            return (self::$runtimeTokenResolvers[$token])($c);
        }

        // 2) Built-in tokens
        if (! isset(self::TOKEN_METHODS[$token])) {
            return null;
        }

        $method = self::TOKEN_METHODS[$token];

        // Call static method by name (PHP 8.2 compatible)
        return self::$method($c);
    }

    private static function toCarbon(CarbonInterface|DateTimeInterface|string $date, ?string $timezone = null): CarbonInterface
    {
        if ($date instanceof CarbonInterface) {
            return $date;
        }

        if ($date instanceof DateTimeInterface) {
            return Carbon::instance($date);
        }

        if (! is_string($date) || $date === '') {
            throw new InvalidArgumentException('Invalid date value.');
        }

        return Carbon::parse($date, $timezone);
    }

    private static function weekdayFullBg(CarbonInterface $c): string
    {
        return match ($c->isoWeekday()) {
            1 => 'Понеделник',
            2 => 'Вторник',
            3 => 'Сряда',
            4 => 'Четвъртък',
            5 => 'Петък',
            6 => 'Събота',
            7 => 'Неделя',
        };
    }

    private static function weekdayShortBg(CarbonInterface $c): string
    {
        return match ($c->isoWeekday()) {
            1 => 'пон',
            2 => 'вто',
            3 => 'сря',
            4 => 'чет',
            5 => 'пет',
            6 => 'съб',
            7 => 'нед',
        };
    }

    private static function weekdayFirstLetterBg(CarbonInterface $c): string
    {
        return match ($c->isoWeekday()) {
            1 => 'П',
            2 => 'В',
            3 => 'С',
            4 => 'Ч',
            5 => 'П',
            6 => 'С',
            7 => 'Н',
        };
    }

    private static function weekdayTwoLettersBg(CarbonInterface $c): string
    {
        return match ($c->isoWeekday()) {
            1 => 'Пн',
            2 => 'Вт',
            3 => 'Ср',
            4 => 'Чт',
            5 => 'Пт',
            6 => 'Сб',
            7 => 'Нд',
        };
    }
}
