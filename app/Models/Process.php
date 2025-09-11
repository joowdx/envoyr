<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Process extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'office_id',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
