<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attachment extends Model
{
    use HasUlids;

    protected $fillable = [
        'document_id',
        'transmittal_id',
    ];

    public static function booted(): void
    {
        static::deleting(fn (self $attachment) => $attachment->contents->each->purge());
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function transmittal(): BelongsTo
    {
        return $this->belongsTo(Transmittal::class);
    }
}
