<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'company',
        'address_1',
        'address_2',
        'number',
        'vat',
        'mol',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public static function forSelect()
    {
        return Client::orderBy('company')->get()->map(function ($client) {
            return [
                'label' => $client->company,
                'value' => $client->id,
            ];
        })->prepend(['label' => 'Избери клиент', 'value' => '']);
    }
}
