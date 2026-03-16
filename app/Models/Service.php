<?php

namespace App\Models;

use App\Lote\Enums\Currency;
use App\Lote\Enums\PriceUnits;
use App\Lote\ValueObjects\Price;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'value',
        'items',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function currency(): Currency
    {

        if (is_null($this->created_at) || $this->created_at > Price::getEuroStartDate()) {
            return Currency::EUR;
        }

        return Currency::BGN;
    }

    public function setValueAttribute($value): void
    {

        if (is_array($value)) {
            $value = $value['number'];
        }
        $this->attributes['value'] = $value * 100;

    }

    public function getValueAttribute(): Price
    {
        return new Price($this->attributes['value'], PriceUnits::MINOR, $this->currency());
    }

    public function getTotalAttribute(): Price
    {
        return new Price($this->value->value() * $this->items, PriceUnits::MINOR, $this->currency());
    }
}
