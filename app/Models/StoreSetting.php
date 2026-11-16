<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'store_name',
        'school_name',
        'logo_path',
        'whatsapp_number',
        'address',
        'description',
    ];

    public static function forStorefront(): self
    {
        return self::find(1) ?? new self([
            'store_name' => config('app.name'),
            'school_name' => 'SMK TI Airlangga Samarinda',
        ]);
    }
}
