<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicSetting extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'whatsapp',
        'logo_path',
    ];

    /**
     * Returns the single clinic settings row, creating it with defaults if absent.
     */
    public static function current(): self
    {
        return self::firstOrCreate([], ['name' => 'VitalTrack']);
    }
}
