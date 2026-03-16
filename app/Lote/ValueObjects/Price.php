<?php

namespace App\Lote\ValueObjects;

use App\Lote\Enums\Currency;
use App\Lote\Enums\PriceUnits;
use App\NeoplaneNext\Models\ExchangeEuroRates;
use Carbon\Carbon;

/**
 * Class Price
 * Immutable Value Object representing monetary value.
 */
class Price implements \JsonSerializable
{
    public readonly int $value;
    public readonly int $pips_value;

    public int $precision = 2;

    protected array $locale;
    const EURO_RATE = 1.95583;
    const VAT_PERCENTAGE = 20;

    public function __construct(
        float|int $value,
        private string|PriceUnits $units = PriceUnits::MAJOR,
        private readonly Currency $currency = Currency::EUR,
    ) {
        // Use null coalescing to ensure array structure even if localeconv fails or returns incomplete data
        $this->locale = localeconv() ?: ['mon_thousands_sep' => ' '];

        if (is_string($this->units)) {
            $this->units = match ($this->units) {
                'ст', 'стотинки', 'cents', 'pence' => PriceUnits::MINOR,
                'pips' => PriceUnits::PIPS,
                default => PriceUnits::MAJOR
            };
        }

        // Calculate immutable values

        // 1. Нормализираме входа до най-малката единица (Pips)
        // Ползваме factor(true), който връща множител спрямо Pips (Major=10000, Minor=100, Pips=1)
        $this->pips_value = (int) round($value * $this->units->factor());

        // 2. Изчисляваме стандартната (minor) стойност ВИНАГИ на база на pips_value.
        // Отношението стотинка към пипс е константно (1 st = 100 pips).
        $this->value = (int) round($this->pips_value / 100);
    }

    /**
     * Factory method to create a Price object directly from PIPS.
     * Useful for internal calculations to avoid double rounding errors.
     */
    protected static function fromPips(int $pips, Currency $currency): self
    {
        // We pass the pips value directly, specifying PIPS unit.
        // Since constructor does round($pips * 1), precision is preserved.
        return new self($pips, PriceUnits::PIPS, $currency);
    }

    public function currency()
    {
        return $this->currency;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function minor(): int
    {
        return $this->value;
    }

    public function pips_value(): int
    {
        return $this->pips_value;
    }

    public function number(?int $precision = null): float
    {
        $precision = $precision ?? $this->precision;
        return round($this->pips_value / PriceUnits::MAJOR->factor(true), $precision);
    }

    public function formatted(?int $precision = null): string
    {
        $precision = $precision ?? $this->precision;
        $text = number_format($this->number($precision), $precision, '.', ' ');

        $thousandsSep = $this->locale['mon_thousands_sep'] ?? ' ';
        $value = str_replace(',', '.', str_replace($thousandsSep, ' ', $text));

        if ($this->currency->prefix()) {
            return $this->currency->prefix().$value;
        }
        return $value.$this->currency->suffix();
    }

    /**
     * @deprecated Presentation logic should be moved to a View Component or Presenter.
     */
    public function formattedWithColor(mixed $comparison_value, ?int $precision = null): string
    {
        $precision = $precision ?? $this->precision;

        if (!($comparison_value instanceof Price)) {
            $comparison_value = new self($comparison_value);
        }

        if ($comparison_value->pips_value() >= $this->pips_value()) {
            return '<span class="text-danger-500">'.$this->formatted($precision).'</span>';
        } else {
            return '<span class="text-success-500">'.$this->formatted($precision).'</span>';
        }
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    public function jsonSerialize(): array
    {
        return [
            'pips_value' => $this->pips_value,
            'value' => $this->value,
            'number' => $this->number(),
            'currency' => $this->currency->value,
            'formatted' => $this->formatted(),
        ];
    }

    public function convertToBgn(): self
    {
        return $this->convertToCurrency(Currency::BGN);
    }

    public function convertToEuro(): self
    {
        return $this->convertToCurrency(Currency::EUR);
    }

    public function convertToDefaultCurrency()
    {
        return $this->convertToCurrency(Currency::getDefault());
    }

    public function convertToCurrency(Currency $currency): self
    {
        if ($this->currency === $currency) {
            return self::fromPips($this->pips_value, $currency);
        }

        // We calculate new pips based on exchange rate
        $newPips = (int) round($this->pips_value * ExchangeEuroRates::getRate($this->currency->value, $currency->value));

        return self::fromPips($newPips, $currency);
    }


    /**
     * Aggregates prices by currency, sums them up per currency,
     * converts group sums to current instance currency and returns total pips.
     */
    private function calculateAggregatedPips(array $prices): int
    {
        $groups = [];

        foreach ($prices as $price) {
            $iso = $price->currency->value;
            if (!isset($groups[$iso])) {
                $groups[$iso] = ['pips' => 0, 'currency' => $price->currency];
            }
            $groups[$iso]['pips'] += $price->pips_value;
        }

        $totalPips = 0;

        foreach ($groups as $group) {
            if ($group['currency'] === $this->currency) {
                $totalPips += $group['pips'];
            } else {
                // Convert the sum of the group, not individual items, to minimize rounding errors
                $groupPrice = self::fromPips($group['pips'], $group['currency']);
                $totalPips += $groupPrice->convertToCurrency($this->currency)->pips_value;
            }
        }

        return $totalPips;
    }

    public function sub(Price ...$prices): self
    {
        $pipsToSubtract = $this->calculateAggregatedPips($prices);
        $currentPips = $this->pips_value - $pipsToSubtract;

        return self::fromPips($currentPips, $this->currency);
    }

    public function add(Price ...$prices): self
    {
        $pipsToAdd = $this->calculateAggregatedPips($prices);
        $currentPips = $this->pips_value + $pipsToAdd;

        return self::fromPips($currentPips, $this->currency);
    }

    /**
     * @param Price[] $prices
     */
    public static function subtract(array $prices, ?Currency $currency=null): self
    {
        if (empty($prices)) {
            // Default to 0 BGN if no arguments, or throw exception depending on requirements
            return new self(0, PriceUnits::MAJOR, $currency ?? Currency::BGN);
        }

        $base = array_shift($prices);

        // If a target currency is enforced and base is different, convert base first
        if ($currency && $base->currency !== $currency) {
            $base = $base->convertToCurrency($currency);
        }

        return $base->sub(...$prices);
    }

    /**
     * @param Price[] $prices
     */
    public static function sum(array $prices, ?Currency $currency=null): self
    {
        if (empty($prices)) {
            return new self(0, PriceUnits::MAJOR, $currency ?? Currency::BGN);
        }

        $base = array_shift($prices);

        // If a target currency is enforced and base is different, convert base first
        if ($currency && $base->currency !== $currency) {
            $base = $base->convertToCurrency($currency);
        }

        return $base->add(...$prices);
    }


    public static function getVatPercentage(): float
    {
        return self::VAT_PERCENTAGE;
    }

    public function addVat(): self
    {
        $pips_value = (int) round($this->pips_value + $this->pips_value * (self::getVatPercentage() / 100));

        return self::fromPips($pips_value, $this->currency);
    }

    public function removeVat(): self
    {
        $pips_value = (int) round($this->pips_value - $this->pips_value * (self::getVatPercentage() / 100));

        return self::fromPips($pips_value, $this->currency);
    }

    public function getVat(): self
    {
        $pips_value = (int) round($this->pips_value * (self::getVatPercentage() / 100));

        return self::fromPips($pips_value, $this->currency);
    }

    public static function getEuroStartDate():Carbon
    {
        return Carbon::createFromFormat('Y',2026)->startOfYear();
    }

}
