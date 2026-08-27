<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatteryLog extends Model
{
    protected $fillable = [
        'voltage',
        'percentage',
        'status',
        'rssi',
    ];

    protected $casts = [
        'voltage'    => 'float',
        'percentage' => 'integer',
        'rssi'       => 'integer',
    ];
}