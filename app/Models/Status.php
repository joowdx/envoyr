<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Status extends Model
{
    use HasUlids;

    protected $fillable = [
        'title',
        'classification_id',
        'office_id',
    ];

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
