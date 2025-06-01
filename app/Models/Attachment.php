<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasUlids;

    protected $fillable = [
        'sort',
        'title',
        'file',
        'path',
        'remarks',
        'context',
        'electronic',
        'enclosure_id',
    ];

    protected $casts = [
        'context' => 'json',
        'file' => 'collection',
        'path' => 'collection',
    ];

    public static function booted(): void
    {
        static::deleting(fn (self $attachment) => $attachment->purge());
    }

    public function purge(): void
    {
        $this->file?->each(fn ($file) => Storage::delete($file));
    }

    public function enclosure(): BelongsTo
    {
        return $this->belongsTo(Enclosure::class);
    }
}
