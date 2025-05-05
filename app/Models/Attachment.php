<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasUlids;

    protected $fillable = [
        'remarks',
        'files',
        'paths',
        'attachable_id',
        'attachable_type',
    ];

    protected $casts = [
        'files' => 'collection',
        'paths' => 'collection',
    ];

    public static function booted(): void
    {
        static::deleting(fn (self $attachment) => $attachment->purge());
    }

    public function purge(): void
    {
        $this->files->each(fn ($file) => Storage::delete($file));
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
