<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Enclosure extends Model
{
    use HasUlids;

    protected $fillable = [
        'document_id',
        'transmittal_id',
    ];

    public static function booted(): void
    {
        static::deleting(function (self $enclosure) {
            $enclosure->attachments->each->delete();
        });
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function transmittal()
    {
        return $this->belongsTo(Transmittal::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }
}
