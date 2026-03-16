<?php

namespace App\Lote\Enums;

use App\Lote\Traits\HasEnumUtilities;

enum PriceUnits: string
{
    use HasEnumUtilities;

    case MAJOR = 'major';
    case MINOR = 'minor';

    /*
     *  Във финансовия свят (особено Forex) 4-тият знак след десетичната запетая се нарича Pip (Percentage in Point). Това е класическият термин за 1/100 от 1% (или 0.0001).
     *  Код: $amount->pips
     *  Пример: 1.0000 лв = 10000 pips.
     */
    case PIPS = 'pips';

    public function factor(): bool|int
    {
        return match ($this) {
            self::MAJOR => 10000,
            self::MINOR => 100,
            self::PIPS => 1,
        };
    }
}
