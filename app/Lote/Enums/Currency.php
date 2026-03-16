<?php

namespace App\Lote\Enums;

use App\Lote\Traits\HasEnumUtilities;
use Illuminate\Support\Collection;

enum Currency: string
{

    use HasEnumUtilities;


    case BGN = 'BGN';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case USD = 'USD';
    case RUB = 'RUB';
    case CNY = 'CNY';
    case RON = 'RON';
    case MKD = 'MKD';


    public function label(): bool|string
    {
        return match ($this) {
            self::BGN => 'лева',
            self::EUR => 'евро',
            self::GBP => 'паунд',
            self::USD => 'долара',
            self::RUB => 'рубли',
            self::CNY => 'яни',
            self::RON => 'леи',
            self::MKD => 'денари'
        };
    }

    public function prefix(): bool|string
    {
        return match ($this) {
            self::BGN, self::MKD, self::RON => false,
            self::EUR => '€',
            self::GBP => '£',
            self::USD => '$',
            self::RUB => '₽',
            self::CNY => '¥'
        };
    }
    public function suffix(): bool|string
    {
        return match ($this) {
            self::BGN => " лв.",
            self::EUR => " EUR",
            self::GBP => " GBP",
            self::USD => " USD",
            self::RUB => " RUB",
            self::CNY => " CNY",
            self::RON => " RON",
            self::MKD => " MKD"
        };
    }

    public function sub_unit(): bool|string
    {
        return match ($this) {
            self::BGN => "стотинки",
            self::EUR, self::USD => "cents",
            self::GBP => "pence",
            self::RUB => "kopeks",
            self::CNY => "fen",
            self::RON => "bani",
            self::MKD => "deni"
        };
    }

    public static function getDefault(): Currency
    {

        $res=null;
        if(session()->has('currency')){
            $res = self::tryFrom(session('currency'));
        }

        if($res){
            return $res;
        }

        return self::tryFrom(config('neoplane.currency')) ?? self::EUR;
    }

}
