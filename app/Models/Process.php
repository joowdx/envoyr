<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Process extends Model
{
    use HasUlids;
    use SoftDeletes;
    protected $fillable = [
        'document_id',
        'transmittal_id',
        'user_id',
        'classification_id',
        'office_id',
        'processed_at',
        'status',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function transmittal(): BelongsTo
    {
        return $this->belongsTo(Transmittal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
