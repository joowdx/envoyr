<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Content extends Model
{
    use HasUlids;

    protected $fillable = [
        'transmittal_id',
        'copies',
        'pages_per_copy',
        'control_number',
        'particulars',
        'payee',
        'amount',
        'attachment',
    ];

    public function transmittal(): BelongsTo
    {
        return $this->belongsTo(Transmittal::class);
    }
}
