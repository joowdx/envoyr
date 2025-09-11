<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Content extends Model
{
    use HasUlids;

    protected $fillable = [
        'sort',
        'title',
        'file',
        'path',
        'hash',
        'context',
        'electronic',
        'attachment_id',
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
        $this->file?->each(fn ($file) => Storage::exists($file) && Storage::delete($file));
    }

    public function electronic(): Attribute
    {
        return Attribute::make(fn () => isset($this->hash));
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class);
    }
}
