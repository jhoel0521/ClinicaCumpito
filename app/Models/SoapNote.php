<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoapNote extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'soap_notes';

    protected $fillable = [
        'consultation_id',
        'subjective',
        'objective',
        'assessment',
        'plan',
    ];

    public function consultation(): BelongsTo
    {
        return $this->belongsTo(Consultation::class);
    }
}
