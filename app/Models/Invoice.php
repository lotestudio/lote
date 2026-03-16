<?php

namespace App\Models;

use App\Lote\Enums\Currency;
use App\Lote\Enums\PriceUnits;
use App\Lote\ValueObjects\Price;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'client_details',
        'num',
        'vat',
        'date',
        'recipient',
    ];

    protected $casts = [
        'date' => 'datetime',
        'client_details' => 'array',
    ];


    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public static function getNextNumber(): int
    {
        // start config
        $start = 174;

        return (Invoice::max('num') + 1) > $start ? Invoice::max('num') + 1 : $start;
    }

    public function currency(): Currency
    {

        if (is_null($this->created_at) || $this->created_at > Price::getEuroStartDate()) {
            return Currency::EUR;
        }

        return Currency::BGN;
    }

    /*
     *
     * Accessors
     *
     */

    //    TODO:: use global scope with sum(value*items)!!!
    public function getTotalAttribute(): Price
    {
        $this->loadMissing('services');

        $res = 0;
        foreach ($this->services as $service) {
            $res += $service->value->value * $service->items;
        }

        return new Price($res, PriceUnits::MINOR, $this->currency());
    }

    public function getVatAttribute(): Price
    {
        return $this->total->getVat();
    }

    public function getTotalWithVatAttribute(): Price
    {
        return $this->total->addVat();
    }

    public function getDateAttribute($value): string
    {
        return Carbon::parse($value)->format('Y-m-d');
    }


    public static function amounts(): array
    {
        $invoices = self::with('services')->get()->groupBy(function ($invoice) {
            return Carbon::parse($invoice->date)->format('Y'); // grouping by years
            // return Carbon::parse($date->created_at)->format('m'); // grouping by months
        });

        $amounts = [];

        foreach ($invoices as $year => $invoices_by_year) {
            $amounts[$year]['total'] = 0; // $invoices_by_year->sum('total');
            $amounts[$year]['vat'] = 0; // $invoices_by_year->sum('vat');
            $amounts[$year]['total_with_vat'] = 0; // $invoices_by_year->sum('total_with_vat');
        }
        krsort($amounts);

        return $amounts;
    }
}
